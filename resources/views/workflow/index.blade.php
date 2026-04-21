@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Workflow & Approval Management</h2>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Active Workflows</h6>
            <a href="{{ route('workflow.approvals') }}" class="btn btn-sm btn-info">
                <i class="fas fa-check-double me-1"></i> My Approvals
            </a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workflows as $wf)
                    <tr>
                        <td>{{ $wf->module_name }}</td>
                        <td>
                            <span class="badge {{ $wf->status == 'approved' ? 'bg-success' : ($wf->status == 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($wf->status) }}
                            </span>
                        </td>
                        <td>{{ $wf->creator->name }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">View Details</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
