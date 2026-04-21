@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit text-warning"></i> Edit Return / Repair</h2>
        <a href="{{ route('pos.returns.show', $return) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0"><i class="fas fa-edit"></i> Update Return Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.returns.update', $return) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sale</label>
                            <input type="text" class="form-control bg-light" value="Invoice #{{ $return->sale->invoice_no }} - {{ $return->sale->customer->name ?? 'Walk-in' }}" readonly>
                            <input type="hidden" name="pos_sale_id" value="{{ $return->pos_sale_id }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Return Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="return" {{ old('type', $return->type) == 'return' ? 'selected' : '' }}>Return</option>
                                <option value="exchange" {{ old('type', $return->type) == 'exchange' ? 'selected' : '' }}>Exchange</option>
                                <option value="repair" {{ old('type', $return->type) == 'repair' ? 'selected' : '' }}>Repair</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $return->reason) }}" required>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Refund Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" name="refund_amount" class="form-control @error('refund_amount') is-invalid @enderror" value="{{ old('refund_amount', $return->refund_amount) }}" step="0.01" min="0">
                                </div>
                                @error('refund_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Refund Method</label>
                                <select name="refund_method" class="form-select @error('refund_method') is-invalid @enderror">
                                    <option value="original" {{ old('refund_method', $return->refund_method) == 'original' ? 'selected' : '' }}>Original Payment Method</option>
                                    <option value="cash" {{ old('refund_method', $return->refund_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('refund_method', $return->refund_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('refund_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $return->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save"></i> Update Return Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
