@extends('layouts.app')

@section('title', 'Stock Alerts | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-bell me-2 text-danger"></i>Inventory Stock Alerts</h2>
        <form action="{{ route('inventory.alerts.check') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-dark">
                <i class="fas fa-sync me-2"></i>Run Reorder Check
            </button>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Message</th>
                        <th>Triggered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $alert->product->name }}</div>
                            <small class="text-muted">{{ $alert->product->sku }}</small>
                        </td>
                        <td>{{ $alert->product->category->name }}</td>
                        <td>
                            <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : 'warning' }}">
                                {{ strtoupper($alert->severity) }}
                            </span>
                        </td>
                        <td>{{ $alert->message }}</td>
                        <td>{{ $alert->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <form action="{{ route('inventory.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check me-1"></i>Acknowledge
                                </button>
                            </form>
                            <a href="{{ route('inventory.products.show', $alert->product) }}" class="btn btn-sm btn-dark">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-25"></i>
                            <p class="text-muted">No active stock alerts. All levels are stable.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alerts->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $alerts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
