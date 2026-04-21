@extends('layouts.app')

@section('title', 'Customers - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 24px;
        box-shadow: var(--card-shadow);
    }
    .registry-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item small"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item small active">Customers</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold">Customer Registry</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-download me-1"></i> Export</button>
        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> Add Customer</a>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Filters -->
    <div class="filter-card">
        <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name, code, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="business" {{ request('type') == 'business' ? 'selected' : '' }}>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Tier</label>
                <select name="level" class="form-select form-select-sm">
                    <option value="">All Tiers</option>
                    <option value="bronze" {{ request('level') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                    <option value="silver" {{ request('level') == 'silver' ? 'selected' : '' }}>Silver</option>
                    <option value="gold" {{ request('level') == 'gold' ? 'selected' : '' }}>Gold</option>
                    <option value="platinum" {{ request('level') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                <select name="active" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 fw-bold">Apply Filters</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-white border px-3" title="Reset"><i class="fas fa-sync-alt"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Registry Table -->
    <div class="registry-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 small fw-bold text-muted text-uppercase">Code</th>
                        <th class="small fw-bold text-muted text-uppercase">Customer Name</th>
                        <th class="small fw-bold text-muted text-uppercase">Contact</th>
                        <th class="small fw-bold text-muted text-uppercase">Type/Tier</th>
                        <th class="small fw-bold text-muted text-uppercase">Balance</th>
                        <th class="small fw-bold text-muted text-uppercase">Status</th>
                        <th class="text-end pe-3 small fw-bold text-muted text-uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="ps-3"><span class="fw-bold text-primary">{{ $customer->customer_code }}</span></td>
                        <td>
                            <div class="fw-bold text-dark">{{ $customer->full_name }}</div>
                            @if($customer->company_name)
                                <div class="text-muted small">{{ $customer->company_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i> {{ $customer->phone }}</div>
                            <div class="small text-muted"><i class="fas fa-envelope me-1 text-muted"></i> {{ $customer->email ?: 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border me-1">{{ strtoupper($customer->customer_type) }}</span>
                            @php
                                $tierClass = match($customer->membership_level) {
                                    'platinum' => 'dark',
                                    'gold' => 'warning',
                                    'silver' => 'secondary',
                                    default => 'light'
                                };
                            @endphp
                            <span class="badge bg-{{ $tierClass }} {{ $tierClass === 'warning' ? 'text-dark' : '' }} border">
                                <i class="fas fa-crown me-1"></i>{{ strtoupper($customer->membership_level) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold {{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                Rs. {{ number_format($customer->current_balance, 2) }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.7rem;">Limit: Rs. {{ number_format($customer->credit_limit, 0) }}</div>
                        </td>
                        <td>
                            @if($customer->is_active)
                                <span class="badge bg-success-soft text-success rounded-pill px-2 py-1 small fw-bold">ACTIVE</span>
                            @else
                                <span class="badge bg-danger-soft text-danger rounded-pill px-2 py-1 small fw-bold">INACTIVE</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-white border" title="View"><i class="fas fa-eye text-primary"></i></a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-white border" title="Edit"><i class="fas fa-edit text-warning"></i></a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-white border" title="Delete" onclick="return confirm('Archive this customer profile?')"><i class="fas fa-trash-alt text-danger"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-3 opacity-25"></i>
                            <p class="mb-0">No customer records matching the current filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
        </div>
        <div>
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
