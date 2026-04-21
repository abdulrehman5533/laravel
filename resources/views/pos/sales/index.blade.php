@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Sales Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">POS Terminal</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Transaction History</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <button class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-file-export me-2 text-secondary"></i>Export Log
                </button>
                <a href="{{ route('pos.sales.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>Initialize New Sale
                </a>
            </div>
        </div>
    </div>

    <!-- Analytical Filter -->
    <div class="stat-card border-0 mb-5 p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-700 text-muted small text-uppercase">Search Transaction</label>
                <div class="input-group rounded-12 overflow-hidden border">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-gold"></i></span>
                    <input type="text" name="invoice_no" class="form-control border-0" placeholder="Invoice # or Customer..." value="{{ request('invoice_no') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-700 text-muted small text-uppercase">Lifecycle Status</label>
                <select name="status" class="form-select rounded-12 border">
                    <option value="">All Transactions</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Active Terminal</option>
                    <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Settled</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-700 text-muted small text-uppercase">Time Horizon</label>
                <input type="date" name="date" class="form-control rounded-12 border" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-premium-dark w-100 py-2 rounded-12 fw-700">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Premium Sales Table -->
    <div class="table-custom shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Transaction ID</th>
                        <th>Client Profile</th>
                        <th class="text-center">Volume</th>
                        <th>Financial Projection</th>
                        <th>Settlement Status</th>
                        <th>Timestamp</th>
                        <th class="text-end pe-4">Orchestration</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="group">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-gold-light text-gold me-3 mb-0" style="width: 35px; height: 35px; font-size: 14px;">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <div class="fw-800 text-dark">{{ $sale->invoice_no }}</div>
                                    <div class="text-muted small fw-600">Ref: #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-user text-muted small"></i>
                                </div>
                                <span class="fw-700 text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-gold-light text-gold rounded-pill px-3 py-2 fw-700">
                                {{ $sale->items->count() }} Manifest Items
                            </span>
                        </td>
                        <td>
                            <div>
                                <div class="fw-800 text-dark">Rs. {{ number_format($sale->total, 2) }}</div>
                                <div class="text-muted small fw-600">Tax: Rs. {{ number_format($sale->tax_amount, 2) }}</div>
                            </div>
                        </td>
                        <td>
                            @if($sale->status === 'completed')
                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-check-circle me-1"></i> SETTLED
                                </span>
                            @elseif($sale->status === 'held')
                                <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-pause-circle me-1"></i> ON HOLD
                                </span>
                            @elseif($sale->status === 'open')
                                <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-spinner fa-spin me-1"></i> ACTIVE
                                </span>
                            @else
                                <span class="badge bg-secondary-soft text-secondary rounded-pill px-3 py-2 fw-700">
                                    {{ strtoupper($sale->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark fw-600">{{ $sale->sale_time ? $sale->sale_time->format('M d, Y') : 'N/A' }}</div>
                            <div class="text-muted small fw-500">{{ $sale->sale_time ? $sale->sale_time->format('h:i A') : '' }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm rounded-12 overflow-hidden">
                                <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-white btn-sm px-3" title="View Intelligence">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                @if($sale->status === 'open' || $sale->status === 'held')
                                    <a href="{{ route('pos.sales.edit', $sale) }}" class="btn btn-white btn-sm px-3" title="Resume Terminal">
                                        <i class="fas fa-play text-success"></i>
                                    </a>
                                @endif
                                <div class="btn-group">
                                    <a href="{{ route('pos.sales.invoice-a4', $sale) }}" target="_blank" class="btn btn-white btn-sm px-2" title="Print A4 Invoice">
                                        <i class="fas fa-print text-secondary"></i>
                                    </a>
                                    <button type="button" class="btn btn-white btn-sm px-2 dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-12 mt-2">
                                        <li>
                                            <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.invoice-a4', $sale) }}" target="_blank">
                                                <i class="fas fa-print text-primary me-2"></i> Print A4 Invoice
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.invoice-pdf', $sale) }}" target="_blank">
                                                <i class="fas fa-file-pdf text-danger me-2"></i> Download PDF
                                            </a>
                                        </li>
                                        @if($sale->customer && $sale->customer->phone)
                                        <li>
                                            @php
                                                $whatsappMessage = "Hello, here is your invoice #{$sale->invoice_no} from " . config('app.name', 'MAGIA LUPOS') . ". Total Amount: Rs. " . number_format($sale->total, 2) . ". View here: " . route('public.invoice', ['token' => Crypt::encryptString($sale->id)]);
                                                $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $sale->customer->phone) . "?text=" . urlencode($whatsappMessage);
                                            @endphp
                                            <a class="dropdown-item py-2 fw-600" href="{{ $whatsappUrl }}" target="_blank">
                                                <i class="fab fa-whatsapp text-success me-2"></i> Share via WhatsApp
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                                <form action="{{ route('pos.sales.destroy', $sale) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-white btn-sm px-3" onclick="return confirm('Archive this transaction manifest?')" title="Void Transaction">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-folder-open fa-3x text-gold-light mb-3"></i>
                                <h5 class="fw-800 text-dark">No Transactions Detected</h5>
                                <p class="text-muted fw-600">The current filter criteria yielded no matching registry entries.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analytical Pagination -->
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small fw-600 italic">
            Showing audit trail of {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} transactions
        </div>
        <div>
            {{ $sales->links() }}
        </div>
    </div>
</div>

<style>
    .bg-premium-dark { background: #1a1a1a !important; }
    .btn-premium-dark {
        background: #1a1a1a;
        color: white;
        transition: all 0.3s;
    }
    .btn-premium-dark:hover {
        background: #000;
        color: #d4af37;
        transform: translateY(-2px);
    }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
    .bg-info-soft { background: rgba(59, 130, 246, 0.1); }
    .bg-secondary-soft { background: rgba(156, 163, 175, 0.1); }
    .text-gold { color: #d4af37 !important; }
    .bg-gold-light { background: rgba(212, 175, 55, 0.1) !important; }
    .shadow-gold { box-shadow: 0 10px 30px -5px rgba(212, 175, 55, 0.3) !important; }
    .rounded-12 { border-radius: 12px !important; }
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    
    .table-custom tr { transition: all 0.2s ease; }
    .table-custom tr.group:hover {
        background-color: #fcfaf2 !important;
        transform: scale(1.002);
    }
    .btn-white {
        background: white;
        border: 1px solid #edf2f7;
    }
    .btn-white:hover {
        background: #f9fafb;
    }
    .pagination {
        margin-bottom: 0;
        gap: 5px;
    }
    .page-item .page-link {
        border-radius: 8px !important;
        border: none;
        color: #1a1a1a;
        font-weight: 600;
        padding: 8px 16px;
    }
    .page-item.active .page-link {
        background: var(--gold-gradient);
        color: #1a1a1a;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
    }
</style>
@endsection
