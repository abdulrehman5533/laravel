@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Asset Management</h3>
                    <a href="{{ route('assets.create') }}" class="btn btn-primary">Add New Asset</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Branch</th>
                                <th>Purchase Cost</th>
                                <th>Current Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                            <tr>
                                <td>{{ $asset->asset_code }}</td>
                                <td>{{ $asset->name }}</td>
                                <td>{{ $asset->category }}</td>
                                <td>{{ $asset->branch->name }}</td>
                                <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                                <td>{{ number_format($asset->current_value, 2) }}</td>
                                <td>{{ $asset->status }}</td>
                                <td>
                                    <form action="{{ route('assets.depreciation', $asset) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">Depreciate</button>
                                    </form>
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
