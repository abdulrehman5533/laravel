@extends('layouts.app')
@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-calendar-check me-2 text-primary"></i>Attendance Management</h2>
            <p class="text-muted small mb-0">Track, manage and monitor employee attendance</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.attendance.report') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> Monthly Report
            </a>
            <a href="{{ route('hr.attendance.export-excel', request()->all()) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('hr.attendance.export-pdf', request()->all()) }}" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#lockModal">
                <i class="fas fa-lock me-1"></i> Lock Month
            </button>
            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#breakModal">
                <i class="fas fa-coffee me-1"></i> Break
            </button>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manualModal">
                <i class="fas fa-edit me-1"></i> Manual Entry
            </button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#markModal">
                <i class="fas fa-clock me-1"></i> Mark Attendance
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Today's KPI Stats --}}
    <div class="row g-3 mb-4">
        @php
            $kpis = [
                ['label'=>'Total Employees', 'value'=>$todayStats['total'],    'icon'=>'fa-users',          'color'=>'primary'],
                ['label'=>'Present Today',   'value'=>$todayStats['present'],  'icon'=>'fa-user-check',     'color'=>'success'],
                ['label'=>'Absent Today',    'value'=>$todayStats['absent'],   'icon'=>'fa-user-times',     'color'=>'danger'],
                ['label'=>'Late Arrivals',   'value'=>$todayStats['late'],     'icon'=>'fa-clock',          'color'=>'warning'],
                ['label'=>'On Leave',        'value'=>$todayStats['on_leave'], 'icon'=>'fa-calendar-minus', 'color'=>'info'],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-{{ $kpi['color'] }} bg-opacity-10 p-3 text-{{ $kpi['color'] }}" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas {{ $kpi['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;">{{ $kpi['label'] }}</div>
                        <div class="fw-bold fs-4 lh-1 mt-1">{{ $kpi['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('hr.attendance.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', today()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="present"  {{ request('status') == 'present'  ? 'selected' : '' }}>Present</option>
                        <option value="absent"   {{ request('status') == 'absent'   ? 'selected' : '' }}>Absent</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="holiday"  {{ request('status') == 'holiday'  ? 'selected' : '' }}>Holiday</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('hr.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold"><i class="fas fa-list me-2 text-primary"></i>
                Attendance Records —
                <span class="text-muted fw-normal">{{ request('date', today()->toDateString()) }}</span>
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                {{ $attendances->total() }} Records
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 small fw-bold text-muted text-uppercase">Employee</th>
                        <th class="small fw-bold text-muted text-uppercase">Date</th>
                        <th class="small fw-bold text-muted text-uppercase">Clock In</th>
                        <th class="small fw-bold text-muted text-uppercase">Clock Out</th>
                        <th class="small fw-bold text-muted text-uppercase">Working Hours</th>
                        <th class="small fw-bold text-muted text-uppercase">Status</th>
                        <th class="small fw-bold text-muted text-uppercase">Late / OT</th>
                        <th class="small fw-bold text-muted text-uppercase">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    @php
                        $emp = $att->employee;
                        $statusMap = [
                            'present'    => ['success', 'Present'],
                            'verified'   => ['success', 'Verified'],
                            'checked_in' => ['info',    'Checked In'],
                            'checked_out'=> ['primary', 'Checked Out'],
                            'absent'     => ['danger',  'Absent'],
                            'half_day'   => ['warning', 'Half Day'],
                            'on_leave'   => ['secondary','On Leave'],
                            'holiday'    => ['dark',    'Holiday'],
                        ];
                        [$sc, $sl] = $statusMap[$att->status] ?? ['secondary', ucfirst($att->status)];
                        $workHrs = $att->working_hours ?? 0;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            @if($emp)
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width:34px;height:34px;font-size:0.75rem;flex-shrink:0;">
                                    {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">{{ $emp->employee_code }} · {{ $emp->department }}</div>
                                </div>
                            </div>
                            @else
                            <span class="text-muted small">User #{{ $att->user_id }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $att->check_in_time ? $att->check_in_time->format('d M Y') : '—' }}</td>
                        <td>
                            @if($att->check_in_time)
                            <span class="fw-bold text-success">{{ $att->check_in_time->format('h:i A') }}</span>
                            @if($att->check_in_address)
                            <div class="text-muted" style="font-size:0.7rem;"><i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($att->check_in_address, 20) }}</div>
                            @endif
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_out_time)
                            <span class="fw-bold text-danger">{{ $att->check_out_time->format('h:i A') }}</span>
                            @else
                            <span class="badge bg-warning bg-opacity-15 text-warning px-2">Active</span>
                            @endif
                        </td>
                        <td>
                            @if($workHrs > 0)
                            <span class="fw-bold {{ $workHrs >= 8 ? 'text-success' : 'text-warning' }}">
                                {{ number_format($workHrs, 1) }}h
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $sc }} bg-opacity-15 text-{{ $sc }} px-3 py-2">
                                {{ $sl }}
                            </span>
                        </td>
                        <td>
                            @if($att->late_flag)
                            <span class="badge bg-danger bg-opacity-10 text-danger me-1">
                                <i class="fas fa-clock me-1"></i>Late
                            </span>
                            @endif
                            @php $otMins = $att->total_working_seconds ? max(0, ($att->total_working_seconds - 28800) / 60) : 0; @endphp
                            @if($otMins > 0)
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-plus me-1"></i>OT {{ round($otMins) }}m
                            </span>
                            @endif
                            @if(!$att->late_flag && $otMins <= 0)
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:0.7rem;">
                                {{ ucfirst($att->source ?? 'manual') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted">No attendance records found</h6>
                            <p class="text-muted small">Try changing the date or filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
        <div class="p-3 border-top bg-light">
            {{ $attendances->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ===== MARK ATTENDANCE MODAL ===== --}}
<div class="modal fade" id="markModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Mark Attendance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="markForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">PIN (if required)</label>
                        <input type="password" name="pin" class="form-control" placeholder="Enter attendance PIN" maxlength="6">
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <button type="button" onclick="submitAttendance('clock_in')" class="btn btn-success w-100 py-3 fw-bold">
                                <i class="fas fa-sign-in-alt fa-2x d-block mb-2 mx-auto"></i>CLOCK IN
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" onclick="submitAttendance('clock_out')" class="btn btn-danger w-100 py-3 fw-bold">
                                <i class="fas fa-sign-out-alt fa-2x d-block mb-2 mx-auto"></i>CLOCK OUT
                            </button>
                        </div>
                    </div>
                    <div id="markResult" class="mt-3"></div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== BREAK MODAL ===== --}}
<div class="modal fade" id="breakModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-coffee me-2"></i>Toggle Break</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="breakForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Break Type</label>
                        <select name="break_type" class="form-select">
                            <option value="lunch">Lunch Break</option>
                            <option value="tea">Tea Break</option>
                            <option value="personal">Personal Break</option>
                        </select>
                    </div>
                    <button type="button" onclick="submitBreak()" class="btn btn-warning w-100 fw-bold py-2">
                        <i class="fas fa-toggle-on me-2"></i>TOGGLE BREAK
                    </button>
                    <div id="breakResult" class="mt-3"></div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MANUAL ENTRY MODAL ===== --}}
