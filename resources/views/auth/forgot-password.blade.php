{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'Recover Access - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-dark: #A67C00;
        --luxury-black: #1A1A1A;
    }

    .recovery-container {
        animation: fadeInScale 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .recovery-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .recovery-title {
        font-family: 'Playfair Display', serif;
        font-size: 30px;
        color: #2C1810;
        margin-bottom: 12px;
    }

    .recovery-subtitle {
        font-family: 'Inter', sans-serif;
        color: #666;
        font-size: 14px;
        line-height: 1.6;
        padding: 0 20px;
    }

    .form-floating-group {
        position: relative;
        margin-bottom: 30px;
    }

    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gold-primary);
        font-size: 18px;
    }

    .form-floating-group input {
        width: 100%;
        background: #F9F9F9;
        border: 1px solid #E0E0E0;
        border-radius: 14px;
        padding: 16px 16px 16px 52px;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 500;
        color: var(--luxury-black);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-floating-group input:focus {
        background: #FFF;
        border-color: var(--gold-primary);
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        outline: none;
    }

    .btn-recover {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #2C1810 0%, #1A1A1A 100%);
        border: 1px solid #D4AF37;
        border-radius: 14px;
        color: #FFF;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-recover:hover {
        background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold-primary) 100%);
        color: #2C1810;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(212, 175, 55, 0.25);
    }

    .alert-premium {
        background: rgba(46, 204, 113, 0.1);
        border-left: 4px solid #2ecc71;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        color: #27ae60;
        font-weight: 500;
        font-size: 14px;
        animation: slideDown 0.5s ease;
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .back-link {
        font-family: 'Inter', sans-serif;
        color: #888;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .back-link:hover {
        color: var(--gold-primary);
    }

    .error-message {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>

<div class="recovery-container">
    <div class="recovery-header">
        <div class="recovery-title">Recover Access</div>
        <div class="recovery-subtitle">Enter your administrative email to receive a secure restoration link</div>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        
        <div class="form-floating-group">
            <i class="fas fa-envelope-open-text input-icon"></i>
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="Administrative Email"
                   required 
                   autofocus>
            @error('email')
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ $message }}</span>
            </div>
            @enderror
        </div>
        
        <button type="submit" class="btn-recover">
            <span>REQUEST LINK</span>
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Return to Elite Access
            </a>
        </div>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = this.querySelector('.btn-recover');
        const span = btn.querySelector('span');
        const icon = btn.querySelector('i');
        
        span.textContent = 'TRANSMITTING...';
        icon.className = 'fas fa-spinner fa-spin';
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.9';
    });
</script>
@endsection
