@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Warehouse</h3>
                </div>
                <form action="{{ route('inventory.warehouses.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">Warehouse Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="location">Location (City/Address)</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" id="location" value="{{ old('location') }}" required>
                            @error('location')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="capacity_volume">Capacity Volume (m³)</label>
                            <input type="number" step="0.01" name="capacity_volume" class="form-control @error('capacity_volume') is-invalid @enderror" id="capacity_volume" value="{{ old('capacity_volume') }}">
                            @error('capacity_volume')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="iot_sensor_id">IoT Sensor ID (Optional)</label>
                            <input type="text" name="iot_sensor_id" class="form-control @error('iot_sensor_id') is-invalid @enderror" id="iot_sensor_id" value="{{ old('iot_sensor_id') }}">
                            @error('iot_sensor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="rfid_enabled" value="0">
                            <input type="checkbox" name="rfid_enabled" class="form-check-input" id="rfid_enabled" value="1" {{ old('rfid_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="rfid_enabled">Enable RFID Tracking</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Warehouse</button>
                        <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
