@extends('layouts.app')
@section('title', 'Cashier Shifts')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Cashier Shifts</h1>
            <small class="text-muted">Manage daily cash shifts</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> POS</a>
            @if(!$activeShift)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                <i class="fas fa-play me-1"></i> Open Shift
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Active Shift Banner --}}
    @if($activeShift)
    <div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
        <div>
            <i class="fas fa-circle text-success me-2"></i>
            <strong>Active Shift</strong> — Opened at {{ \Carbon\Carbon::parse($activeShift->opened_at)->format('h:i A') }}
            | Opening Balance: Rs. {{ number_format($activeShift->opening_balance, 0) }}
            | Branch: {{ $activeShift->branch->name ?? '—' }}
        </div>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
            <i class="fas fa-stop me-1"></i> Close Shift
        </button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Shift History</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Date</th><th>Cashier</th><th>Branch</th><th>Opened</th><th>Closed</th><th class="text-end">Opening</th><th class="text-end">Cash In</th><th class="text-end">Physical Count</th><th class="text-end">Variance</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($shift->shift_date)->format('d M Y') }}</td>
                        <td>{{ $shift->cashier->name ?? '—' }}</td>
                        <td>{{ $shift->branch->name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($shift->opened_at)->format('h:i A') }}</td>
                        <td>{{ $shift->closed_at ? \Carbon\Carbon::parse($shift->closed_at)->format('h:i A') : '—' }}</td>
                        <td class="text-end">Rs. {{ number_format($shift->opening_balance, 0) }}</td>
                        <td class="text-end text-success">Rs. {{ number_format($shift->total_cash_in, 0) }}</td>
                        <td class="text-end">{{ $shift->physical_count ? 'Rs. '.number_format($shift->physical_count, 0) : '—' }}</td>
                        <td class="text-end {{ ($shift->variance ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $shift->variance !== null ? 'Rs. '.number_format($shift->variance, 0) : '—' }}
                        </td>
                        <td><span class="badge bg-{{ $shift->status == 'open' ? 'success' : 'secondary' }}">{{ ucfirst($shift->status) }}</span></td>
                        <td><a href="{{ route('pos.shifts.show', $shift) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No shifts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $shifts->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- Open Shift Modal --}}
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('pos.shifts.open') }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Open New Shift</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ auth()->user()->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opening Cash Balance (Rs.) <span class="text-danger">*</span></label>
                    <input type="number" name="opening_balance" class="form-control" step="0.01" min="0" required placeholder="e.g. 5000">
                    <small class="text-muted">Count the cash in drawer before starting</small>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Open Shift</button></div>
        </div>
    </form></div>
</div>

{{-- Close Shift Modal --}}
@if($activeShift)
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('pos.shifts.close', $activeShift) }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Close Shift</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    Opening Balance: <strong>Rs. {{ number_format($activeShift->opening_balance, 0) }}</strong>
                </div>
                <div class="mb-3">
                    <label class="form-label">Physical Cash Count (Rs.) <span class="text-danger">*</span></label>
                    <input type="number" name="physical_count" class="form-control" step="0.01" min="0" required placeholder="Count actual cash in drawer">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Any remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Close Shift</button></div>
        </div>
    </form></div>
</div>
@endif
@endsection
