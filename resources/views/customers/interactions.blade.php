@extends('layouts.app')

@section('title', 'Interactions - ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-comments text-primary me-2"></i>
            Interactions: {{ $customer->full_name }}
        </h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInteractionModal">
                <i class="fas fa-plus-circle me-1"></i> Add Interaction
            </button>
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Profile
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Interaction History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Created By</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($interactions as $interaction)
                                    <tr>
                                        <td class="ps-4">
                                            {{ $interaction->interaction_date ? \Carbon\Carbon::parse($interaction->interaction_date)->format('M d, Y') : $interaction->created_at->format('M d, Y') }}
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
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
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

<!-- Add Interaction Modal -->
<div class="modal fade" id="addInteractionModal" tabindex="-1" aria-labelledby="addInteractionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInteractionModalLabel text-white">Record New Interaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('crm.customers.add-interaction', $customer) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Interaction Type</label>
                        <select name="interaction_type" class="form-select" required>
                            <option value="call">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="in-person">In-Person Visit</option>
                            <option value="whatsapp">WhatsApp / SMS</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interaction Date</label>
                        <input type="date" name="interaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes / Details</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Summarize the interaction..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Interaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
