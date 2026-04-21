@extends('layouts.app')

@section('title', 'Service Management Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Service Management Dashboard</h1>
        <a href="{{ route('service.jobs.create') }}" class="btn btn-gold">
            <i class="fas fa-plus-circle me-2"></i> New Service Job
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body p-3 text-center">
                    <p class="text-muted small mb-1">Total Jobs</p>
                    <h3 class="mb-0">{{ $totalJobs }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card h-100 border-start border-primary border-4">
                <div class="card-body p-3 text-center">
                    <p class="text-muted small mb-1">Pending</p>
                    <h3 class="mb-0 text-primary">{{ $pendingJobs }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="card-body p-3 text-center">
                    <p class="text-muted small mb-1">Overdue</p>
                    <h3 class="mb-0 text-danger">{{ $overdueJobs }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="card-body p-3 text-center">
                    <p class="text-muted small mb-1">Completed</p>
                    <h3 class="mb-0 text-success">{{ $completedToday }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <a href="{{ route('accounts.suppliers.index', ['supplier_type' => 'Karigar']) }}" class="text-decoration-none">
                <div class="card stat-card h-100 border-start border-info border-4">
                    <div class="card-body p-3 text-center">
                        <p class="text-muted small mb-1">Karigars</p>
                        <h3 class="mb-0 text-info">{{ $totalKarigars }}</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="card-body p-3 text-center">
                    <p class="text-muted small mb-1">Pending Bills</p>
                    <h3 class="mb-0 text-warning">{{ $pendingKarigarInvoices }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0">Job Work Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('service.jobs.create') }}" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i> New Service Job
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('service.invoices.create') }}" class="btn btn-success w-100 py-3">
                                <i class="fas fa-file-invoice-dollar fa-2x d-block mb-2"></i> Generate Karigar Bill
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('service.invoices.index') }}" class="btn btn-info text-white w-100 py-3">
                                <i class="fas fa-history fa-2x d-block mb-2"></i> Billing History
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('accounts.suppliers.create', ['type' => 'Karigar']) }}" class="btn btn-warning text-white w-100 py-3">
                                <i class="fas fa-user-plus fa-2x d-block mb-2"></i> Add New Karigar
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('service.jobs.urgent') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-exclamation-triangle me-2"></i> Urgent Jobs
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('service.jobs.overdue') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-clock me-2"></i> Overdue Jobs
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('service.reports.karigar') }}" class="btn btn-outline-dark w-100">
                                <i class="fas fa-chart-line me-2"></i> Purity & Wastage Report
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('service.jobs.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-list me-2"></i> View All Jobs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0">Karigar Status Overview</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @php 
                            $statusCounts = \App\Models\ServiceJob::groupBy('status')->select('status', \DB::raw('count(*) as total'))->get();
                        @endphp
                        @foreach(['received', 'checking', 'workshop', 'polishing', 'completed', 'delivered'] as $st)
                            @php $count = $statusCounts->where('status', $st)->first()?->total ?? 0; @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-capitalize">{{ $st }}</span>
                                <span class="badge bg-secondary rounded-pill">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
