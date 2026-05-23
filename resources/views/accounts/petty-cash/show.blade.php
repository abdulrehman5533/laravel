@extends('layouts.app')
@section('title', 'Petty Cash Fund')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Petty Cash — {{ $pettyCash->branch->name ?? 'Fund #'.$pettyCash->id }}</h1>
            <small class="text-muted">Fund limit: Rs. {{ number_format($pettyCash->limit, 2) }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.petty-cash.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                <i class="fas fa-plus me-1"></i> Add Entry
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Summary --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Current Balance</p>
                    <h3 class="{{ $pettyCash->current_balance < ($pettyCash->limit * 0.2) ? 'text-danger' : 'text-success' }}">
                        Rs. {{ number_format($pettyCash->current_balance, 2) }}
                    </h3>
                    @if($pettyCash->current_balance < ($pettyCash->limit * 0.2))
                        <span class="badge bg-danger">Low Balance — Replenish Needed</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Total Spent (All Time)</p>
                    <h3 class="text-danger">Rs. {{ number_format($pettyCash->entries->where('type','cash_out')->sum('amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Total Replenished</p>
                    <h3 class="text-primary">Rs. {{ number_format($pettyCash->entries->where('type','cash_in')->sum('amount'), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Entries --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Transaction History</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pettyCash->entries->sortByDesc('date') as $entry)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $entry->type == 'cash_in' ? 'success' : 'danger' }}">
                                {{ $entry->type == 'cash_in' ? 'Cash In' : 'Cash Out' }}
                            </span>
                        </td>
                        <td>{{ $entry->category }}</td>
                        <td><small class="text-muted">{{ $entry->description ?? '—' }}</small></td>
                        <td class="text-end fw-semibold {{ $entry->type == 'cash_in' ? 'text-success' : 'text-danger' }}">
                            {{ $entry->type == 'cash_in' ? '+' : '-' }} Rs. {{ number_format($entry->amount, 2) }}
                        </td>
                        <td><span class="badge bg-{{ $entry->status == 'approved' ? 'success' : ($entry->status == 'rejected' ? 'danger' : 'warning text-dark') }}">{{ ucfirst($entry->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Entry Modal --}}
<div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('accounts.petty-cash.entry.store', $pettyCash) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Petty Cash Entry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="cash_out">Cash Out (Expense)</option>
                                <option value="cash_in">Cash In (Replenishment)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Tea/Refreshments">Tea/Refreshments</option>
                                <option value="Transport">Transport</option>
                                <option value="Repairs">Repairs</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Replenishment">Replenishment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (Rs.)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
