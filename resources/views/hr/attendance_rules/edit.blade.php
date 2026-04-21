@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-edit mr-2"></i>Edit Attendance Rule: {{ $attendanceRule->rule_name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr.attendance-rules.update', $attendanceRule->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Rule Name</label>
                                <input type="text" name="rule_name" class="form-control" value="{{ $attendanceRule->rule_name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Branch</label>
                                <select name="branch_id" class="form-control">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $attendanceRule->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Shift Start Window</label>
                                <input type="time" name="shift_start" class="form-control" value="{{ $attendanceRule->shift_start }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Shift End Window</label>
                                <input type="time" name="shift_end" class="form-control" value="{{ $attendanceRule->shift_end }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold">Grace Time (Minutes)</label>
                                <input type="number" name="grace_time_minutes" class="form-control" value="{{ $attendanceRule->grace_time_minutes }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold">Half Day Late (Minutes)</label>
                                <input type="number" name="half_day_late_minutes" class="form-control" value="{{ $attendanceRule->half_day_late_minutes }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold">OT Rate Multiplier</label>
                                <input type="number" step="0.1" name="ot_rate_multiplier" class="form-control" value="{{ $attendanceRule->ot_rate_multiplier }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <hr>
                                <h6 class="font-weight-bold text-secondary">Security & Geofencing</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="require_gps" class="custom-control-input" id="require_gps" value="1" {{ $attendanceRule->require_gps ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="require_gps">Require GPS Location</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="require_selfie" class="custom-control-input" id="require_selfie" value="1" {{ $attendanceRule->require_selfie ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="require_selfie">Require Selfie on Punch</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_default" class="custom-control-input" id="is_default" value="1" {{ $attendanceRule->is_default ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_default">Set as Default Rule?</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Allowed IP Range (Optional)</label>
                                <input type="text" name="allowed_ip_range" class="form-control" value="{{ $attendanceRule->allowed_ip_range }}" placeholder="e.g. 192.168.1.*">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Geofencing Radius (Meters)</label>
                                <input type="number" name="geofencing_radius_meters" class="form-control" value="{{ $attendanceRule->geofencing_radius_meters }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Office Latitude</label>
                                <input type="number" step="any" name="office_latitude" class="form-control" value="{{ $attendanceRule->office_latitude }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Office Longitude</label>
                                <input type="number" step="any" name="office_longitude" class="form-control" value="{{ $attendanceRule->office_longitude }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <hr>
                                <h6 class="font-weight-bold text-secondary">Weekly Off Days</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                        <div class="custom-control custom-checkbox mr-3">
                                            <input type="checkbox" name="weekly_off_days[]" class="custom-control-input" id="day_{{ $day }}" value="{{ $day }}" 
                                                {{ in_array($day, $attendanceRule->weekly_off_days ?? []) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="day_{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('hr.attendance-rules.index') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
