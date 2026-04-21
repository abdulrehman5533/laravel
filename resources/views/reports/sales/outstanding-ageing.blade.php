@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.sales.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Receivables Management</h4>
                    <span class="text-muted small">Ageing Analysis & Risk Assessment</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-inline-flex gap-2">
                <input type="hidden" name="type" value="age">
                <a href="{{ route('reports.sales.outstanding.print', ['type' => 'age']) }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
                    <i class="fas fa-print me-1"></i> Print Proper Report
                </a>
            </form>
        </div>
    </div>

    <!-- Professional Report Container -->
    <div class="report-paper shadow-sm mx-auto bg-white" id="printableReport">
        <!-- Corporate Letterhead -->
        <div class="report-header border-bottom-double mb-4">
            <div class="row align-items-center">
                <div class="col-7">
                    <h2 class="company-name mb-1">{{ config('app.name') }}</h2>
                    <p class="company-details mb-0 text-uppercase tracking-tighter">
                        Premium Jewelry Solutions & ERP<br>
                        Credit Control & Risk Management<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">RECEIVABLES AGEING</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            PORTFOLIO SNAPSHOT
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- AGEING SUMMARY CARDS -->
            <div class="row mb-5 g-3">
                @foreach($ageing as $period => $data)
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 {{ $period == '90+' ? 'bg-soft-danger' : 'bg-light' }} text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">{{ $period }} Days</h6>
                        <h4 class="fw-800 mb-0 {{ $period == '90+' ? 'text-danger' : 'text-dark' }}">Rs. {{ number_format($data['amount'], 2) }}</h4>
                        <p class="smaller fw-bold mb-0 mt-1">{{ $data['count'] }} Bills</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- TAB NAVIGATION (Web Only) -->
            <div class="d-print-none mb-4">
                <ul class="nav nav-pills rounded-pill bg-light p-1">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 fw-bold" href="{{ route('reports.sales.outstanding', ['type' => 'bill']) }}">Bill-wise View</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill px-4 fw-bold" href="{{ route('reports.sales.outstanding', ['type' => 'age']) }}">Ageing Analysis</a>
                    </li>
                </ul>
            </div>

            <!-- AGEING COMPOSITION TABLE -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Ageing Distribution Matrix</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Age Bracket</th>
                        <th class="text-center text-uppercase tracking-wider">Invoices</th>
                        <th class="text-end text-uppercase tracking-wider">Total Amount</th>
                        <th class="text-end text-uppercase tracking-wider">% of Portfolio</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = collect($ageing)->sum('amount'); @endphp
                    @foreach($ageing as $period => $data)
                        <tr>
                            <td class="fw-bold text-dark">{{ $period }} Days</td>
                            <td class="text-center fw-600">{{ $data['count'] }}</td>
                            <td class="text-end fw-800 {{ $period == '90+' ? 'text-danger' : 'text-dark' }}">
                                Rs. {{ number_format($data['amount'], 2) }}
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end">
                                    <span class="me-2 fw-bold small">{{ $totalAmount > 0 ? round(($data['amount'] / $totalAmount) * 100, 1) : 0 }}%</span>
                                    <div class="progress" style="width: 60px; height: 6px;">
                                        <div class="progress-bar {{ $period == '90+' ? 'bg-danger' : 'bg-gold' }}" role="progressbar" style="width: {{ $totalAmount > 0 ? ($data['amount'] / $totalAmount) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td colspan="2" class="text-end fw-800 text-uppercase smaller">Consolidated Total</td>
                        <td class="text-end fw-800 text-dark">Rs. {{ number_format($totalAmount, 2) }}</td>
                        <td class="text-end fw-800">100%</td>
                    </tr>
                </tfoot>
            </table>

            <!-- RISK ASSESSMENT -->
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="audit-note p-4 border rounded-24 bg-white shadow-sm border-gold">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Credit Risk Assessment Note</h6>
                        <p class="small text-muted mb-0">
                            <strong>Standard Observation:</strong> Portfolio health is determined by the percentage of receivables within the 0-60 day bracket. 
                            Any balance exceeding 90 days is classified as 'High Risk' and requires immediate legal or collection escalation. 
                            Currently, {{ $totalAmount > 0 ? round(($ageing['90+']['amount'] / $totalAmount) * 100, 1) : 0 }}% of the portfolio is in the high-risk zone.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Credit Analyst</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Internal Auditor</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">General Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
    }

    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    
    .report-paper {
        width: 100%;
        max-width: 1000px;
        min-height: 29.7cm;
        padding: 50px 70px;
        color: #1a1a1a;
    }

    .company-name {
        font-weight: 800;
        letter-spacing: -0.02em;
        font-size: 1.5rem;
    }

    .company-details {
        font-size: 0.7rem;
        line-height: 1.5;
        color: #6c757d;
        font-weight: 500;
    }

    .report-title-box {
        border-left: 3px solid #d4af37;
        padding-left: 20px;
    }

    .report-type-title {
        font-weight: 800;
        letter-spacing: 0.1em;
        font-size: 1.25rem;
    }

    .border-bottom-double {
        border-bottom: 3px double #dee2e6;
        padding-bottom: 25px;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 15px 10px;
    }

    .table-report tbody td {
        padding: 15px 10px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.05); }
    .border-gold { border-color: #d4af37 !important; }
    .rounded-16 { border-radius: 16px; }
    .rounded-24 { border-radius: 24px; }

    .sig-line {
        height: 1px;
        background: #1a1a1a;
        margin-top: 40px;
    }

    .smaller { font-size: 0.65rem; }
    .tracking-wider { letter-spacing: 0.08em; }
    .tracking-tighter { letter-spacing: -0.01em; }

    @media print {
        body { background-color: white !important; }
        .no-print-padding { padding: 0 !important; }
        .report-paper {
            width: 100%;
            max-width: none;
            box-shadow: none !important;
            padding: 0;
            margin: 0;
        }
        .d-print-none { display: none !important; }
        @page { size: A4; margin: 1.5cm; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection
