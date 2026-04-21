@extends('layouts.app')

@section('title', 'Sale Details - ' . $sale->invoice_no)

@section('content')
<div class="container-fluid px-4 no-print-padding">
    <!-- ERP Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('pos.sales.index') }}" class="btn btn-white shadow-sm border-0 rounded-circle me-3">
                    <i class="fas fa-arrow-left text-primary"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-800 text-dark">Transaction Management</h4>
                    <span class="text-muted small fw-600">Sale Registry #{{ $sale->invoice_no }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            @if($sale->status === 'open')
                <a href="{{ route('pos.sales.edit', $sale) }}" class="btn btn-gold shadow-sm border-0 px-3 fw-700 text-white">
                    <i class="fas fa-edit me-1"></i> Edit Manifest
                </a>
                <button type="button" class="btn btn-success shadow-sm border-0 px-3 fw-700" data-bs-toggle="modal" data-bs-target="#invoiceTypeModal">
                    <i class="fas fa-check-circle me-1"></i> Complete & Pay
                </button>
            @endif

            @if($sale->status === 'completed')
                <a href="{{ route('pos.sales.invoice-a4', $sale) }}" target="_blank" class="btn btn-premium-dark shadow-sm border-0 px-4 fw-700">
                    <i class="fas fa-file-invoice me-1"></i> View Professional Invoice
                </a>
                <a href="{{ route('pos.sales.invoice-a4', $sale) }}" target="_blank" class="btn btn-white shadow-sm border-0 px-3 fw-700">
                    <i class="fas fa-print me-1"></i> Print A4
                </a>
            @endif

            <div class="dropdown">
                <button class="btn btn-white shadow-sm border-0 px-3 fw-700 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-share-alt me-1 text-primary"></i> Share
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-16 mt-2">
                    <li>
                        <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.invoice-a4', $sale) }}" target="_blank">
                            <i class="fas fa-print text-primary me-2"></i> Print A4 Invoice
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-600" href="#" data-bs-toggle="modal" data-bs-target="#emailSaleModal">
                            <i class="fas fa-envelope text-primary me-2"></i> Email Invoice
                        </a>
                    </li>
                    <li>
                        @php
                            $whatsappMessage = "Hello, here is your invoice #{$sale->invoice_no} from " . config('app.name', 'MAGIA LUPOS') . ". Total Amount: {$sale->currency} " . number_format($sale->total, 2) . ". View here: " . route('public.invoice', ['token' => Crypt::encryptString($sale->id)]);
                            $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $sale->customer->phone ?? '') . "?text=" . urlencode($whatsappMessage);
                        @endphp
                        <a class="dropdown-item py-2 fw-600" href="{{ $whatsappUrl }}" target="_blank">
                            <i class="fab fa-whatsapp text-success me-2"></i> WhatsApp
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.link', $sale) }}">
                            <i class="fas fa-link text-info me-2"></i> Copy Secure Link
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.invoice-pdf', $sale) }}" target="_blank">
                            <i class="fas fa-file-pdf text-danger me-2"></i> PDF Invoice
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 fw-600" href="{{ route('pos.sales.invoice-thermal', $sale) }}" target="_blank">
                            <i class="fas fa-receipt text-secondary me-2"></i> Print Thermal Receipt
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-16 mb-4 py-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3"></i>
            <div class="fw-600">{{ session('success') }}</div>
        </div>
    </div>
    @endif

    @if(session('share_link'))
    <div class="alert alert-info border-0 shadow-sm rounded-16 mb-4 py-3" role="alert">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-link fs-4 me-3"></i>
                <div>
                    <span class="fw-bold">Public Viewing Link:</span><br>
                    <code class="text-dark">{{ session('share_link') }}</code>
                </div>
            </div>
            <button class="btn btn-sm btn-white border shadow-sm px-3" onclick="copyToClipboard('{{ session('share_link') }}')">
                <i class="fas fa-copy me-1"></i> Copy Link
            </button>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Main Manifest -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm rounded-24 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex align-items-stretch">
                        <div class="bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'cancelled' ? 'danger' : 'warning') }} px-4 d-flex align-items-center">
                            <i class="fas fa-{{ $sale->status === 'completed' ? 'check-double' : ($sale->status === 'cancelled' ? 'times-circle' : 'clock') }} text-white fs-3"></i>
                        </div>
                        <div class="p-4 flex-grow-1 bg-white">
                            <div class="row align-items-center text-center text-md-start">
                                <div class="col-md-4 border-end-md">
                                    <span class="small fw-800 text-muted text-uppercase tracking-wider">Transaction State</span>
                                    <h4 class="mb-0 fw-800 mt-1 {{ $sale->status === 'completed' ? 'text-success' : 'text-warning' }}">{{ strtoupper($sale->status) }}</h4>
                                </div>
                                <div class="col-md-4 border-end-md mt-3 mt-md-0">
                                    <span class="small fw-800 text-muted text-uppercase tracking-wider">Payment Status</span>
                                    <div class="mt-1">
                                        <span class="badge rounded-pill bg-{{ $sale->payment_status === 'Paid' ? 'success' : 'warning' }} px-3">
                                            {{ strtoupper($sale->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0">
                                    <span class="small fw-800 text-muted text-uppercase tracking-wider">Registry Date</span>
                                    <div class="fw-800 text-dark mt-1">{{ $sale->sale_time->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Table -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-800 text-dark"><i class="fas fa-list-ul me-2 text-gold"></i> Itemized Manifest</h5>
                    <span class="badge bg-light text-dark fw-bold border rounded-pill px-3">{{ $sale->items->count() }} Entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-report align-middle mb-0">
                        <thead class="bg-light-soft text-muted">
                            <tr>
                                <th class="ps-4 py-3 small fw-800">ITEM & SKU</th>
                                <th class="text-center py-3 small fw-800">WEIGHT/QTY</th>
                                <th class="text-center py-3 small fw-800">PURITY</th>
                                <th class="text-end py-3 small fw-800">UNIT PRICE</th>
                                <th class="text-end pe-4 py-3 small fw-800">LINE TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-800 text-dark">{{ $item->description ?? 'Unnamed Item' }}</div>
                                    <div class="smaller text-muted fw-600 tracking-wider">SKU: {{ $item->sku }}</div>
                                </td>
                                <td class="text-center py-3 fw-800 text-dark">
                                    {{ number_format($item->quantity, 2) }}
                                    @if($item->gross_weight > 0)
                                        <div class="smaller text-muted">G: {{ number_format($item->gross_weight, 3) }}g</div>
                                        <div class="smaller text-muted">S: {{ number_format($item->stone_weight, 3) }}g</div>
                                        <div class="smaller text-danger">N: {{ number_format($item->net_weight, 3) }}g</div>
                                    @elseif($item->weight > 0)
                                        <div class="smaller text-muted">{{ number_format($item->weight, 3) }}g</div>
                                    @endif
                                </td>
                                <td class="text-center py-3">
                                    <span class="badge bg-gold-soft text-gold rounded-pill px-3 fw-700">{{ $item->gold_purity ?? 'N/A' }}</span>
                                </td>
                                <td class="text-end py-3">
                                    <div class="small fw-bold text-dark">Rs. {{ number_format($item->unit_price, 2) }}</div>
                                    @if($item->making_charge > 0)
                                        <div class="smaller text-muted fw-600">
                                            Making: {{ number_format($item->making_charge, 2) }} 
                                            @if($item->making_charge_type === 'per_gram') /g @elseif($item->making_charge_type === 'per_piece') /pc @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-4 py-3 fw-800 text-primary">Rs. {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($sale->status === 'open')
            <!-- Quick Add Item -->
            <div class="card border-0 shadow-sm rounded-24">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-800 text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i> Quick Manifest Entry</h5>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#productSelectorModal">
                        <i class="fas fa-boxes me-1"></i> Browse Products
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('pos.sales.items.store', $sale) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Product / SKU</label>
                                <div class="position-relative">
                                    <div class="input-group glass-input-group rounded-12 overflow-hidden border">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-barcode"></i></span>
                                        <input type="text" id="sku_input" name="sku" class="form-control border-0 bg-transparent py-2" placeholder="Scan or type SKU..." autocomplete="off">
                                    </div>
                                    <input type="hidden" name="product_id" id="product_id_input">
                                    <div id="skuResults" class="list-group shadow-lg border-0" style="display: none; position: absolute; z-index: 1050; width: 100%; top: 100%; left: 0; max-height: 300px; overflow-y: auto; margin-top: 2px; border-radius: 0 0 12px 12px;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Description</label>
                                <input type="text" name="description" class="form-control rounded-12 py-2" placeholder="Item name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Quantity</label>
                                <input type="number" name="quantity" class="form-control rounded-12 py-2 fw-bold text-center" value="1" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Unit Price</label>
                                <input type="number" name="unit_price" class="form-control rounded-12 py-2 fw-bold" step="0.01" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Gross Weight (g)</label>
                                <input type="number" name="gross_weight" id="gross_weight" class="form-control rounded-12 py-2 fw-bold" step="0.001">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Stone Weight (g)</label>
                                <input type="number" name="stone_weight" id="stone_weight" class="form-control rounded-12 py-2 fw-bold" step="0.001" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Net Weight (g)</label>
                                <input type="number" name="net_weight" id="net_weight" class="form-control rounded-12 py-2 fw-bold bg-light" step="0.001" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Purity</label>
                                <select name="gold_purity" id="gold_purity_select" class="form-select rounded-12 py-2">
                                    <option value="24K">24K</option>
                                    <option value="22K" selected>22K</option>
                                    <option value="21K">21K</option>
                                    <option value="18K">18K</option>
                                    <option value="14K">14K</option>
                                    <option value="Silver">Silver</option>
                                    <option value="Silver 925">Silver 925</option>
                                    <option value="N/A">N/A</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Making Charge</label>
                                <input type="number" name="making_charge" class="form-control rounded-12 py-2 fw-bold" step="0.01" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Charge Type</label>
                                <select name="making_charge_type" class="form-select rounded-12 py-2">
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="per_gram">Per Gram</option>
                                    <option value="per_piece">Per Piece</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Wastage %</label>
                                <input type="number" name="wastage_percent" class="form-control rounded-12 py-2 fw-bold" step="0.01" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-800 text-muted text-uppercase tracking-wider">Tax %</label>
                                <input type="number" name="tax_percent" class="form-control rounded-12 py-2 fw-bold" step="0.01" value="0">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 rounded-12 py-2 fw-800 shadow-sm">
                                    <i class="fas fa-plus me-1"></i> Add Entry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Financials -->
        <div class="col-lg-4">
            <!-- Account Holder -->
            <div class="card border-0 shadow-sm rounded-24 mb-4 overflow-hidden">
                <div class="card-header bg-premium-dark text-white py-3 px-4">
                    <h6 class="mb-0 fw-800 tracking-wider small text-uppercase">Account Holder</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="d-flex align-items-center mb-4 p-3 rounded-20 bg-light-soft border">
                        <div class="bg-gold bg-opacity-10 text-gold p-3 rounded-circle me-3">
                            <i class="fas fa-user-tie fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">{{ $sale->customer->name ?? 'Walk-in Customer' }}</h5>
                            <span class="text-muted small fw-600"><i class="fas fa-phone me-1 smaller"></i>{{ $sale->customer->phone ?? 'Cash Transaction' }}</span>
                        </div>
                    </div>
                    
                    @if($sale->customer)
                    <div class="p-3 bg-light rounded-20">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-700 text-uppercase tracking-tighter">Current Balance</span>
                            <span class="fw-800 {{ $sale->customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                Rs. {{ number_format($sale->customer->current_balance, 2) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small fw-700 text-uppercase tracking-tighter">Metal Credit (G)</span>
                            <span class="fw-800 text-warning">{{ number_format($sale->customer->current_gold_balance, 3) }}g</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Financial Computation -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark text-white py-3 px-4 border-0">
                    <h6 class="mb-0 fw-800 tracking-wider small text-uppercase">Financial Computation</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom-dashed">
                        <span class="text-muted fw-700">Subtotal</span>
                        <span class="fw-800 text-dark">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom-dashed text-success">
                        <span class="fw-700">Aggregate Discount</span>
                        <span class="fw-800">- Rs. {{ number_format($sale->discount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom-dashed">
                        <span class="text-muted fw-700">Making / Service</span>
                        <span class="fw-800 text-dark">Rs. {{ number_format($sale->making_charges, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted fw-700">Tax Levy</span>
                        <span class="fw-800 text-dark">Rs. {{ number_format($sale->tax_amount, 2) }}</span>
                    </div>

                    <div class="bg-premium-dark text-white p-4 rounded-24 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-800 text-gold tracking-widest small text-uppercase">Grand Total</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h2 class="mb-0 fw-800">Rs. {{ number_format($sale->total, 2) }}</h2>
                            <span class="badge bg-gold text-white fw-bold">{{ $sale->currency }}</span>
                        </div>
                    </div>

                    @if($sale->outstanding_balance > 0)
                    <div class="p-3 bg-danger-soft rounded-20 text-center border-dashed-danger">
                        <span class="text-danger fw-800 small text-uppercase tracking-wider">
                            Outstanding Balance: Rs. {{ number_format($sale->outstanding_balance, 2) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transaction Audit -->
            <div class="card border-0 shadow-sm rounded-24">
                <div class="card-header bg-light py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-800 text-dark tracking-wider small text-uppercase">Transaction Audit</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex mb-3">
                        <i class="fas fa-id-badge text-gold me-3 mt-1"></i>
                        <div>
                            <span class="text-muted smaller fw-700 text-uppercase d-block">Authorized By</span>
                            <span class="fw-800 text-dark">{{ $sale->createdBy->name ?? 'System' }}</span>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <i class="fas fa-fingerprint text-gold me-3 mt-1"></i>
                        <div>
                            <span class="text-muted smaller fw-700 text-uppercase d-block">Hash / Invoice ID</span>
                            <span class="fw-800 text-dark font-monospace small">{{ $sale->invoice_no }}</span>
                        </div>
                    </div>
                    <div class="d-flex">
                        <i class="fas fa-network-wired text-gold me-3 mt-1"></i>
                        <div>
                            <span class="text-muted smaller fw-700 text-uppercase d-block">Origin Node</span>
                            <span class="fw-800 text-dark">{{ $sale->branch->name ?? 'Main Terminal' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailSaleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-24">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-800"><i class="fas fa-envelope text-gold me-2"></i> Share via Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pos.sales.email', $sale) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-800 text-muted text-uppercase">Recipient Email</label>
                        <input type="email" name="email" class="form-control rounded-12" value="{{ $sale->customer->email ?? '' }}" required placeholder="customer@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-800 text-muted text-uppercase">Subject</label>
                        <input type="text" name="subject" class="form-control rounded-12" value="Invoice #{{ $sale->invoice_no }} from {{ config('pos.shop_name', 'JEWELS') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-800 text-muted text-uppercase">Message</label>
                        <textarea name="message" class="form-control rounded-12" rows="3">Dear {{ $sale->customer->name ?? 'Customer' }}, please find attached your invoice #{{ $sale->invoice_no }} for your recent purchase. Thank you!</textarea>
                    </div>
                    <div class="bg-light-soft p-3 rounded-12 border border-dashed text-center">
                        <i class="fas fa-paperclip text-muted me-2"></i>
                        <span class="small fw-700 text-muted">Invoice-{{ $sale->invoice_no }}.pdf attached</span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-12 px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold rounded-12 px-4 text-white fw-bold">Send Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Invoice Type Selection Modal -->
<div class="modal fade" id="invoiceTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-premium rounded-24">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-800">Finalize Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="finalizeSaleForm" action="{{ route('pos.sales.complete', $sale) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted fw-600 mb-4">Select the invoice classification for this sale. This will determine the legal layout and tax presentation.</p>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="invoice_type" id="type_tax" value="tax" checked>
                            <label class="btn btn-outline-primary w-100 p-3 rounded-16 text-start d-flex align-items-center" for="type_tax">
                                <div class="bg-primary-soft p-3 rounded-12 me-3">
                                    <i class="fas fa-file-invoice-dollar fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-800">Formal Tax Invoice</div>
                                    <div class="small text-muted fw-600">GST/VAT Compliant • Professional Layout</div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="invoice_type" id="type_estimate" value="estimate">
                            <label class="btn btn-outline-gold w-100 p-3 rounded-16 text-start d-flex align-items-center" for="type_estimate">
                                <div class="bg-gold-soft p-3 rounded-12 me-3">
                                    <i class="fas fa-calculator fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-800">Estimate / Wholesale</div>
                                    <div class="small text-muted fw-600">Pro-forma Layout • Internal Record</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-12 px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="proceedToConfirmBtn" class="btn btn-success rounded-12 px-4 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> Confirm & Proceed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Final Confirmation Modal -->
<div class="modal fade" id="finalConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-premium rounded-24">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-800">Final Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="bg-warning-soft p-4 rounded-circle d-inline-block mb-3">
                    <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
                </div>
                <h4 class="fw-800 mb-2">Complete Transaction?</h4>
                <p class="text-muted fw-600">You are about to finalize this sale. This will update inventory levels and generate the final invoice. This action cannot be reversed.</p>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-12 px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#invoiceTypeModal">Go Back</button>
                <button type="button" id="confirmFinalizeBtn" class="btn btn-success rounded-12 px-4 fw-bold shadow-sm">
                    <i class="fas fa-check-double me-1"></i> Yes, Finalize Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Professional Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.9); z-index: 9999;">
    <div class="d-flex flex-column align-items-center justify-content-center h-100">
        <div class="spinner-border text-primary mb-4" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.25em;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="fw-800 text-dark mb-2">Processing transaction</h4>
        <p class="text-muted fw-600">Please wait while we finalize the transaction and prepare the invoice.</p>
    </div>
</div>

<!-- Product Selector Modal -->
<div class="modal fade" id="productSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-premium rounded-24">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-800">Browse & Select Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" id="productSearchInput" class="form-control rounded-12 py-2" placeholder="Search by SKU, barcode, or product name...">
                </div>
                <div id="productSearchResults" class="row g-3">
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>Start typing to search products...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Sharing link copied to clipboard!');
        });
    }

    // Product search and auto-fill functionality
    document.addEventListener('DOMContentLoaded', function() {
        const skuInput = document.getElementById('sku_input');
        const skuResults = document.getElementById('skuResults');
        const productSearchInput = document.getElementById('productSearchInput');
        const productSearchResults = document.getElementById('productSearchResults');
        let searchTimeout;

        function selectProduct(product) {
            document.getElementById('sku_input').value = product.sku;
            document.getElementById('product_id_input').value = product.id;

            const descInput = document.querySelector('input[name="description"]');
            const unitPriceInput = document.querySelector('input[name="unit_price"]');
            const puritySelect = document.getElementById('gold_purity_select');
            const quantityInput = document.querySelector('input[name="quantity"]');
            const grossWeightInput = document.getElementById('gross_weight');
            const stoneWeightInput = document.getElementById('stone_weight');
            const netWeightInput = document.getElementById('net_weight');
            const makingChargeInput = document.querySelector('input[name="making_charge"]');
            const makingChargeTypeSelect = document.querySelector('select[name="making_charge_type"]');
            const wastagePercentInput = document.querySelector('input[name="wastage_percent"]');
            const taxPercentInput = document.querySelector('input[name="tax_percent"]');

            descInput.value = product.name;
            unitPriceInput.value = product.selling_price;

            // Set purity - try exact match first, then partial match
            if (puritySelect) {
                const options = Array.from(puritySelect.options);
                const exactMatch = options.find(o => o.value === product.purity);
                const partialMatch = options.find(o => product.purity && product.purity.toLowerCase().includes(o.value.toLowerCase()));
                if (exactMatch) puritySelect.value = exactMatch.value;
                else if (partialMatch) puritySelect.value = partialMatch.value;
                else {
                    // Add dynamic option if not found
                    const newOpt = new Option(product.purity, product.purity, true, true);
                    puritySelect.add(newOpt);
                    puritySelect.value = product.purity;
                }
            }

            if (product.gross_weight && product.gross_weight > 0) {
                grossWeightInput.value = product.gross_weight;
                const stoneW = Math.max(0, product.gross_weight - product.net_weight);
                stoneWeightInput.value = stoneW.toFixed(3);
                netWeightInput.value = product.net_weight > 0 ? product.net_weight : product.gross_weight;
            } else if (product.weight && product.weight > 0) {
                grossWeightInput.value = product.weight;
                stoneWeightInput.value = 0;
                netWeightInput.value = product.weight;
            }

            makingChargeInput.value = product.making_charge || 0;
            makingChargeTypeSelect.value = product.making_charge_type || 'fixed';
            wastagePercentInput.value = product.wastage_percent || 0;
            if (taxPercentInput) taxPercentInput.value = product.tax_rate || 0;

            quantityInput.focus();
            skuResults.style.display = 'none';
        }

        // Weight auto-calculation
        const grossWeightInput = document.getElementById('gross_weight');
        const stoneWeightInput = document.getElementById('stone_weight');
        const netWeightInput = document.getElementById('net_weight');

        function calculateNetWeight() {
            const gross = parseFloat(grossWeightInput.value) || 0;
            const stone = parseFloat(stoneWeightInput.value) || 0;
            const net = Math.max(0, gross - stone);
            netWeightInput.value = net.toFixed(3);
        }

        if (grossWeightInput && stoneWeightInput) {
            grossWeightInput.addEventListener('input', calculateNetWeight);
            stoneWeightInput.addEventListener('input', calculateNetWeight);
        }

        // SKU input live search
        let selectedIndex = -1;

        skuInput.addEventListener('keydown', function(e) {
            const items = skuResults.querySelectorAll('.list-group-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(items);
            } else if (e.key === 'Enter') {
                if (selectedIndex > -1 && items[selectedIndex]) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                skuResults.style.display = 'none';
            }
        });

        function updateSelection(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        skuInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            selectedIndex = -1;

            if (query.length < 2) {
                skuResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('pos.items.search') }}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        skuResults.innerHTML = '';
                        if (!data.products || data.products.length === 0) {
                            skuResults.style.display = 'none';
                            return;
                        }

                        data.products.forEach((product, index) => {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.className = 'list-group-item list-group-item-action py-2 border-start-0 border-end-0';
                            a.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark">${product.name}</div>
                                        <div class="small text-muted">SKU: ${product.sku} • Stock: ${product.current_stock}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary">Rs. ${product.selling_price.toFixed(2)}</div>
                                        <div class="small text-muted">${product.purity}</div>
                                    </div>
                                </div>
                            `;
                            a.addEventListener('click', (e) => {
                                e.preventDefault();
                                selectProduct(product);
                            });
                            skuResults.appendChild(a);
                        });
                        skuResults.style.display = 'block';
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });

        // Modal product search
        productSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                productSearchResults.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2"></i><p>Start typing to search products...</p></div>';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('pos.items.search') }}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        productSearchResults.innerHTML = '';
                        if (!data.products || data.products.length === 0) {
                            productSearchResults.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-inbox"></i><p>No products found</p></div>';
                            return;
                        }

                        data.products.forEach(product => {
                            const card = document.createElement('div');
                            card.className = 'col-md-4';
                            card.innerHTML = `
                                <div class="card border shadow-sm h-100 cursor-pointer product-card" style="cursor: pointer;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-dark">${product.name}</h6>
                                        <p class="small text-muted mb-2">SKU: <code>${product.sku}</code></p>
                                        <div class="mb-3">
                                            <span class="badge bg-success">Stock: ${product.current_stock}</span>
                                            <span class="badge bg-primary">${product.purity}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-primary">Rs. ${product.selling_price.toFixed(2)}</span>
                                            <button type="button" class="btn btn-sm btn-primary select-product-btn">
                                                <i class="fas fa-check me-1"></i> Select
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            card.querySelector('.select-product-btn').addEventListener('click', (e) => {
                                e.preventDefault();
                                selectProduct(product);
                                bootstrap.Modal.getInstance(document.getElementById('productSelectorModal')).hide();
                            });
                            productSearchResults.appendChild(card);
                        });
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.input-group') && !e.target.closest('#skuResults')) {
                skuResults.style.display = 'none';
            }
        });

        // Transaction Finalization Flow
        const proceedToConfirmBtn = document.getElementById('proceedToConfirmBtn');
        const confirmFinalizeBtn = document.getElementById('confirmFinalizeBtn');
        const finalizeSaleForm = document.getElementById('finalizeSaleForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const finalConfirmationModalEl = document.getElementById('finalConfirmationModal');
        const finalConfirmationModal = finalConfirmationModalEl ? new bootstrap.Modal(finalConfirmationModalEl) : null;

        if (proceedToConfirmBtn) {
            proceedToConfirmBtn.addEventListener('click', function() {
                const typeModalEl = document.getElementById('invoiceTypeModal');
                const bootstrapTypeModal = bootstrap.Modal.getInstance(typeModalEl);
                if (bootstrapTypeModal) {
                    bootstrapTypeModal.hide();
                    setTimeout(() => {
                        if (finalConfirmationModal) finalConfirmationModal.show();
                    }, 400);
                }
            });
        }

        if (confirmFinalizeBtn) {
            confirmFinalizeBtn.addEventListener('click', function() {
                // Show loading overlay immediately
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) overlay.classList.remove('d-none');
                
                // Hide the confirmation modal
                if (finalConfirmationModal) {
                    finalConfirmationModal.hide();
                }
                
                // Set a small delay to allow UI to update and ensure form submission isn't blocked by UI transitions
                setTimeout(() => {
                    // Disable all buttons to prevent duplicate submissions
                    document.querySelectorAll('button').forEach(btn => {
                        btn.disabled = true;
                    });

                    // Execute form submission
                    const form = document.getElementById('finalizeSaleForm');
                    if (form) {
                        // Prevent session security system from logging out the user
                        if (typeof window.isInternalNavigation !== 'undefined') {
                            window.isInternalNavigation = true;
                        }
                        form.submit();
                    } else {
                        // Fallback if form is not found
                        if (overlay) overlay.classList.add('d-none');
                        document.querySelectorAll('button').forEach(btn => {
                            btn.disabled = false;
                        });
                        alert('Error: Transaction form not found. Please refresh and try again.');
                    }
                }, 150);
            });
        }
    });
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-12 { border-radius: 12px; }
    .rounded-16 { border-radius: 16px; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.15em; }
    .tracking-tighter { letter-spacing: -0.01em; }
    
    .bg-gold { background-color: #d4af37 !important; }
    .text-gold { color: #d4af37 !important; }
    .btn-gold { background-color: #d4af37; color: white; }
    .btn-gold:hover { background-color: #b8962d; color: white; }
    
    .bg-premium-dark { background-color: #1a1a1a; }
    .bg-light-soft { background-color: #f8f9fa; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-gold-soft { background-color: rgba(212, 175, 55, 0.1); color: #d4af37; }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    
    .btn-outline-gold { border-color: #d4af37; color: #d4af37; }
    .btn-outline-gold:hover, .btn-check:checked + .btn-outline-gold { background-color: #d4af37; color: white !important; }
    .btn-check:checked + .btn-outline-gold .text-muted { color: rgba(255,255,255,0.8) !important; }
    .btn-check:checked + .btn-outline-gold .bg-gold-soft { background-color: rgba(255,255,255,0.2); color: white; }
    
    .btn-check:checked + .btn-outline-primary .text-muted { color: rgba(255,255,255,0.8) !important; }
    .btn-check:checked + .btn-outline-primary .bg-primary-soft { background-color: rgba(255,255,255,0.2); color: white; }
    
    .shadow-premium { box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
    .border-bottom-dashed { border-bottom: 1px dashed #eee; }
    .border-dashed-danger { border: 1px dashed #dc3545; }
    
    .table-report thead th { font-size: 0.65rem; padding: 12px 15px; text-transform: uppercase; letter-spacing: 0.1em; }
    .table-report tbody td { padding: 15px; border-color: #f1f1f1; vertical-align: middle; }
    
    .smaller { font-size: 0.7rem; }
    .btn-white { background: #fff; border: 1px solid #eee; }
    .btn-white:hover { background: #f8f9fa; }

    @media print {
        .d-print-none { display: none !important; }
        .no-print-padding { padding: 0 !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; margin: 0 !important; border-radius: 0 !important; }
    }
</style>
@endsection
