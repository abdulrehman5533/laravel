@extends('layouts.app')

@section('title', 'Edit Service Job')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Service Job #{{ $job->id }}</h1>
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

    <form action="{{ route('service.jobs.update', $job) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $job->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone ?? $customer->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Service Type</label>
                                <select name="service_type" class="form-select @error('service_type') is-invalid @enderror">
                                    <option value="">Select Service Type</option>
                                    <option value="repair" {{ old('service_type', $job->service_type) == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="polish" {{ old('service_type', $job->service_type) == 'polish' ? 'selected' : '' }}>Polish</option>
                                    <option value="resize" {{ old('service_type', $job->service_type) == 'resize' ? 'selected' : '' }}>Resize</option>
                                    <option value="stone_setting" {{ old('service_type', $job->service_type) == 'stone_setting' ? 'selected' : '' }}>Stone Setting</option>
                                    <option value="engraving" {{ old('service_type', $job->service_type) == 'engraving' ? 'selected' : '' }}>Engraving</option>
                                    <option value="cleaning" {{ old('service_type', $job->service_type) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="custom_order" {{ old('service_type', $job->service_type) == 'custom_order' ? 'selected' : '' }}>Custom Order</option>
                                    <option value="other" {{ old('service_type', $job->service_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Type</label>
                                <input type="text" name="item_type" class="form-control @error('item_type') is-invalid @enderror" 
                                    placeholder="e.g., Ring, Necklace, Bracelet" value="{{ old('item_type', $job->item_type) }}">
                                @error('item_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Weight (grams)</label>
                                <input type="number" step="0.001" name="item_weight" class="form-control @error('item_weight') is-invalid @enderror" 
                                    placeholder="0.000" value="{{ old('item_weight', $job->item_weight) }}">
                                @error('item_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Item Description</label>
                                <textarea name="item_description" class="form-control @error('item_description') is-invalid @enderror" 
                                    rows="2" placeholder="Describe the item...">{{ old('item_description', $job->item_description) }}</textarea>
                                @error('item_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Issue/Work Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                    rows="3" placeholder="Describe the issue or work to be done..." required>{{ old('description', $job->issue_description) }}</textarea>
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
                                    value="{{ old('expected_completion_date', optional($job->expected_completion_date)->format('Y-m-d')) }}" required>
                                @error('expected_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estimated Charge</label>
                                <input type="number" step="0.01" name="estimated_charge" 
                                    class="form-control @error('estimated_charge') is-invalid @enderror" 
                                    placeholder="0.00" value="{{ old('estimated_charge', $job->estimated_charge) }}">
                                @error('estimated_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Special Instructions</label>
                                <textarea name="special_instructions" class="form-control @error('special_instructions') is-invalid @enderror" 
                                    rows="2" placeholder="Any special instructions...">{{ old('special_instructions', $job->special_instructions) }}</textarea>
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
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $job->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_urgent" value="1" class="form-check-input" id="is_urgent" 
                                    {{ old('is_urgent', $job->is_urgent) ? 'checked' : '' }}>
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
                        @if($job->before_photos)
                            <div class="mb-2">
                                <small class="text-muted">Current photos:</small>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    @foreach($job->before_photos as $photo)
                                        <img src="{{ asset('storage/' . $photo) }}" alt="Before photo" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <input type="file" name="before_photos[]" class="form-control @error('before_photos') is-invalid @enderror" 
                            multiple accept="image/*">
                        <small class="text-muted d-block mt-2">Upload new photos to replace existing ones (optional)</small>
                        @error('before_photos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Update Service Job
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection