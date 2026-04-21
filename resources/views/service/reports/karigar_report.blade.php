@extends('layouts.app')

@section('title', 'Karigar Work Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Karigar Work & Purity Report</h1>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('service.reports.karigar') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter by Karigar</label>
                    <select name="karigar_id" class="form-select">
                        <option value="">All Karigars</option>
                        @foreach($karigars as $karigar)
                            <option value="{{ $karigar->id }}" {{ $selectedKarigarId == $karigar->id ? 'selected' : '' }}>
                                {{ $karigar->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Karigar</th>
                            <th>Job #</th>
                            <th>Ornament</th>
                            <th>Issued (g)</th>
                            <th>Recv (g)</th>
                            <th>Loss (g)</th>
                            <th>Purity (Exp)</th>
                            <th>Purity (Recv)</th>
                            <th>Wastage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td>{{ $item->serviceJob->karigar->name ?? 'N/A' }}</td>
                            <td>{{ $item->serviceJob->job_number }}</td>
                            <td>{{ $item->ornament_name }}</td>
                            <td>{{ number_format($item->weight_issued, 3) }}</td>
                            <td>{{ number_format($item->weight_received, 3) }}</td>
                            <td class="text-danger">{{ number_format($item->getWeightLoss(), 3) }}</td>
                            <td>{{ $item->purity_expected }}%</td>
                            <td>{{ $item->purity_received }}%</td>
                            <td>{{ number_format($item->wastage_actual, 3) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-white">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
