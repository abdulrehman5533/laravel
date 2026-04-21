@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">My Pending Approvals</h2>

    <div class="card shadow">
        <div class="card-body">
            @if($approvals->isEmpty())
                <p class="text-center">No pending approvals found.</p>
            @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Request Type</th>
                        <th>Module ID</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvals as $approval)
                    <tr>
                        <td>{{ $approval->workflow->module_name }}</td>
                        <td>#{{ $approval->workflow->model_id }}</td>
                        <td>{{ $approval->created_at->diffForHumans() }}</td>
                        <td>
                            <form action="{{ route('workflow.approve', $approval->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form action="{{ route('workflow.reject', $approval->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
