@extends('layouts.app')

@section('title', 'Calculation History - Weight Calculator')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-history me-2" style="color: var(--primary);"></i>
                Calculation History
            </h2>
            <p class="text-muted">View and manage your saved weight & price calculations</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('calculator.index') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>New Calculation
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-4">
                    <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('calculator.history') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Calculations Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th>Date</th>
                        <th>Weight</th>
                        <th>Karat</th>
                        <th>Rate/g</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calculations as $calc)
                        <tr>
                            <td>
                                <small class="text-muted">{{ $calc->created_at->format('M d, Y') }}</small><br>
                                <small>{{ $calc->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong>{{ $calc->input_weight }}</strong><br>
                                <small class="text-muted">{{ strtoupper($calc->input_unit) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ $calc->karat_value }}K</span>
                            </td>
                            <td>Rs.{{ number_format($calc->rate_per_gram, 2) }}</td>
                            <td>
                                <strong class="text-success">Rs.{{ number_format($calc->final_price, 2) }}</strong>
                            </td>
                            <td>
                                @if($calc->is_saved_as_product)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>As Product
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-save me-1"></i>Saved
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('calculator.view', $calc->id) }}" class="btn btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('calculator.view', $calc->id) }}?print=1" class="btn btn-success" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('calculator.export', $calc->id) }}" class="btn btn-secondary" title="Download PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <button class="btn btn-warning" onclick="editCalculation({{ $calc->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="deleteCalculation({{ $calc->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">No calculations found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $calculations->links() }}
    </div>
</div>

@push('scripts')
<script>
    function editCalculation(id) {
        // Load calculation data and populate form
        fetch(`/calculator/${id}`)
            .then(r => r.json())
            .then(data => {
                // Redirect to calculator with pre-filled data
                window.location.href = `/calculator?calc=${id}`;
            });
    }

    function deleteCalculation(id) {
        if (confirm('Are you sure you want to delete this calculation?')) {
            fetch(`/calculator/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting calculation');
                }
            });
        }
    }
</script>
@endpush
@endsection
