@extends('layouts.app')

@section('title', 'Tax & Compliance')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Tax & Compliance</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('compliance.tax.reports') }}" class="btn btn-primary">
                <i class="fas fa-file-invoice-dollar me-1"></i> Tax Reports
            </a>
            <a href="{{ route('compliance.tax.itc') }}" class="btn btn-outline-primary">
                <i class="fas fa-receipt me-1"></i> Input Tax Credit
            </a>
            <a href="{{ route('compliance.aml.alerts') }}" class="btn btn-outline-danger">
                <i class="fas fa-shield-alt me-1"></i> AML Alerts
                @if($amlAlerts->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $amlAlerts->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Net Tax Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Output Tax (Sales) — This Month</p>
                            <h4 class="mb-0 text-danger">Rs. {{ number_format($outputTax, 2) }}</h4>
                        </div>
                        <div class="rounded p-3" style="background:#fff0f0"><i class="fas fa-arrow-up text-danger fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Input Tax Credit (Purchases) — This Month</p>
                            <h4 class="mb-0 text-success">Rs. {{ number_format($inputTax, 2) }}</h4>
                        </div>
                        <div class="rounded p-3" style="background:#f0fff4"><i class="fas fa-arrow-down text-success fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Net Tax Payable — This Month</p>
                            <h4 class="mb-0 text-warning">Rs. {{ number_format($netTaxPayable, 2) }}</h4>
                        </div>
                        <div class="rounded p-3" style="background:#fffbf0"><i class="fas fa-balance-scale text-warning fa-lg"></i></div>
                    </div>
                    <small class="text-muted">Output Tax − Input Tax Credit</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="complianceTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-slabs"><i class="fas fa-percent me-1"></i>Tax Slabs</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-hsn"><i class="fas fa-tags me-1"></i>HSN / Tax Config</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-filing"><i class="fas fa-file-alt me-1"></i>Filing History</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-aml"><i class="fas fa-exclamation-triangle me-1"></i>AML Alerts <span class="badge bg-danger ms-1">{{ $amlAlerts->count() }}</span></a></li>
    </ul>

    <div class="tab-content">

        {{-- TAB 1: Tax Slabs --}}
        <div class="tab-pane fade show active" id="tab-slabs">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Tax Slabs (GST / VAT Rates)</span>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSlabModal">
                        <i class="fas fa-plus me-1"></i> Add Slab
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Rate (%)</th>
                                <th>HSN Code</th>
                                <th>Default</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxSlabs as $slab)
                            <tr>
                                <td>{{ $slab->name }}</td>
                                <td><span class="badge bg-secondary">{{ $slab->tax_type }}</span></td>
                                <td><strong>{{ number_format($slab->rate, 2) }}%</strong></td>
                                <td><code>{{ $slab->hsn_code ?? '—' }}</code></td>
                                <td>@if($slab->is_default)<span class="badge bg-primary">Default</span>@endif</td>
                                <td><span class="badge bg-{{ $slab->is_active ? 'success' : 'secondary' }}">{{ $slab->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No tax slabs configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: HSN / Tax Configuration --}}
        <div class="tab-pane fade" id="tab-hsn">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">HSN/SAC Code & Product-wise Tax Configuration</span>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addConfigModal">
                        <i class="fas fa-plus me-1"></i> Add Config
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Jewellery HSN codes: <strong>7113</strong> (Articles of jewellery), <strong>7114</strong> (Articles of goldsmiths), <strong>7116</strong> (Articles of natural stones), <strong>9983</strong> (Making charges - SAC)
                    </div>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tax Type</th>
                                <th>Rate (%)</th>
                                <th>Applicable To</th>
                                <th>Product Category</th>
                                <th>HSN/SAC Code</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxConfigs as $config)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $config->tax_type }}</span></td>
                                <td><strong>{{ number_format($config->rate, 2) }}%</strong></td>
                                <td>{{ $config->applicable_to }}</td>
                                <td>{{ $config->product_category ?? '—' }}</td>
                                <td><code>{{ $config->hsn_sac_code ?? '—' }}</code></td>
                                <td>{{ $config->effective_from?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $config->effective_to?->format('d M Y') ?? 'Ongoing' }}</td>
                                <td><span class="badge bg-{{ $config->is_active ? 'success' : 'secondary' }}">{{ $config->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">No configurations added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: Filing History --}}
        <div class="tab-pane fade" id="tab-filing">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">GST / Tax Filing History</span>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addFilingModal">
                        <i class="fas fa-plus me-1"></i> Record Filing
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tax Type</th>
                                <th>Period</th>
                                <th>Taxable Sales</th>
                                <th>Output Tax</th>
                                <th>Input Tax Credit</th>
                                <th>Net Payable</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filingHistory as $filing)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $filing->tax_type }}</span></td>
                                <td>{{ $filing->period_start?->format('d M Y') }} – {{ $filing->period_end?->format('d M Y') }}</td>
                                <td>Rs. {{ number_format($filing->total_taxable_sales, 2) }}</td>
                                <td>Rs. {{ number_format($filing->total_tax_on_sales, 2) }}</td>
                                <td>Rs. {{ number_format($filing->total_tax_on_purchases, 2) }}</td>
                                <td><strong>Rs. {{ number_format($filing->net_tax_payable, 2) }}</strong></td>
                                <td>
                                    @php $colors = ['draft'=>'secondary','filed'=>'primary','paid'=>'success']; @endphp
                                    <span class="badge bg-{{ $colors[$filing->status] ?? 'secondary' }}">{{ ucfirst($filing->status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No filing records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: AML Alerts --}}
        <div class="tab-pane fade" id="tab-aml">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-1"></i> High-Value & Suspicious Transactions (AML)
                </div>
                <div class="card-body">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Transactions exceeding <strong>Rs. 50,000</strong> are flagged for mandatory reporting. Verify customer CNIC/NTN for compliance.
                    </div>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sale ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Risk Level</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amlAlerts as $alert)
                            <tr class="{{ $alert['risk_level'] === 'High' ? 'table-danger' : 'table-warning' }}">
                                <td>#{{ $alert['sale_id'] }}</td>
                                <td>{{ $alert['customer'] }}</td>
                                <td><strong>Rs. {{ number_format($alert['amount'], 2) }}</strong></td>
                                <td>{{ $alert['date'] }}</td>
                                <td><span class="badge bg-{{ $alert['risk_level'] === 'High' ? 'danger' : 'warning text-dark' }}">{{ $alert['risk_level'] }}</span></td>
                                <td><small>{{ $alert['reason'] }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success me-1"></i> No suspicious transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- end tab-content --}}
</div>

{{-- Modal: Add Tax Slab --}}
<div class="modal fade" id="addSlabModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('compliance.tax.slabs.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Tax Slab</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. GST on Making Charges">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="tax_type" class="form-select" required>
                                <option value="GST">GST</option>
                                <option value="VAT">VAT</option>
                                <option value="CESS">CESS</option>
                                <option value="WHT">Withholding Tax</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rate (%)</label>
                            <input type="number" name="rate" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">HSN/SAC Code <small class="text-muted">(optional)</small></label>
                        <input type="text" name="hsn_code" class="form-control" placeholder="e.g. 7113, 9983">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default">
                        <label class="form-check-label" for="is_default">Set as default slab</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Slab</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add HSN Tax Config --}}
