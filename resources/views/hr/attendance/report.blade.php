@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice mr-2"></i>Attendance Monthly Report
                    </h5>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('hr.attendance.report') }}" method="GET" class="form-inline mr-3">
                            <select name="month" class="form-control form-control-sm mr-2">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="form-control form-control-sm mr-2">
                                @foreach(range(date('Y')-2, date('Y')) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Generate</button>
                        </form>
                        <div class="btn-group">
                            <a href="{{ route('hr.attendance.export-excel', request()->all()) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </a>
                            <a href="{{ route('hr.attendance.export-pdf', request()->all()) }}" class="btn btn-sm btn-danger">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="mr-3 font-weight-bold small text-muted text-uppercase">Legend:</span>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-soft-success mr-1">P</span> <span class="small">Present</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-soft-warning mr-1">L</span> <span class="small">Late</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-soft-danger mr-1">A</span> <span class="small">Absent</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-soft-info mr-1">HD</span> <span class="small">Half Day</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-soft-secondary mr-1">OL</span> <span class="small">Leave</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-primary text-white mr-1">H</span> <span class="small">Holiday</span>
                                </div>
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="status-badge bg-light mr-1 text-muted">W</span> <span class="small">Weekly Off</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm attendance-table">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th class="sticky-col text-left">Employee</th>
                                    @foreach($dates as $date)
                                        <th style="min-width: 35px;">
                                            <div class="small font-weight-bold">{{ \Carbon\Carbon::parse($date)->format('D') }}</div>
                                            <div>{{ \Carbon\Carbon::parse($date)->format('d') }}</div>
                                        </th>
                                    @endforeach
                                    <th class="bg-soft-success">P</th>
                                    <th class="bg-soft-danger">A</th>
                                    <th class="bg-soft-warning">L</th>
                                    <th class="bg-soft-secondary">W</th>
                                    <th class="bg-soft-info">OT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report as $emp)
                                <tr>
                                    <td class="sticky-col font-weight-bold shadow-sm">
                                        {{ $emp['name'] }}<br>
                                        <code class="text-primary small">{{ $emp['code'] }}</code>
                                    </td>
                                    @foreach($dates as $date)
                                        <td class="text-center p-1">
                                            @php $status = $emp['days'][$date]; @endphp
                                            @if($status == 'present')
                                                <span class="status-badge bg-soft-success">P</span>
                                            @elseif($status == 'late')
                                                <span class="status-badge bg-soft-warning">L</span>
                                            @elseif($status == 'absent')
                                                <span class="status-badge bg-soft-danger">A</span>
                                            @elseif($status == 'half_day')
                                                <span class="status-badge bg-soft-info">HD</span>
                                            @elseif($status == 'on_leave')
                                                <span class="status-badge bg-soft-secondary">OL</span>
                                            @elseif($status == 'holiday')
                                                <span class="status-badge bg-primary text-white">H</span>
                                            @elseif($status == 'weekly_off')
                                                <span class="status-badge bg-light text-muted border">W</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center bg-light font-weight-bold text-success">{{ $emp['summary']['present'] }}</td>
                                    <td class="text-center bg-light font-weight-bold text-danger">{{ $emp['summary']['absent'] }}</td>
                                    <td class="text-center bg-light font-weight-bold text-warning">{{ $emp['summary']['late'] }}</td>
                                    <td class="text-center bg-light font-weight-bold text-muted">{{ $emp['summary']['weekly_off'] }}</td>
                                    <td class="text-center bg-light font-weight-bold text-info">{{ $emp['summary']['overtime_hrs'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background-color: #e8f5e9; color: #2e7d32; }
    .bg-soft-danger { background-color: #ffebee; color: #c62828; }
    .bg-soft-warning { background-color: #fffde7; color: #f9a825; }
    .bg-soft-info { background-color: #e3f2fd; color: #1565c0; }
    .bg-soft-secondary { background-color: #f5f5f5; color: #616161; }
    
    .attendance-table th, .attendance-table td {
        vertical-align: middle !important;
        border: 1px solid #dee2e6;
    }
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 10;
        min-width: 150px;
    }
    .table-responsive {
        max-height: 700px;
        overflow-y: auto;
    }
    .status-badge {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
    }
</style>
@endsection
