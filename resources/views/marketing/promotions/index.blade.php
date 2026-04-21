@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Marketing & Promotions</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#promotionModal">
            <i class="fas fa-plus me-2"></i> New Promotion
        </button>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Period</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promotions as $promo)
                    <tr>
                        <td>{{ $promo->name }}</td>
                        <td>{{ ucfirst($promo->type) }}</td>
                        <td>{{ $promo->value }}{{ $promo->type == 'percentage' ? '%' : ' Fixed' }}</td>
                        <td>{{ $promo->start_date }} to {{ $promo->end_date }}</td>
                        <td>
                            <span class="badge {{ $promo->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $promo->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
