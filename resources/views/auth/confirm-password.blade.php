{{-- resources/views/auth/confirm-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'Confirm Password - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    
    <div class="mb-4">
        <h4 class="text-center mb-4">Confirm Your Password</h4>
        <p class="text-muted text-center">Please confirm your password to continue</p>
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
    
    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                   id="password" name="password" required autofocus>
        </div>
        @error('password')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-check"></i> Confirm
    </button>
    
    <p class="text-center text-muted">
        <a href="{{ route('login') }}" class="text-primary">Back to Login</a>
    </p>
</form>
@endsection
