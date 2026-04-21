@extends('layouts.app')

@section('title', 'Create Gold Rate Entry')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Market Rate Entry</h5>
                    <a href="{{ route('gold-rates.index') }}" class="btn btn-sm btn-light">Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('gold-rates.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Effective Date</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="auto_calculate" id="auto_calculate" value="1" checked>
                                    <label class="form-check-label" for="auto_calculate">Auto-calculate Purities</label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">24K Gold Rate (Selling)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" name="rate_24k" class="form-control form-control-lg border-primary" required>
                                </div>
                                <small class="text-muted">Base rate for calculations</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Silver Rate (Selling)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" name="silver_rate" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div id="manual_rates" class="row g-3 mb-4 d-none">
                            <div class="col-md-3">
                                <label class="form-label text-xs">22K Rate</label>
                                <input type="number" step="0.01" name="rate_22k" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs">21K Rate</label>
                                <input type="number" step="0.01" name="rate_21k" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs">18K Rate</label>
                                <input type="number" step="0.01" name="rate_18k" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs">14K Rate</label>
                                <input type="number" step="0.01" name="rate_14k" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Old Gold Buy-back (24K)</label>
                                <input type="number" step="0.01" name="buy_rate_24k" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Old Silver Buy-back</label>
                                <input type="number" step="0.01" name="buy_silver_rate" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Market Comments / Notes</label>
                            <textarea name="comment" class="form-control" rows="2" placeholder="e.g. Market trend up due to global factors"></textarea>
                        </div>

                        <div class="alert alert-info py-2">
                            <i class="fas fa-info-circle me-1"></i> New rates will be saved as <strong>Draft</strong> and must be approved before they take effect in POS.
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-5">Save Rate Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('auto_calculate').addEventListener('change', function() {
    const manualRates = document.getElementById('manual_rates');
    if (this.checked) {
        manualRates.classList.add('d-none');
    } else {
        manualRates.classList.remove('d-none');
    }
});
</script>
@endsection
