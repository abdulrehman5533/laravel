@extends('layouts.app')
@section('title', 'Edit Petty Cash Fund')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Petty Cash Fund</h1>
        <a href="{{ route('accounts.petty-cash.show', $pettyCash) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <div class="card shadow-sm" style="max-width:500px">
        <div class="card-body">
            <form action="{{ route('accounts.petty-cash.update', $pettyCash) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Fund Limit (Rs.)</label>
                    <input type="number" name="limit" class="form-control" step="0.01" min="0" required value="{{ $pettyCash->limit }}">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $pettyCash->is_active ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
