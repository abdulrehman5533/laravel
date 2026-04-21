@extends('layouts.app')

@section('title', 'Invoice - ' . $invoice->sale->invoice_no)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">📄 Invoice Details</h1>
            <p class="text-muted mb-0">Invoice #{{ $invoice->sale->invoice_no }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos.invoices.index') }}" class="btn btn-white shadow-sm border-0 px-3 fw-600">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            
            <div class="dropdown">
                <button class="btn btn-gold shadow-sm border-0 px-3 fw-600 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-share-alt me-1"></i> Share Invoice
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-12 mt-2">
                    <li>
                        <a class="dropdown-item py-2 fw-500" href="#" data-bs-toggle="modal" data-bs-target="#emailInvoiceModal">
                            <i class="fas fa-envelope text-primary me-2"></i> Email Invoice
                        </a>
                    </li>
                    <li>
                        @php
                            $whatsappMessage = "Hello, here is your invoice #{$invoice->sale->invoice_no} from " . config('app.name') . ". Total Amount: {$invoice->sale->currency} " . number_format($invoice->sale->total, 2) . ". View here: " . route('public.invoice', ['token' => Crypt::encryptString($invoice->sale->id)]);
                            $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $invoice->sale->customer->phone ?? '') . "?text=" . urlencode($whatsappMessage);
                        @endphp
                        <a class="dropdown-item py-2 fw-500" href="{{ $whatsappUrl }}" target="_blank">
                            <i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Message
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-500" href="{{ route('pos.invoices.link', $invoice->sale) }}">
                            <i class="fas fa-link text-info me-2"></i> Copy Secure Link
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 fw-500" href="{{ route('pos.sales.invoice-pdf', $invoice->sale) }}" target="_blank">
                            <i class="fas fa-file-pdf text-danger me-2"></i> Download PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-500" href="#" onclick="window.print()">
                            <i class="fas fa-print text-secondary me-2"></i> Print Invoice
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-12 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('share_link'))
    <div class="alert alert-info border-0 shadow-sm rounded-12 mb-4" role="alert">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-link fs-4 me-3"></i>
                <div>
                    <span class="fw-bold">Secure Link Generated:</span><br>
                    <code class="text-dark">{{ session('share_link') }}</code>
                </div>
            </div>
            <button class="btn btn-sm btn-white border-0 shadow-sm" onclick="copyToClipboard('{{ session('share_link') }}')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-12 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Invoice Card -->
    <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5" id="invoiceContainer">
        <!-- Invoice Header -->
        <div class="card-body p-5 bg-white border-bottom-double">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <svg width="48" height="48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-3">
                            <path d="M50 95C74.8528 95 95 74.8528 95 50C95 25.1472 74.8528 5 50 5C25.1472 5 5 25.1472 5 50C5 74.8528 25.1472 95 50 95Z" stroke="#d4af37" stroke-width="2"/>
                            <path d="M50 20L35 45L50 80L65 45L50 20Z" fill="#d4af37"/>
                            <path d="M50 20L25 40L50 80L75 40L50 20Z" stroke="#d4af37" stroke-width="1"/>
                            <path d="M42 35L45 30L50 33L55 30L58 35" stroke="white" stroke-width="2" fill="none"/>
                        </svg>
                        <div>
                            <h2 class="company-name mb-0">{{ config('app.name', 'MAGIA LUPOS') }}</h2>
                        </div>
                    </div>
                    <div class="company-details">
                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-gold"></i> {{ config('pos.shop_address', 'Shop Address') }}</p>
                        <p class="mb-1"><i class="fas fa-phone-alt me-2 text-gold"></i> {{ config('pos.shop_phone', '+92-300-000-0000') }}</p>
                        <p class="mb-0"><i class="fas fa-envelope me-2 text-gold"></i> info@magialupos.com</p>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                    <h3 class="text-gold fw-800 tracking-wider mb-3">{{ $invoice->sale->is_wholesale ? 'WHOLESALE INVOICE' : 'TAX INVOICE' }}</h3>
                    <div class="invoice-meta">
                        <p class="mb-1"><strong>Invoice #:</strong> <span class="text-dark fw-bold fs-5 ms-2">{{ $invoice->sale->invoice_no }}</span></p>
                        <p class="mb-1"><strong>Date:</strong> <span class="text-muted ms-2">{{ $invoice->sale->sale_time->format('d M, Y') }}</span></p>
                        <p class="mb-0"><strong>Time:</strong> <span class="text-muted ms-2">{{ $invoice->sale->sale_time->format('H:i A') }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="card-body p-5 border-bottom bg-light-soft">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-gold fw-800 text-uppercase tracking-widest small mb-3">Billed To</h6>
                    <div class="billing-box p-3 bg-white rounded-16 shadow-sm border-start-gold">
                        @if($invoice->sale->customer)
                            <h5 class="fw-bold mb-2">{{ $invoice->sale->customer->name }}</h5>
                            <p class="text-muted mb-1 small"><i class="fas fa-phone me-2"></i>{{ $invoice->sale->customer->phone ?? 'N/A' }}</p>
                            <p class="text-muted mb-0 small"><i class="fas fa-map-marker-alt me-2"></i>{{ $invoice->sale->customer->address ?? 'N/A' }}</p>
                        @else
                            <h5 class="fw-bold mb-0">Walk-in Customer</h5>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                    <h6 class="text-gold fw-800 text-uppercase tracking-widest small mb-3">Payment Summary</h6>
                    <div class="d-inline-block text-start p-3 bg-white rounded-16 shadow-sm border-end-gold">
                        <p class="mb-1 small"><strong>Status:</strong> 
                            <span class="badge rounded-pill bg-{{ $invoice->sale->payment_status === 'Paid' ? 'success' : 'warning' }} px-3">
                                {{ ucfirst($invoice->sale->payment_status) }}
                            </span>
                        </p>
                        <p class="mb-1 small"><strong>Salesman:</strong> <span class="text-muted">{{ auth()->user()->name ?? 'N/A' }}</span></p>
                        <p class="mb-0 small"><strong>Currency:</strong> <span class="text-muted">{{ $invoice->sale->currency }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-report mb-0">
                    <thead class="bg-premium-dark text-white">
                        <tr>
                            <th class="ps-5 py-3">DESCRIPTION</th>
                            <th class="text-center py-3">QTY</th>
                            <th class="text-end py-3">UNIT PRICE</th>
                            <th class="text-end py-3">TAX</th>
                            <th class="text-end pe-5 py-3">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->sale->items as $item)
                        <tr>
                            <td class="ps-5 py-3">
                                <div class="fw-bold text-dark">{{ $item->description }}</div>
                                @if($item->sku)
                                <span class="smaller text-muted tracking-wider text-uppercase">SKU: {{ $item->sku }}</span>
                                @endif
                            </td>
                            <td class="text-center py-3 fw-600">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end py-3">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end py-3">{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                            <td class="text-end pe-5 py-3 fw-bold text-dark">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="card-body p-5">
            <div class="row">
                <div class="col-lg-7">
                    @if($invoice->sale->notes)
                    <div class="p-3 bg-light-soft rounded-16 border h-100">
                        <h6 class="fw-bold small text-uppercase tracking-wider mb-2">Terms & Notes</h6>
                        <p class="small text-muted mb-0">{{ $invoice->sale->notes }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="p-4 bg-premium-dark text-white rounded-24 shadow-sm">
                        <table class="table table-borderless table-sm mb-0 text-white-50">
                            <tr>
                                <td class="pb-2">Subtotal</td>
                                <td class="text-end text-white pb-2">{{ number_format($invoice->sale->sub_total ?? $invoice->sale->total - $invoice->sale->tax_amount, 2) }}</td>
                            </tr>
                            @if($invoice->sale->discount > 0)
                            <tr>
                                <td class="pb-2">Discount</td>
                                <td class="text-end text-danger pb-2">-{{ number_format($invoice->sale->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->sale->tax_amount > 0)
                            <tr>
                                <td class="pb-2">Tax</td>
                                <td class="text-end text-white pb-2">{{ number_format($invoice->sale->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->sale->making_charges > 0)
                            <tr>
                                <td class="pb-3">Making Charges</td>
                                <td class="text-end text-white pb-3">{{ number_format($invoice->sale->making_charges, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-top border-secondary pt-3">
                                <td class="pt-3 fw-800 fs-5 text-gold">GRAND TOTAL</td>
                                <td class="pt-3 text-end fw-800 fs-4 text-white">Rs. {{ number_format($invoice->sale->total, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    @if($invoice->sale->outstanding_balance > 0)
                    <div class="mt-3 p-3 bg-danger-soft rounded-16 text-center border-dashed-danger">
                        <span class="text-danger fw-bold small text-uppercase">Outstanding Balance: Rs. {{ number_format($invoice->sale->outstanding_balance, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-body p-4 bg-light-soft text-center border-top">
            <p class="smaller text-muted mb-0 text-uppercase tracking-widest fw-600">
                Authorized Digital Invoice &copy; {{ now()->year }} | {{ config('app.name') }}
            </p>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-24">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-envelope text-gold me-2"></i> Share via Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pos.invoices.email', $invoice->sale) }}" method="POST">
                @csrf
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Recipient Email</label>
                        <input type="email" name="email" class="form-control rounded-12" value="{{ $invoice->sale->customer->email ?? '' }}" required placeholder="customer@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Subject</label>
                        <input type="text" name="subject" class="form-control rounded-12" value="Invoice #{{ $invoice->sale->invoice_no }} from {{ config('pos.shop_name', 'JEWELS') }}" required text="Invoice #{{ $invoice->sale->invoice_no }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Message (Optional)</label>
                        <textarea name="message" class="form-control rounded-12" rows="4">Dear {{ $invoice->sale->customer->name ?? 'Customer' }}, please find attached your invoice #{{ $invoice->sale->invoice_no }} for your recent purchase. Thank you for shopping with us!</textarea>
                    </div>
                    <div class="p-3 bg-light rounded-12 border">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-paperclip me-3 text-gold"></i>
                            <div class="small fw-600 text-muted">Invoice-{{ $invoice->sale->invoice_no }}.pdf attached automatically</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-12 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold rounded-12 px-4 text-white fw-bold">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link copied to clipboard!');
        });
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8f9fa;
    }

    .fw-800 { font-weight: 800; }
    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.15em; }
    
    .bg-gold { background-color: #d4af37 !important; }
    .text-gold { color: #d4af37 !important; }
    
    .rounded-12 { border-radius: 12px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-24 { border-radius: 24px; }
    
    .bg-premium-dark { background-color: #1a1a1a; }
    .bg-light-soft { background-color: #fcfcfc; }
    
    .border-start-gold { border-left: 4px solid #d4af37; }
    .border-end-gold { border-right: 4px solid #d4af37; }
    .border-bottom-double { border-bottom: 3px double #f1f1f1; }
    .border-dashed-danger { border: 1px dashed #dc3545; }
    
    .shadow-premium { box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
    .btn-white { background: #fff; border: 1px solid #eee; }
    .btn-white:hover { background: #f8f9fa; }

    .company-name { font-weight: 800; letter-spacing: -0.02em; color: #1a1a1a; font-size: 1.5rem; }
    .company-details { font-size: 0.8rem; color: #6c757d; font-weight: 500; }

    .table-report thead th {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 15px;
        letter-spacing: 0.1em;
    }

    .table-report tbody td {
        vertical-align: middle;
        font-size: 0.9rem;
        border-color: #f8f9fa;
    }

    .smaller { font-size: 0.75rem; }

    @media print {
        .d-print-none { display: none !important; }
        body { background: white !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; }
        .rounded-24 { border-radius: 0 !important; }
        .bg-premium-dark { background-color: #1a1a1a !important; -webkit-print-color-adjust: exact; }
        .text-white { color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection
