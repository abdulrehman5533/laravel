{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MAGIA LUPOS')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 stroke=%22%23d4af37%22 stroke-width=%222%22 fill=%22none%22/><path d=%22M50 20L35 45L50 80L65 45L50 20Z%22 fill=%22%23d4af37%22/><path d=%22M42 35L45 30L50 33L55 30L58 35%22 stroke=%22white%22 stroke-width=%222%22 fill=%22none%22/></svg>">
    <link rel="shortcut icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 stroke=%22%23d4af37%22 stroke-width=%222%22 fill=%22none%22/><path d=%22M50 20L35 45L50 80L65 45L50 20Z%22 fill=%22%23d4af37%22/><path d=%22M42 35L45 30L50 33L55 30L58 35%22 stroke=%22white%22 stroke-width=%222%22 fill=%22none%22/></svg>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #d4af37;
            --accent: #34495e;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --radius-sm: 4px;
            --radius-md: 6px;
            --radius-lg: 8px;
            --gold-gradient: linear-gradient(135deg, #d4af37 0%, #c5a02e 100%);
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .sidebar {
            background: var(--sidebar-bg);
            box-shadow: 1px 0 0 rgba(0, 0, 0, 0.05);
            height: 100vh;
            position: fixed;
            width: 260px;
            z-index: 1000;
            transition: all 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            top: 0;
            left: 0;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px 12px;
        }

        .sidebar-content::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            transition: all 0.2s ease-in-out;
            background: #f1f5f9;
            min-height: 100vh;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.mobile-show {
                transform: translateX(0);
                width: 260px;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 16px;
            }
        }
        
        .navbar-custom {
            background: #ffffff;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            box-shadow: var(--card-shadow);
        }
        
        .brand-logo {
            font-size: 1.25rem;
            letter-spacing: 0.5px;
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .nav-link {
            color: #94a3b8;
            padding: 10px 14px;
            margin: 2px 0;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
        }
        
        .nav-link.active {
            background: #334155;
            color: #ffffff !important;
            font-weight: 600;
        }
        
        .nav-link i {
            font-size: 1rem;
            width: 24px;
            margin-right: 8px;
        }
        
        .stat-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }
        
        .btn-gold {
            background: var(--gold-gradient);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
        }
        
        .btn-gold:hover {
            opacity: 0.9;
            color: #ffffff;
        }
        
        .table-custom {
            background: white;
            border-radius: var(--radius-md);
            border: 1px solid #e2e8f0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table-custom thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .form-control, .form-select {
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--info);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .menu-label {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 20px 14px 8px;
        }

        .card {
            border-radius: var(--radius-md);
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
        }

        .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            font-weight: 600;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Brand Section -->
        <div class="brand-section">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('dashboard') }}" class="brand-logo d-flex align-items-center">
                    <svg width="32" height="32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                        <path d="M50 95C74.8528 95 95 74.8528 95 50C95 25.1472 74.8528 5 50 5C25.1472 5 5 25.1472 5 50C5 74.8528 25.1472 95 50 95Z" stroke="#d4af37" stroke-width="2"/>
                        <path d="M50 20L35 45L50 80L65 45L50 20Z" fill="#d4af37"/>
                        <path d="M50 20L25 40L50 80L75 40L50 20Z" stroke="#d4af37" stroke-width="1"/>
                        <path d="M42 35L45 30L50 33L55 30L58 35" stroke="white" stroke-width="2" fill="none"/>
                    </svg>
                    <span class="d-none d-md-inline">MAGIA LUPOS</span>
                </a>
                <button id="sidebarToggle" class="collapse-btn" title="Toggle Sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
        </div>

        <!-- Sidebar Search -->
        <div class="sidebar-search px-3 mb-2">
            <div class="position-relative">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem;"></i>
                <input type="text" class="form-control sidebar-search-input ps-5" placeholder="Quick Search..." id="sidebarSearch">
            </div>
        </div>
        
        <!-- Scrollable Navigation -->
        <div class="sidebar-content">
            <ul class="nav flex-column">
                <div class="menu-label">Main Menu</div>
                <!-- Core Modules -->
                <li class="nav-item">
                    <a href="{{ route(Auth::user()->getDashboardRoute()) }}" class="nav-link {{ request()->routeIs('dashboard', 'manager.dashboard', 'viewer.dashboard', 'accounts.accounting-dashboard.index', 'pos.index') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Girvi Management Module -->
                @can('girvi.view')
                <div class="menu-label">Lending & Girvi</div>
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('girvi.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#girviMenu">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Girvi (Pledge)</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('girvi.*') ? 'show' : '' }}" id="girviMenu">
                    <li class="nav-item">
                        <a href="{{ route('girvi.dashboard') }}" class="nav-link {{ request()->routeIs('girvi.dashboard') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('girvi.loans.index') }}" class="nav-link {{ request()->routeIs('girvi.loans.index') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-file-invoice"></i>
                            <span>Loans Registry</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('girvi.loans.create') }}" class="nav-link {{ request()->routeIs('girvi.loans.create') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-plus-circle"></i>
                            <span>Issue New Loan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('girvi.reports.aging') }}" class="nav-link {{ request()->routeIs('girvi.reports.aging') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-history"></i>
                            <span>Aging Report</span>
                        </a>
                    </li>
                </ul>
                @endcan

                <!-- HR & Payroll Module -->
                @can('hr.view')
                <div class="menu-label">Human Resources</div>
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('hr.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#hrMenu">
                        <i class="fas fa-users-cog"></i>
                        <span>HR & Payroll</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('hr.*') ? 'show' : '' }}" id="hrMenu">
                    <li class="nav-item">
                        <a href="{{ route('hr.employees.index') }}" class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-user-tie"></i>
                            <span>Employees</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.attendance.index') }}" class="nav-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-user-check"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.payroll.index') }}" class="nav-link {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-money-check-alt"></i>
                            <span>Payroll</span>
                        </a>
                    </li>
                </ul>
                @endcan

                @can('calculator.view')
                <li class="nav-item">
                    <a href="{{ route('calculator.index') }}" class="nav-link {{ request()->routeIs('calculator.*') ? 'active' : '' }}">
                        <i class="fas fa-calculator"></i>
                        <span>Price Calculator</span>
                    </a>
                </li>
                @endcan

                @can('gold_rate.view')
                <li class="nav-item">
                    <a href="{{ route('gold-rates.index') }}" class="nav-link {{ request()->routeIs('gold-rates.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Market Rates</span>
                    </a>
                </li>
                @endcan
                
                <!-- Divider -->
                @if(Auth::user()->hasAnyPermission(['inventory.view', 'calculator.view']))
                <div class="menu-label">Inventory & Management</div>
                @endif

                <!-- Inventory & Product Management Module -->
                @can('inventory.view')
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('inventory.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#inventoryMenu" aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}">
                        <i class="fas fa-warehouse"></i>
                        <span>Inventory</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventoryMenu">
                    <li class="nav-item">
                        <a href="{{ route('inventory.products.index') }}" class="nav-link {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-gem"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.products.create') }}" class="nav-link {{ request()->routeIs('inventory.products.create') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Product</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.low-stock-alert') }}" class="nav-link {{ request()->routeIs('inventory.low-stock-alert') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span>Low Stock Alert</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.stock-aging') }}" class="nav-link {{ request()->routeIs('inventory.stock-aging') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Stock Aging</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.wastage-report') }}" class="nav-link {{ request()->routeIs('inventory.wastage-report') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-trash-alt"></i>
                            <span>Wastage Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.multi-location-stock') }}" class="nav-link {{ request()->routeIs('inventory.multi-location-stock') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Multi-Location Stock</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('inventory.warehouses.index') }}" class="nav-link {{ request()->routeIs('inventory.warehouses.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-warehouse"></i>
                            <span>Warehouse & Logistics</span>
                        </a>
                    </li>
                </ul>
                @endcan

                <!-- Weight & Price Calculator Module -->
                @can('calculator.view')
                <li class="nav-item mt-3">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('calculator.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#calculatorMenu" aria-expanded="{{ request()->routeIs('calculator.*') ? 'true' : 'false' }}">
                        <i class="fas fa-calculator me-2" style="color: #667eea;"></i>
                        <span>Weight Calculator</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('calculator.*') ? 'show' : '' }}" id="calculatorMenu">
                    <li class="nav-item">
                        <a href="{{ route('calculator.index') }}" class="nav-link {{ request()->routeIs('calculator.index') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-calculator"></i>
                            <span>Calculator</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('calculator.history') }}" class="nav-link {{ request()->routeIs('calculator.history') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </li>
                </ul>
                @endcan

                <!-- POS & Sales Module -->
                @can('pos.view')
                <div class="menu-label">POS & Transactions</div>
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('pos.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#posMenu" aria-expanded="{{ request()->routeIs('pos.*') ? 'true' : 'false' }}">
                        <i class="fas fa-cash-register"></i>
                        <span>POS & Sales</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('pos.*') ? 'show' : '' }}" id="posMenu">
                    <li class="nav-item">
                        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-home"></i>
                            <span>POS Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.sales.create') }}" class="nav-link {{ request()->routeIs('pos.sales.create') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-plus-circle"></i>
                            <span>New Sale</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.sales.index') }}" class="nav-link {{ request()->routeIs('pos.sales.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-list"></i>
                            <span>Sales</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('pos.payments.index') }}" class="nav-link {{ request()->routeIs('pos.payments.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-credit-card"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.returns.index') }}" class="nav-link {{ request()->routeIs('pos.returns.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Returns/Repairs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.invoices.index') }}" class="nav-link {{ request()->routeIs('pos.invoices.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-receipt"></i>
                            <span>Invoices</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.mobile.index') }}" class="nav-link {{ request()->routeIs('pos.mobile.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile POS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.promotions.index') }}" class="nav-link {{ request()->routeIs('pos.promotions.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-percentage"></i>
                            <span>Promotions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-code-branch"></i>
                            <span>Branches</span>
                        </a>
                    </li>
                </ul>
                @endcan

                @can('purchase.view')
                <div class="menu-label">Supply Chain</div>
                
                <!-- Purchase & Supplier Management -->
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('accounts.suppliers.*', 'accounts.purchases.orders.*', 'accounts.purchases.returns.*', 'accounts.purchases.payments.*', 'accounts.purchases.analytics.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#purchaseMenu" aria-expanded="{{ request()->routeIs('accounts.suppliers.*', 'accounts.purchases.orders.*', 'accounts.purchases.returns.*') ? 'true' : 'false' }}">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Purchase & Suppliers</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('accounts.suppliers.*', 'accounts.purchases.orders.*', 'accounts.purchases.returns.*', 'accounts.purchases.payments.*', 'accounts.purchases.analytics.*') ? 'show' : '' }}" id="purchaseMenu">
                    <li class="nav-item">
                        <a href="{{ route('accounts.suppliers.index') }}" class="nav-link {{ request()->routeIs('accounts.suppliers.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-building"></i>
                            <span>Suppliers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.purchases.orders.index') }}" class="nav-link {{ request()->routeIs('accounts.purchases.orders.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-file-alt"></i>
                            <span>Purchase Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.purchases.orders.overdue') }}" class="nav-link {{ request()->routeIs('accounts.purchases.orders.overdue') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Overdue Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.purchases.returns.index') }}" class="nav-link {{ request()->routeIs('accounts.purchases.returns.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-undo"></i>
                            <span>Purchase Returns</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.purchases.payments.index') }}" class="nav-link {{ request()->routeIs('accounts.purchases.payments.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Supplier Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.purchases.analytics.dashboard') }}" class="nav-link {{ request()->routeIs('accounts.purchases.analytics.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-tools"></i>
                            <span>Asset Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('vendors.ratings.index') }}" class="nav-link {{ request()->routeIs('vendors.ratings.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-star"></i>
                            <span>Vendor Ratings</span>
                        </a>
                    </li>
                </ul>
                @endcan
                
                @can('reports.view')
                <div class="menu-label">Financial Control</div>

                <!-- Accounting & Finance Module -->
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('accounts.accounting-dashboard.*', 'accounts.general-ledger.*', 'accounts.financial-reports.*', 'accounts.bank-payments.*', 'accounts.cashbook.*', 'accounts.expense.*', 'accounts.installment.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#accountingMenu" aria-expanded="{{ request()->routeIs('accounts.accounting-dashboard.*', 'accounts.general-ledger.*', 'accounts.financial-reports.*', 'accounts.bank-payments.*') ? 'true' : 'false' }}">
                        <i class="fas fa-calculator"></i>
                        <span>Accounting & Finance</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('accounts.accounting-dashboard.*', 'accounts.general-ledger.*', 'accounts.financial-reports.*', 'accounts.bank-payments.*', 'accounts.cashbook.*', 'accounts.expense.*', 'accounts.installment.*') ? 'show' : '' }}" id="accountingMenu">
                    <li class="nav-item">
                        <a href="{{ route('accounts.accounting-dashboard.index') }}" class="nav-link {{ request()->routeIs('accounts.accounting-dashboard.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Accounting Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.general-ledger.index') }}" class="nav-link {{ request()->routeIs('accounts.general-ledger.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-book"></i>
                            <span>General Ledger</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.financial-reports.trial-balance') }}" class="nav-link {{ request()->routeIs('accounts.financial-reports.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-file-invoice"></i>
                            <span>Financial Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.bank-payments.index') }}" class="nav-link {{ request()->routeIs('accounts.bank-payments.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-university"></i>
                            <span>Bank & Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.cashbook.index') }}" class="nav-link {{ request()->routeIs('accounts.cashbook.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-money-bill"></i>
                            <span>Cashbook</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.expense.index') }}" class="nav-link {{ request()->routeIs('accounts.expense.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-credit-card"></i>
                            <span>Expenses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.installment.index') }}" class="nav-link {{ request()->routeIs('accounts.installment.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-calendar-check"></i>
                            <span>Installments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('compliance.tax.index') }}" class="nav-link {{ request()->routeIs('compliance.tax.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Tax & Compliance</span>
                        </a>
                    </li>
                </ul>
                @endcan

                @can('crm.view')
                <div class="menu-label">Customer Relations</div>

                <!-- CRM & Customer Management -->
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('crm.*') || request()->routeIs('customers.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#crmMenu" aria-expanded="{{ request()->routeIs('crm.*') || request()->routeIs('customers.*') ? 'true' : 'false' }}">
                        <i class="fas fa-handshake"></i>
                        <span>CRM Management</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('crm.*') || request()->routeIs('customers.*') ? 'show' : '' }}" id="crmMenu">
                    <li class="nav-item">
                        <a href="{{ route('crm.dashboard') }}" class="nav-link {{ request()->routeIs('crm.dashboard') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-home"></i>
                            <span>CRM Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('crm.interactions.index') }}" class="nav-link {{ request()->routeIs('crm.interactions.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-comments"></i>
                            <span>Interactions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('crm.loyalty.index') }}" class="nav-link {{ request()->routeIs('crm.loyalty.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-medal"></i>
                            <span>Loyalty Programs</span>
                        </a>
                    </li>
                </ul>
                @endcan

                <!-- Service & Repair Module -->
                @can('service.view')
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('service.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#serviceMenu" aria-expanded="{{ request()->routeIs('service.*') ? 'true' : 'false' }}">
                        <i class="fas fa-wrench"></i>
                        <span>Service Management</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('service.*') ? 'show' : '' }}" id="serviceMenu">
                    <li class="nav-item">
                        <a href="{{ route('service.dashboard') }}" class="nav-link {{ request()->routeIs('service.dashboard') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-home"></i>
                            <span>Service Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('service.jobs.index') }}" class="nav-link {{ request()->routeIs('service.jobs.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-tasks"></i>
                            <span>Service Jobs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('service.jobs.urgent') }}" class="nav-link {{ request()->routeIs('service.jobs.urgent') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Urgent Jobs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('service.jobs.overdue') }}" class="nav-link {{ request()->routeIs('service.jobs.overdue') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-clock"></i>
                            <span>Overdue Jobs</span>
                        </a>
                    </li>
                </ul>
                @endcan

                <!-- Reports & Analytics -->
                @can('reports.view')
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                    <li class="nav-item">
                        <a href="{{ route('reports.dashboard') }}" class="nav-link {{ request()->routeIs('reports.dashboard') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-home"></i>
                            <span>Reports Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.sales.index') }}" class="nav-link {{ request()->routeIs('reports.sales.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Sales Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.financial.index') }}" class="nav-link {{ request()->routeIs('reports.financial.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Financial Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.inventory.index') }}" class="nav-link {{ request()->routeIs('reports.inventory.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-warehouse"></i>
                            <span>Inventory Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.suppliers.index') }}" class="nav-link {{ request()->routeIs('reports.suppliers.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-truck"></i>
                            <span>Supplier Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.performance.index') }}" class="nav-link {{ request()->routeIs('reports.performance.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-chart-line"></i>
                            <span>Performance Reports</span>
                        </a>
                    </li>
                </ul>
                @endcan

                @can('security_manager.view')
                <div class="menu-label">System & Intelligence</div>

                <!-- Security Manager Module -->
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('security-manager.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#securityMenu" aria-expanded="{{ request()->routeIs('security-manager.*') ? 'true' : 'false' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security Manager</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('security-manager.*') ? 'show' : '' }}" id="securityMenu">
                    <li class="nav-item">
                        <a href="{{ route('security-manager.dashboard') }}" class="nav-link {{ request()->routeIs('security-manager.dashboard') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.users.index') }}" class="nav-link {{ request()->routeIs('security-manager.users.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.roles.index') }}" class="nav-link {{ request()->routeIs('security-manager.roles.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-lock"></i>
                            <span>Roles & Permissions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.roles.permission-matrix') }}" class="nav-link {{ request()->routeIs('security-manager.roles.permission-matrix') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-th"></i>
                            <span>Permission Matrix</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.sessions.index') }}" class="nav-link {{ request()->routeIs('security-manager.sessions.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-network-wired"></i>
                            <span>Session Control</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.audit.index') }}" class="nav-link {{ request()->routeIs('security-manager.audit.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-file-alt"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.audit.login-history') }}" class="nav-link {{ request()->routeIs('security-manager.audit.login-history') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-history"></i>
                            <span>Login History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security-manager.settings.index') }}" class="nav-link {{ request()->routeIs('security-manager.settings.index') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-cog"></i>
                            <span>Security Settings</span>
                        </a>
                    </li>
                </ul>
                @endcan

                @can('admin.view')
                <li class="nav-item">
                    <button class="nav-link w-100 text-start {{ request()->routeIs('admin.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#adminMenu">
                        <i class="fas fa-cogs"></i>
                        <span>Enterprise Admin</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                </li>
                <ul class="list-unstyled ps-3 collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminMenu">
                    <li class="nav-item">
                        <a href="{{ route('admin.workflows.index') }}" class="nav-link {{ request()->routeIs('admin.workflows.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-project-diagram"></i>
                            <span>Workflow & Approvals</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.api.index') }}" class="nav-link {{ request()->routeIs('admin.api.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-code"></i>
                            <span>API Manager</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.backup.index') }}" class="nav-link {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-database"></i>
                            <span>Backup & Recovery</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" style="font-size: 0.9rem;">
                            <i class="fas fa-bell"></i>
                            <span>Notifications Config</span>
                        </a>
                    </li>
                </ul>
                @endcan
            </ul>
        </div>
        
        <!-- User Profile Footer -->
        <div class="px-3 pb-3" style="flex-shrink: 0; border-top: 1px solid rgba(212, 175, 55, 0.1); margin-top: auto; padding-top: 15px; background: rgba(0,0,0,0.2);">
            <div class="d-flex align-items-center mb-3">
                <div class="position-relative">
                    <img src="{{ auth()->user()->avatar_url }}" alt="User" class="rounded-circle border-2 border-secondary" width="40" height="40" style="object-fit: cover;">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                </div>
                <div class="ms-2">
                    <div class="fw-bold text-white small" style="letter-spacing: 0.5px;">{{ auth()->user()->name }}</div>
                    <div style="font-size: 0.7rem; color: #9ca3af;">{{ auth()->user()->role?->name ?? 'User' }}</div>
                </div>
                <button class="btn btn-sm btn-link text-secondary ms-auto p-0" title="Account Settings"><i class="fas fa-cog"></i></button>
            </div>
            <div class="d-flex gap-2">
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="w-100">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100 btn-outline-danger border-0" style="background: rgba(239, 68, 68, 0.1); font-size: 0.75rem;">
                        <i class="fas fa-sign-out-alt me-1"></i> LOGOUT
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <!-- Navbar -->
        <nav class="navbar navbar-custom d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center">
                <button id="mobileToggle" class="btn btn-link text-dark d-md-none me-3 p-0">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="navbar-text d-none d-lg-block me-4">
                    <div class="small text-muted mb-0">Market Overview</div>
                    <div class="fw-bold"><i class="fas fa-coins text-warning me-1"></i> GOLD: <span id="liveGoldRate" class="text-success">...</span></div>
                </div>
                <!-- Global Search -->
                <div class="position-relative d-none d-md-block" style="width: 300px;">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control border-0 bg-light rounded-pill ps-5" placeholder="Search anything..." id="globalSearch">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-link text-dark p-0 position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-3" style="width: 300px;">
                        <li class="px-3 py-2 fw-bold border-bottom">Notifications</li>
                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-exclamation-circle text-warning me-2"></i> Stock reaching reorder level</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-info-circle text-info me-2"></i> System update scheduled</a></li>
                    </ul>
                </div>
                <a href="{{ route('pos.index') }}" class="btn btn-gold px-4">
                    <i class="fas fa-cash-register me-2"></i> POS
                </a>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Session Tab Manager - User-wise session kill on tab close -->
    @auth
    <script src="{{ asset('js/session-tab-manager.js') }}"></script>
    @endauth
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $(this).parent()
                });
            });
        });
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        // Live gold rate update
        function updateGoldRate() {
            fetch('/api/gold-rate')
                .then(response => response.json())
                .then(data => {
                    if(data.rate_22k) {
                        document.getElementById('liveGoldRate').textContent = `Rs.${data.rate_22k.toLocaleString()}/g`;
                    }
                });
        }
        setInterval(updateGoldRate, 300000);
        updateGoldRate();
        // Sidebar collapse toggle
        document.getElementById('sidebarToggle').onclick = function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            const icon = this.querySelector('i');
            if(sidebar.classList.contains('collapsed')) {
                icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            } else {
                icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            }
        };

        // Mobile toggle
        document.getElementById('mobileToggle').onclick = function() {
            document.getElementById('sidebar').classList.toggle('mobile-show');
        };
        // Dark/Light mode toggle
        document.getElementById('themeToggle').onclick = function() {
            document.body.classList.toggle('dark-mode');
            if(document.body.classList.contains('dark-mode')) {
                this.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                this.innerHTML = '<i class="fas fa-moon"></i>';
            }
        };
        // Theme presets
        function setTheme(theme) {
            document.body.classList.remove('theme-gold', 'theme-dark', 'theme-light', 'theme-glass');
            if(theme === 'gold') document.body.classList.add('theme-gold');
            if(theme === 'dark') document.body.classList.add('theme-dark');
            if(theme === 'light') document.body.classList.add('theme-light');
            if(theme === 'glass') document.body.classList.add('theme-glass');
        }
        // Onboarding tooltips (example)
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Sidebar search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('sidebarSearch');
            const sidebar = document.querySelector('.sidebar');
            if (!searchInput || !sidebar) return;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const navItems = sidebar.querySelectorAll('.nav-item');
                const menuLabels = sidebar.querySelectorAll('.menu-label');
                const collapses = sidebar.querySelectorAll('.collapse');

                if (!searchTerm) {
                    navItems.forEach(item => item.style.display = '');
                    menuLabels.forEach(label => label.style.display = '');
                    collapses.forEach(collapse => {
                        const trigger = sidebar.querySelector(`[data-bs-target="#${collapse.id}"]`);
                        if (trigger && !trigger.classList.contains('active')) {
                            collapse.classList.remove('show');
                        }
                    });
                    return;
                }

                navItems.forEach(item => item.style.display = 'none');
                menuLabels.forEach(label => label.style.display = 'none');
                collapses.forEach(collapse => collapse.classList.remove('show'));

                navItems.forEach(item => {
                    const link = item.querySelector('.nav-link') || item.querySelector('button.nav-link');
                    if (!link) return;

                    const text = link.textContent.toLowerCase().trim();
                    if (text.includes(searchTerm)) {
                        item.style.display = '';
                        
                        let current = item.parentElement;
                        while (current && current !== sidebar) {
                            if (current.classList.contains('collapse')) {
                                current.classList.add('show');
                                const trigger = sidebar.querySelector(`[data-bs-target="#${current.id}"]`);
                                if (trigger) {
                                    const triggerItem = trigger.closest('.nav-item');
                                    if (triggerItem) triggerItem.style.display = '';
                                }
                            }
                            if (current.classList.contains('nav-item')) {
                                current.style.display = '';
                            }
                            current = current.parentElement;
                        }

                        const targetId = link.getAttribute('data-bs-target');
                        if (targetId && targetId.startsWith('#')) {
                            const targetCollapse = sidebar.querySelector(targetId);
                            if (targetCollapse) {
                                targetCollapse.classList.add('show');
                                targetCollapse.querySelectorAll('.nav-item').forEach(child => {
                                    child.style.display = '';
                                });
                            }
                        }
                    }
                });

                menuLabels.forEach(label => {
                    let next = label.nextElementSibling;
                    let hasVisible = false;
                    while (next && !next.classList.contains('menu-label')) {
                        if (next.style.display !== 'none') {
                            hasVisible = true;
                            break;
                        }
                        next = next.nextElementSibling;
                    }
                    if (hasVisible) label.style.display = '';
                });
            });
        });
    </script>
    <style>
        body.theme-gold {
            background: linear-gradient(120deg, #fffbe6 60%, #f8f9fa 100%) !important;
        }
        body.theme-dark {
            background: #181818 !important;
            color: #e0e0e0 !important;
        }
        body.theme-dark .main-content {
            background: linear-gradient(120deg, #232526 60%, #414345 100%) !important;
        }
        body.theme-dark .navbar-custom {
            background: rgba(44,24,16,0.95) !important;
            color: #fff !important;
        }
        body.theme-dark .sidebar {
            background: rgba(44,24,16,0.98) !important;
            color: #fff !important;
        }
        body.theme-dark .nav-link {
            color: #e0e0e0 !important;
        }
        body.theme-dark .nav-link.active, body.theme-dark .nav-link:hover {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.28) 0%, rgba(255, 215, 0, 0.18) 100%) !important;
            color: #FFD700 !important;
        }
        body.theme-dark .stat-card {
            background: rgba(44,24,16,0.92) !important;
            color: #FFD700 !important;
        }
        body.theme-light {
            background: #f8f9fa !important;
            color: #232526 !important;
        }
        body.theme-glass {
            background: linear-gradient(120deg, #e0eafc 60%, #cfdef3 100%) !important;
        }
    </style>
    <style>
        body.dark-mode {
            background: #181818 !important;
            color: #e0e0e0 !important;
        }
        body.dark-mode .main-content {
            background: linear-gradient(120deg, #232526 60%, #414345 100%) !important;
        }
        body.dark-mode .navbar-custom {
            background: rgba(44,24,16,0.95) !important;
            color: #fff !important;
        }
        body.dark-mode .sidebar {
            background: rgba(44,24,16,0.98) !important;
            color: #fff !important;
        }
        body.dark-mode .nav-link {
            color: #e0e0e0 !important;
        }
        body.dark-mode .nav-link.active, body.dark-mode .nav-link:hover {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.28) 0%, rgba(255, 215, 0, 0.18) 100%) !important;
            color: #FFD700 !important;
        }
        body.dark-mode .stat-card {
            background: rgba(44,24,16,0.92) !important;
            color: #FFD700 !important;
        }
    </style>
    
    @stack('scripts')

    <!-- AI Chatbot Floating Button and Window -->
    <style>
        #aiChatbotBtn {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 1200;
            background: linear-gradient(135deg, #232526 60%, #8B4513 100%);
            color: #FFD700;
            border: none;
            border-radius: 50%;
            width: 64px;
            height: 64px;
            box-shadow: 0 8px 32px rgba(44,24,16,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            cursor: pointer;
        }
        transition: box-shadow 0.2s, background 0.2s;
        #aiChatbotBtn:hover {
            background: linear-gradient(135deg, #181818 60%, #D4AF37 100%);
            color: #fffbe6;
            box-shadow: 0 12px 40px rgba(44,24,16,0.28);
        }
        #aiChatbotWindow {
            position: fixed;
            bottom: 110px;
            right: 32px;
            width: 370px;
            max-width: 95vw;
            height: 520px;
            background: linear-gradient(135deg, #181818 80%, #232526 100%);
            color: #FFD700;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(44,24,16,0.22);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1300;
            border: 1.5px solid #D4AF37;
            animation: chatbot-fade-in 0.3s;
        }
        @keyframes chatbot-fade-in {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #aiChatbotHeader {
            background: linear-gradient(90deg, #232526 60%, #8B4513 100%);
            color: #FFD700;
            padding: 16px 20px 12px 20px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #D4AF37;
        }
        #aiChatbotHeader .close {
            color: #FFD700;
            font-size: 1.3rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        #aiChatbotHeader .close:hover {
            opacity: 1;
        }
        #aiChatbotBody {
            flex: 1;
            padding: 18px 16px 12px 16px;
            background: transparent;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ai-bubble {
            background: rgba(44,24,16,0.92);
            color: #FFD700;
            border-radius: 14px 14px 14px 4px;
            padding: 12px 16px;
            max-width: 85%;
            align-self: flex-start;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(44,24,16,0.10);
            animation: chatbot-fade-in 0.4s;
        }
        .ai-bubble.user {
            background: #D4AF37;
            color: #232526;
            border-radius: 14px 14px 4px 14px;
            align-self: flex-end;
        }
        .ai-timestamp {
            font-size: 0.8rem;
            color: #bfa76a;
            margin-top: 2px;
            margin-bottom: 6px;
            text-align: right;
        }
        /* Thinking Animation */
        .typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .typing-dots span {
            width: 6px;
            height: 6px;
            background-color: #D4AF37;
            border-radius: 50%;
            display: inline-block;
            animation: typing-bounce 1.4s infinite ease-in-out both;
        }
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing-bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
        /* Suggestion Pills */
        .ai-suggestion {
            display: inline-block;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #D4AF37;
            color: #D4AF37;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ai-suggestion:hover {
            background: #D4AF37;
            color: #181818;
        }
        #aiChatbotFooter {
            background: #232526;
            padding: 10px 16px;
            border-top: 1px solid #D4AF37;
            display: flex;
            align-items: center;
        }
        #aiChatbotInput {
            flex: 1;
            background: #181818;
            color: #FFD700;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            margin-right: 8px;
            font-size: 1rem;
        }
        #aiChatbotInput:focus {
            outline: none;
            box-shadow: 0 0 0 2px #D4AF37;
        }
        #aiChatbotSend {
            background: #D4AF37;
            color: #232526;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        #aiChatbotSend:hover {
            background: #FFD700;
        }
    </style>
    <button id="aiChatbotBtn" title="AI Assistant">
        <i class="fas fa-robot"></i>
    </button>
    <div id="aiChatbotWindow">
        <div id="aiChatbotHeader">
            <span><i class="fas fa-robot me-2"></i>AI Assistant</span>
            <span class="close" id="aiChatbotClose">&times;</span>
        </div>
        <div id="aiChatbotBody">
            <div class="ai-bubble">
                <div class="ai-content">
                    Welcome! I am your <strong>MAGIA Native Intel</strong> assistant. How can I help you today?
                    <div style="margin-top: 10px; display: flex; flex-wrap: wrap;">
                        <span class="ai-suggestion" onclick="handleSuggestion('Today\'s sales')">📈 Today's Sales</span>
                        <span class="ai-suggestion" onclick="handleSuggestion('Inventory value')">💰 Stock Value</span>
                        <span class="ai-suggestion" onclick="handleSuggestion('Low stock')">⚠️ Low Stock</span>
                        <span class="ai-suggestion" onclick="handleSuggestion('Gold rates')">✨ Gold Rates</span>
                    </div>
                </div>
                <div class="ai-timestamp">{{ now()->format('h:i A') }}</div>
            </div>
        </div>
        <div id="aiChatbotFooter">
            <input id="aiChatbotInput" type="text" placeholder="Type your question..." autocomplete="off" />
            <button id="aiChatbotSend">Send</button>
        </div>
    </div>
    <script>
        const aiBtn = document.getElementById('aiChatbotBtn');
        const aiWindow = document.getElementById('aiChatbotWindow');
        const aiClose = document.getElementById('aiChatbotClose');
        const aiInput = document.getElementById('aiChatbotInput');
        const aiSend = document.getElementById('aiChatbotSend');
        const aiBody = document.getElementById('aiChatbotBody');
        aiBtn.onclick = function() {
            aiWindow.style.display = 'flex';
        };
        aiClose.onclick = function() {
            aiWindow.style.display = 'none';
        };
        function formatAiText(text) {
            // Simple markdown-style formatting
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }

        function addBubble(text, isUser = false, useTyping = false) {
            const bubble = document.createElement('div');
            bubble.className = 'ai-bubble' + (isUser ? ' user' : '');
            
            const content = document.createElement('div');
            content.className = 'ai-content';
            
            const timestamp = document.createElement('div');
            timestamp.className = 'ai-timestamp';
            timestamp.innerText = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            bubble.appendChild(content);
            bubble.appendChild(timestamp);
            aiBody.appendChild(bubble);
            
            if (isUser || !useTyping) {
                content.innerHTML = isUser ? text : formatAiText(text);
                aiBody.scrollTop = aiBody.scrollHeight;
            } else {
                let i = 0;
                const formatted = formatAiText(text);
                // We need to type the HTML correctly, which is tricky. 
                // For simplicity, we'll type the plain text and then swap to HTML, 
                // or just type by words to keep it snappy and handle tags.
                const words = text.split(' ');
                content.innerHTML = '';
                
                const timer = setInterval(() => {
                    if (i < words.length) {
                        content.innerHTML = formatAiText(words.slice(0, i + 1).join(' '));
                        aiBody.scrollTop = aiBody.scrollHeight;
                        i++;
                    } else {
                        clearInterval(timer);
                    }
                }, 30);
            }
        }

        function handleSuggestion(text) {
            aiInput.value = text;
            aiSend.click();
        }

        async function aiReply(userText) {
            // Show loading bubble with animated dots
            const loading = document.createElement('div');
            loading.className = 'ai-bubble';
            loading.innerHTML = `
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            `;
            aiBody.appendChild(loading);
            aiBody.scrollTop = aiBody.scrollHeight;
            
            try {
                const res = await fetch('/ai-chatbot/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: userText })
                });
                
                if (!res.ok) {
                    const errData = await res.json();
                    throw new Error(errData.answer || 'Server error');
                }

                const data = await res.json();
                loading.remove();
                addBubble(data.answer || 'No answer.', false, true);
            } catch (e) {
                loading.remove();
                addBubble('⚠️ ' + (e.message || 'AI service unavailable.'), false);
                console.error('AI Chat Error:', e);
            }
        }
        aiSend.onclick = function() {
            const val = aiInput.value.trim();
            if (!val) return;
            addBubble(val, true);
            aiInput.value = '';
            aiReply(val);
        };
        aiInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') aiSend.click();
        });
    </script>

    <!-- Session Expiry Warning Modal -->
    <div class="modal fade" id="sessionExpiryModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <div class="display-1 text-warning mb-3">
                            <i class="fas fa-user-shield animate-pulse"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Security Session Update</h3>
                    <p class="text-muted mb-4">Your session is about to expire due to inactivity. For your security, you will be logged out automatically unless you continue.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm" id="stayConnectedBtn">
                            Continue Working
                        </button>
                        <button type="button" class="btn btn-link text-muted" id="manualLogoutBtn">
                            Logout Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * PROFESSIONAL SESSION SECURITY SYSTEM
         * Handles Idle Timeout, Heartbeat, and Single Session Enforcement
         */
        (function() {
            const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
            
            let config = {
                idle_timeout: {{ config('session.lifetime') }}, // Sync with Laravel session lifetime
                warning_threshold: 2,
                heartbeat_interval: 60000
            };

            let timers = {
                idle: null,
                heartbeat: null,
                warning: null
            };

            let isUserIdle = false;

            let modalElement = document.getElementById('sessionExpiryModal');
            let modal = modalElement ? new bootstrap.Modal(modalElement) : null;
            let stayConnectedBtn = document.getElementById('stayConnectedBtn');
            let manualLogoutBtn = document.getElementById('manualLogoutBtn');

            /**
             * Send Heartbeat to server to record last_activity
             */
            function sendHeartbeat() {
                if (!isAuthenticated || isUserIdle) return;

                fetch("{{ route('api.heartbeat') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = "{{ route('login') }}?expired=1&reason=session_lost";
                        return;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.status === 'active') {
                        // Success - session is alive
                        resetIdleTimer(false);
                    } else if (data && data.status === 'expired') {
                        window.location.href = "{{ route('login') }}?expired=1&reason=" + (data.reason || 'idle');
                    }
                })
                .catch(err => console.debug('Heartbeat connectivity issue'));
            }

            /**
             * Reset the inactivity timer
             */
            function resetIdleTimer(triggerHeartbeat = true) {
                clearTimeout(timers.idle);
                clearTimeout(timers.warning);

                if (!isAuthenticated) return;

                if (isUserIdle) {
                    isUserIdle = false;
                    if (triggerHeartbeat) sendHeartbeat();
                }

                // Start warning timer
                const warnAt = (config.idle_timeout - config.warning_threshold) * 60 * 1000;
                if (warnAt > 0 && modal) {
                    timers.warning = setTimeout(() => {
                        isUserIdle = true;
                        modal.show();
                    }, warnAt);
                }

                // Start absolute logout timer (add 10 seconds buffer to let server-side session handle it first if possible)
                timers.idle = setTimeout(() => {
                    performLogout('idle_timeout');
                }, (config.idle_timeout * 60 * 1000) + 10000);
            }

            /**
             * Professional Logout Handler
             */
            function performLogout(reason) {
                const logoutUrl = "{{ route('api.session-logout') }}";
                
                fetch(logoutUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reason: reason })
                }).finally(() => {
                    window.location.href = "{{ route('login') }}?expired=1&reason=" + reason;
                });
            }

            // Event Listeners for Activity
            if (isAuthenticated) {
                const activityEvents = ['mousedown', 'keydown', 'scroll', 'click', 'touchstart'];
                activityEvents.forEach(event => {
                    document.addEventListener(event, () => resetIdleTimer(true), { passive: true });
                });

                if (stayConnectedBtn) {
                    stayConnectedBtn.onclick = () => {
                        if (modal) modal.hide();
                        sendHeartbeat();
                        resetIdleTimer(true);
                    };
                }

                if (manualLogoutBtn) {
                    manualLogoutBtn.onclick = () => {
                        performLogout('manual');
                    };
                }

                // Initialize
                resetIdleTimer(false);
                timers.heartbeat = setInterval(sendHeartbeat, config.heartbeat_interval);
                
                // Handle visibility change (re-sync when coming back to tab)
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        sendHeartbeat();
                        resetIdleTimer(true);
                    }
                });
            }
        })();
    </script>

    <!-- No fragile JS logout hacks. Session is managed server-side only. -->
</body>
</html>