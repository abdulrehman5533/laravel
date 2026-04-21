@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit text-primary me-2"></i>Edit Supplier
            </h1>
            <p class="text-muted mt-1">Update supplier information</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('accounts.suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-user text-primary me-2"></i>Basic Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $supplier->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                                           value="{{ old('contact_person', $supplier->contact_person) }}">
                                    @error('contact_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $supplier->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Primary Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone_primary" class="form-control @error('phone_primary') is-invalid @enderror"
                                           value="{{ old('phone_primary', $supplier->phone_primary) }}" required>
                                    @error('phone_primary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Secondary Phone</label>
                                    <input type="tel" name="phone_secondary" class="form-control @error('phone_secondary') is-invalid @enderror"
                                           value="{{ old('phone_secondary', $supplier->phone_secondary) }}">
                                    @error('phone_secondary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Supplier Type <span class="text-danger">*</span></label>
                                    <select name="supplier_type" class="form-select @error('supplier_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="Gold" {{ old('supplier_type', $supplier->supplier_type) == 'Gold' ? 'selected' : '' }}>Gold</option>
                                        <option value="Silver" {{ old('supplier_type', $supplier->supplier_type) == 'Silver' ? 'selected' : '' }}>Silver</option>
                                        <option value="Diamond" {{ old('supplier_type', $supplier->supplier_type) == 'Diamond' ? 'selected' : '' }}>Diamond</option>
                                        <option value="Gems" {{ old('supplier_type', $supplier->supplier_type) == 'Gems' ? 'selected' : '' }}>Gems</option>
                                        <option value="Jewelry" {{ old('supplier_type', $supplier->supplier_type) == 'Jewelry' ? 'selected' : '' }}>Jewelry</option>
                                        <option value="Machinery" {{ old('supplier_type', $supplier->supplier_type) == 'Machinery' ? 'selected' : '' }}>Machinery</option>
                                        <option value="Packaging" {{ old('supplier_type', $supplier->supplier_type) == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                                        <option value="Services" {{ old('supplier_type', $supplier->supplier_type) == 'Services' ? 'selected' : '' }}>Services</option>
                                        <option value="Karigar" {{ old('supplier_type', $supplier->supplier_type) == 'Karigar' ? 'selected' : '' }}>Karigar</option>
                                        <option value="Other" {{ old('supplier_type', $supplier->supplier_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('supplier_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>Address Information
                            </h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                           value="{{ old('address', $supplier->address) }}" placeholder="Street address">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                           value="{{ old('city', $supplier->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">State</label>
                                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                           value="{{ old('state', $supplier->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $supplier->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Country</label>
                                    <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                           value="{{ old('country', $supplier->country ?? 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Branch</label>
                                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $supplier->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tax & Registration -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-file-invoice text-primary me-2"></i>Tax & Registration
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">STRN (Sales Tax Reg No)</label>
                                    <input type="text" name="gstin" class="form-control @error('gstin') is-invalid @enderror"
                                           value="{{ old('gstin', $supplier->gstin) }}" placeholder="STRN Number">
                                    @error('gstin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">NTN (National Tax No)</label>
                                    <input type="text" name="pan" class="form-control @error('pan') is-invalid @enderror"
                                           value="{{ old('pan', $supplier->pan) }}" placeholder="NTN Number">
                                    @error('pan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="gst_registered" value="1" class="form-check-input"
                                               {{ old('gst_registered', $supplier->gst_registered) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">
                                            GST Registered
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Registration Number</label>
                                    <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror"
                                           value="{{ old('registration_number', $supplier->registration_number) }}">
                                    @error('registration_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Registration Date</label>
                                    <input type="date" name="registration_date" class="form-control @error('registration_date') is-invalid @enderror"
                                           value="{{ old('registration_date', $supplier->registration_date?->format('Y-m-d')) }}">
                                    @error('registration_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Banking Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-university text-primary me-2"></i>Banking Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                           value="{{ old('bank_name', $supplier->bank_name) }}">
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Account Number</label>
                                    <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror"
                                           value="{{ old('bank_account_number', $supplier->bank_account_number) }}">
                                    @error('bank_account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror"
                                           value="{{ old('ifsc_code', $supplier->ifsc_code) }}">
                                    @error('ifsc_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Payment Terms -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-handshake text-primary me-2"></i>Payment Terms
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Payment Terms</label>
                                    <input type="text" name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror"
                                           value="{{ old('payment_terms', $supplier->payment_terms) }}" placeholder="e.g., Net 30, 2/10 Net 30">
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Payment Days</label>
                                    <input type="number" name="payment_days" class="form-control @error('payment_days') is-invalid @enderror"
                                           value="{{ old('payment_days', $supplier->payment_days ?? 30) }}" min="0">
                                    @error('payment_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Credit Limit</label>
                                    <input type="number" name="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror"
                                           value="{{ old('credit_limit', $supplier->credit_limit ?? 0) }}" step="0.01" min="0">
                                    @error('credit_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-box text-primary me-2"></i>Product Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Product Specialty</label>
                                    <input type="text" name="product_specialty" class="form-control @error('product_specialty') is-invalid @enderror"
                                           value="{{ old('product_specialty', $supplier->product_specialty) }}" placeholder="e.g., Gold Jewelry, Diamond Stones">
                                    @error('product_specialty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="Active" {{ old('status', $supplier->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('status', $supplier->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="Suspended" {{ old('status', $supplier->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Any additional notes about this supplier">{{ old('notes', $supplier->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Supplier
                                </button>
                                <a href="{{ route('accounts.suppliers.show', $supplier) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
