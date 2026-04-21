@extends('layouts.app')

@section('title', 'Girvi Aging Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-history me-2 text-secondary"></i>Girvi Aging & Risk Report</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Export PDF
            </button>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>Girvi #</th>
                        <th>Customer</th>
                        <th>Principal</th>
                        <th>Interest Due</th>
                        <th>Days Old</th>
                        <th>Risk Level</th>
                        <th>Maturity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($girvis as $girvi)
                    <tr>
                        <td>{{ $girvi->girvi_number }}</td>
                        <td>{{ $girvi->customer->name }}</td>
                        <td>Rs. {{ number_format($girvi->loan_amount, 2) }}</td>
                        <td class="text-danger">Rs. {{ number_format($girvi->interest_accrued - $girvi->interest_paid, 2) }}</td>
                        <td>{{ $girvi->days_old }} Days</td>
                        <td>
                            @if($girvi->risk_indicator === 'High')
                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>HIGH RISK</span>
                            @elseif($girvi->risk_indicator === 'Medium')
                                <span class="badge bg-warning text-dark">MEDIUM</span>
                            @else
                                <span class="badge bg-success">STABLE</span>
                            @endif
                        </td>
                        <td>{{ $girvi->maturity_date ? $girvi->maturity_date->format('d M Y') : 'Open Ended' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
