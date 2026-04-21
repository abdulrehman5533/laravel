@extends('layouts.app')

@section('title', 'Karigar Invoices')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Karigar Invoices</h1>
        <a href="{{ route('service.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Invoice
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Karigar</th>
                            <th>Date</th>
                            <th>Total Weight</th>
                            <th>Total Labor</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->karigar->name }}</td>
                            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td>{{ number_format($invoice->total_weight, 3) }}g</td>
                            <td>Rs. {{ number_format($invoice->total_labor, 2) }}</td>
                            <td>
                                <span class="badge {{ $invoice->status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('service.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No Karigar invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
