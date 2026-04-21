@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Warehouse Details</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $warehouse->name }}</p>
                    <p><strong>Location:</strong> {{ $warehouse->location }}</p>
                    <p><strong>Branch:</strong> {{ $warehouse->branch->name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge {{ $warehouse->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                    <p><strong>Capacity:</strong> {{ $warehouse->capacity_volume ?? '0' }} m³</p>
                    <p><strong>RFID Tracking:</strong> {{ $warehouse->rfid_enabled ? 'Enabled' : 'Disabled' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">IoT Sensor Status</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status:</span>
                        <span class="badge {{ $iotStatus['status'] == 'normal' ? 'bg-success' : ($iotStatus['status'] == 'warning' ? 'bg-warning' : 'bg-danger') }}">
                            {{ strtoupper($iotStatus['status']) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Temperature:</strong> {{ $iotStatus['readings']['temperature'] }}°C
                    </div>
                    <div class="mb-2">
                        <strong>Humidity:</strong> {{ $iotStatus['readings']['humidity'] }}%
                    </div>
                    <hr>
                    <h6>Recent Alerts</h6>
                    @forelse($iotStatus['alerts'] as $alert)
                        <div class="alert {{ $alert['severity'] == 'critical' ? 'alert-danger' : 'alert-warning' }} py-1 px-2 mb-1">
                            <small>{{ $alert['message'] }}</small>
                        </div>
                    @empty
                        <p class="text-success small">No active alerts.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Storage Bins</h3>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBinModal">Add Bin</button>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Bin Code</th>
                                <th>Type</th>
                                <th>Stored Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouse->bins as $bin)
                            <tr>
                                <td>{{ $bin->bin_code }}</td>
                                <td>{{ ucfirst($bin->bin_type) }}</td>
                                <td>0</td> {{-- Placeholder for item count --}}
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No bins configured for this warehouse.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for adding bins (simplified for now) -->
<div class="modal fade" id="addBinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.warehouses.bins.store', $warehouse) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Storage Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bin_code" class="form-label">Bin Code</label>
                        <input type="text" name="bin_code" id="bin_code" class="form-control" placeholder="e.g. A-101" required>
                    </div>
                    <div class="mb-3">
                        <label for="bin_type" class="form-label">Bin Type</label>
                        <select name="bin_type" id="bin_type" class="form-control" required>
                            <option value="shelf">Shelf</option>
                            <option value="vault">Vault</option>
                            <option value="drawer">Drawer</option>
                            <option value="pallet">Pallet</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Bin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
