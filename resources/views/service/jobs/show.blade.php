@extends('layouts.app')

@section('title', 'Service Job Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Job #{{ $job->job_number }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('service.jobs.index') }}">Service Jobs</a></li>
                    <li class="breadcrumb-item active">Job Details</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#receiveItemsModal">
                <i class="fas fa-download me-1"></i> Receive Ornaments
            </button>
            <a href="{{ route('service.jobs.edit', $job) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit Job
            </a>
            <a href="{{ route('service.jobs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Service Information</h5>
                    {!! $job->getStatusBadge() !!}
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Service Type</label>
                            <p class="h6">{{ ucfirst($job->service_type) }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Job Type</label>
                            <p class="h6"><span class="badge bg-info">{{ ucfirst($job->job_type) }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Received Date</label>
                            <p class="h6">{{ $job->received_date->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small d-block mb-2">Ornaments / Items</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>Ornament</th>
                                        <th>Metal</th>
                                        <th>Expected Purity</th>
                                        <th>Issued Weight</th>
                                        <th>Barcode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($job->items as $item)
                                        <tr>
                                            <td>{{ $item->ornament_name }}</td>
                                            <td>{{ $item->metal_type }}</td>
                                            <td>{{ $item->purity_expected }}%</td>
                                            <td>{{ number_format($item->weight_issued, 3) }}g</td>
                                            <td><code class="small">{{ $item->barcode }}</code></td>
                                            <td>
                                                <span class="badge {{ $item->status == 'approved' ? 'bg-success' : 'bg-secondary' }} small">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted small">No specific items listed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Item Details</label>
                            <p class="h6">{{ $job->item_type }} - {{ $job->item_weight }}g</p>
                            <p class="text-muted small">{{ $job->item_description }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Expected Completion</label>
                            <p class="h6 {{ $job->isOverdue() ? 'text-danger' : '' }}">
                                {{ $job->expected_completion_date->format('d M Y') }}
                                @if($job->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small d-block">Issue / Work Description</label>
                        <div class="p-3 bg-light rounded">
                            {{ $job->issue_description }}
                        </div>
                    </div>

                    @if($job->special_instructions)
                    <div class="mb-4">
                        <label class="text-muted small d-block">Special Instructions</label>
                        <div class="p-3 bg-light rounded border-start border-warning border-3">
                            {{ $job->special_instructions }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Workflow History -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Workflow History</h5>
                </div>
                <div class="card-body">
                    @if($job->workflows->count() > 0)
                        <div class="timeline-simple">
                            @foreach($job->workflows as $workflow)
                                <div class="timeline-item mb-3 pb-3 border-bottom last-child-border-0">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">{{ $workflow->getWorkflowLabel() }}</h6>
                                        <small class="text-muted">{{ $workflow->created_at->format('d M, h:i A') }}</small>
                                    </div>
                                    <p class="small text-muted mb-0">{{ $workflow->description }}</p>
                                    @if($workflow->notes)
                                        <div class="mt-2 p-2 bg-light rounded small italic">"{{ $workflow->notes }}"</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks text-muted fa-3x mb-3"></i>
                            <p class="text-muted">No workflow history available yet.</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#moveWorkflowModal">
                                Start Workflow
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Karigar Card -->
            @if($job->karigar)
            <div class="card shadow-sm mb-4 border-start border-warning border-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Karigar (Worker)</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-1">{{ $job->karigar->name }}</h6>
                    <p class="small text-muted mb-2">{{ $job->karigar->company_name }}</p>
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="fas fa-phone me-2 text-muted"></i> {{ $job->karigar->phone_primary }}</li>
                        @if($job->karigar_instructions)
                        <li class="mt-2 text-warning italic">
                            <i class="fas fa-info-circle me-1"></i> Instructions: {{ $job->karigar_instructions }}
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- Customer Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <span class="h5 mb-0">{{ substr($job->customer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $job->customer->name }}</h6>
                            <small class="text-muted">{{ $job->customer->customer_code }}</small>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i> {{ $job->customer->phone }}
                        </li>
                        @if($job->customer->email)
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-muted"></i> {{ $job->customer->email }}
                        </li>
                        @endif
                    </ul>
                    <hr>
                    <a href="{{ route('customers.show', $job->customer) }}" class="btn btn-sm btn-outline-secondary w-100">View Profile</a>
                </div>
            </div>

            <!-- Financials -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estimated Charge</span>
                        <span class="fw-bold">Rs. {{ number_format($job->estimated_charge, 2) }}</span>
                    </div>
                    @if($job->final_charge)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Final Charge</span>
                        <span class="fw-bold text-success">Rs. {{ number_format($job->final_charge, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Photos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Photos</h5>
                    <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#uploadPhotosModal">
                        Add Photos
                    </button>
                </div>
                <div class="card-body">
                    @php $photos = $job->before_photos ?? []; @endphp
                    @if(count($photos) > 0)
                        <div class="row g-2">
                            @foreach($photos as $photo)
                                <div class="col-6">
                                    <a href="{{ asset('storage/' . $photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $photo) }}" class="img-fluid rounded border" alt="Job Photo">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center my-3">No photos uploaded.</p>
                    @endif
                </div>
            </div>

            <!-- Assignment -->
            <div class="card shadow-sm mb-4 border-start border-info border-4">
                <div class="card-body">
                    <h6 class="card-title d-flex justify-content-between">
                        Assignment
                        <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#assignModal">Change</button>
                    </h6>
                    @if($job->assignee)
                        <div class="d-flex align-items-center mt-3">
                            <i class="fas fa-user-circle fa-2x me-2 text-info"></i>
                            <div>
                                <p class="mb-0 fw-bold">{{ $job->assignee->name }}</p>
                                <small class="text-muted">Assigned Technician</small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mt-2">Currently unassigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Photos Modal -->
<div class="modal fade" id="uploadPhotosModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('service.jobs.upload-photos', $job) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Job Photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Photos</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Move Workflow Modal -->
<div class="modal fade" id="moveWorkflowModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('service.jobs.move-workflow', $job) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Job Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="new_status" class="form-select" required>
                            <option value="received" {{ $job->status == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="checking" {{ $job->status == 'checking' ? 'selected' : '' }}>Checking</option>
                            <option value="workshop" {{ $job->status == 'workshop' ? 'selected' : '' }}>Workshop</option>
                            <option value="polishing" {{ $job->status == 'polishing' ? 'selected' : '' }}>Polishing</option>
                            <option value="completed" {{ $job->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('service.jobs.assign', $job) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Staff</label>
                        @php 
                            $staff = \App\Models\User::where('is_active', true)->get();
                        @endphp
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select Technician</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}" {{ $job->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Receive Items Modal -->
<div class="modal fade" id="receiveItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('service.jobs.receive-items', $job) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Receive Ornaments from Karigar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Scan Barcode</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="barcode-scanner" class="form-control" placeholder="Scan or enter ornament barcode...">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="receiving-table">
                            <thead>
                                <tr>
                                    <th>Ornament</th>
                                    <th>Purity (Recv)</th>
                                    <th>Weight (Recv)</th>
                                    <th>Wastage</th>
                                    <th>Labor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($job->items as $item)
                                @if($item->status != 'approved')
                                <tr data-barcode="{{ $item->barcode }}">
                                    <td>
                                        {{ $item->ornament_name }}
                                        <input type="hidden" name="received_items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                    </td>
                                    <td><input type="number" step="0.01" name="received_items[{{ $item->id }}][purity_received]" class="form-control form-control-sm" placeholder="{{ $item->purity_expected }}"></td>
                                    <td><input type="number" step="0.001" name="received_items[{{ $item->id }}][weight_received]" class="form-control form-control-sm" placeholder="{{ $item->weight_issued }}"></td>
                                    <td><input type="number" step="0.001" name="received_items[{{ $item->id }}][wastage_actual]" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="received_items[{{ $item->id }}][labor_charge]" class="form-control form-control-sm"></td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Receipt & Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#barcode-scanner').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                let barcode = $(this).val().trim();
                let row = $('#receiving-table tr[data-barcode="' + barcode + '"]');
                if (row.length > 0) {
                    row.addClass('table-success');
                    row.find('input').first().focus();
                    $(this).val('');
                } else {
                    alert('Barcode not found in this job!');
                }
            }
        });
    });
</script>
@endpush
@endsection
