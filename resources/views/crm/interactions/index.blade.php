@extends('layouts.app')

@section('title', 'Customer Interactions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-comments text-primary me-2"></i>
            All Interactions
        </h2>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Interaction History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($interactions as $interaction)
                                    <tr>
                                        <td class="ps-4">
                                            {{ $interaction->interaction_date ? $interaction->interaction_date->format('M d, Y') : $interaction->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @if($interaction->customer)
                                                <a href="{{ route('customers.show', $interaction->customer) }}" class="text-decoration-none fw-bold">
                                                    {{ $interaction->customer->full_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-info" style="background-color: #e0f7fa;">
                                                {{ ucfirst($interaction->interaction_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-wrap" style="max-width: 400px;">{{ $interaction->description }}</p>
                                        </td>
                                        <td>{{ $interaction->createdBy->name ?? 'System' }}</td>
                                        <td class="text-end pe-4">
                                            @php
                                                $statusClass = match($interaction->status) {
                                                    'completed' => 'success',
                                                    'follow_up_needed' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $interaction->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-comment-slash fa-3x mb-3 d-block"></i>
                                            No interactions recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($interactions->hasPages())
                    <div class="card-footer bg-white">
                        {{ $interactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
