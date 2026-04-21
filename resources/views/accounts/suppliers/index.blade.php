@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Supply Chain Hub</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Stakeholder Management</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <button class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-excel text-success me-2"></i>Export Dir
                </button>
                <a href="{{ route('accounts.suppliers.create') }}" class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-plus-circle me-2"></i>Onboard Supplier
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Intelligence Panel -->
    <div class="card border-0 shadow-premium rounded-24 mb-5 overflow-hidden">
        <div class="card-header bg-premium-dark py-3 px-4">
            <h6 class="text-white mb-0 fw-700 small text-uppercase ls-1">
                <i class="fas fa-search me-2 text-gold"></i>Supplier Intelligence Search
            </h6>
        </div>
        <div class="card-body p-4 bg-glass">
            <form method="GET" class="row g-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Geographic Type</label>
                    <div class="input-group glass-input-group">
                        <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-globe text-gold"></i></span>
                        <select name="type" class="form-select border-0 bg-transparent py-2">
                            <option value="">All Regions</option>
                            <option value="local" {{ request('type') == 'local' ? 'selected' : '' }}>Local (Domestic)</option>
                            <option value="international" {{ request('type') == 'international' ? 'selected' : '' }}>International (Global)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Partner Identity</label>
                    <div class="input-group glass-input-group">
                        <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-building text-gold"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent py-2" placeholder="Search by name, company or registration...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-gold w-100 rounded-12 py-2 fw-700 h-100">
                        <i class="fas fa-magic me-2"></i>Analyze
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('accounts.suppliers.index') }}" class="btn btn-premium-dark w-100 rounded-12 py-2 h-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Premium Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-800 text-dark">Strategic Partners</h5>
                <p class="text-muted small mb-0 fw-600">Verification status and credit exposure monitoring</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark rounded-pill px-3 py-2 fw-700 shadow-sm border">
                    <i class="fas fa-user-tie me-2 text-gold"></i>{{ $suppliers->total() }} Entities
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover-premium align-middle mb-0">
                    <thead class="bg-premium-dark text-white">
                        <tr>
                            <th class="ps-5 py-3 small text-uppercase ls-1">Supplier Entity</th>
                            <th class="py-3 small text-uppercase ls-1">Contact Protocol</th>
                            <th class="py-3 small text-uppercase ls-1">Classification</th>
                            <th class="py-3 small text-uppercase ls-1">Credit Liquidity</th>
                            <th class="py-3 small text-uppercase ls-1 text-end">Net Exposure</th>
                            <th class="pe-5 py-3 small text-uppercase ls-1 text-center">Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="ps-5 py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-box me-3">
                                            <div class="bg-gold-soft text-gold rounded-12 d-flex align-items-center justify-content-center fw-800" style="width: 42px; height: 42px;">
                                                {{ substr($supplier->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-800 text-dark mb-0">{{ $supplier->name }}</div>
                                            <div class="text-muted smaller fw-600">{{ $supplier->company_name ?? 'Individual Professional' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="d-flex flex-column">
                                        <div class="fw-700 text-dark small"><i class="fas fa-phone-alt text-gold me-2"></i>{{ $supplier->phone ?? 'N/A' }}</div>
                                        <div class="text-muted smaller fw-600"><i class="fas fa-envelope text-gold me-2"></i>{{ $supplier->email ?? 'no-reply@vault.com' }}</div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @switch($supplier->supplier_type)
                                        @case('local')
                                            <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-800 smaller">DOMESTIC</span>
                                            @break
                                        @case('international')
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-800 smaller">INTERNATIONAL</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-800 smaller">GLOBAL</span>
                                    @endswitch
                                </td>
                                <td class="py-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted smaller fw-700 uppercase">Limit</span>
                                        <span class="fw-800 text-dark">Rs. {{ number_format($supplier->creditLimits->first()->credit_limit ?? 0, 2) }}</span>
                                    </div>
                                </td>
                                <td class="py-4 text-end">
                                    <div class="running-balance-pill {{ ($supplier->outstanding_amount ?? 0) > 0 ? 'bg-danger-soft text-danger' : 'bg-success-soft text-success' }} rounded-pill px-3 py-1 d-inline-block border">
                                        <span class="fw-900">Rs. {{ number_format($supplier->outstanding_amount ?? 0, 2) }}</span>
                                    </div>
                                </td>
                                <td class="pe-5 py-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('accounts.suppliers.show', $supplier) }}" class="btn btn-icon-premium" title="Examine">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('accounts.suppliers.ledger', $supplier) }}" class="btn btn-icon-premium text-info" title="Journal Ledger">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-icon-premium" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow-lg rounded-12">
                                                <li><a class="dropdown-item fw-600 py-2" href="{{ route('accounts.suppliers.edit', $supplier) }}"><i class="fas fa-edit me-2 text-warning"></i>Configure</a></li>
                                                <li><a class="dropdown-item fw-600 py-2" href="{{ route('accounts.suppliers.price-history', $supplier) }}"><i class="fas fa-chart-line me-2 text-primary"></i>Pricing Audit</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item fw-600 py-2 text-danger" href="#"><i class="fas fa-ban me-2"></i>Blacklist</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center">
                                    <div class="empty-state p-5">
                                        <div class="icon-circle bg-gold-soft mb-4 mx-auto">
                                            <i class="fas fa-truck-loading text-gold fs-1"></i>
                                        </div>
                                        <h5 class="fw-800 text-dark">No Suppliers Found</h5>
                                        <p class="text-muted fw-600">Start by onboarding your first strategic supply partner.</p>
                                        <a href="{{ route('accounts.suppliers.create') }}" class="btn btn-gold rounded-12 px-4 py-2 mt-3 fw-800">
                                            <i class="fas fa-plus me-2"></i>Begin Onboarding
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Hub -->
    <div class="d-flex justify-content-center mt-5">
        {{ $suppliers->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    
    .text-secondary { color: #d4af37 !important; }
    .text-gold { color: #d4af37; }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4); color: #1a1a1a; }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; transition: all 0.3s ease; }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .btn-premium-dark { background: #1a1a1a; color: #fff; border: none; transition: all 0.3s ease; }
    .btn-premium-dark:hover { background: #000; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-2px); color: #fff; }

    .glass-input-group {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .glass-input-group:focus-within {
        background: #fff;
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        border-color: #d4af37;
    }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; cursor: pointer; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.002); }
    
    .btn-icon-premium {
        width: 36px; height: 36px; border-radius: 10px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .bg-glass { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); }
    .smaller { font-size: 0.75rem; }
    .empty-state .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>
@endsection
