@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Edit Branch</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Branches</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Branch List
            </a>
            <button type="submit" form="branchForm" class="btn btn-sm btn-primary">
                <i class="fas fa-save me-1"></i> Update Branch
            </button>
        </div>
    </div>

    <form action="{{ route('branches.update', $branch) }}" method="POST" id="branchForm">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-xl-8 col-lg-9">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-code-branch me-2"></i>Branch Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $branch->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label">Branch Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="{{ $branch->code }}" required>
                            </div>
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $branch->address }}">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $branch->city }}">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ $branch->state }}">
                            </div>
                            <div class="col-md-4">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ $branch->country }}">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ $branch->phone }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $branch->email }}">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="1" @if($branch->is_active) selected @endif>Active</option>
                                    <option value="0" @if(!$branch->is_active) selected @endif>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
