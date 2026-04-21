@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Employee Profile: {{ $employee->first_name }} {{ $employee->last_name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.employees.index') }}">HR Management</a></li>
                    <li class="breadcrumb-item active">{{ $employee->employee_code }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.employees.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> List
            </a>
            <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar: Personal Info -->
        <div class="col-xl-4">
            <div class="card card-profile shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mt-3">
                        <h4>{{ $employee->first_name }} {{ $employee->last_name }}</h4>
                        <p class="text-muted mb-1">{{ $employee->designation }} | {{ $employee->department }}</p>
                        <p class="text-muted font-size-sm">{{ $employee->branch->name }}</p>
                        <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'danger' }} mb-2">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Personal Details</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Employee Code</strong>
                            <span>{{ $employee->employee_code }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Email</strong>
                            <span>{{ $employee->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Phone</strong>
                            <span>{{ $employee->phone }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Joining Date</strong>
                            <span>{{ $employee->joining_date->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <strong>Country</strong>
                            <span>{{ $employee->country_code }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-xl-8">
            <div class="nav-wrapper">
                <ul class="nav nav-pills nav-fill flex-column flex-md-row" id="tabs-icons-text" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0 active" id="lifecycle-tab" data-bs-toggle="tab" href="#lifecycle" role="tab">Lifecycle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab">Documents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="attendance-tab" data-bs-toggle="tab" href="#attendance" role="tab">Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="leaves-tab" data-bs-toggle="tab" href="#leaves" role="tab">Leaves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="performance-tab" data-bs-toggle="tab" href="#performance" role="tab">Performance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="finance-tab" data-bs-toggle="tab" href="#finance" role="tab">Loans & Expenses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="compensation-tab" data-bs-toggle="tab" href="#compensation" role="tab">Compensation</a>
                    </li>
                </ul>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <!-- Lifecycle -->
                        <div class="tab-pane fade show active" id="lifecycle" role="tabpanel">
                            <h5 class="mb-4">Career Timeline</h5>
                            <div class="timeline timeline-one-side">
                                @foreach($employee->lifecycleEvents as $event)
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="text-dark text-sm font-weight-bold mb-0">{{ ucfirst($event->event_type) }}</h6>
                                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">{{ $event->effective_date->format('d M Y') }}</p>
                                        </div>
                                        <p class="text-sm mt-1 mb-0">{{ $event->description }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5>Employee Documents</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">Upload New</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->documents as $doc)
                                        <tr>
                                            <td>{{ $doc->title }}</td>
                                            <td>{{ ucfirst($doc->document_type) }}</td>
                                            <td>{{ $doc->expiry_date ? $doc->expiry_date->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $doc->is_verified ? 'success' : 'warning' }}">
                                                    {{ $doc->is_verified ? 'Verified' : 'Pending' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-link text-info p-0"><i class="fas fa-download"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Attendance -->
                        <div class="tab-pane fade" id="attendance" role="tabpanel">
                            <h5 class="mb-4">Recent Attendance (Last 30 Days)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Clock In</th>
                                            <th>Clock Out</th>
                                            <th>Status</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->attendances as $attendance)
                                        <tr>
                                            <td>{{ $attendance->date->format('d M Y') }}</td>
                                            <td>{{ $attendance->clock_in ? $attendance->clock_in->format('h:i A') : '-' }}</td>
                                            <td>{{ $attendance->clock_out ? $attendance->clock_out->format('h:i A') : '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $attendance->working_hours }}h</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Leaves -->
                        <div class="tab-pane fade" id="leaves" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5>Leave History</h5>
                                <a href="{{ route('hr.leaves.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary btn-sm">Apply Leave</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Period</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->leaveApplications as $leave)
                                        <tr>
                                            <td>{{ ucfirst($leave->leave_type) }}</td>
                                            <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}</td>
                                            <td>{{ $leave->total_days }}</td>
                                            <td>
                                                <span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($leave->status) }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($leave->reason, 20) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Performance -->
                        <div class="tab-pane fade" id="performance" role="tabpanel">
                            <h5>Performance Appraisals</h5>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Score</th>
                                            <th>Reviewer</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->appraisals as $appraisal)
                                        <tr>
                                            <td>{{ $appraisal->appraisal_date->format('d M Y') }}</td>
                                            <td>{{ $appraisal->total_score }} / 100</td>
                                            <td>{{ $appraisal->reviewer->name }}</td>
                                            <td>{{ ucfirst($appraisal->status) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Finance -->
                        <div class="tab-pane fade" id="finance" role="tabpanel">
                            <h5 class="mb-3">Active Loans</h5>
                            <div class="table-responsive mb-4">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Remaining</th>
                                            <th>Installment</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->loans as $loan)
                                        <tr>
                                            <td>{{ number_format($loan->amount, 2) }}</td>
                                            <td>{{ number_format($loan->remaining_balance, 2) }}</td>
                                            <td>{{ number_format($loan->monthly_installment, 2) }}</td>
                                            <td>{{ ucfirst($loan->status) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="mb-3">Recent Expenses</h5>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->expenses as $expense)
                                        <tr>
                                            <td>{{ $expense->title }}</td>
                                            <td>{{ number_format($expense->amount, 2) }}</td>
                                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                            <td>{{ ucfirst($expense->status) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Compensation -->
                        <div class="tab-pane fade" id="compensation" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Salary Structure</h5>
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editSalaryModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Component</th>
                                                    <th>Type</th>
                                                    <th>Amount/Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-primary">
                                                    <td><strong>Base Salary</strong></td>
                                                    <td>Earning</td>
                                                    <td>{{ number_format($employee->base_salary, 2) }}</td>
                                                </tr>
                                                @foreach($employee->salaryStructures as $structure)
                                                <tr>
                                                    <td>{{ $structure->component->name }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $structure->component->type === 'earning' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($structure->component->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ number_format($structure->amount, 2) }}
                                                        {{ $structure->component->calculation_type === 'percentage_of_basic' ? '%' : '' }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    @if($employee->is_karigar)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Karigar Making Rates</h5>
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editKarigarModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Rate/Gram</th>
                                                    <th>Rate/Piece</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($employee->karigarRates as $rate)
                                                <tr>
                                                    <td>{{ $rate->item_category }}</td>
                                                    <td>{{ number_format($rate->rate_per_gram, 2) }}</td>
                                                    <td>{{ number_format($rate->rate_per_piece, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No rates defined</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Sales Commission Rates</h5>
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editSalesModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Commission %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($employee->salesCommissionRates as $rate)
                                                <tr>
                                                    <td>{{ $rate->product_category }}</td>
                                                    <td>{{ $rate->commission_percent }}%</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No commission rules</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.employees.documents.store', $employee->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Document Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Document Type</label>
                        <select name="document_type" class="form-control" required>
                            <option value="contract">Contract</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="certification">Certification</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>File</label>
                        <input type="file" name="document" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Salary Structure Modal -->
<div class="modal fade" id="editSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.employees.compensation.salary.update', $employee->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Salary Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Component</label>
                        <select name="component_id" class="form-control" required>
                            @foreach(\App\Models\HR\SalaryComponent::all() as $component)
                            <option value="{{ $component->id }}">{{ $component->name }} ({{ ucfirst($component->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Amount / Percentage</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Karigar Rate Modal -->
@if($employee->is_karigar)
<div class="modal fade" id="editKarigarModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.employees.compensation.karigar.update', $employee->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Karigar Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Category (e.g. Ring, Necklace)</label>
                        <input type="text" name="item_category" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Rate per Gram</label>
                            <input type="number" name="rate_per_gram" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label>Rate per Piece</label>
                            <input type="number" name="rate_per_piece" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Edit Sales Commission Modal -->
<div class="modal fade" id="editSalesModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.employees.compensation.sales.update', $employee->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Sales Commission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Product Category (e.g. Gold, Diamond)</label>
                        <input type="text" name="product_category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Commission Percentage</label>
                        <input type="number" name="commission_percent" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
