@extends('layouts.app')

@section('title', 'Vendor: ' . $vendor->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>{{ $vendor->name }}</h3>
        <div>
            <a href="{{ route('vendors.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-outline-primary">Edit</a>
            <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5>Vendor Details</h5>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <p><strong>Code:</strong> {{ $vendor->vendor_code }}</p>
                            <p><strong>Name:</strong> {{ $vendor->name }}</p>
                            <p><strong>Contact Person:</strong> {{ $vendor->contact_person ?? '-' }}</p>
                            <p><strong>Email:</strong> {{ $vendor->email ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> {{ $vendor->phone }}</p>
                            <p><strong>Address:</strong> {{ $vendor->address ?? '-' }}</p>
                            <p><strong>GST Number:</strong> {{ $vendor->gst_number ?? '-' }}</p>
                            <p><strong>Material Speciality:</strong> {{ $vendor->material_speciality ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Status</h5>
                    @if($vendor->is_active)
                        <span class="badge bg-success p-2">Active</span>
                    @else
                        <span class="badge bg-secondary p-2">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
