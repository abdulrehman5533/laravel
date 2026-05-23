@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Journal Entries</h1>
            <small class="text-muted">Manual double-entry journal vouchers</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Ledger
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newJournalModal">
                <i class="fas fa-plus me-1"></i> New Journal Entry
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Journal Entries</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Narration</th>
                        <th class="text-end">Total Debit</th>
                        <th class="text-end">Total Credit</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Lines</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                    <tr>
                        <td><code>{{ $j->reference_number }}</code></td>
                        <td>{{ $j->entry_date->format('d M Y') }}</td>
                        <td>{{ Str::limit($j->narration, 60) }}</td>
                        <td class="text-end text-primary fw-semibold">{{ number_format($j->items->sum('debit'), 2) }}</td>
                        <td class="text-end text-success fw-semibold">{{ number_format($j->items->sum('credit'), 2) }}</td>
                        <td><span class="badge bg-{{ $j->status == 'posted' ? 'success' : 'warning' }}">{{ ucfirst($j->status) }}</span></td>
                        <td><small>{{ $j->createdBy->name ?? '—' }}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#lines{{ $j->id }}">
                                {{ $j->items->count() }} lines
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="lines{{ $j->id }}">
                        <td colspan="8" class="p-0">
                            <table class="table table-sm mb-0 bg-light">
                                <thead><tr><th class="ps-4">Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th>Description</th></tr></thead>
                                <tbody>
                                    @foreach($j->items as $item)
                                    <tr>
                                        <td class="ps-4"><code>{{ $item->account->account_code ?? '' }}</code> {{ $item->account->account_name ?? '—' }}</td>
                                        <td class="text-end">{{ $item->debit > 0 ? number_format($item->debit, 2) : '—' }}</td>
                                        <td class="text-end">{{ $item->credit > 0 ? number_format($item->credit, 2) : '—' }}</td>
                                        <td><small class="text-muted">{{ $item->description }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No journal entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $journals->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- New Journal Entry Modal --}}
<div class="modal fade" id="newJournalModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('accounts.general-ledger.journals.store') }}" method="POST" id="journalForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Journal Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Narration <span class="text-danger">*</span></label>
                            <input type="text" name="narration" class="form-control" required placeholder="e.g. Gold purchase from supplier">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Total Debit must equal Total Credit (Double Entry Rule)
                    </div>

                    <table class="table table-bordered" id="journalLines">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Account <span class="text-danger">*</span></th>
                                <th>Description</th>
                                <th style="width:15%">Debit (Rs.)</th>
                                <th style="width:15%">Credit (Rs.)</th>
                                <th style="width:5%"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                            <tr class="line-row">
                                <td>
                                    <select name="lines[0][account_id]" class="form-select form-select-sm" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ $acc->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="lines[0][description]" class="form-control form-control-sm" placeholder="Optional"></td>
                                <td><input type="number" name="lines[0][debit]" class="form-control form-control-sm debit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
                                <td><input type="number" name="lines[0][credit]" class="form-control form-control-sm credit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
                            </tr>
                            <tr class="line-row">
                                <td>
                                    <select name="lines[1][account_id]" class="form-select form-select-sm" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ $acc->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="lines[1][description]" class="form-control form-control-sm" placeholder="Optional"></td>
                                <td><input type="number" name="lines[1][debit]" class="form-control form-control-sm debit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
                                <td><input type="number" name="lines[1][credit]" class="form-control form-control-sm credit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-semibold">Totals:</td>
                                <td><span id="totalDebit" class="fw-bold text-primary">0.00</span></td>
                                <td><span id="totalCredit" class="fw-bold text-success">0.00</span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5">
                                    <span id="balanceStatus" class="badge bg-secondary">Enter amounts to check balance</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLine()">
                        <i class="fas fa-plus me-1"></i> Add Line
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-check me-1"></i> Post Journal Entry
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let lineIndex = 2;
const accountOptions = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ $acc->account_name }}</option>@endforeach`;

function addLine() {
    const tbody = document.getElementById('linesBody');
    const row = document.createElement('tr');
    row.className = 'line-row';
    row.innerHTML = `
        <td>
            <select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm" required>
                <option value="">Select Account</option>
                ${accountOptions}
            </select>
        </td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm" placeholder="Optional"></td>
        <td><input type="number" name="lines[${lineIndex}][debit]" class="form-control form-control-sm debit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
        <td><input type="number" name="lines[${lineIndex}][credit]" class="form-control form-control-sm credit-input" step="0.01" min="0" value="0" oninput="calcTotals()"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(row);
    lineIndex++;
}

function removeLine(btn) {
    const rows = document.querySelectorAll('.line-row');
    if (rows.length <= 2) { alert('Minimum 2 lines required.'); return; }
    btn.closest('tr').remove();
    calcTotals();
}

function calcTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('.debit-input').forEach(i => debit += parseFloat(i.value) || 0);
    document.querySelectorAll('.credit-input').forEach(i => credit += parseFloat(i.value) || 0);

    document.getElementById('totalDebit').textContent  = debit.toFixed(2);
    document.getElementById('totalCredit').textContent = credit.toFixed(2);

    const status = document.getElementById('balanceStatus');
    const btn    = document.getElementById('submitBtn');
    if (Math.abs(debit - credit) < 0.01 && debit > 0) {
        status.className = 'badge bg-success';
        status.textContent = '✓ Balanced — Ready to Post';
        btn.disabled = false;
    } else {
        status.className = 'badge bg-danger';
        status.textContent = `✗ Not Balanced — Difference: ${Math.abs(debit - credit).toFixed(2)}`;
        btn.disabled = true;
    }
}

document.getElementById('submitBtn').disabled = true;
</script>
@endpush
@endsection
