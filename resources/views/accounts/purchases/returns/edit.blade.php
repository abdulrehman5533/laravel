@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit text-warning me-2"></i>Edit Purchase Return
            </h1>
            <p class="text-muted mt-1">Update return details for {{ $purchaseReturn->return_number }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('accounts.purchases.returns.update', $purchaseReturn) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Return Number</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchaseReturn->return_number }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Purchase Order</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchaseReturn->purchaseOrder->po_number }}" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Reason Details</label>
                                <textarea name="return_reason_details" class="form-control @error('return_reason_details') is-invalid @enderror" 
                                          rows="5" placeholder="Provide more details about the return">{{ old('return_reason_details', $purchaseReturn->return_reason_details) }}</textarea>
                                @error('return_reason_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end">
                                <a href="{{ route('accounts.purchases.returns.show', $purchaseReturn) }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Update Return
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
