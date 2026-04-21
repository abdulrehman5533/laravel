@extends('layouts.app')

@section('title', 'Gold Rate History & Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gold Rate Management</h1>
        <a href="{{ route('gold-rates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Rate Entry
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Today's Active Rate Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Today's Active Rate (24K)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($todayRate && $todayRate->status === 'approved')
                                    Rs. {{ number_format($todayRate->rate_24k, 2) }}
                                @else
                                    <span class="text-danger">Not Set</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 22K Rate -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Active Rate (22K)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($todayRate && $todayRate->status === 'approved')
                                    Rs. {{ number_format($todayRate->rate_22k, 2) }}
                                @else
                                    <span class="text-danger">Not Set</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gem fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Silver Rate -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Today's Active Rate (Silver)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($todayRate && $todayRate->status === 'approved')
                                    Rs. {{ number_format($todayRate->silver_rate, 2) }}
                                @else
                                    <span class="text-danger">Not Set</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ring fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Rate History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>24K Rate</th>
                            <th>22K Rate</th>
                            <th>18K Rate</th>
                            <th>Silver Rate</th>
                            <th>Status</th>
                            <th>Lock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->date->format('d M, Y') }}</td>
                                <td>Rs. {{ number_format($rate->rate_24k, 2) }}</td>
                                <td>Rs. {{ number_format($rate->rate_22k, 2) }}</td>
                                <td>Rs. {{ number_format($rate->rate_18k, 2) }}</td>
                                <td>Rs. {{ number_format($rate->silver_rate, 2) }}</td>
                                <td>
                                    @if($rate->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rate->is_locked)
                                        <i class="fas fa-lock text-danger" title="Locked"></i>
                                    @else
                                        <i class="fas fa-lock-open text-success" title="Unlocked"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($rate->status === 'draft')
                                            <form action="{{ route('gold-rates.approve', $rate) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('gold-rates.edit', $rate) }}" class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if($rate->is_locked && Auth::user()->hasRole('Super Admin'))
                                            <form action="{{ route('gold-rates.unlock', $rate) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning" title="Unlock">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            </form>
                                        @elseif(!$rate->is_locked && $rate->status === 'approved')
                                            <form action="{{ route('gold-rates.lock', $rate) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" title="Lock">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No rate records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $rates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