<div class="modal fade" id="manualModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Manual Attendance Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.attendance.manual') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Clock In</label>
                            <input type="time" name="clock_in" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Clock Out</label>
                            <input type="time" name="clock_out" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="half_day">Half Day</option>
                                <option value="on_leave">On Leave</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Reason / Notes <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="Reason for manual entry..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark fw-bold px-4">
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== LOCK MONTH MODAL ===== --}}
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>Lock Attendance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.attendance.lock') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 small">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Locking attendance will prevent any further edits for the selected month. This is typically done before payroll generation.
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Month</label>
                            <select name="month" class="form-select" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Year</label>
                            <select name="year" class="form-select" required>
                                @for($y = now()->year - 1; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary fw-bold px-4"
                        onclick="return confirm('Are you sure? This cannot be undone.')">
                        <i class="fas fa-lock me-2"></i>Lock Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitAttendance(type) {
    const form   = document.getElementById('markForm');
    const data   = new FormData(form);
    const result = document.getElementById('markResult');
    data.append('type', type);

    result.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i>Processing...</div>';

    const send = (fd) => {
        fetch("{{ route('hr.attendance.mark') }}", {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.error) {
                result.innerHTML = `<div class="alert alert-danger border-0 py-2 small"><i class="fas fa-times-circle me-1"></i>${d.error}</div>`;
            } else {
                result.innerHTML = `<div class="alert alert-success border-0 py-2 small"><i class="fas fa-check-circle me-1"></i>${d.message} at ${d.time}</div>`;
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(() => {
            result.innerHTML = '<div class="alert alert-danger border-0 py-2 small">Request failed. Please try again.</div>';
        });
    };

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => { data.append('latitude', pos.coords.latitude); data.append('longitude', pos.coords.longitude); send(data); },
            ()  => send(data)
        );
    } else {
        send(data);
    }
}

function submitBreak() {
    const form   = document.getElementById('breakForm');
    const data   = new FormData(form);
    const result = document.getElementById('breakResult');

    fetch("{{ route('hr.attendance.break') }}", {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) {
            result.innerHTML = `<div class="alert alert-danger border-0 py-2 small">${d.error}</div>`;
        } else {
            result.innerHTML = `<div class="alert alert-success border-0 py-2 small"><i class="fas fa-check-circle me-1"></i>${d.message}</div>`;
            setTimeout(() => location.reload(), 1500);
        }
    });
}
</script>
@endpush
@endsection
