@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">System Backup & Recovery</h2>
        <form action="{{ route('admin.backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-database me-2"></i> Run Manual Backup
            </button>
        </form>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="text-center">No backups found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
