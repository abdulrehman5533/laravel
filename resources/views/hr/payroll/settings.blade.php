@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Salary Components</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                        <i class="fas fa-plus me-1"></i> Add Component
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Calculation</th>
                                    <th>Default</th>
                                    <th>Mandatory</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($components as $component)
                                <tr>
                                    <td>{{ $component->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $component->type === 'earning' ? 'success' : 'danger' }}">
                                            {{ ucfirst($component->type) }}
                                        </span>
                                    </td>
                                    <td>{{ str_replace('_', ' ', $component->calculation_type) }}</td>
                                    <td>{{ number_format($component->default_value, 2) }}</td>
                                    <td>{!! $component->is_mandatory ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Attendance & Shift Rules</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                        <i class="fas fa-plus me-1"></i> Add Rule
                    </button>
                </div>
                <div class="card-body">
                    @foreach($rules as $rule)
                    <div class="border rounded p-3 mb-3 {{ $rule->is_default ? 'border-primary' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">{{ $rule->rule_name }}</h6>
                            @if($rule->is_default)
                            <span class="badge bg-primary">Default</span>
                            @endif
                        </div>
                        <div class="row text-sm">
                            <div class="col-6">
                                <strong>Shift:</strong> {{ $rule->shift_start }} - {{ $rule->shift_end }}
                            </div>
                            <div class="col-6">
                                <strong>Grace:</strong> {{ $rule->grace_time_minutes }} mins
                            </div>
                            <div class="col-6 mt-1">
                                <strong>Half Day:</strong> {{ $rule->half_day_late_minutes }} mins
                            </div>
                            <div class="col-6 mt-1">
                                <strong>OT Multiplier:</strong> {{ $rule->ot_rate_multiplier }}x
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.payroll.settings.components.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Salary Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Component Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. HRA, Medical Allowance">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Calculation Type</label>
                            <select name="calculation_type" class="form-control">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage_of_basic">% of Basic Salary</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Default Value</label>
                        <input type="number" name="default_value" class="form-control" step="0.01" required>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_taxable" value="1" checked id="taxableCheck">
                            <label class="form-check-label" for="taxableCheck">Taxable</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" value="1" id="mandatoryCheck">
                            <label class="form-check-label" for="mandatoryCheck">Mandatory</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Component</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.payroll.settings.rules.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Attendance Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Rule Name</label>
                        <input type="text" name="rule_name" class="form-control" required placeholder="e.g. General Shift">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Shift Start</label>
                            <input type="time" name="shift_start" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Shift End</label>
                            <input type="time" name="shift_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Grace Time (Mins)</label>
                            <input type="number" name="grace_time_minutes" class="form-control" value="15" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Half Day Late (Mins)</label>
                            <input type="number" name="half_day_late_minutes" class="form-control" value="120" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>OT Rate Multiplier</label>
                        <input type="number" name="ot_rate_multiplier" class="form-control" step="0.1" value="1.5" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defaultCheck">
                        <label class="form-check-label" for="defaultCheck">Set as Default Rule</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
