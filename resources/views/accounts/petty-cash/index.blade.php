@extends('layouts.app')
@section('title', 'Petty Cash')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Petty Cash Funds</h1>
            <small class="text-muted">Manage petty cash funds per branch</small>
        </div>
        <a href="{{ route('accounts.petty-cash.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> New Fund
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Branch</th>
                        <th class="text-end">Fund Limit</th>
                        <th class="text-end">Current Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pettyCashes as $pc)
                    <tr>
                        <td>{{ $pc->id }}</td>
                        <td>{{ $pc->branch->name ?? '—' }}</td>
                        <td class="text-end">Rs. {{ number_format($pc->limit, 2) }}</td>
                        <td class="text-end fw-semibold {{ $pc->current_balance < ($pc->limit * 0.2) ? 'text-danger' : 'text-success' }}">
                            Rs. {{ number_format($pc->current_balance, 2) }}
                            @if($pc->current_balance < ($pc->limit * 0.2))
                                <span class="badge bg-danger ms-1">Low</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $pc->is_active ? 'success' : 'secondary' }}">{{ $pc->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('accounts.petty-cash.show', $pc) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('accounts.petty-cash.edit', $pc) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No petty cash funds created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $pettyCashes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