<div class="modal fade" id="addConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('compliance.tax.config.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add HSN / Tax Configuration</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="tax_type" class="form-select" required>
                                <option value="GST">GST</option>
                                <option value="VAT">VAT</option>
                                <option value="CESS">CESS</option>
                                <option value="WHT">Withholding Tax</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Rate (%)</label>
                            <input type="number" name="rate" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac_code" class="form-control" placeholder="e.g. 7113">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Applicable To</label>
                            <select name="applicable_to" class="form-select" required>
                                <option value="gold_jewellery">Gold Jewellery</option>
                                <option value="silver_jewellery">Silver Jewellery</option>
                                <option value="diamond_jewellery">Diamond Jewellery</option>
                                <option value="making_charges">Making Charges</option>
                                <option value="stone_charges">Stone Charges</option>
                                <option value="repair_service">Repair / Service</option>
                                <option value="all_products">All Products</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Category <small class="text-muted">(optional)</small></label>
                            <input type="text" name="product_category" class="form-control" placeholder="e.g. Necklace, Ring">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Effective From</label>
                            <input type="date" name="effective_from" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Effective To <small class="text-muted">(leave blank = ongoing)</small></label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Configuration</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Record Filing --}}
<div class="modal fade" id="addFilingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('compliance.tax.filing.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Record Tax Filing</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="tax_type" class="form-select" required>
                                <option value="GST">GST</option>
                                <option value="VAT">VAT</option>
                                <option value="Sales Tax">Sales Tax</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Period Start</label>
                            <input type="date" name="period_start" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Period End</label>
                            <input type="date" name="period_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Taxable Sales</label>
                            <input type="number" name="total_taxable_sales" class="form-control" step="0.01" min="0" required value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Output Tax (on Sales)</label>
                            <input type="number" name="total_tax_on_sales" class="form-control" step="0.01" min="0" required value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Taxable Purchases</label>
                            <input type="number" name="total_taxable_purchases" class="form-control" step="0.01" min="0" required value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Input Tax Credit</label>
                            <input type="number" name="total_tax_on_purchases" class="form-control" step="0.01" min="0" required value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Net Tax Payable</label>
                            <input type="number" name="net_tax_payable" class="form-control" step="0.01" required value="0">
                            <small class="text-muted">Output Tax − Input Tax Credit</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Filing Status</label>
                            <select name="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="filed">Filed</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Filing Record</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
