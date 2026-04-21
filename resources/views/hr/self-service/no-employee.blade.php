@extends('layouts.app')

@section('title', 'Employee Record Required')

@section('content')
<div class="container py-5 text-center">
    <div class="stat-card p-5">
        <div class="display-1 text-muted mb-4">
            <i class="fas fa-user-slash"></i>
        </div>
        <h2 class="fw-bold mb-3">No Employee Record Found</h2>
        <p class="text-muted mb-4 lead">
            Your user account <strong>({{ auth()->user()->email }})</strong> is not currently linked to any employee record in the HR system.
        </p>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card bg-light border-0">
                    <div class="card-body text-start">
                        <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i>How to fix this:</h5>
                        <ol class="mb-0">
                            <li>An <strong>Administrator</strong> or <strong>HR Manager</strong> must go to <strong>HR & Payroll > Employees</strong>.</li>
                            <li>Edit your employee record (or create a new one if it doesn't exist).</li>
                            <li>In the <strong>"System User Link"</strong> dropdown, select your account: <strong>{{ auth()->user()->email }}</strong>.</li>
                            <li>Save the changes.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            @can('hr.view')
            <a href="{{ route('hr.employees.index') }}" class="btn btn-gold px-4">
                <i class="fas fa-users me-2"></i>Manage Employees
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
