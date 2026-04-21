@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Warehouse & Logistics</h3>
                    <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">Add New Warehouse</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Bins</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouses as $warehouse)
                            <tr>
                                <td>{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->location }}</td>
                                <td>{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>{{ $warehouse->bins->count() }}</td>
                                <td>
                                    <a href="{{ route('inventory.warehouses.show', $warehouse) }}" class="btn btn-sm btn-info">Manage Bins</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
