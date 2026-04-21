@extends('layouts.app')

@section('title', 'Urgent Service Jobs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Urgent Service Jobs</h1>
        <a href="{{ route('service.jobs.create') }}" class="btn btn-gold">
            <i class="fas fa-plus-circle me-2"></i> New Service Job
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Job ID</th>
                            <th>Customer</th>
                            <th>Item Description</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Expected Completion</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr>
                                <td><strong>#{{ $job->id }}</strong></td>
                                <td>{{ $job->customer->name ?? 'Unknown' }}</td>
                                <td>{{ Str::limit($job->item_description, 30) }}</td>
                                <td>
                                    @switch($job->status ?? 'received')
                                        @case('received')
                                            <span class="badge bg-info">Received</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge bg-secondary">Delivered</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($job->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($job->is_urgent)
                                        <span class="badge bg-danger">High</span>
                                    @else
                                        <span class="badge bg-warning">Medium</span>
                                    @endif
                                </td>
                                <td>{{ $job->expected_completion_date ? $job->expected_completion_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $job->assigned_to_name ?? 'Unassigned' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('service.jobs.show', $job) }}" class="btn btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('service.jobs.edit', $job) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No urgent service jobs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jobs instanceof \Illuminate\Pagination\Paginator)
                <div class="d-flex justify-content-center mt-3">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
