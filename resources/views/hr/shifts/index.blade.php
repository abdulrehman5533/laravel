@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-business-time mr-2"></i>Shift Management
                    </h5>
                    <a href="{{ route('hr.shifts.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Create Shift
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Grace Period</th>
                                    <th>Type</th>
                                    <th>Color</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shifts as $shift)
                                <tr>
                                    <td class="font-weight-bold">{{ $shift->name }}</td>
                                    <td>{{ $shift->start_time }}</td>
                                    <td>{{ $shift->end_time }}</td>
                                    <td>{{ $shift->grace_period_minutes ?? 0 }} mins</td>
                                    <td>
                                        @if($shift->is_night_shift)
                                            <span class="badge badge-dark">Night Shift</span>
                                        @else
                                            <span class="badge badge-info">Day Shift</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="width: 20px; height: 20px; background-color: {{ $shift->color_code }}; border-radius: 4px;"></div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('hr.shifts.edit', $shift->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('hr.shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
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
@endsection
