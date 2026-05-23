@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Setup Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.):</p>
                    <div class="text-center mb-4">
                        <img src="{{ $qrCode }}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <form action="{{ route('2fa.enable') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="code">Verification Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" placeholder="000000" maxlength="6" required>
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Enable 2FA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
