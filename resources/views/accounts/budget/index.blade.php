@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Budgets</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('budget.create') }}" class="btn btn-primary">Create Budget</a>
        </div>
    </div>

    @if($budgets->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Total Budget</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                        <tr>
                            <td>{{ $budget->name }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($budget->budget_period) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $budget->status === 'approved' ? 'success' : ($budget->status === 'active' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($budget->status) }}
                                </span>
                            </td>
                            <td>₹{{ number_format($budget->total_budget, 2) }}</td>
                            <td>
                                <a href="{{ route('budget.show', $budget) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('budget.edit', $budget) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('budget.destroy', $budget) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info">No budgets found. <a href="{{ route('budget.create') }}">Create one</a></div>
    @endif
</div>
@endsection
