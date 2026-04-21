@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt text-secondary"></i> Invoices</h2>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Format</th>
                        <th>Total Amount</th>
                        <th>Customer</th>
                        <th>Generated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td><strong>{{ $invoice->sale->invoice_no }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $invoice->format === 'thermal' ? 'info' : ($invoice->format === 'a4' ? 'primary' : 'success') }}">
                                {{ ucfirst($invoice->format) }}
                            </span>
                        </td>
                        <td>${{ number_format($invoice->sale->total, 2) }}</td>
                        <td>{{ $invoice->sale->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('pos.invoices.show', $invoice) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No invoices generated</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $invoices->links() }}
</div>
@endsection
