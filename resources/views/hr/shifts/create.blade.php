@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-plus mr-2"></i>Create New Shift
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr.shifts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label font-weight-bold">Shift Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Morning Shift" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Grace Period (Minutes)</label>
                                <input type="number" name="grace_period_minutes" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Shift Color Code</label>
                                <input type="color" name="color_code" class="form-control form-control-color" value="#007bff">
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_night_shift" class="custom-control-input" id="is_night_shift" value="1">
                                    <label class="custom-control-label" for="is_night_shift">Is Night Shift?</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('hr.shifts.index') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Shift</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
