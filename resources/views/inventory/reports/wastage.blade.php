@extends('layouts.app')

@section('title', 'Wastage Report - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="fas fa-trash-alt me-2" style="color: #FF6B6B;"></i>
                Wastage Report
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <form method="GET" class="row g-2">
                <div class="col-auto">
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-auto">
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="text-muted">Total Wastage</h6>
            <h2 class="text-danger">{{ number_format($totalWastage, 2) }} grams</h2>
        </div>
    </div>

    <!-- Wastage Details -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Quantity (g)</th>
                        <th>Reason</th>
                        <th>Recorded By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wastage as $record)
                        <tr>
                            <td>{{ $record->created_at->format('M d, Y') }}</td>
                            <td><strong>{{ $record->product->name }}</strong></td>
                            <td class="text-danger">{{ number_format($record->quantity, 2) }}</td>
                            <td>
                                <span class="badge bg-warning text-dark">{{ ucfirst($record->reason) }}</span>
                            </td>
                            <td>{{ $record->createdBy->name }}</td>
                            <td>{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No wastage records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($wastage->hasPages())
        <div class="mt-4">
            {{ $wastage->links() }}
        </div>
    @endif
</div>
@endsection
