@extends('layouts.app')

@section('title', 'Girvi Loan Registry - ' . config('app.name'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-hand-holding-usd me-2 text-secondary"></i>Girvi Loans Registry</h2>
            <p class="text-muted small mb-0">Manage and track all customer pledge agreements</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li><a class="dropdown-item" href="{{ route('girvi.loans.export', array_merge(request()->all(), ['format' => 'excel'])) }}"><i class="fas fa-file-excel me-2 text-success"></i>Export Excel</a></li>
                    <li><a class="dropdown-item" href="{{ route('girvi.loans.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"><i class="fas fa-file-pdf me-2 text-danger"></i>Export PDF</a></li>
                </ul>
            </div>
            <a href="{{ route('girvi.bulk.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-tasks me-1"></i> Bulk Ops
            </a>
            <a href="{{ route('girvi.loans.create') }}" class="btn btn-gold px-4">
                <i class="fas fa-plus me-2"></i>New Girvi
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="fas fa-folder-open"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">ACTIVE LOANS</p>
                        <h4 class="fw-bold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card border-start border-danger border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3"><i class="fas fa-rupee-sign"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">OUTSTANDING</p>
                        <h4 class="fw-bold mb-0">Rs. {{ number_format($stats['outstanding'] / 100000, 1) }}L</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="fas fa-clock"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">OVERDUE</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ $stats['overdue'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="fas fa-check-double"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">SETTLED/MONTH</p>
                        <h4 class="fw-bold mb-0">{{ $stats['settled_month'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="fas fa-bell"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">MATURING SOON</p>
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['maturing_soon'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card border-start border-dark border-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-dark bg-opacity-10 text-dark me-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">HIGH RISK</p>
                        <h4 class="fw-bold mb-0 text-danger">{{ $stats['high_risk'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="stat-card mb-4 shadow-sm">
        <form action="{{ route('girvi.loans.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">Status</label>
                <select name="status" class="form-select border-0 bg-light" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="settled" {{ request('status') == 'settled' ? 'selected' : '' }}>Settled</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="auctioned" {{ request('status') == 'auctioned' ? 'selected' : '' }}>Auctioned</option>
                    <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">Branch</label>
                <select name="branch_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">From Date</label>
                <input type="date" name="from_date" class="form-control border-0 bg-light" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">To Date</label>
                <input type="date" name="to_date" class="form-control border-0 bg-light" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="customer" class="form-control border-0 bg-light" placeholder="Name, phone or Girvi number..." value="{{ request('customer') }}">
                </div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
        @if(request()->hasAny(['status','branch_id','from_date','to_date','customer']))
        <div class="mt-2">
            <a href="{{ route('girvi.loans.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear Filters</a>
            <span class="text-muted small ms-2">Showing filtered results</span>
        </div>
        @endif
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="alert alert-primary d-none mb-3 d-flex align-items-center gap-3">
        <span><strong id="selectedCount">0</strong> loans selected</span>
        <form action="{{ route('girvi.bulk.reminders') }}" method="POST" class="d-inline">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="btn btn-sm btn-success"><i class="fab fa-whatsapp me-1"></i>Send Reminders</button>
        </form>
        <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">Clear</button>
    </div>

    <!-- Table -->
    <div class="stat-card shadow-sm p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Girvi Number</th>
                        <th>Customer</th>
                        <th>Principal</th>
                        <th>Interest Rate</th>
                        <th>Outstanding</th>
                        <th>Maturity / Days</th>
                        <th>NPA Status</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($girvis as $girvi)
                    @php
                        $daysOverdue = $girvi->maturity_date ? now()->diffInDays($girvi->maturity_date, false) : null;
                        $npaStatus = 'Standard';
                        if ($girvi->maturity_date && $girvi->maturity_date->isPast() && $girvi->status === 'active') {
                            $overdueDays = abs($daysOverdue);
                            if ($overdueDays > 180) $npaStatus = 'Loss/NPA';
                            elseif ($overdueDays > 90) $npaStatus = 'Doubtful';
                            elseif ($overdueDays > 0) $npaStatus = 'Sub-Standard';
                        }
                        $npaClass = match($npaStatus) {
                            'Sub-Standard' => 'warning',
                            'Doubtful' => 'orange',
                            'Loss/NPA' => 'danger',
                            default => 'success'
                        };
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $girvi->id }}" onchange="updateBulkBar()">
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $girvi->girvi_number }}</div>
                            <small class="text-muted">{{ $girvi->girvi_date->format('d M, Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $girvi->customer->name }}</div>
                            <small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $girvi->customer->phone }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">Rs. {{ number_format($girvi->loan_amount, 0) }}</div>
                            <small class="badge bg-light text-dark border fw-normal">{{ $girvi->branch->name }}</small>
                        </td>
                        <td>
                            <div>{{ $girvi->interest_rate }}%</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">{{ $girvi->interest_cycle }}</small>
                        </td>
                        <td>
                            <div class="fw-bold {{ $girvi->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                                Rs. {{ number_format($girvi->outstanding_amount, 0) }}
                            </div>
                            @if($girvi->interest_accrued > 0)
                                <small class="text-muted" style="font-size: 0.75rem;">Int: Rs.{{ number_format($girvi->interest_accrued - $girvi->interest_paid, 0) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($girvi->maturity_date)
                                <div class="{{ $girvi->maturity_date->isPast() && $girvi->status === 'active' ? 'text-danger fw-bold' : '' }}">
                                    {{ $girvi->maturity_date->format('d M Y') }}
                                </div>
                                @if($girvi->status === 'active')
                                    @if($daysOverdue < 0)
                                        <small class="text-danger fw-bold">{{ abs((int)$daysOverdue) }} days overdue</small>
                                    @elseif($daysOverdue <= 7)
                                        <small class="text-warning fw-bold">{{ (int)$daysOverdue }} days left</small>
                                    @else
                                        <small class="text-muted">{{ (int)$daysOverdue }} days left</small>
                                    @endif
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $npaClass === 'orange' ? 'warning' : $npaClass }} bg-opacity-15 text-{{ $npaClass === 'orange' ? 'warning' : $npaClass }} px-2 py-1" style="font-size:0.7rem;">
                                {{ $npaStatus }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($girvi->status) {
                                    'active' => 'success',
                                    'settled' => 'primary',
                                    'auctioned' => 'warning',
                                    'overdue' => 'danger',
                                    'transferred' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }} px-3 py-2">
                                {{ strtoupper($girvi->status) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('girvi.loans.show', $girvi) }}" class="btn btn-sm btn-white border" title="View Details">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('girvi.loans.print', $girvi) }}" target="_blank" class="btn btn-sm btn-white border" title="Print">
                                    <i class="fas fa-print text-secondary"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-white border dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    <li><a class="dropdown-item" href="{{ route('girvi.loans.ledger', $girvi) }}"><i class="fas fa-history me-2 text-info"></i>Loan Ledger</a></li>
                                    <li><a class="dropdown-item" href="{{ route('girvi.customers.ledger', $girvi->customer_id) }}"><i class="fas fa-user me-2 text-primary"></i>Customer Ledger</a></li>
                                    @if($girvi->status === 'active')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('girvi.bulk.reminders') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="girvi_ids[]" value="{{ $girvi->id }}">
                                            <button type="submit" class="dropdown-item"><i class="fab fa-whatsapp me-2 text-success"></i>Send Reminder</button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-search fa-3x text-light mb-3 d-block"></i>
                            <h5 class="text-muted">No Girvi records found</h5>
                            <p class="text-muted small">Try adjusting your filters or search criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($girvis->hasPages())
        <div class="p-4 border-top bg-light">
            {{ $girvis->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    function updateBulkBar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const bar = document.getElementById('bulkActionsBar');
        const container = document.getElementById('bulkIdsContainer');
        document.getElementById('selectedCount').textContent = checked.length;
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'girvi_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        bar.classList.toggle('d-none', checked.length === 0);
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox, #selectAll').forEach(cb => cb.checked = false);
        updateBulkBar();
    }
</script>
@endpush
@endsection
