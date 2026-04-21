@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Vendor Performance Ratings</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ratingModal">
            <i class="fas fa-star me-2"></i> Rate a Vendor
        </button>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ratings as $rating)
                    <tr>
                        <td>{{ $rating->supplier->name }}</td>
                        <td>
                            @for($i=1; $i<=5; $i++)
                                <i class="fas fa-star {{ $i <= $rating->rating ? 'text-warning' : 'text-gray-300' }}"></i>
                            @endfor
                        </td>
                        <td>{{ $rating->review }}</td>
                        <td>{{ $rating->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('vendors.ratings.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Vendor Rating</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (1-5)</label>
                        <input type="number" name="rating" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review/Comments</label>
                        <textarea name="review" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Rating</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
