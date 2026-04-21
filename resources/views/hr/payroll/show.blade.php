@extends('layouts.app')
@section('title', 'Payslip — ' . $payroll->month_year)

@section('content')
<div class="container" style="max-width:900px;">

    {{-- Action Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            @if($payroll->payment_status === 'pending')
            <form action="{{ route('hr.payroll.approve', $payroll->id) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Approve</button>
            </form>
            @elseif($payroll->payment_status === 'approved')
            <form action="{{ route('hr.payroll.pay', $payroll->id) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-primary btn-sm" onclick="return confirm('Mark as paid?')">
                    <i class="fas fa-money-bill me-1"></i>Mark Paid
                </button>
            </form>
            @endif
            <button onclick="window.print()" class="btn btn-dark btn-sm">
                <i class="fas fa-print me-1"></i>Print / Save PDF
            </button>
        </div>
    </div>

    {{-- PAYSLIP --}}
    <div id="payslip" class="bg-white shadow-sm" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">

        {{-- ===== HEADER ===== --}}
        <div style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%); padding:28px 32px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:1.4rem; font-weight:800; color:#f8fafc; letter-spacing:0.5px;">
                        {{ config('app.name') }}
                    </div>
                    <div style="color:#94a3b8; font-size:0.8rem; margin-top:3px;">
                        <i class="fas fa-file-invoice-dollar me-1"></i>SALARY SLIP
                    </div>
                </div>
                <div class="text-end">
                    <div style="font-size:1.1rem; font-weight:700; color:#f1f5f9;">
                        {{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}
                    </div>
                    @php
                        $statusColor = match($payroll->payment_status) {
                            'paid'     => '#22c55e',
                            'approved' => '#38bdf8',
                            default    => '#f59e0b'
                        };
                    @endphp
                    <span style="background:{{ $statusColor }}22; color:{{ $statusColor }}; border:1px solid {{ $statusColor }}44;
                                 padding:3px 12px; border-radius:20px; font-size:0.72rem; font-weight:700; letter-spacing:1px;">
                        {{ strtoupper($payroll->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ===== EMPLOYEE INFO ===== --}}
        <div style="padding:24px 32px; border-bottom:1px solid #e2e8f0; background:#f8fafc;">
            <div class="row g-3">
                <div class="col-md-6">
                    <table style="width:100%; font-size:0.85rem;">
                        <tr>
                            <td style="color:#64748b; width:140px; padding:4px 0; font-weight:600;">Employee Name</td>
                            <td style="font-weight:700; color:#1e293b;">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Employee Code</td>
                            <td style="color:#475569;">{{ $payroll->employee->employee_code }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Designation</td>
                            <td style="color:#475569;">{{ $payroll->employee->designation ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Department</td>
                            <td style="color:#475569;">{{ $payroll->employee->department ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table style="width:100%; font-size:0.85rem;">
                        <tr>
                            <td style="color:#64748b; width:140px; padding:4px 0; font-weight:600;">Branch</td>
                            <td style="color:#475569;">{{ $payroll->employee->branch->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Joining Date</td>
                            <td style="color:#475569;">{{ $payroll->employee->joining_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Pay Period</td>
                            <td style="font-weight:700; color:#1e293b;">{{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b; padding:4px 0; font-weight:600;">Payment Date</td>
                            <td style="color:#475569;">{{ $payroll->paid_on ? $payroll->paid_on->format('d M Y') : '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== ATTENDANCE SUMMARY ===== --}}
        @php
            $breakdown = $payroll->salary_breakdown ?? [];
            $att       = $breakdown['attendance'] ?? [];
            $allowances= $breakdown['allowances'] ?? [];
            $profDed   = $breakdown['profile_deductions'] ?? [];
        @endphp
        @if(!empty($att))
        <div style="padding:20px 32px; border-bottom:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                Attendance Summary
            </div>
            <div class="row g-2">
                @php
                    $attCards = [
                        ['label'=>'Present Days',   'value'=>$att['present_days'] ?? 0,          'color'=>'#22c55e', 'icon'=>'fa-calendar-check'],
                        ['label'=>'Absent Days',    'value'=>$att['absent_days'] ?? 0,           'color'=>'#ef4444', 'icon'=>'fa-calendar-times'],
                        ['label'=>'Late Arrivals',  'value'=>$att['late_count'] ?? 0,            'color'=>'#f59e0b', 'icon'=>'fa-clock'],
                        ['label'=>'Overtime Hours', 'value'=>($att['overtime_hours'] ?? 0).'h',  'color'=>'#3b82f6', 'icon'=>'fa-hourglass-half'],
                    ];
                @endphp
                @foreach($attCards as $card)
                <div class="col-3">
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; text-align:center;">
                        <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }}; font-size:1.1rem;"></i>
                        <div style="font-size:1.3rem; font-weight:800; color:{{ $card['color'] }}; margin-top:4px;">{{ $card['value'] }}</div>
                        <div style="font-size:0.7rem; color:#94a3b8; font-weight:600;">{{ $card['label'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ===== EARNINGS & DEDUCTIONS ===== --}}
        <div style="padding:24px 32px; border-bottom:1px solid #e2e8f0;">
            <div class="row g-4">

                {{-- EARNINGS --}}
                <div class="col-md-6">
                    <div style="font-size:0.7rem; font-weight:700; color:#22c55e; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                        <i class="fas fa-plus-circle me-1"></i>Earnings
                    </div>
                    <table style="width:100%; font-size:0.85rem; border-collapse:collapse;">
                        @php
                            $grossEarnings = 0;
                            $earningRows = [];

                            $earningRows[] = ['label'=>'Basic Salary', 'amount'=>(float)($payroll->basic_salary ?? 0)];

                            if(!empty($allowances['hra']))        $earningRows[] = ['label'=>'House Rent Allowance (HRA)',  'amount'=>(float)$allowances['hra']];
                            if(!empty($allowances['medical']))    $earningRows[] = ['label'=>'Medical Allowance',           'amount'=>(float)$allowances['medical']];
                            if(!empty($allowances['transport']))  $earningRows[] = ['label'=>'Transport Allowance',         'amount'=>(float)$allowances['transport']];
                            if($payroll->overtime_pay > 0)        $earningRows[] = ['label'=>'Overtime Pay',                'amount'=>(float)$payroll->overtime_pay];
                            if($payroll->sales_commission > 0)    $earningRows[] = ['label'=>'Sales Commission',            'amount'=>(float)$payroll->sales_commission];
                            if($payroll->karigar_making_charges > 0) $earningRows[] = ['label'=>'Karigar Making Charges',  'amount'=>(float)$payroll->karigar_making_charges];
                            if($payroll->bonus_performance > 0)   $earningRows[] = ['label'=>'Performance Bonus',          'amount'=>(float)$payroll->bonus_performance];
                            if($payroll->expense_reimbursement > 0) $earningRows[] = ['label'=>'Expense Reimbursement',    'amount'=>(float)$payroll->expense_reimbursement];

                            foreach(($breakdown['components']['earnings'] ?? []) as $c) {
                                $earningRows[] = ['label'=>$c['name'], 'amount'=>(float)$c['amount']];
                            }

                            foreach($earningRows as $row) $grossEarnings += $row['amount'];
                        @endphp
                        @foreach($earningRows as $row)
                        <tr>
                            <td style="padding:6px 0; color:#475569; border-bottom:1px solid #f1f5f9;">{{ $row['label'] }}</td>
                            <td style="padding:6px 0; text-align:right; color:#1e293b; font-weight:600; border-bottom:1px solid #f1f5f9;">
                                PKR {{ number_format($row['amount'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                        <tr style="background:#f0fdf4;">
                            <td style="padding:8px 6px; font-weight:700; color:#15803d; border-radius:4px;">Gross Earnings</td>
                            <td style="padding:8px 6px; text-align:right; font-weight:800; color:#15803d;">
                                PKR {{ number_format($grossEarnings, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- DEDUCTIONS --}}
                <div class="col-md-6">
                    <div style="font-size:0.7rem; font-weight:700; color:#ef4444; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                        <i class="fas fa-minus-circle me-1"></i>Deductions
                    </div>
                    <table style="width:100%; font-size:0.85rem; border-collapse:collapse;">
                        @php
                            $totalDeductions = 0;
                            $deductionRows = [];

                            if(!empty($profDed['income_tax']))  $deductionRows[] = ['label'=>'Income Tax',          'amount'=>(float)$profDed['income_tax']];
                            if(!empty($profDed['eobi']))        $deductionRows[] = ['label'=>'EOBI Contribution',   'amount'=>(float)$profDed['eobi']];
                            if(!empty($profDed['pessi']))       $deductionRows[] = ['label'=>'PESSI / SESSI',       'amount'=>(float)$profDed['pessi']];
                            if($payroll->tax_amount > 0)        $deductionRows[] = ['label'=>'Additional Tax (TDS)','amount'=>(float)$payroll->tax_amount];
                            if($payroll->social_security_contribution > 0) $deductionRows[] = ['label'=>'Social Security', 'amount'=>(float)$payroll->social_security_contribution];
                            if($payroll->loan_deduction > 0)    $deductionRows[] = ['label'=>'Loan EMI Deduction',  'amount'=>(float)$payroll->loan_deduction];
                            if($payroll->late_deduction > 0)    $deductionRows[] = ['label'=>'Late Arrival Deduction','amount'=>(float)$payroll->late_deduction];

                            foreach(($breakdown['components']['deductions'] ?? []) as $d) {
                                $deductionRows[] = ['label'=>$d['name'], 'amount'=>(float)$d['amount']];
                            }

                            foreach($deductionRows as $row) $totalDeductions += $row['amount'];
                        @endphp
                        @forelse($deductionRows as $row)
                        <tr>
                            <td style="padding:6px 0; color:#475569; border-bottom:1px solid #f1f5f9;">{{ $row['label'] }}</td>
                            <td style="padding:6px 0; text-align:right; color:#dc2626; font-weight:600; border-bottom:1px solid #f1f5f9;">
                                - PKR {{ number_format($row['amount'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="padding:6px 0; color:#94a3b8; font-style:italic;">No deductions</td>
                        </tr>
                        @endforelse
                        <tr style="background:#fef2f2;">
                            <td style="padding:8px 6px; font-weight:700; color:#dc2626;">Total Deductions</td>
                            <td style="padding:8px 6px; text-align:right; font-weight:800; color:#dc2626;">
                                - PKR {{ number_format($totalDeductions, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== NET SALARY ===== --}}
        <div style="padding:24px 32px; background:linear-gradient(135deg,#1e293b 0%,#334155 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="color:#94a3b8; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Net Salary Payable</div>
                    <div style="color:#64748b; font-size:0.78rem; margin-top:2px;">
                        {{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}
                        &nbsp;·&nbsp; {{ $payroll->employee->bank_details['bank_name'] ?? 'Bank Transfer' }}
                    </div>
                </div>
                <div class="text-end">
                    <div style="font-size:2rem; font-weight:900; color:#f8fafc; letter-spacing:-0.5px;">
                        PKR {{ number_format($payroll->net_salary, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== BANK DETAILS ===== --}}
        @php $bank = $payroll->employee->bank_details ?? []; @endphp
        @if(!empty($bank['account_number']))
        <div style="padding:16px 32px; background:#f8fafc; border-top:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                <i class="fas fa-university me-1"></i>Bank Details
            </div>
            <div class="row" style="font-size:0.82rem; color:#475569;">
                <div class="col-md-3"><strong>Bank:</strong> {{ $bank['bank_name'] ?? '—' }}</div>
                <div class="col-md-3"><strong>Account:</strong> {{ $bank['account_number'] ?? '—' }}</div>
                <div class="col-md-3"><strong>Title:</strong> {{ $bank['account_name'] ?? '—' }}</div>
                <div class="col-md-3"><strong>Branch Code:</strong> {{ $bank['ifsc_code'] ?? '—' }}</div>
            </div>
        </div>
        @endif

        {{-- ===== SIGNATURES ===== --}}
        <div style="padding:32px 32px 24px; border-top:1px solid #e2e8f0;">
            <div class="row text-center">
                <div class="col-4">
                    <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin:0 20px;">
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600;">Employee Signature</div>
                        <div style="font-size:0.72rem; color:#94a3b8; margin-top:2px;">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin:0 20px;">
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600;">HR Manager</div>
                        <div style="font-size:0.72rem; color:#94a3b8; margin-top:2px;">Human Resources</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin:0 20px;">
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600;">Authorized Signatory</div>
                        <div style="font-size:0.72rem; color:#94a3b8; margin-top:2px;">Management</div>
                    </div>
                </div>
            </div>
            <p style="text-align:center; color:#94a3b8; font-size:0.72rem; margin-top:20px; margin-bottom:0;">
                This is a computer-generated payslip and does not require a physical signature. &nbsp;·&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>

    </div>{{-- end #payslip --}}
</div>

<style>
@media print {
    .d-print-none { display: none !important; }
    body { background: white !important; margin: 0; }
    #payslip { box-shadow: none !important; border: none !important; }
    .container { max-width: 100% !important; padding: 0 !important; }
}
</style>
@endsection
