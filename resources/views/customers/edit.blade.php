@extends('layouts.app')

@section('title', 'Edit Customer - ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1 text-dark fw-bold">Edit Customer Profile</h1>
            <p class="text-muted small mb-0">Updating information for: <strong class="text-primary">{{ $customer->full_name }}</strong></p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="btn-group shadow-sm">
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-white border px-4">
                    <i class="fas fa-eye me-2"></i>View
                </a>
                <a href="{{ route('customers.index') }}" class="btn btn-light border px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Left Column: Primary Information -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="card border-0 shadow-sm rounded-12 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-user text-primary me-2"></i>Personal Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control rounded-8 @error('first_name') is-invalid @enderror" value="{{ old('first_name', $customer->first_name) }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control rounded-8 @error('last_name') is-invalid @enderror" value="{{ old('last_name', $customer->last_name) }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 rounded-end-8 @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}">
                                </div>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control border-start-0 rounded-end-8 @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                                </div>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Alternative Mobile</label>
                                <input type="text" name="mobile" class="form-control rounded-8 @error('mobile') is-invalid @enderror" value="{{ old('mobile', $customer->mobile) }}">
                                @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Gender</label>
                                <select name="gender" class="form-select rounded-8">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $customer->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control rounded-8" value="{{ old('date_of_birth', $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card border-0 shadow-sm rounded-12 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>Address Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Address Line 1</label>
                                <input type="text" name="address_line_1" class="form-control rounded-8" value="{{ old('address_line_1', $customer->address_line_1) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Address Line 2</label>
                                <input type="text" name="address_line_2" class="form-control rounded-8" value="{{ old('address_line_2', $customer->address_line_2) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">City</label>
                                <input type="text" name="city" class="form-control rounded-8" value="{{ old('city', $customer->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">State / Province</label>
                                <input type="text" name="state" class="form-control rounded-8" value="{{ old('state', $customer->state) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control rounded-8" value="{{ old('postal_code', $customer->postal_code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Country</label>
                                <input type="text" name="country" class="form-control rounded-8" value="{{ old('country', $customer->country) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Status -->
            <div class="col-lg-4">
                <!-- Account Type & Loyalty -->
                <div class="card border-0 shadow-sm rounded-12 mb-4 bg-dark text-white overflow-hidden">
                    <div class="card-body p-4 position-relative">
                        <div style="position: absolute; right: -20px; top: -20px; font-size: 100px; color: rgba(255,255,255,0.05); z-index: 0;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="position-relative" style="z-index: 1;">
                            <h5 class="fw-bold mb-4">Account Type</h5>
                            
                            <div class="mb-3">
                                <label class="form-label small text-uppercase text-white-50">Customer Classification</label>
                                <select name="customer_type" id="customer_type" class="form-select border-0 rounded-8 fw-bold">
                                    <option value="individual" {{ old('customer_type', $customer->customer_type) == 'individual' ? 'selected' : '' }}>Individual Customer</option>
                                    <option value="business" {{ old('customer_type', $customer->customer_type) == 'business' ? 'selected' : '' }}>Business Entity</option>
                                </select>
                            </div>

                            <div id="business_fields" class="{{ old('customer_type', $customer->customer_type) == 'business' ? '' : 'd-none' }}">
                                <div class="bg-white bg-opacity-10 rounded-8 p-3 mb-3">
                                    <div class="mb-3">
                                        <label class="form-label small text-uppercase text-white-50">Company Name</label>
                                        <input type="text" name="company_name" class="form-control border-0 rounded-8" value="{{ old('company_name', $customer->company_name) }}">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small text-uppercase text-white-50">Tax ID / NTN</label>
                                        <input type="text" name="tax_id" class="form-control border-0 rounded-8" value="{{ old('tax_id', $customer->tax_id) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label small text-uppercase text-white-50">Membership Tier</label>
                                <select name="membership_level" class="form-select border-0 rounded-8 fw-bold">
                                    <option value="bronze" {{ old('membership_level', $customer->membership_level) == 'bronze' ? 'selected' : '' }}>Bronze Tier</option>
                                    <option value="silver" {{ old('membership_level', $customer->membership_level) == 'silver' ? 'selected' : '' }}>Silver Tier</option>
                                    <option value="gold" {{ old('membership_level', $customer->membership_level) == 'gold' ? 'selected' : '' }}>Gold Tier</option>
                                    <option value="platinum" {{ old('membership_level', $customer->membership_level) == 'platinum' ? 'selected' : '' }}>Platinum Tier</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Settings -->
                <div class="card border-0 shadow-sm rounded-12 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-wallet text-success me-2"></i>Financial Settings
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Credit Limit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs</span>
                                <input type="number" step="0.01" name="credit_limit" class="form-control border-start-0 rounded-end-8 fw-bold" value="{{ old('credit_limit', $customer->credit_limit) }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Default Discount (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="special_discount_percentage" class="form-control rounded-start-8 fw-bold text-primary" value="{{ old('special_discount_percentage', $customer->special_discount_percentage) }}">
                                <span class="input-group-text bg-light border-start-0">%</span>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Payment Preference</label>
                            <select name="preferred_payment_method" class="form-select rounded-8">
                                <option value="">Not Specified</option>
                                <option value="cash" {{ old('preferred_payment_method', $customer->preferred_payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('preferred_payment_method', $customer->preferred_payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="credit_card" {{ old('preferred_payment_method', $customer->preferred_payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="cheque" {{ old('preferred_payment_method', $customer->preferred_payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card border-0 shadow-sm rounded-12">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-cog text-muted me-2"></i>System & Notes
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Assigned Branch</label>
                            <select name="branch_id" class="form-select rounded-8">
                                <option value="">Default Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $customer->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Internal Notes</label>
                            <textarea name="notes" class="form-control rounded-8" rows="3" placeholder="Additional customer details...">{{ old('notes', $customer->notes) }}</textarea>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="is_active">Account Active</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="marketing_consent" id="marketing_consent" value="1" {{ old('marketing_consent', $customer->marketing_consent) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="marketing_consent">Marketing Subscription</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row mt-4 mb-5">
            <div class="col-12 text-center">
                <hr class="my-4 opacity-10">
                <button type="reset" class="btn btn-outline-secondary px-5 py-2 rounded-8 me-3 fw-bold">Reset Changes</button>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-8 fw-bold shadow">
                    <i class="fas fa-save me-2"></i>Update Customer Profile
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .rounded-8 { border-radius: 8px !important; }
    .rounded-12 { border-radius: 12px !important; }
    .fw-bold { font-weight: 700 !important; }
    .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
    .card { transition: transform 0.2s ease; }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.1);
    }
    .btn-primary { background-color: #3b82f6; border-color: #3b82f6; }
    .btn-primary:hover { background-color: #2563eb; border-color: #2563eb; }
    .btn-white { background-color: #fff; color: #333; }
</style>

@push('scripts')
<script>
    document.getElementById('customer_type').addEventListener('change', function() {
        const businessFields = document.getElementById('business_fields');
        if (this.value === 'business') {
            businessFields.classList.remove('d-none');
        } else {
            businessFields.classList.add('d-none');
        }
    });
</script>
@endpush
@endsection
