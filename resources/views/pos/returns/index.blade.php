@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exchange-alt text-danger"></i> Returns & Repairs</h2>
        <a href="{{ route('pos.returns.create') }}" class="btn btn-danger">
            <i class="fas fa-plus"></i> New Return
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Refund Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td><strong>{{ $return->sale->invoice_no ?? 'N/A' }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $return->type === 'return' ? 'danger' : 'warning' }}">
                                {{ ucfirst($return->type) }}
                            </span>
                        </td>
                        <td>{{ $return->reason ?? '-' }}</td>
                        <td>{{ $return->sale->currency ?? 'Rs' }} {{ number_format($return->refund_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $return->status === 'processed' ? 'success' : 'info' }}">
                                {{ ucfirst($return->status) }}
                            </span>
                        </td>
                        <td>{{ $return->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('pos.returns.show', $return) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No returns recorded</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $returns->links() }}
</div>
@endsection
