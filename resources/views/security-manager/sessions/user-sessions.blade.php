@extends('layouts.app')
@section('title', 'User Sessions')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0">{{ $user->name }}'s Sessions</h1>
                <a href="{{ route('security-manager.sessions.index') }}" class="btn btn-outline-dark btn-sm">Back</a>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Active Sessions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f9fafb;">
                        <tr>
                            <th>Session ID</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Last Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSessions as $session)
                        <tr>
                            <td><small class="font-monospace">{{ substr($session->session_id, 0, 16) }}...</small></td>
                            <td><small>{{ $session->ip_address }}</small></td>
                            <td><small>{{ $session->login_at->format('M d, H:i') }}</small></td>
                            <td><small>{{ $session->last_activity_at->diffForHumans() }}</small></td>
                            <td>
                                <form method="POST" action="{{ route('security-manager.sessions.terminate', $session) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Terminate this session?')">Terminate</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No active sessions</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
