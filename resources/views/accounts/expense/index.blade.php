@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Expense Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Expense Tracking</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.expense.export', request()->all()) }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-download me-2 text-secondary"></i>Export CSV
                </a>
                <a href="{{ route('accounts.expense.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>New Expense
                </a>
            </div>
        </div>
    </div>

    @php
        $totalSpend = $expenses->sum('amount');
        $pendingCount = $expenses->where('status', 'pending')->count();
        $approvedCount = $expenses->where('status', 'approved')->count();
        $rejectedCount = $expenses->where('status', 'rejected')->count();
    @endphp

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-money-bill-wave text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total (This Page)</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($totalSpend, 2) }}</h2>
                <span class="text-muted small fw-600">{{ $expenses->total() }} recorded entries</span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-clock text-warning opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-warning-soft text-warning me-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <span class="text-muted fw-600 small">Pending Approval</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $pendingCount }}</h2>
                <span class="text-muted small fw-600">Awaiting verification</span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-check-circle text-success opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-success-soft text-success me-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span class="text-muted fw-600 small">Approved</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $approvedCount }}</h2>
                <div class="progress w-100" style="height: 6px; border-radius: 10px; background: #f0f0f0;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $expenses->count() > 0 ? ($approvedCount / $expenses->count()) * 100 : 0 }}%; border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden bg-premium-dark text-white">
                <div class="card-bg-icon">
                    <i class="fas fa-chart-pie text-gold opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-gold text-dark me-3">
                        <i class="fas fa-tags"></i>
                    </div>
                    <span class="text-white-50 fw-600 small">Categories</span>
                </div>
                <h2 class="fw-800 mb-2 text-white">{{ $categories->count() }}</h2>
                <span class="text-white-50 small fw-600">Active expense types</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-light rounded-10 me-3">
                    <i class="fas fa-filter text-primary"></i>
                </div>
                <h5 class="mb-0 fw-800">Smart Filters</h5>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <form method="GET" action="{{ route('accounts.expense.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Branch Location</label>
                    <select name="branch_id" class="form-select rounded-12 border-light shadow-none">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Expense Category</label>
                    <select name="category_id" class="form-select rounded-12 border-light shadow-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Status</label>
                    <select name="status" class="form-select rounded-12 border-light shadow-none">
                        <option value="">Every Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100 shadow-sm rounded-12 overflow-hidden">
                        <button type="submit" class="btn btn-primary py-2 fw-600">
                            Apply Filters
                        </button>
                        <a href="{{ route('accounts.expense.index') }}" class="btn btn-light py-2 fw-600">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Transaction Registry</h5>
                    <p class="text-muted small mb-0">List of all recorded business expenses</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-8 px-3" type="button" data-bs-toggle="dropdown">
                        Sort By <i class="fas fa-chevron-down ms-2 small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-12">
                        <li><a class="dropdown-item py-2" href="#">Newest First</a></li>
                        <li><a class="dropdown-item py-2" href="#">Highest Amount</a></li>
                        <li><a class="dropdown-item py-2" href="#">Status</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Date & Reference</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Category & Branch</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Payee/Vendor</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-end">Amount</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Status</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="ps-4 py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 bg-light rounded-10 me-3 text-center" style="min-width: 50px;">
                                            <span class="d-block fw-800 text-dark lh-1">{{ $expense->date->format('d') }}</span>
                                            <span class="small text-uppercase text-muted fw-600">{{ $expense->date->format('M') }}</span>
                                        </div>
                                        <div>
                                            <span class="d-block fw-700 text-dark">#EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            <span class="text-muted small">{{ $expense->date->format('Y') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-600 mb-1 d-inline-block">
                                        {{ optional($expense->category)->name }}
                                    </span>
                                    @if($expense->subcategory)
                                        <span class="d-block text-primary smaller fw-700 ms-1">{{ $expense->subcategory->name }}</span>
                                    @endif
                                    <span class="d-block text-muted small mt-1"><i class="fas fa-map-marker-alt me-1"></i>{{ optional($expense->branch)->name }}</span>
                                </td>
                                <td>
                                    <div class="fw-700 text-dark">{{ $expense->vendor_name ?? 'Internal Expense' }}</div>
                                    <div class="text-muted small">{{ ucfirst($expense->payment_method) }} Payment</div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-800 text-dark fs-5">Rs.{{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($expense->status === 'approved')
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700">
                                            <i class="fas fa-check-circle me-1"></i> Approved
                                        </span>
                                    @elseif($expense->status === 'pending')
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-700">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-700">
                                            <i class="fas fa-times-circle me-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('accounts.expense.show', $expense->id) }}" class="btn btn-icon btn-light rounded-10" title="View Details">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                        @if($expense->status === 'pending')
                                            <a href="{{ route('accounts.expense.edit', $expense->id) }}" class="btn btn-icon btn-light rounded-10" title="Edit">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                            <form action="{{ route('accounts.expense.destroy', $expense->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-light rounded-10" title="Delete" onclick="return confirm('Are you sure you want to delete this expense?')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-5 text-center">
                                    <div class="py-5">
                                        <div class="empty-state-icon mb-4">
                                            <i class="fas fa-receipt fa-4x text-light"></i>
                                        </div>
                                        <h4 class="fw-800 text-dark">No Expenses Found</h4>
                                        <p class="text-muted">We couldn't find any expense records matching your current filters.</p>
                                        <a href="{{ route('accounts.expense.create') }}" class="btn btn-gold rounded-12 px-4 py-2 mt-2">
                                            Add First Expense
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($expenses->hasPages())
            <div class="card-footer bg-white border-0 p-4">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Premium Design System Expansion */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-10 { border-radius: 10px; }
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    
    .tracking-wider { letter-spacing: 0.05em; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-gold { background: #d4af37; }
    
    .text-gold { color: #d4af37; }
    
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    
    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        color: #1a1a1a;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px -15px rgba(0,0,0,0.1);
    }
    .card-bg-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.25rem;
    }

    .btn-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.2s;
    }
    .btn-icon:hover {
        transform: scale(1.1);
        background-color: #fff !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-select:focus {
        border-color: #d4af37;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.1);
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    /* Custom Pagination Styling */
    .pagination {
        margin-bottom: 0;
        gap: 5px;
    }
    .page-item .page-link {
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        color: #6c757d;
        font-weight: 600;
    }
    .page-item.active .page-link {
        background: #1a1a1a;
        color: #fff;
    }
</style>
@endsection
