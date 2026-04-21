@extends('layouts.app')

@section('title', 'Employee Management | ' . config('app.name'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Employee Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">HR Management</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.employees.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Employee
            </a>
        </div>
    </div>

    <!-- Metrics Row (Optional, adding for professional look) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Total Employees</p>
                            <h5 class="fw-bold mb-0">{{ $employees->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-user-check text-success"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Active</p>
                            <h5 class="fw-bold mb-0">{{ $employees->where('status', 'active')->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-hammer text-info"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Karigars</p>
                            <h5 class="fw-bold mb-0">{{ $employees->where('is_karigar', true)->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">On Leave</p>
                            <h5 class="fw-bold mb-0">{{ $employees->where('status', 'on_leave')->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small uppercase">
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="py-3">Branch & Dept</th>
                            <th class="py-3">Designation</th>
                            <th class="py-3">Contact</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <span class="text-primary fw-bold">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $employee->first_name }} {{ $employee->last_name }}</h6>
                                        <span class="text-muted small">{{ $employee->employee_code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $employee->branch->name }}</div>
                                <div class="text-muted extra-small">{{ $employee->department }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $employee->designation }}</span>
                            </td>
                            <td>
                                <div class="small"><i class="fas fa-envelope me-1 text-muted"></i> {{ $employee->email }}</div>
                                <div class="small"><i class="fas fa-phone me-1 text-muted"></i> {{ $employee->phone ?: 'N/A' }}</div>
                            </td>
                            <td>
                                @php
                                    $statusColor = match($employee->status) {
                                        'active' => 'success',
                                        'inactive' => 'danger',
                                        'on_leave' => 'warning',
                                        'terminated' => 'dark',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} px-3">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group">
                                    <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-sm btn-outline-info" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-sm btn-outline-warning" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$employee->user_id)
                                        <a href="{{ route('hr.employees.edit', $employee) }}#account-linking" class="btn btn-sm btn-outline-primary" title="Link System User">
                                            <i class="fas fa-user-plus"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-success" title="User Linked" disabled>
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="{{ asset('assets/img/illustrations/empty.svg') }}" alt="No Data" style="height: 100px;" class="mb-3 opacity-50">
                                <h6 class="text-muted">No employees found</h6>
                                <a href="{{ route('hr.employees.create') }}" class="btn btn-sm btn-primary mt-2">Add First Employee</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.7rem; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
