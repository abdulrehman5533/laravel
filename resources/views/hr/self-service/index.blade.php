@extends('layouts.app')

@section('title', 'Employee Self-Service')

@section('content')
<div class="dashboard-header mb-4">
    <div class="dashboard-header-content">
        <h1 class="dashboard-title">
            <i class="fas fa-user-clock me-2"></i>Self-Service Portal
        </h1>
        <p class="dashboard-subtitle">Manage your attendance and leave requests</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success me-3">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Present (This Month)</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['present_count'] }} Days</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Late Arrivals</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['late_count'] }} Times</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info me-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Leaves</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['pending_leaves'] }} Requests</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary me-3">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Designation</div>
                        <div class="h5 fw-bold mb-0 text-truncate">{{ $employee->designation }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Attendance & Recent History -->
        <div class="col-lg-8">
            <div class="row">
                <!-- Attendance Widget -->
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-12">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-clock text-primary me-2"></i>Attendance Marking</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center border-end">
                                    <h2 id="liveClock" class="fw-bold text-dark mb-0">00:00:00</h2>
                                    <p class="text-muted small mb-2">{{ now()->format('l, d M Y') }}</p>
                                    <div id="currentLocation" class="small text-muted text-truncate px-2" style="max-width: 200px; margin: 0 auto;">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>Detecting location...
                                    </div>
                                </div>
                                <div class="col-md-8 ps-md-4">
                                    <div class="d-grid gap-3">
                                        @if(!$attendance || !$attendance->clock_in)
                                            <button onclick="markAttendance('clock_in')" class="btn btn-primary btn-lg py-3 rounded-12">
                                                <i class="fas fa-sign-in-alt me-2"></i>Check In Now
                                            </button>
                                        @elseif(!$attendance->clock_out)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill">
                                                    <i class="fas fa-check-circle me-1"></i> Checked In at {{ $attendance->clock_in->format('H:i A') }}
                                                </span>
                                                @if($attendance->is_late)
                                                    <span class="badge bg-danger-soft text-danger px-3 py-2 rounded-pill">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> Late by {{ $attendance->late_minutes }} min
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <button onclick="toggleBreak()" class="btn btn-{{ $activeBreak ? 'warning' : 'info' }} w-100 py-3 rounded-12 text-white shadow-sm">
                                                        <i class="fas fa-{{ $activeBreak ? 'play' : 'pause' }} me-2"></i>
                                                        {{ $activeBreak ? 'End Break' : 'Start Break' }}
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button onclick="markAttendance('clock_out')" class="btn btn-danger w-100 py-3 rounded-12 shadow-sm">
                                                        <i class="fas fa-sign-out-alt me-2"></i>Check Out
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-secondary border-0 text-center mb-0 py-4">
                                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                                <h5 class="fw-bold">Shift Completed</h5>
                                                <p class="mb-0">You have completed your shift for today.</p>
                                                <small class="text-muted">In: {{ $attendance->clock_in->format('H:i A') }} | Out: {{ $attendance->clock_out->format('H:i A') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance History -->
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-12">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-history text-muted me-2"></i>Recent Attendance</h5>
                            <span class="badge bg-light text-dark">Last 7 Entries</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Status</th>
                                            <th>Clock In</th>
                                            <th>Clock Out</th>
                                            <th>Work Hrs</th>
                                            <th class="pe-4 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentAttendance as $att)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold">{{ $att->date->format('d M, Y') }}</div>
                                                    <small class="text-muted">{{ $att->date->format('l') }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $att->status === 'present' ? 'success' : ($att->status === 'absent' ? 'danger' : 'warning') }} rounded-pill">
                                                        {{ ucfirst($att->status) }}
                                                    </span>
                                                    @if($att->is_late)
                                                        <span class="badge bg-warning-soft text-warning ms-1" title="Late Arrival">L</span>
                                                    @endif
                                                </td>
                                                <td>{{ $att->clock_in ? $att->clock_in->format('H:i A') : '--' }}</td>
                                                <td>{{ $att->clock_out ? $att->clock_out->format('H:i A') : '--' }}</td>
                                                <td>
                                                    @if($att->clock_in && $att->clock_out)
                                                        {{ $att->clock_in->diffAsCarbonInterval($att->clock_out)->format('%Hh %Im') }}
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td class="pe-4 text-end">
                                                    @if(!$att->is_locked)
                                                        <button class="btn btn-sm btn-light" title="Request Correction" onclick="requestCorrection({{ $att->id }})">
                                                            <i class="fas fa-edit text-muted"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No attendance records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Leave & Balances -->
        <div class="col-lg-4">
            <!-- Leave Balances -->
            <div class="card shadow-sm border-0 rounded-12 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie text-info me-2"></i>Leave Balances</h5>
                </div>
                <div class="card-body">
                    @forelse($stats['leave_balance'] as $balance)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold">{{ $balance->leaveType->name }}</span>
                                <span class="text-muted">{{ $balance->used_days }} / {{ $balance->entitled_days }}</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px;">
                                @php
                                    $percentage = ($balance->entitled_days > 0) ? ($balance->used_days / $balance->entitled_days) * 100 : 0;
                                    $color = $percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'success');
                                @endphp
                                <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-3">No leave balances assigned.</p>
                    @endforelse
                </div>
            </div>

            <!-- Leave Request Form -->
            <div class="card shadow-sm border-0 rounded-12 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-plus text-success me-2"></i>Apply for Leave</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr.self-service.leave.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Leave Type</label>
                                <select name="leave_type_id" class="form-select rounded-8" required>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control rounded-8" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control rounded-8" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Reason</label>
                                <textarea name="reason" class="form-control rounded-8" rows="3" required placeholder="Brief description..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-gold w-100 py-2 rounded-8">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Leave Requests History (Bottom) -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 rounded-12">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list text-muted me-2"></i>Recent Leave History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th class="pe-4">Applied At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $leave)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold">{{ $leave->leaveType->name }}</span>
                                        </td>
                                        <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}</td>
                                        <td>{{ $leave->days_taken }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'cancelled' => 'secondary'
                                                ][$leave->status];
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }} rounded-pill px-3">
                                                {{ ucfirst($leave->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-4">{{ $leave->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No recent leave history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], { hour12: false });
        document.getElementById('liveClock').textContent = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Location Detection and Address Fetching
    function detectLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Fetch address using OpenStreetMap Nominatim
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || "Location detected";
                        document.getElementById('currentLocation').innerHTML = `<i class="fas fa-map-marker-alt text-danger me-1"></i>${address}`;
                        document.getElementById('currentLocation').title = address;
                    })
                    .catch(err => {
                        document.getElementById('currentLocation').innerHTML = `<i class="fas fa-map-marker-alt text-danger me-1"></i>${lat.toFixed(4)}, ${lon.toFixed(4)}`;
                    });
            }, function(error) {
                document.getElementById('currentLocation').innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i>Location access denied`;
            });
        }
    }
    detectLocation();

    function markAttendance(type) {
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by your browser.");
            return;
        }

        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        navigator.geolocation.getCurrentPosition(function(position) {
            fetch("{{ route('hr.self-service.attendance.mark') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    type: type,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }, function(error) {
            alert("Unable to retrieve your location. Attendance cannot be marked without GPS.");
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function toggleBreak() {
        fetch("{{ route('hr.self-service.attendance.break') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                break_type: 'General'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) alert(data.error);
            else {
                location.reload();
            }
        });
    }

    function requestCorrection(attendanceId) {
        Swal.fire({
            title: 'Request Correction',
            text: 'Enter the reason for correction (e.g., missed punch, machine error):',
            input: 'textarea',
            inputPlaceholder: 'Reason for correction...',
            showCancelButton: true,
            confirmButtonText: 'Submit Request',
            showLoaderOnConfirm: true,
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Reason is required');
                    return false;
                }
                // Placeholder for actual API call
                return { success: true };
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Submitted!', 'Your correction request has been sent for approval.', 'success');
            }
        });
    }
</script>
@endpush
@endsection
