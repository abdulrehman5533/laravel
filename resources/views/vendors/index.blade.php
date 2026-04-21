@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Vendors</h3>
        <a href="{{ route('vendors.create') }}" class="btn btn-primary">+ Add Vendor</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, or code" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="material" class="form-control" placeholder="Material speciality" value="{{ request('material') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                </div>
                @if(request('search') || request('material'))
                <div class="col-md-2">
                    <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($vendors->count())
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Speciality</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $vendor)
                            <tr>
                                <td>{{ $vendor->vendor_code }}</td>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->contact_person ?? '-' }}</td>
                                <td>{{ $vendor->email ?? '-' }}</td>
                                <td>{{ $vendor->phone }}</td>
                                <td>{{ $vendor->material_speciality ?? '-' }}</td>
                                <td>
                                    @if($vendor->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $vendors->links() }}
                </div>
            @else
                <p class="text-muted">No vendors found. <a href="{{ route('vendors.create') }}">Add the first vendor</a>.</p>
            @endif
        </div>
    </div>
</div>
@endsection
