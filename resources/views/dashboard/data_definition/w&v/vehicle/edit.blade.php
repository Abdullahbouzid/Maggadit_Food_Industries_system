@extends('dashboard.partials.layout')

@section('title', 'Edit Vehicle')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit"></i> Edit Vehicle
                        </h1>
                        <p class="text-muted">Update vehicle information.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_name" class="form-control @error('vehicle_name') is-invalid @enderror" value="{{ old('vehicle_name', $vehicle->vehicle_name) }}" required>
                                    @error('vehicle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vehicle Code <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_code" class="form-control @error('vehicle_code') is-invalid @enderror" value="{{ old('vehicle_code', $vehicle->vehicle_code) }}" required>
                                    @error('vehicle_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Plate No. <span class="text-danger">*</span></label>
                                    <input type="text" name="plate_no" class="form-control @error('plate_no') is-invalid @enderror" value="{{ old('plate_no', $vehicle->plate_no) }}" required>
                                    @error('plate_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Barcode</label>
                                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $vehicle->barcode) }}">
                                    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Organization <span class="text-danger">*</span></label>
                                    <select name="organization_id" class="form-select @error('organization_id') is-invalid @enderror" required>
                                        <option value="">Select Organization</option>
                                        @foreach($organizations as $org)
                                            <option value="{{ $org->id }}" {{ old('organization_id', $vehicle->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
                                    <select name="vehicle_type_id" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <!-- Same numeric fields as create -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Odometer</label>
                                    <input type="number" step="0.01" name="odometer" class="form-control" value="{{ old('odometer', $vehicle->odometer) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Capacity</label>
                                    <input type="number" step="0.01" name="capacity" class="form-control" value="{{ old('capacity', $vehicle->capacity) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Amount</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $vehicle->amount) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Volume</label>
                                    <input type="number" step="0.01" name="volume" class="form-control" value="{{ old('volume', $vehicle->volume) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Min Load QTY</label>
                                    <input type="number" name="min_load_qty" class="form-control" value="{{ old('min_load_qty', $vehicle->min_load_qty) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4 pt-2">
                                        <input class="form-check-input" type="checkbox" name="inactive" value="1" id="inactiveSwitch" {{ old('inactive', $vehicle->inactive) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="inactiveSwitch">Inactive</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Update Vehicle</button>
                                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary px-5">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush