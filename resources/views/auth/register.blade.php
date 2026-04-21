@extends('layouts.guest')

@section('title', 'Start Your Free Trial - MAGIA LUPOS')

@section('content')
<style>
    .plan-card {
        cursor: pointer;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    .plan-card:hover {
        border-color: #af9144;
        background: #fffcf5;
    }
    .plan-card.active {
        border-color: #af9144;
        background: #fffcf5;
        box-shadow: 0 0 0 2px #af9144;
    }
    .form-section-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #af9144;
        font-weight: 700;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
</style>

<form method="POST" action="{{ route('register') }}">
    @csrf
    
    <div class="mb-4">
        <h4 class="text-center mb-2">Create Your Enterprise</h4>
        <p class="text-muted text-center small">Set up your jewellery management ecosystem in minutes.</p>
    </div>
    
    @if($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="form-section-title">Business Information</div>
    
    <div class="mb-3">
        <label for="shop_name" class="form-label small fw-bold">Shop Name</label>
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-store text-muted"></i></span>
            <input type="text" class="form-control" id="shop_name" name="shop_name" 
                   value="{{ old('shop_name') }}" required placeholder="e.g. Royal Gems & Diamonds">
        </div>
    </div>

    <div class="mb-3">
        <label for="subdomain" class="form-label small fw-bold">Subdomain</label>
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-globe text-muted"></i></span>
            <input type="text" class="form-control" id="subdomain" name="subdomain" 
                   value="{{ old('subdomain') }}" required placeholder="your-shop-name">
            <span class="input-group-text bg-light">.{{ request()->getHost() }}</span>
        </div>
        <div class="form-text" style="font-size: 0.7rem;">Your unique application URL.</div>
    </div>

    <div class="form-section-title mt-4">Account Owner</div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label small fw-bold">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name') }}" required placeholder="John Doe">
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label small fw-bold">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="{{ old('email') }}" required placeholder="john@example.com">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label small fw-bold">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="col-md-6 mb-4">
            <label for="password_confirmation" class="form-label small fw-bold">Confirm</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
    </div>

    <div class="form-section-title mt-2">Select Your Plan</div>
    <div class="row g-2 mb-4">
        @foreach($plans as $plan)
        <div class="col-4">
            <div class="plan-card p-2 text-center rounded {{ $loop->first ? 'active' : '' }}" 
                 onclick="selectPlan(this, {{ $plan->id }})">
                <div class="fw-bold small">{{ $plan->name }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">₹{{ number_format($plan->price) }}</div>
            </div>
        </div>
        @endforeach
    </div>
    <input type="hidden" name="plan_id" id="plan_id" value="{{ $plans->first()->id ?? '' }}">
    
    <button type="submit" class="btn btn-dark w-100 py-2 mb-3 shadow-sm" style="background: #111; border: 1px solid #af9144;">
        <i class="fas fa-rocket me-2" style="color: #af9144;"></i> CREATE MY PLATFORM
    </button>
    
    <p class="text-center text-muted small">
        Already have an account? <a href="{{ route('login') }}" class="text-primary fw-bold">Sign in</a>
    </p>
</form>

<script>
    function selectPlan(element, planId) {
        document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('plan_id').value = planId;
    }

    // Auto-generate subdomain from shop name
    document.getElementById('shop_name').addEventListener('input', function() {
        const subdomainInput = document.getElementById('subdomain');
        if (!subdomainInput.dataset.manual) {
            subdomainInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });

    document.getElementById('subdomain').addEventListener('input', function() {
        this.dataset.manual = true;
    });
</script>
@endsection
