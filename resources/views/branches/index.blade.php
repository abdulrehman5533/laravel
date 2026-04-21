@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Branches</h1>
    <a href="{{ route('branches.create') }}" class="btn btn-primary mb-3">Add Branch</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>City</th>
                <th>State</th>
                <th>Country</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($branches as $branch)
            <tr>
                <td>{{ $branch->id }}</td>
                <td>{{ $branch->name }}</td>
                <td>{{ $branch->code }}</td>
                <td>{{ $branch->city }}</td>
                <td>{{ $branch->state }}</td>
                <td>{{ $branch->country }}</td>
                <td>{{ $branch->is_active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
