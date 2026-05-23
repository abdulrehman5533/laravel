@extends('layouts.app')
@section('title', 'New Petty Cash Fund')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Petty Cash Fund</h1>
        <a href="{{ route('accounts.petty-cash.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <div class="card shadow-sm" style="max-width:600px">
        <div class="card-body">
            <form action="{{ route('accounts.petty-cash.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fund Limit (Rs.) <span class="text-danger">*</span></label>
                    <input type="number" name="limit" class="form-control" step="0.01" min="0" required placeholder="e.g. 10000">
                </div>
                <div class="mb-3">
                    <label class="form-label">Opening Balance (Rs.) <span class="text-danger">*</span></label>
                    <input type="number" name="current_balance" class="form-control" step="0.01" min="0" required placeholder="e.g. 5000">
                </div>
                <button type="submit" class="btn btn-success">Create Fund</button>
            </form>
        </div>
    </div>
</div>
@endsection
