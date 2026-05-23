@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Chart of Accounts</h1>
            <small class="text-muted">Manage all financial accounts</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Ledger
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="fas fa-plus me-1"></i> Add Account
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        @foreach(['Asset','Liability','Equity','Income','Expense'] as $type)
        @php $count = $accounts->where('account_type', $type)->count(); @endphp
        <div class="col">
            <div class="card border-0 shadow-sm text-center py-3">
                <h5 class="mb-0">{{ $count }}</h5>
                <small class="text-muted">{{ $type }}</small>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Accounts Table --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Accounts ({{ $accounts->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Parent</th>
                        <th class="text-end">Opening Balance</th>
                        <th class="text-end">Current Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Asset','Liability','Equity','Income','Expense'] as $type)
                    @php $group = $accounts->where('account_type', $type); @endphp
                    @if($group->count())
                    <tr class="table-secondary">
                        <td colspan="9" class="fw-bold py-2">{{ $type }} Accounts</td>
                    </tr>
                    @foreach($group as $acc)
                    <tr>
                        <td><code>{{ $acc->account_code }}</code></td>
                        <td>
                            {{ $acc->parent ? '↳ ' : '' }}
                            <a href="{{ route('accounts.general-ledger.show', $acc->id) }}" class="text-decoration-none">
                                {{ $acc->account_name }}
                            </a>
                        </td>
                        <td><span class="badge bg-secondary">{{ $acc->account_type }}</span></td>
                        <td><small>{{ $acc->account_category ?? '—' }}</small></td>
                        <td><small class="text-muted">{{ $acc->parent->account_name ?? '—' }}</small></td>
                        <td class="text-end">{{ number_format($acc->opening_balance, 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($acc->current_balance, 2) }}</td>
                        <td><span class="badge bg-{{ $acc->is_active ? 'success' : 'secondary' }}">{{ $acc->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $acc->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $acc->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('accounts.general-ledger.coa.update', $acc->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">Edit: {{ $acc->account_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Account Name</label>
                                            <input type="text" name="account_name" class="form-control" value="{{ $acc->account_name }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Type</label>
                                                <select name="account_type" class="form-select" required>
                                                    @foreach(['Asset','Liability','Equity','Income','Expense'] as $t)
                                                    <option value="{{ $t }}" {{ $acc->account_type == $t ? 'selected' : '' }}>{{ $t }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Category</label>
                                                <input type="text" name="account_category" class="form-control" value="{{ $acc->account_category }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Opening Balance</label>
                                            <input type="number" name="opening_balance" class="form-control" step="0.01" value="{{ $acc->opening_balance }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $acc->description }}</textarea>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $acc->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('accounts.general-ledger.coa.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add New Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" class="form-control" required placeholder="e.g. 1010">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" required placeholder="e.g. Cash in Hand">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Asset">Asset</option>
                                <option value="Liability">Liability</option>
                                <option value="Equity">Equity</option>
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="account_category" class="form-control" placeholder="e.g. Current Asset">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Account</label>
                            <select name="parent_account_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                @foreach($parents as $p)
                                <option value="{{ $p->id }}">{{ $p->account_code }} — {{ $p->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" name="opening_balance" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <strong>Jewellery Common Accounts:</strong><br>
                        1010 Cash in Hand | 1020 Bank Account | 1310 Gold Stock | 1320 Diamond Stock |
                        1330 Silver Stock | 1220 Girvi Loan Receivable | 4100 Gold Sales | 4200 Making Charges Income |
                        5100 Gold Purchase | 5200 Expense
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
