{{-- resources/views/auth/verify-email.blade.php --}}
@extends('layouts.guest')

@section('title', 'Verify Email - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="text-center">
    <div class="mb-4">
        <h4>Verify Your Email</h4>
        <p class="text-muted">A verification link has been sent to your email address</p>
    </div>
    
    @if($status)
    <div class="alert alert-success mb-4">
        {{ $status }}
    </div>
    @endif
    
    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <p class="mb-3 text-muted">Didn't receive the email?</p>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-envelope"></i> Resend Verification Email
        </button>
    </form>
    
    <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </form>
</div>
@endsection
