@extends('layouts.app')

@section('title', 'Gold Rate Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gold Rate Management</h3>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Current Gold Rates (Today)</h5>
                </div>
                <div class="card-body">
                    @if($goldRate)
                        <p><strong>22K Gold:</strong> Rs.{{ number_format($goldRate->rate_22k, 2) }}/g</p>
                        <p><strong>24K Gold:</strong> Rs.{{ number_format($goldRate->rate_24k, 2) }}/g</p>
                        <p><strong>18K Gold:</strong> Rs.{{ number_format($goldRate->rate_18k, 2) }}/g</p>
                        <p><strong>Silver:</strong> Rs.{{ number_format($goldRate->silver_rate, 2) }}/g</p>
                        <p><strong>Date:</strong> {{ $goldRate->date->format('Y-m-d') }}</p>
                    @else
                        <p class="text-muted">No rates set for today yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Update Gold Rates</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('gold-rate.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">22K Gold Rate (Rs./g)</label>
                            <input type="number" step="0.01" name="rate_22k" class="form-control" 
                                   value="{{ old('rate_22k', optional($goldRate)->rate_22k) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">24K Gold Rate (Rs./g)</label>
                            <input type="number" step="0.01" name="rate_24k" class="form-control" 
                                   value="{{ old('rate_24k', optional($goldRate)->rate_24k) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">18K Gold Rate (Rs./g)</label>
                            <input type="number" step="0.01" name="rate_18k" class="form-control" 
                                   value="{{ old('rate_18k', optional($goldRate)->rate_18k) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Silver Rate (Rs./g)</label>
                            <input type="number" step="0.01" name="silver_rate" class="form-control" 
                                   value="{{ old('silver_rate', optional($goldRate)->silver_rate) }}" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update Rates</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Rate Information</h5>
        </div>
        <div class="card-body">
            <p><strong>22K Gold:</strong> Most commonly used in jewellery (91.67% pure)</p>
            <p><strong>24K Gold:</strong> Pure gold (99.9% pure) - highest purity</p>
            <p><strong>18K Gold:</strong> 75% pure gold - good for delicate designs</p>
            <p><strong>Silver:</strong> Used in jewellery making and alloys</p>
            <p class="text-muted mt-3">Rates are typically updated daily based on market prices. These rates are used across the system for product pricing and calculations.</p>
        </div>
    </div>
</div>
@endsection
