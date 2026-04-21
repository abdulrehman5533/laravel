@extends('layouts.app')

@section('title', 'Create Service Job')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create New Service Job</h1>
        <a href="{{ route('service.jobs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Jobs
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('service.jobs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Customer & Item Details -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer & Item Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone ?? $customer->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Karigar (Worker)</label>
                                <select name="karigar_id" class="form-select @error('karigar_id') is-invalid @enderror">
                                    <option value="">Select Karigar</option>
                                    @foreach($karigars as $karigar)
                                        <option value="{{ $karigar->id }}" {{ old('karigar_id') == $karigar->id ? 'selected' : '' }}>
                                            {{ $karigar->name }} ({{ $karigar->company_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('karigar_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Job Type</label>
                                <select name="job_type" class="form-select @error('job_type') is-invalid @enderror">
                                    <option value="repair" {{ old('job_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="manufacturing" {{ old('job_type') == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                    <option value="custom" {{ old('job_type') == 'custom' ? 'selected' : '' }}>Custom Order</option>
                                </select>
                                @error('job_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Service Type</label>
                                <select name="service_type" class="form-select @error('service_type') is-invalid @enderror">
                                    <option value="">Select Service Type</option>
                                    <option value="repair" {{ old('service_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="polish" {{ old('service_type') == 'polish' ? 'selected' : '' }}>Polish</option>
                                    <option value="resize" {{ old('service_type') == 'resize' ? 'selected' : '' }}>Resize</option>
                                    <option value="stone_setting" {{ old('service_type') == 'stone_setting' ? 'selected' : '' }}>Stone Setting</option>
                                    <option value="engraving" {{ old('service_type') == 'engraving' ? 'selected' : '' }}>Engraving</option>
                                    <option value="cleaning" {{ old('service_type') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="custom_order" {{ old('service_type') == 'custom_order' ? 'selected' : '' }}>Custom Order</option>
                                    <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <h6 class="mb-3">Ornaments / Items</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="ornaments-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Ornament Name <span class="text-danger">*</span></th>
                                                <th>Metal Type</th>
                                                <th>Expected Purity</th>
                                                <th>Issued Weight</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="ornament-row">
                                                <td><input type="text" name="items[0][ornament_name]" class="form-control" required></td>
                                                <td>
                                                    <select name="items[0][metal_type]" class="form-select">
                                                        <option value="Gold">Gold</option>
                                                        <option value="Silver">Silver</option>
                                                        <option value="Platinum">Platinum</option>
                                                        <option value="Mixed">Mixed</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" step="0.01" name="items[0][purity_expected]" class="form-control" placeholder="91.6"></td>
                                                <td><input type="number" step="0.001" name="items[0][weight_issued]" class="form-control" placeholder="0.000"></td>
                                                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add-ornament">
                                    <i class="fas fa-plus me-1"></i> Add Another Ornament
                                </button>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Item Description (General)</label>
                                <textarea name="item_description" class="form-control @error('item_description') is-invalid @enderror" 
                                    rows="2" placeholder="Describe the item...">{{ old('item_description') }}</textarea>
                                @error('item_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Karigar Instructions</label>
                                <textarea name="karigar_instructions" class="form-control @error('karigar_instructions') is-invalid @enderror" 
                                    rows="2" placeholder="Instructions specifically for the Karigar...">{{ old('karigar_instructions') }}</textarea>
                                @error('karigar_instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Issue/Work Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                    rows="3" placeholder="Describe the issue or work to be done..." required>{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Timeline -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Timeline & Pricing</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Expected Completion Date <span class="text-danger">*</span></label>
                                <input type="date" name="expected_completion_date" 
                                    class="form-control @error('expected_completion_date') is-invalid @enderror" 
                                    value="{{ old('expected_completion_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                                @error('expected_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estimated Charge</label>
                                <input type="number" step="0.01" name="estimated_charge" 
                                    class="form-control @error('estimated_charge') is-invalid @enderror" 
                                    placeholder="0.00" value="{{ old('estimated_charge') }}">
                                @error('estimated_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Special Instructions</label>
                                <textarea name="special_instructions" class="form-control @error('special_instructions') is-invalid @enderror" 
                                    rows="2" placeholder="Any special instructions...">{{ old('special_instructions') }}</textarea>
                                @error('special_instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Assignment & Status -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-user-cog me-2"></i>Assignment & Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Unassigned</option>
                                @foreach($staff as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_urgent" value="1" class="form-check-input" id="is_urgent" 
                                    {{ old('is_urgent') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_urgent">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i> Mark as Urgent
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-camera me-2"></i>Before Photos</h6>
                    </div>
                    <div class="card-body">
                        <input type="file" name="before_photos[]" class="form-control @error('before_photos') is-invalid @enderror" 
                            multiple accept="image/*">
                        <small class="text-muted d-block mt-2">Upload photos of the item before servicing (optional)</small>
                        @error('before_photos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Create Service Job
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        let ornamentIndex = 1;
        
        $('#add-ornament').click(function() {
            let newRow = `
                <tr class="ornament-row">
                    <td><input type="text" name="items[${ornamentIndex}][ornament_name]" class="form-control" required></td>
                    <td>
                        <select name="items[${ornamentIndex}][metal_type]" class="form-select">
                            <option value="Gold">Gold</option>
                            <option value="Silver">Silver</option>
                            <option value="Platinum">Platinum</option>
                            <option value="Mixed">Mixed</option>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="items[${ornamentIndex}][purity_expected]" class="form-control" placeholder="91.6"></td>
                    <td><input type="number" step="0.001" name="items[${ornamentIndex}][weight_issued]" class="form-control" placeholder="0.000"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
            $('#ornaments-table tbody').append(newRow);
            ornamentIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#ornaments-table tbody tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    });
</script>
@endpush
@endsection