<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payslip — {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }} — {{ $payroll->month_year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
            line-height: 1.5;
        }

        .page {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 0;
        }

        /* ===== HEADER ===== */
        .header {
            background: #1e293b;
            padding: 22px 30px;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 200px; height: 100%;
            background: linear-gradient(135deg, transparent 40%, rgba(212,175,55,0.12) 100%);
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #D4AF37;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .brand-tagline {
            font-size: 9px;
            color: #94a3b8;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .payslip-label {
            font-size: 10px;
            color: #94a3b8;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .pay-period {
            font-size: 16px;
            font-weight: 700;
            color: #f1f5f9;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .status-paid     { background: rgba(34,197,94,0.2);  color: #22c55e; border: 1px solid rgba(34,197,94,0.4); }
        .status-approved { background: rgba(56,189,248,0.2); color: #38bdf8; border: 1px solid rgba(56,189,248,0.4); }
        .status-pending  { background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.4); }

        /* ===== GOLD DIVIDER ===== */
        .gold-bar {
            height: 3px;
            background: linear-gradient(90deg, #D4AF37 0%, #c5a02e 50%, #D4AF37 100%);
        }

        /* ===== EMPLOYEE INFO ===== */
        .emp-section {
            background: #f8fafc;
            padding: 18px 30px;
            border-bottom: 1px solid #e2e8f0;
        }
        .emp-table {
            width: 100%;
            border-collapse: collapse;
        }
        .emp-table td {
            padding: 4px 8px 4px 0;
            font-size: 10.5px;
            vertical-align: top;
            width: 25%;
        }
        .emp-label {
            color: #64748b;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 1px;
        }
        .emp-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 11px;
        }

        /* ===== ATTENDANCE STRIP ===== */
        .att-strip {
            background: #ffffff;
            padding: 14px 30px;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-heading {
            font-size: 9px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }
        .att-boxes {
            display: table;
            width: 100%;
        }
        .att-box {
            display: table-cell;
            text-align: center;
            padding: 10px 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            width: 25%;
        }
        .att-box + .att-box { margin-left: 8px; }
        .att-num {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
        }
        .att-lbl {
            font-size: 8.5px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        .c-green  { color: #22c55e; }
        .c-red    { color: #ef4444; }
        .c-yellow { color: #f59e0b; }
        .c-blue   { color: #3b82f6; }

        /* ===== EARNINGS / DEDUCTIONS ===== */
        .body-section {
            padding: 18px 30px;
            border-bottom: 1px solid #e2e8f0;
        }
        .two-col {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
        }
        .col-half {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .comp-table {
            width: 100%;
            border-collapse: collapse;
        }
        .comp-table tr td {
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10.5px;
        }
        .comp-table tr:last-child td { border-bottom: none; }
        .comp-table .lbl { color: #475569; }
        .comp-table .amt { text-align: right; font-weight: 600; color: #1e293b; }
        .comp-table .amt-green { text-align: right; font-weight: 600; color: #16a34a; }
        .comp-table .amt-red   { text-align: right; font-weight: 600; color: #dc2626; }
        .total-row td {
            padding: 7px 6px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            border-top: 2px solid #e2e8f0 !important;
            border-bottom: none !important;
        }
        .total-earn { background: #f0fdf4; border-radius: 4px; }
        .total-ded  { background: #fef2f2; border-radius: 4px; }
        .total-earn td { color: #15803d !important; }
        .total-ded  td { color: #dc2626 !important; }

        /* ===== NET SALARY BOX ===== */
        .net-box {
            background: #1e293b;
            padding: 20px 30px;
            display: table;
            width: 100%;
        }
        .net-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }
        .net-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        .net-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .net-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }
        .net-amount {
            font-size: 26px;
            font-weight: 900;
            color: #D4AF37;
            letter-spacing: -0.5px;
        }
        .net-currency {
            font-size: 13px;
            font-weight: 700;
            color: #94a3b8;
            margin-right: 4px;
        }

        /* ===== BANK DETAILS ===== */
        .bank-section {
            padding: 12px 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .bank-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-table td {
            padding: 3px 8px 3px 0;
            font-size: 10px;
            width: 25%;
        }

        /* ===== SIGNATURES ===== */
        .sig-section {
            padding: 24px 30px 18px;
            display: table;
            width: 100%;
        }
        .sig-col {
            display: table-cell;
            text-align: center;
            width: 33.33%;
            padding: 0 10px;
        }
        .sig-line {
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            margin-top: 30px;
        }
        .sig-name {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
        }
        .sig-title {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 10px 30px;
            text-align: center;
        }
        .footer p {
            font-size: 8.5px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .footer .gold-text { color: #D4AF37; font-weight: 700; }

        /* ===== WATERMARK for PAID ===== */
        @if($payroll->payment_status === 'paid')
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(34, 197, 94, 0.06);
            text-transform: uppercase;
            letter-spacing: 10px;
            pointer-events: none;
            z-index: 0;
        }
        @endif
    </style>
</head>
<body>
<div class="page">

    @if($payroll->payment_status === 'paid')
    <div class="watermark">PAID</div>
    @endif

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="brand-name">&#9670; MAGIA LUPOS</div>
                <div class="brand-tagline">Jewellery Management System</div>
            </div>
            <div class="header-right">
                <div class="payslip-label">Salary Slip</div>
                <div class="pay-period">
                    {{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}
                </div>
                @php
                    $statusClass = match($payroll->payment_status) {
                        'paid'     => 'status-paid',
                        'approved' => 'status-approved',
                        default    => 'status-pending',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ strtoupper($payroll->payment_status) }}</span>
            </div>
        </div>
    </div>
    <div class="gold-bar"></div>

    {{-- ===== EMPLOYEE INFO ===== --}}
    <div class="emp-section">
        <table class="emp-table">
            <tr>
                <td>
                    <span class="emp-label">Employee Name</span>
                    <span class="emp-value">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</span>
                </td>
                <td>
                    <span class="emp-label">Employee Code</span>
                    <span class="emp-value">{{ $payroll->employee->employee_code }}</span>
                </td>
                <td>
                    <span class="emp-label">Designation</span>
                    <span class="emp-value">{{ $payroll->employee->designation ?? '—' }}</span>
                </td>
                <td>
                    <span class="emp-label">Department</span>
                    <span class="emp-value">{{ $payroll->employee->department ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding-top:10px;">
                    <span class="emp-label">Branch</span>
                    <span class="emp-value">{{ $payroll->employee->branch->name ?? '—' }}</span>
                </td>
                <td style="padding-top:10px;">
                    <span class="emp-label">Joining Date</span>
                    <span class="emp-value">{{ $payroll->employee->joining_date?->format('d M Y') ?? '—' }}</span>
                </td>
                <td style="padding-top:10px;">
                    <span class="emp-label">Pay Period</span>
                    <span class="emp-value">{{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}</span>
                </td>
                <td style="padding-top:10px;">
                    <span class="emp-label">Payment Date</span>
                    <span class="emp-value">{{ $payroll->paid_on ? $payroll->paid_on->format('d M Y') : 'Pending' }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== ATTENDANCE SUMMARY ===== --}}
    @php
        $breakdown  = $payroll->salary_breakdown ?? [];
        $att        = $breakdown['attendance'] ?? [];
        $allowances = $breakdown['allowances'] ?? [];
        $profDed    = $breakdown['profile_deductions'] ?? [];
    @endphp
    @if(!empty($att))
    <div class="att-strip">
        <div class="section-heading">Attendance Summary</div>
        <table style="width:100%; border-collapse:separate; border-spacing:8px 0;">
            <tr>
                <td style="text-align:center; background:#f0fdf4; border:1px solid #dcfce7; border-radius:6px; padding:10px;">
                    <div class="att-num c-green">{{ $att['present_days'] ?? 0 }}</div>
                    <div class="att-lbl">Present Days</div>
                </td>
                <td style="text-align:center; background:#fef2f2; border:1px solid #fee2e2; border-radius:6px; padding:10px;">
                    <div class="att-num c-red">{{ $att['absent_days'] ?? 0 }}</div>
                    <div class="att-lbl">Absent Days</div>
                </td>
                <td style="text-align:center; background:#fffbeb; border:1px solid #fef3c7; border-radius:6px; padding:10px;">
                    <div class="att-num c-yellow">{{ $att['late_count'] ?? 0 }}</div>
                    <div class="att-lbl">Late Arrivals</div>
                </td>
                <td style="text-align:center; background:#eff6ff; border:1px solid #dbeafe; border-radius:6px; padding:10px;">
                    <div class="att-num c-blue">{{ $att['overtime_hours'] ?? 0 }}h</div>
                    <div class="att-lbl">Overtime Hours</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ===== EARNINGS & DEDUCTIONS ===== --}}
    @php
        $grossEarnings = 0;
        $earningRows   = [];
        $earningRows[] = ['label' => 'Basic Salary',              'amount' => (float)($payroll->basic_salary ?? 0)];
        if(!empty($allowances['hra']))       $earningRows[] = ['label' => 'House Rent Allowance (HRA)', 'amount' => (float)$allowances['hra']];
        if(!empty($allowances['medical']))   $earningRows[] = ['label' => 'Medical Allowance',          'amount' => (float)$allowances['medical']];
        if(!empty($allowances['transport'])) $earningRows[] = ['label' => 'Transport Allowance',        'amount' => (float)$allowances['transport']];
        if($payroll->overtime_pay > 0)       $earningRows[] = ['label' => 'Overtime Pay',               'amount' => (float)$payroll->overtime_pay];
        if($payroll->sales_commission > 0)   $earningRows[] = ['label' => 'Sales Commission',           'amount' => (float)$payroll->sales_commission];
        if($payroll->karigar_making_charges > 0) $earningRows[] = ['label' => 'Karigar Making Charges', 'amount' => (float)$payroll->karigar_making_charges];
        if($payroll->bonus_performance > 0)  $earningRows[] = ['label' => 'Performance Bonus',          'amount' => (float)$payroll->bonus_performance];
        if($payroll->expense_reimbursement > 0) $earningRows[] = ['label' => 'Expense Reimbursement',   'amount' => (float)$payroll->expense_reimbursement];
        foreach(($breakdown['components']['earnings'] ?? []) as $c) {
            $earningRows[] = ['label' => $c['name'], 'amount' => (float)$c['amount']];
        }
        foreach($earningRows as $r) $grossEarnings += $r['amount'];

        $totalDeductions = 0;
        $deductionRows   = [];
        if(!empty($profDed['income_tax'])) $deductionRows[] = ['label' => 'Income Tax',         'amount' => (float)$profDed['income_tax']];
        if(!empty($profDed['eobi']))       $deductionRows[] = ['label' => 'EOBI Contribution',  'amount' => (float)$profDed['eobi']];
        if(!empty($profDed['pessi']))      $deductionRows[] = ['label' => 'PESSI / SESSI',      'amount' => (float)$profDed['pessi']];
        if($payroll->tax_amount > 0 && empty($profDed['income_tax'])) $deductionRows[] = ['label' => 'Income Tax', 'amount' => (float)$payroll->tax_amount];
        if($payroll->social_security_contribution > 0 && empty($profDed['eobi']) && empty($profDed['pessi'])) $deductionRows[] = ['label' => 'Social Security', 'amount' => (float)$payroll->social_security_contribution];
        if($payroll->loan_deduction > 0)  $deductionRows[] = ['label' => 'Loan EMI Deduction', 'amount' => (float)$payroll->loan_deduction];
        if($payroll->late_deduction > 0)  $deductionRows[] = ['label' => 'Late Deduction',     'amount' => (float)$payroll->late_deduction];
        foreach(($breakdown['components']['deductions'] ?? []) as $d) {
            $deductionRows[] = ['label' => $d['name'], 'amount' => (float)$d['amount']];
        }
        foreach($deductionRows as $r) $totalDeductions += $r['amount'];
    @endphp

    <div class="body-section">
        <table style="width:100%; border-collapse:separate; border-spacing:16px 0;">
            <tr>
                {{-- EARNINGS --}}
                <td style="width:50%; vertical-align:top;">
                    <div class="section-heading" style="color:#16a34a;">&#43; Earnings</div>
                    <table class="comp-table">
                        @foreach($earningRows as $row)
                        <tr>
                            <td class="lbl">{{ $row['label'] }}</td>
                            <td class="amt-green">PKR {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row total-earn">
                            <td>Gross Earnings</td>
                            <td style="text-align:right;">PKR {{ number_format($grossEarnings, 2) }}</td>
                        </tr>
                    </table>
                </td>
                {{-- DEDUCTIONS --}}
                <td style="width:50%; vertical-align:top;">
                    <div class="section-heading" style="color:#dc2626;">&#8722; Deductions</div>
                    <table class="comp-table">
                        @forelse($deductionRows as $row)
                        <tr>
                            <td class="lbl">{{ $row['label'] }}</td>
                            <td class="amt-red">- PKR {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="color:#94a3b8; font-style:italic; padding:6px 0;">No deductions</td></tr>
                        @endforelse
                        <tr class="total-row total-ded">
                            <td>Total Deductions</td>
                            <td style="text-align:right;">- PKR {{ number_format($totalDeductions, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== NET SALARY ===== --}}
    <div class="net-box">
        <div class="net-left">
            <div class="net-label">Net Salary Payable</div>
            <div class="net-sub">
                {{ \Carbon\Carbon::createFromFormat('m-Y', $payroll->month_year)->format('F Y') }}
                &nbsp;&bull;&nbsp;
                {{ $payroll->employee->bank_details['bank_name'] ?? 'Bank Transfer' }}
            </div>
        </div>
        <div class="net-right">
            <div class="net-amount">
                <span class="net-currency">PKR</span>{{ number_format($payroll->net_salary, 2) }}
            </div>
        </div>
    </div>

    {{-- ===== BANK DETAILS ===== --}}
    @php $bank = $payroll->employee->bank_details ?? []; @endphp
    @if(!empty($bank['account_number']))
    <div class="bank-section">
        <div class="section-heading">Bank Account Details</div>
        <table class="bank-table">
            <tr>
                <td><span class="emp-label">Bank Name</span><span class="emp-value">{{ $bank['bank_name'] ?? '—' }}</span></td>
                <td><span class="emp-label">Account Title</span><span class="emp-value">{{ $bank['account_name'] ?? '—' }}</span></td>
                <td><span class="emp-label">Account Number</span><span class="emp-value">{{ $bank['account_number'] ?? '—' }}</span></td>
                <td><span class="emp-label">Branch Code</span><span class="emp-value">{{ $bank['ifsc_code'] ?? '—' }}</span></td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ===== SIGNATURES ===== --}}
    <table style="width:100%; border-collapse:collapse; padding:24px 30px;">
        <tr>
            <td style="width:33%; text-align:center; padding:24px 20px 16px;">
                <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin-top:30px;">
                    <div style="font-size:10px; font-weight:700; color:#475569;">Employee Signature</div>
                    <div style="font-size:9px; color:#94a3b8; margin-top:2px;">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</div>
                </div>
            </td>
            <td style="width:33%; text-align:center; padding:24px 20px 16px;">
                <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin-top:30px;">
                    <div style="font-size:10px; font-weight:700; color:#475569;">HR Manager</div>
                    <div style="font-size:9px; color:#94a3b8; margin-top:2px;">Human Resources</div>
                </div>
            </td>
            <td style="width:33%; text-align:center; padding:24px 20px 16px;">
                <div style="border-top:1px solid #cbd5e1; padding-top:8px; margin-top:30px;">
                    <div style="font-size:10px; font-weight:700; color:#475569;">Authorized Signatory</div>
                    <div style="font-size:9px; color:#94a3b8; margin-top:2px;">Management</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== FOOTER ===== --}}
    <div class="footer">
        <p>
            <span class="gold-text">MAGIA LUPOS</span> &bull; Jewellery Management System &bull;
            This is a computer-generated payslip and does not require a physical signature. &bull;
            Generated: {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>

</div>
</body>
</html>
