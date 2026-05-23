@extends('layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">General Ledger</h1>
            <small class="text-muted">Double-entry accounting records</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.general-ledger.coa') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Chart of Accounts
            </a>
            <a href="{{ route('accounts.general-ledger.journals') }}" class="btn btn-outline-primary">
                <i class="fas fa-book me-1"></i> Journal Entries
            </a>
            <a href="{{ route('accounts.general-ledger.export-pdf', request()->all()) }}" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('accounts.general-ledger.export-excel', request()->all()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Account</label>
                    <select name="account_id" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->account_code }} — {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Transaction Type</label>
                    <select name="ref_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="sale" {{ request('ref_type') == 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="purchase" {{ request('ref_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                        <option value="girvi" {{ request('ref_type') == 'girvi' ? 'selected' : '' }}>Girvi</option>
                        <option value="expense" {{ request('ref_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="payment" {{ request('ref_type') == 'payment' ? 'selected' : '' }}>Payment</option>
                        <option value="journal_entry" {{ request('ref_type') == 'journal_entry' ? 'selected' : '' }}>Journal Entry</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="glTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-ledger"><i class="fas fa-book-open me-1"></i> Ledger Entries</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-trial"><i class="fas fa-balance-scale me-1"></i> Trial Balance</a></li>
    </ul>

    <div class="tab-content">

        {{-- TAB 1: Ledger Entries --}}
        <div class="tab-pane fade show active" id="tab-ledger">

            {{-- Account Summary Cards --}}
            @if(request('account_id') && $selectedAccount)
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Account</p>
                            <h5 class="mb-0">{{ $selectedAccount->account_name }}</h5>
                            <span class="badge bg-secondary">{{ $selectedAccount->account_code }}</span>
                            <span class="badge bg-info ms-1">{{ $selectedAccount->account_type }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Opening Balance</p>
                            <h4 class="mb-0">Rs. {{ number_format($openingBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm" style="background:#1a1a1a">
                        <div class="card-body">
                            <p class="text-white-50 small mb-1">Closing Balance</p>
                            <h4 class="mb-0 text-warning">Rs. {{ number_format($closingBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Ledger Records</span>
                    <span class="badge bg-secondary">{{ $ledgerEntries->total() }} entries</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                @if(request('account_id'))
                                <th class="text-end">Balance</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                            <tr>
                                <td>{{ $entry->date->format('d M Y') }}</td>
                                <td><code>{{ $entry->journalEntry->reference_number ?? 'N/A' }}</code></td>
                                <td>
                                    <a href="{{ route('accounts.general-ledger.show', $entry->account_id) }}" class="text-decoration-none">
                                        {{ $entry->account->account_name ?? '—' }}
                                    </a>
                                </td>
                                <td>
                                    @if($entry->reference_type)
                                        <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $entry->reference_type)) }}</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ Str::limit($entry->description, 50) }}</small></td>
                                <td class="text-end">
                                    @if($entry->debit > 0)
                                        <span class="text-primary fw-semibold">{{ number_format($entry->debit, 2) }}</span>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                <td class="text-end">
                                    @if($entry->credit > 0)
                                        <span class="text-success fw-semibold">{{ number_format($entry->credit, 2) }}</span>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                @if(request('account_id'))
                                <td class="text-end">
                                    <span class="badge bg-light text-dark border fw-semibold">{{ number_format($entry->running_balance ?? 0, 2) }}</span>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No ledger entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $ledgerEntries->withQueryString()->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>

        {{-- TAB 2: Trial Balance --}}
        <div class="tab-pane fade" id="tab-trial">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Trial Balance — {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
                    <span class="badge bg-secondary">{{ $trialBalance->count() }} accounts</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th class="text-end">Opening Balance</th>
                                <th class="text-end">Period Debit</th>
                                <th class="text-end">Period Credit</th>
                                <th class="text-end">Net Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groups = $trialBalance->groupBy('account_type');
                                $totalDebit = 0; $totalCredit = 0; $totalNet = 0;
                            @endphp
                            @foreach(['Asset','Liability','Equity','Income','Expense'] as $type)
                                @if($groups->has($type))
                                <tr class="table-secondary">
                                    <td colspan="7" class="fw-bold">{{ $type }}</td>
                                </tr>
                                @foreach($groups[$type] as $acc)
                                @php $totalDebit += $acc->period_debit; $totalCredit += $acc->period_credit; $totalNet += $acc->net_balance; @endphp
                                <tr>
                                    <td><code>{{ $acc->account_code }}</code></td>
                                    <td>
                                        <a href="{{ route('accounts.general-ledger.show', $acc->id) }}" class="text-decoration-none">
                                            {{ $acc->account_name }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $acc->account_type }}</span></td>
                                    <td class="text-end">{{ number_format($acc->opening_balance, 2) }}</td>
                                    <td class="text-end text-primary">{{ $acc->period_debit > 0 ? number_format($acc->period_debit, 2) : '—' }}</td>
                                    <td class="text-end text-success">{{ $acc->period_credit > 0 ? number_format($acc->period_credit, 2) : '—' }}</td>
                                    <td class="text-end fw-semibold {{ $acc->net_balance >= 0 ? 'text-dark' : 'text-danger' }}">
                                        {{ number_format($acc->net_balance, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="4" class="fw-bold">TOTALS</td>
                                <td class="text-end fw-bold text-warning">{{ number_format($totalDebit, 2) }}</td>
                                <td class="text-end fw-bold text-warning">{{ number_format($totalCredit, 2) }}</td>
                                <td class="text-end fw-bold text-warning">{{ number_format($totalNet, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- end tab-content --}}
</div>
@endsection
