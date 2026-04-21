@extends('layouts.app')

@section('title', 'Payroll Management')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-money-check-alt me-2 text-primary"></i>Payroll Management</h2>
            <p class="text-muted small mb-0">Generate, approve and disburse employee salaries</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.payroll.settings') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cog me-1"></i> Settings
            </a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-play me-1"></i> Generate Payroll
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        @php
            $totalPending  = $payrolls->where('payment_status', 'pending')->count();
            $totalApproved = $payrolls->where('payment_status', 'approved')->count();
            $totalPaid     = $payrolls->where('payment_status', 'paid')->count();
            $totalNetThisMonth = $payrolls->where('month_year', sprintf('%02d-%d', date('n'), date('Y')))->sum('net_salary');
        @endphp
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-15 p-3 text-warning fs-4"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Pending Approval</div>
                        <div class="fw-bold fs-4">{{ $totalPending }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-15 p-3 text-info fs-4"><i class="fas fa-thumbs-up"></i></div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Approved / Ready</div>
                        <div class="fw-bold fs-4">{{ $totalApproved }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-15 p-3 text-success fs-4"><i class="fas fa-check-double"></i></div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Paid This Month</div>
                        <div class="fw-bold fs-4">{{ $totalPaid }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-15 p-3 text-primary fs-4"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Net Salary (This Month)</div>
                        <div class="fw-bold fs-4">PKR {{ number_format($totalNetThisMonth, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Month/Year</label>
                    <input type="month" name="filter_month" class="form-control form-control-sm"
                        value="{{ request('filter_month', date('Y-m')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="filter_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending"  {{ request('filter_status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('filter_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid"     {{ request('filter_status') == 'paid'     ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search Employee</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
                    <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div id="bulkBar" class="alert alert-primary d-none mb-3 d-flex align-items-center gap-3 shadow-sm border-0">
        <span><strong id="bulkCount">0</strong> selected</span>
        <form id="bulkApproveForm" method="POST" action="{{ route('hr.payroll.bulk-approve') }}" class="d-inline">
            @csrf
            <div id="bulkInputs"></div>
            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Bulk Approve</button>
        </form>
        <form id="bulkPayForm" method="POST" action="{{ route('hr.payroll.bulk-pay') }}" class="d-inline">
            @csrf
            <div id="bulkInputsPay"></div>
            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Mark all selected as paid?')">
                <i class="fas fa-money-bill me-1"></i>Bulk Pay
            </button>
        </form>
        <button class="btn btn-sm btn-outline-secondary" onclick="clearBulk()">Clear</button>
    </div>

    {{-- Payroll Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th>Employee</th>
                            <th>Month</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Overtime</th>
                            <th class="text-end">Commission</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Salary</th>
                            <th>Status</th>
                            <th>Paid On</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                        @php
                            $totalDeductions = ($payroll->tax_amount ?? 0)
                                + ($payroll->social_security_contribution ?? 0)
                                + ($payroll->loan_deduction ?? 0)
                                + ($payroll->late_deduction ?? 0);
                            $totalEarnings = ($payroll->basic_salary ?? 0)
                                + ($payroll->overtime_pay ?? 0)
                                + ($payroll->sales_commission ?? 0)
                                + ($payroll->karigar_making_charges ?? 0)
                                + ($payroll->bonus_performance ?? 0)
                                + ($payroll->expense_reimbursement ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input row-cb" value="{{ $payroll->id }}" onchange="updateBulk()">
                            </td>
                            <td>
                                <div class="fw-bold">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</div>
                                <small class="text-muted">{{ $payroll->employee->employee_code }} · {{ $payroll->employee->designation }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $payroll->month_year }}</span></td>
                            <td class="text-end">PKR {{ number_format($payroll->basic_salary, 0) }}</td>
                            <td class="text-end text-success">
                                @if($payroll->overtime_pay > 0)
                                    +PKR {{ number_format($payroll->overtime_pay, 0) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-info">
                                @php $comm = ($payroll->sales_commission ?? 0) + ($payroll->karigar_making_charges ?? 0); @endphp
                                @if($comm > 0)
                                    +PKR {{ number_format($comm, 0) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-danger">
                                @if($totalDeductions > 0)
                                    -PKR {{ number_format($totalDeductions, 0) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold fs-6">PKR {{ number_format($payroll->net_salary, 0) }}</td>
                            <td>
                                @php
                                    $sc = match($payroll->payment_status) {
                                        'paid'     => 'success',
                                        'approved' => 'info',
                                        default    => 'warning'
                                    };
                                @endphp
                                <span class="badge bg-{{ $sc }} bg-opacity-15 text-{{ $sc }} px-3 py-2">
                                    {{ ucfirst($payroll->payment_status) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $payroll->paid_on ? $payroll->paid_on->format('d M Y') : '—' }}
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm shadow-sm">
                                    <a href="{{ route('hr.payroll.show', $payroll->id) }}"
                                       class="btn btn-white border" title="View Payslip">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    @if($payroll->payment_status === 'pending')
                                    <form action="{{ route('hr.payroll.approve', $payroll->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-white border" title="Approve">
                                            <i class="fas fa-check text-success"></i>
                                        </button>
                                    </form>
                                    @elseif($payroll->payment_status === 'approved')
                                    <form action="{{ route('hr.payroll.pay', $payroll->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-white border" title="Mark Paid"
                                            onclick="return confirm('Mark PKR {{ number_format($payroll->net_salary,0) }} as paid?')">
                                            <i class="fas fa-money-bill text-primary"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('hr.payroll.show', $payroll->id) }}?download=1"
                                       class="btn btn-white border" title="Download Payslip">
                                        <i class="fas fa-download text-secondary"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">No payroll records found</h6>
                                <p class="text-muted small">Generate payroll using the button above</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($payrolls->isNotEmpty())
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="7" class="ps-3 text-end">Total Net Payable:</td>
                            <td class="text-end text-primary fs-6">PKR {{ number_format($payrolls->sum('net_salary'), 0) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Generate Payroll Modal --}}
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.payroll.generate') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-play me-2 text-primary"></i>Generate Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 small">
                        <i class="fas fa-info-circle me-2"></i>
                        This will calculate salary for all <strong>active employees</strong> based on attendance, overtime, commissions and deductions.
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Month</label>
                            <select name="month" class="form-select" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0,0,0,$i,1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Year</label>
                            <select name="year" class="form-select" required>
                                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="overwrite" id="overwriteCheck" value="1">
                            <label class="form-check-label small" for="overwriteCheck">
                                Overwrite existing payroll records for this month
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play me-2"></i>Generate Now
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.row-cb').forEach(cb => cb.checked = this.checked);
        updateBulk();
    });

    function updateBulk() {
        const checked = [...document.querySelectorAll('.row-cb:checked')];
        document.getElementById('bulkCount').textContent = checked.length;
        document.getElementById('bulkBar').classList.toggle('d-none', checked.length === 0);

        ['bulkInputs', 'bulkInputsPay'].forEach(containerId => {
            const c = document.getElementById(containerId);
            c.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'payroll_ids[]'; inp.value = cb.value;
                c.appendChild(inp);
            });
        });
    }

    function clearBulk() {
        document.querySelectorAll('.row-cb, #selectAll').forEach(cb => cb.checked = false);
        updateBulk();
    }
</script>
@endpush
@endsection
