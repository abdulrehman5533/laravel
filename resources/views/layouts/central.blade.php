<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Central Admin') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #5a5c69;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
            padding: 15px 20px;
        }
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .nav-link.active {
            color: #fff;
            font-weight: 700;
        }
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-4 text-center border-bottom border-light border-opacity-10">
            <h4 class="mb-0">Central Admin</h4>
            <small class="text-white-50">SaaS Platform</small>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link {{ request()->routeIs('central.dashboard') ? 'active' : '' }}" href="{{ route('central.dashboard') }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('central.tenants.*') ? 'active' : '' }}" href="{{ route('central.tenants.index') }}">
                <i class="fas fa-building"></i> Tenants
            </a>
            <a class="nav-link {{ request()->routeIs('central.plans.*') ? 'active' : '' }}" href="{{ route('central.plans.index') }}">
                <i class="fas fa-tags"></i> Plans
            </a>
            <div class="mt-auto p-4 border-top border-light border-opacity-10">
                <a class="nav-link text-white-50" href="{{ route('dashboard') }}">
                    <i class="fas fa-arrow-left"></i> Back to App
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent text-white-50 w-100 text-start">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
