@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-gavel mr-2"></i>Attendance Rules Management
                    </h5>
                    <a href="{{ route('hr.attendance-rules.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Create Rule
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Branch</th>
                                    <th>Shift Window</th>
                                    <th>Grace Time</th>
                                    <th>Security</th>
                                    <th>OT Multiplier</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules as $rule)
                                <tr>
                                    <td class="font-weight-bold">{{ $rule->rule_name }}</td>
                                    <td>{{ $rule->branch->name ?? 'All Branches' }}</td>
                                    <td>{{ $rule->shift_start }} - {{ $rule->shift_end }}</td>
                                    <td>{{ $rule->grace_time_minutes }} mins</td>
                                    <td>
                                        @if($rule->require_gps) <span class="badge badge-info">GPS</span> @endif
                                        @if($rule->require_selfie) <span class="badge badge-info">Selfie</span> @endif
                                        @if($rule->allowed_ip_range) <span class="badge badge-info">IP Restricted</span> @endif
                                    </td>
                                    <td>{{ $rule->ot_rate_multiplier }}x</td>
                                    <td>
                                        @if($rule->is_default)
                                            <span class="badge badge-success">Default</span>
                                        @else
                                            <span class="badge badge-secondary">Custom</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('hr.attendance-rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('hr.attendance-rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $rule->is_default ? 'disabled' : '' }}>
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
