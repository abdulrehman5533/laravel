@extends('layouts.app')

@section('title', 'Edit Gold Rate')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Rate Entry ({{ $goldRate->date->format('d M, Y') }})</h5>
                    <a href="{{ route('gold-rates.index') }}" class="btn btn-sm btn-light">Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('gold-rates.update', $goldRate) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">24K Gold Rate</label>
                                <input type="number" step="0.01" name="rate_24k" class="form-control" value="{{ $goldRate->rate_24k }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Silver Rate</label>
                                <input type="number" step="0.01" name="silver_rate" class="form-control" value="{{ $goldRate->silver_rate }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">22K Rate</label>
                                <input type="number" step="0.01" name="rate_22k" class="form-control" value="{{ $goldRate->rate_22k }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">21K Rate</label>
                                <input type="number" step="0.01" name="rate_21k" class="form-control" value="{{ $goldRate->rate_21k }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">18K Rate</label>
                                <input type="number" step="0.01" name="rate_18k" class="form-control" value="{{ $goldRate->rate_18k }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Market Comments / Notes</label>
                            <textarea name="comment" class="form-control" rows="2">{{ $goldRate->comment }}</textarea>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-info px-5">Update Rate Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
