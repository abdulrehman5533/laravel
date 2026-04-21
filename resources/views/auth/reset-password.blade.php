{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'Reset Password - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    
    <input type="hidden" name="token" value="{{ $token }}">
    
    <div class="mb-4">
        <h4 class="text-center mb-4">Reset Password</h4>
        <p class="text-muted text-center">Enter your new password</p>
    </div>
    
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" value="{{ $email ?? old('email') }}" required autofocus>
        </div>
        @error('email')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                   id="password" name="password" required>
        </div>
        @error('password')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" 
                   id="password_confirmation" name="password_confirmation" required>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-check"></i> Reset Password
    </button>
    
    <p class="text-center text-muted">
        <a href="{{ route('login') }}" class="text-primary">Back to Login</a>
    </p>
</form>
@endsection
