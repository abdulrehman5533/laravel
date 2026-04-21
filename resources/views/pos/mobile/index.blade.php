@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Mobile POS Overview</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    Mobile App Status
                    <div class="text-white-50 small">Online & Syncing</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    Today's Mobile Sales
                    <div class="text-white-50 small">5 Transactions</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Connected Devices</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Device Name</th>
                            <th>Last Active</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Samsung Galaxy S21 (POS-01)</td>
                            <td>{{ now()->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
