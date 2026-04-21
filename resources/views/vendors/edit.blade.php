@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Edit Vendor</h3>
        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Back to list</a>
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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('vendors.update', $vendor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $vendor->contact_person) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $vendor->gst_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Material Speciality</label>
                        <input type="text" name="material_speciality" class="form-control" value="{{ old('material_speciality', $vendor->material_speciality) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $vendor->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $vendor->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Update Vendor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
