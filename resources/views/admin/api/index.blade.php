@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">API Key Manager</h2>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> Use these keys to authenticate external integrations (Mobile POS, E-commerce, etc.)
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Application Name</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apiKeys as $key)
                    <tr>
                        <td>{{ $key['name'] }}</td>
                        <td><code>{{ $key['key'] }}</code></td>
                        <td>
                            <span class="badge {{ $key['status'] == 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($key['status']) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Regenerate</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
