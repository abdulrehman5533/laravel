{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', 'Login | MAGIA LUPOS')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-gold: #af9144;
        --text-main: #111111;
        --text-muted: #888888;
        --input-border: #eeeeee;
    }

    .login-container {
        font-family: 'Inter', sans-serif;
    }

    .login-intro {
        text-align: center;
        margin-bottom: 50px;
    }

    .login-intro h2 {
        font-family: 'Cinzel', serif;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 5px;
        margin: 0;
    }

    .form-group {
        margin-bottom: 35px;
        position: relative;
    }

    .form-label {
        display: block;
        font-size: 9px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 15px;
        transition: color 0.3s;
    }

    .input-wrapper {
        position: relative;
        border-bottom: 1px solid var(--input-border);
        transition: all 0.4s ease;
    }

    .input-wrapper::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 0;
        height: 1px;
        background: var(--primary-gold);
        transition: width 0.4s ease;
    }

    .input-wrapper:focus-within::after {
        width: 100%;
    }

    .input-wrapper:focus-within {
        border-bottom-color: transparent;
    }

    .input-icon {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-gold);
        font-size: 14px;
        opacity: 0.8;
    }

    .form-group input {
        width: 100%;
        border: none;
        background: transparent;
        padding: 12px 0 12px 35px;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        font-weight: 500;
        letter-spacing: 1px;
    }

    .form-group input::placeholder {
        color: #cccccc;
        font-weight: 400;
        text-transform: lowercase;
        font-style: italic;
    }

    .password-toggle {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #ddd;
        cursor: pointer;
        padding: 5px;
        font-size: 12px;
        transition: color 0.3s;
    }

    .password-toggle:hover {
        color: var(--primary-gold);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        margin-bottom: 50px;
    }

    .custom-check {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .custom-check input {
        width: 14px;
        height: 14px;
        border: 1px solid var(--input-border);
        accent-color: var(--primary-gold);
    }

    .check-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .forgot-link {
        font-size: 11px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid transparent;
        transition: all 0.3s;
    }

    .forgot-link:hover {
        color: var(--primary-gold);
        border-bottom-color: var(--primary-gold);
    }

    .btn-login {
        width: 100%;
        padding: 20px;
        background: #111;
        color: #fff;
        border: none;
        font-family: 'Cinzel', serif;
        font-size: 14px;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 6px;
        cursor: pointer;
        transition: all 0.4s;
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: 0.5s;
    }

    .btn-login:hover {
        background: #000;
        letter-spacing: 8px;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .security-footer {
        margin-top: 60px;
        text-align: center;
    }

    .security-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #cccccc;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
    }
</style>

<div class="login-container">
    <div class="login-intro">
        <h2>Authentication Required</h2>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        @if($errors->any())
        <div class="alert mb-5 border-0 rounded-0 p-3" style="background: #000; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 2px;">
            <i class="fas fa-info-circle me-2" style="color: var(--primary-gold);"></i> {{ $errors->first() }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert mb-5 border-0 rounded-0 p-3" style="background: #000; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 2px;">
            <i class="fas fa-exclamation-triangle me-2" style="color: var(--primary-gold);"></i> {{ session('error') }}
        </div>
        @endif
        
        <div class="form-group">
            <label class="form-label">Digital Identity</label>
            <div class="input-wrapper">
                <i class="far fa-user input-icon"></i>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@boutique.com" required autofocus>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Private Key</label>
            <div class="input-wrapper">
                <i class="far fa-shield-keyhole input-icon"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <div class="password-toggle" id="togglePassword">
                    <i class="far fa-eye"></i>
                </div>
            </div>
        </div>
        
        <div class="form-options">
            @if (Route::has('password.request'))
            <a class="forgot-link" href="{{ route('password.request') }}">Reset</a>
            @endif
        </div>
        
        <button type="submit" class="btn-login" id="loginBtn">
            <span id="btnText">Login</span>
        </button>

        <div class="security-footer">
            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span>Secured Access Layer</span>
            </div>
        </div>
    </form>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.querySelector('#loginBtn');
        const text = document.querySelector('#btnText');
        
        // Mark tab as active for session management
        sessionStorage.setItem('magia_tab_active', 'true');
        
        btn.disabled = true;
        btn.style.opacity = '0.8';
        btn.style.cursor = 'wait';
        text.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Entering...';
    });
</script>
@endsection