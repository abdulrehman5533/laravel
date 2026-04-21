@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="fw-800 mb-0">Cashbook Registry</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active">Cash Management</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <a href="{{ route('accounts.cashbook.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary rounded-10 px-3">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary rounded-10 px-3">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="{{ route('accounts.cashbook.create') }}" class="btn btn-gold rounded-10 px-4 fw-700">
                <i class="fas fa-plus me-1"></i> New Entry
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-10 border-0 shadow-sm">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-10 border-0 shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-16 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <span class="text-muted small fw-600">Total Cash In</span>
                    </div>
                    <h4 class="fw-800 text-dark mb-1">Rs. {{ number_format($totalInflows, 2) }}</h4>
                    <small class="text-muted">{{ $filterLabel ?? 'All time' }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-16 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <span class="text-muted small fw-600">Total Cash Out</span>
                    </div>
                    <h4 class="fw-800 text-dark mb-1">Rs. {{ number_format($totalOutflows, 2) }}</h4>
                    <small class="text-muted">{{ $filterLabel ?? 'All time' }}</small>
                </div>
            </div>
        </div>
        @php $net = $totalInflows - $totalOutflows; @endphp
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-16 h-100 {{ $net >= 0 ? 'bg-dark' : 'bg-danger' }} text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-white bg-opacity-10 text-white me-3">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <span class="text-white-50 small fw-600">Net Cash Position</span>
                    </div>
                    <h4 class="fw-800 mb-1">Rs. {{ number_format($net, 2) }}</h4>
                    <small class="{{ $net >= 0 ? 'text-warning' : 'text-white' }}">
                        <i class="fas {{ $net >= 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-1"></i>
                        {{ $net >= 0 ? 'Healthy' : 'Deficit' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-16 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="text-muted small fw-600">Pending Verification</span>
                    </div>
                    <h4 class="fw-800 text-dark mb-1">{{ $pendingCount }}</h4>
                    <small class="text-muted">Entries awaiting approval</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-16 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-700 text-muted text-uppercase">Branch</label>
                    <select name="branch_id" class="form-select rounded-10 border-light">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-700 text-muted text-uppercase">Type</label>
                    <select name="entry_type" class="form-select rounded-10 border-light">
                        <option value="">All Types</option>
                        <option value="cash_in" {{ request('entry_type') == 'cash_in' ? 'selected' : '' }}>Cash In</option>
                        <option value="cash_out" {{ request('entry_type') == 'cash_out' ? 'selected' : '' }}>Cash Out</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-700 text-muted text-uppercase">Category</label>
                    <select name="category" class="form-select rounded-10 border-light">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-700 text-muted text-uppercase">Status</label>
                    <select name="status" class="form-select rounded-10 border-light">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="reconciled" {{ request('status') == 'reconciled' ? 'selected' : '' }}>Reconciled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-700 text-muted text-uppercase">From</label>
                    <input type="date" name="from_date" class="form-control rounded-10 border-light" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-700 text-muted text-uppercase">To</label>
                    <input type="date" name="to_date" class="form-control rounded-10 border-light" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-10 flex-fill fw-600">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('accounts.cashbook.index') }}" class="btn btn-light rounded-10 border">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card border-0 shadow-sm rounded-16 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-800 mb-0">Cash Flow Registry</h6>
                <small class="text-muted">{{ $cashbooks->total() }} entries found</small>
            </div>
            @can('admin.view')
            <div>
                <form method="POST" action="{{ route('accounts.cashbook.verify-all') }}" class="d-inline" onsubmit="return confirm('Verify all pending entries?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success rounded-10 px-3">
                        <i class="fas fa-check-double me-1"></i> Verify All Pending
                    </button>
                </form>
            </div>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 small fw-700 text-uppercase text-muted border-0">Date / Ref</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0">Type</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0">Category</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0">Description</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0">Branch / User</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0 text-end">Amount</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0 text-end">Balance</th>
                        <th class="py-3 small fw-700 text-uppercase text-muted border-0 text-center">Status</th>
                        <th class="pe-4 py-3 small fw-700 text-uppercase text-muted border-0 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashbooks as $entry)
                    <tr class="{{ $entry->status === 'pending' ? 'table-warning-soft' : '' }}">
                        <td class="ps-4 py-3">
                            <div class="fw-700 text-dark">#CB-{{ str_pad($entry->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="small text-muted">{{ $entry->date->format('d M Y') }}</div>
                        </td>
                        <td>
                            @if($entry->entry_type === 'cash_in')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-arrow-down me-1"></i> Cash In
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-arrow-up me-1"></i> Cash Out
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $entry->category }}</span>
                            @if($entry->subcategory)
                                <div class="small text-muted mt-1">{{ $entry->subcategory }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="text-dark small">{{ Str::limit($entry->description, 40) ?: '—' }}</span>
                            @if($entry->reference_type)
                                <div class="small text-muted"><i class="fas fa-link me-1"></i>{{ $entry->reference_type }} #{{ $entry->reference_id }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-600 small text-dark">{{ $entry->user->name ?? 'System' }}</div>
                            <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $entry->branch->name ?? '—' }}</div>
                        </td>
                        <td class="text-end fw-800 {{ $entry->entry_type === 'cash_in' ? 'text-success' : 'text-danger' }}">
                            {{ $entry->entry_type === 'cash_in' ? '+' : '-' }} Rs. {{ number_format($entry->amount, 2) }}
                        </td>
                        <td class="text-end fw-700 text-dark">
                            Rs. {{ number_format($entry->running_balance ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            @switch($entry->status)
                                @case('verified')
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check me-1"></i>Verified
                                    </span>
                                    @break
                                @case('reconciled')
                                    <span class="badge bg-info rounded-pill px-3">
                                        <i class="fas fa-balance-scale me-1"></i>Reconciled
                                    </span>
                                    @break
                                @default
                                    <span class="badge bg-warning text-dark rounded-pill px-3">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                            @endswitch
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-1">
                                {{-- Verify button (only for pending, admin only) --}}
                                @if($entry->status === 'pending')
                                    @can('admin.view')
                                    <form method="POST" action="{{ route('accounts.cashbook.verify', $entry->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-8 px-2" title="Verify">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Edit (only pending) --}}
                                @if($entry->status === 'pending')
                                <a href="{{ route('accounts.cashbook.edit', $entry->id) }}" class="btn btn-sm btn-light border rounded-8 px-2" title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                @endif

                                {{-- Delete (only pending) --}}
                                @if($entry->status === 'pending')
                                <form method="POST" action="{{ route('accounts.cashbook.destroy', $entry->id) }}" class="d-inline" onsubmit="return confirm('Delete this entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border rounded-8 px-2" title="Delete">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-cash-register fa-3x mb-3 d-block opacity-25"></i>
                            No cash entries found.
                            <a href="{{ route('accounts.cashbook.create') }}" class="d-block mt-2 text-primary fw-600">Record first entry</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($cashbooks->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="5" class="ps-4 py-3 fw-800 text-dark">Page Total</td>
                        <td class="text-end py-3 fw-800 text-dark">
                            <span class="text-success">+Rs. {{ number_format($cashbooks->where('entry_type','cash_in')->sum('amount'), 2) }}</span>
                            <br>
                            <span class="text-danger">-Rs. {{ number_format($cashbooks->where('entry_type','cash_out')->sum('amount'), 2) }}</span>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($cashbooks->hasPages())
        <div class="card-footer bg-white border-0 p-4">
            {{ $cashbooks->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-8  { border-radius: 8px; }
    .rounded-10 { border-radius: 10px; }
    .rounded-16 { border-radius: 16px; }
    .stat-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.1rem; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    .btn-gold { background: linear-gradient(135deg, #d4af37, #c5a02e); color: #1a1a1a; border: none; }
    .btn-gold:hover { opacity: 0.9; color: #1a1a1a; }
    .table-warning-soft { background: rgba(255,193,7,0.04); }
    @media print {
        .btn, form, nav, .card-header .btn { display: none !important; }
    }
</style>
@endsection
