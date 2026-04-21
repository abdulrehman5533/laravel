@extends('layouts.app')

@section('title', 'Edit Employee: ' . $employee->first_name . ' | ' . config('app.name'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Edit Employee: {{ $employee->first_name }} {{ $employee->last_name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.employees.index') }}">HR Management</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.employees.show', $employee) }}">{{ $employee->employee_code }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-eye me-1"></i> View Profile
            </a>
            <button type="submit" form="employeeForm" class="btn btn-sm btn-primary">
                <i class="fas fa-save me-1"></i> Update Employee
            </button>
        </div>
    </div>

    <form action="{{ route('hr.employees.update', $employee) }}" method="POST" id="employeeForm">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-xl-9 col-lg-8">
                <!-- Section 1: Basic Information -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-user-tie me-2"></i>Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Employee Code <span class="text-danger">*</span></label>
                                <input type="text" name="employee_code" class="form-control form-control-sm" value="{{ old('employee_code', $employee->employee_code) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-sm" value="{{ old('first_name', $employee->first_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name', $employee->last_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $employee->email) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Gender</label>
                                <select name="gender" class="form-select form-select-sm">
                                    <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $employee->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control form-control-sm" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">National ID (Aadhar/Passport)</label>
                                <input type="text" name="national_id" class="form-control form-control-sm" value="{{ old('national_id', $employee->national_id) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tax ID (PAN/VAT)</label>
                                <input type="text" name="tax_id" class="form-control form-control-sm" value="{{ old('tax_id', $employee->tax_id) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Residential Address</label>
                                <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address', $employee->address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Employment Details -->
                <div class="card mb-4 border-0 shadow-sm" id="account-linking">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Employment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select form-select-sm select2" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Department <span class="text-danger">*</span></label>
                                <input type="text" name="department" class="form-control form-control-sm" value="{{ old('department', $employee->department) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Designation <span class="text-danger">*</span></label>
                                <select name="designation" class="form-select form-select-sm" required>
                                    <option value="Manager" {{ old('designation', $employee->designation) == 'Manager' ? 'selected' : '' }}>Manager</option>
                                    <option value="Sales Consultant" {{ old('designation', $employee->designation) == 'Sales Consultant' ? 'selected' : '' }}>Sales Consultant</option>
                                    <option value="Goldsmith" {{ old('designation', $employee->designation) == 'Goldsmith' ? 'selected' : '' }}>Goldsmith (Karigar)</option>
                                    <option value="Appraiser" {{ old('designation', $employee->designation) == 'Appraiser' ? 'selected' : '' }}>Appraiser</option>
                                    <option value="Accountant" {{ old('designation', $employee->designation) == 'Accountant' ? 'selected' : '' }}>Accountant</option>
                                    <option value="Security" {{ old('designation', $employee->designation) == 'Security' ? 'selected' : '' }}>Security</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Link System User</label>
                                <select name="user_id" class="form-select form-select-sm select2">
                                    <option value="">-- Not Linked --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 0.65rem;">Connect this employee to their login account.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Joining Date <span class="text-danger">*</span></label>
                                <input type="date" name="joining_date" class="form-control form-control-sm" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Probation End Date</label>
                                <input type="date" name="probation_end_date" class="form-control form-control-sm" value="{{ old('probation_end_date', $employee->probation_end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Biometric ID</label>
                                <input type="text" name="biometric_id" class="form-control form-control-sm" value="{{ old('biometric_id', $employee->biometric_id) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Country <span class="text-danger">*</span></label>
                                <select name="country_code" class="form-select form-select-sm" required>
                                    <option value="PK" {{ old('country_code', $employee->country_code) == 'PK' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="AE" {{ old('country_code', $employee->country_code) == 'AE' ? 'selected' : '' }}>UAE</option>
                                    <option value="SA" {{ old('country_code', $employee->country_code) == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                    <option value="IN" {{ old('country_code', $employee->country_code) == 'IN' ? 'selected' : '' }}>India</option>
                                    <option value="GB" {{ old('country_code', $employee->country_code) == 'GB' ? 'selected' : '' }}>UK</option>
                                    <option value="US" {{ old('country_code', $employee->country_code) == 'US' ? 'selected' : '' }}>USA</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_karigar" value="1" id="is_karigar" {{ old('is_karigar', $employee->is_karigar) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="is_karigar">
                                        Jewelry Maker (Karigar)?
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Salary & Financial -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-money-check-alt me-2"></i>Salary & Financial Details</h6>
                        <span class="badge bg-success" id="netSalaryBadge">Net: PKR 0</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Basic Salary (PKR) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="base_salary" id="base_salary"
                                        class="form-control @error('base_salary') is-invalid @enderror"
                                        value="{{ old('base_salary', $employee->base_salary) }}" required oninput="calcNet()">
                                </div>
                                @error('base_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">House Rent Allowance (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="hra" id="hra"
                                        class="form-control" value="{{ old('hra', $employee->hra) }}" oninput="calcNet()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Medical Allowance (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="medical_allowance" id="medical_allowance"
                                        class="form-control" value="{{ old('medical_allowance', $employee->medical_allowance) }}" oninput="calcNet()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Transport Allowance (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="transport_allowance" id="transport_allowance"
                                        class="form-control" value="{{ old('transport_allowance', $employee->transport_allowance) }}" oninput="calcNet()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">EOBI Deduction (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="eobi_deduction" id="eobi_deduction"
                                        class="form-control" value="{{ old('eobi_deduction', $employee->eobi_deduction) }}" oninput="calcNet()">
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Employee Old-Age Benefits Institution</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">PESSI / SESSI Deduction (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="pessi_deduction" id="pessi_deduction"
                                        class="form-control" value="{{ old('pessi_deduction', $employee->pessi_deduction) }}" oninput="calcNet()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Income Tax Deduction (PKR)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" name="income_tax" id="income_tax"
                                        class="form-control" value="{{ old('income_tax', $employee->income_tax) }}" oninput="calcNet()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Sales Commission (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="commission_percentage"
                                        class="form-control" value="{{ old('commission_percentage', $employee->commission_percentage) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Performance Score (0-100)</label>
                                <input type="number" name="performance_score" class="form-control form-control-sm"
                                    value="{{ old('performance_score', $employee->performance_score) }}" min="0" max="100">
                            </div>

                            {{-- Net Salary Preview --}}
                            <div class="col-12">
                                <div class="bg-dark text-white rounded p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="small opacity-75 text-uppercase fw-bold">Estimated Net Salary</div>
                                        <div class="small opacity-50">Basic + Allowances - Deductions</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold fs-4" id="netSalaryDisplay">PKR 0.00</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-1 opacity-10"></div>

                            {{-- Bank Details --}}
                            <div class="col-12">
                                <h6 class="fw-bold small text-muted text-uppercase mb-3"><i class="fas fa-university me-1"></i>Bank Account Details</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Bank Name</label>
                                <input type="text" name="bank_details[bank_name]" class="form-control form-control-sm"
                                    value="{{ old('bank_details.bank_name', $employee->bank_details['bank_name'] ?? '') }}" placeholder="e.g. HBL, MCB, UBL">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Account Holder Name</label>
                                <input type="text" name="bank_details[account_name]" class="form-control form-control-sm"
                                    value="{{ old('bank_details.account_name', $employee->bank_details['account_name'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Account Number / IBAN</label>
                                <input type="text" name="bank_details[account_number]" class="form-control form-control-sm"
                                    value="{{ old('bank_details.account_number', $employee->bank_details['account_number'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Branch Code / SWIFT</label>
                                <input type="text" name="bank_details[ifsc_code]" class="form-control form-control-sm"
                                    value="{{ old('bank_details.ifsc_code', $employee->bank_details['ifsc_code'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4">
                <!-- Status & Action Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Employment Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Employee
                            </button>
                            <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-ambulance me-2"></i>Emergency Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Contact Name</label>
                            <input type="text" name="emergency_contact[name]" class="form-control form-control-sm" value="{{ old('emergency_contact.name', $employee->emergency_contact['name'] ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Relationship</label>
                            <input type="text" name="emergency_contact[relationship]" class="form-control form-control-sm" value="{{ old('emergency_contact.relationship', $employee->emergency_contact['relationship'] ?? '') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="emergency_contact[phone]" class="form-control form-control-sm" value="{{ old('emergency_contact.phone', $employee->emergency_contact['phone'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-info"><i class="fas fa-tools me-2"></i>Skills & Expertise</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label small fw-bold">Skills (comma separated)</label>
                        @php
                            $skillsValue = '';
                            if (is_array(old('skills'))) {
                                $skillsValue = implode(', ', old('skills'));
                            } elseif (is_string(old('skills'))) {
                                $skillsValue = old('skills');
                            } elseif (is_array($employee->skills)) {
                                $skillsValue = implode(', ', $employee->skills);
                            }
                        @endphp
                        <textarea name="skills" class="form-control form-control-sm" rows="3" placeholder="Gold Casting, Diamond Grading, Sales...">{{ $skillsValue }}</textarea>
                    </div>
                </div>

                {{-- Salary Summary Preview --}}
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold small text-muted text-uppercase mb-3">Salary Breakdown Preview</h6>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Basic Salary</span>
                            <span id="prev_basic" class="fw-bold">PKR 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">+ Allowances</span>
                            <span id="prev_allow" class="text-success fw-bold">PKR 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">- Deductions</span>
                            <span id="prev_ded" class="text-danger fw-bold">PKR 0</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Net Salary</span>
                            <span id="prev_net" class="text-primary">PKR 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function calcNet() {
    const basic     = parseFloat(document.getElementById('base_salary').value) || 0;
    const hra       = parseFloat(document.getElementById('hra').value) || 0;
    const medical   = parseFloat(document.getElementById('medical_allowance').value) || 0;
    const transport = parseFloat(document.getElementById('transport_allowance').value) || 0;
    const eobi      = parseFloat(document.getElementById('eobi_deduction').value) || 0;
    const pessi     = parseFloat(document.getElementById('pessi_deduction').value) || 0;
    const tax       = parseFloat(document.getElementById('income_tax').value) || 0;

    const allowances = hra + medical + transport;
    const deductions = eobi + pessi + tax;
    const net        = basic + allowances - deductions;

    const fmt = v => 'PKR ' + v.toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    document.getElementById('netSalaryDisplay').textContent = fmt(net);
    document.getElementById('netSalaryBadge').textContent   = 'Net: ' + fmt(net);
    document.getElementById('prev_basic').textContent = fmt(basic);
    document.getElementById('prev_allow').textContent = fmt(allowances);
    document.getElementById('prev_ded').textContent   = fmt(deductions);
    document.getElementById('prev_net').textContent   = fmt(net);
}
document.addEventListener('DOMContentLoaded', calcNet);
</script>
@endpush
@endsection
