@extends('dashboard.partials.layout')

@section('title', 'Create Vehicle')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Create جديد Vehicle
        </h2>
        <p class="nova-page-subtitle">Fill in the vehicle details below.</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('vehicles.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Vehicle Information</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('vehicles.store') }}" method="POST">
            @csrf
            <div class="nova-row nova-gap-4">
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Vehicle Name <span class="nova-text-danger">*</span></label>
                        <input type="text" name="vehicle_name" class="nova-input @error('vehicle_name') nova-invalid @enderror" value="{{ old('vehicle_name') }}" required>
                        @error('vehicle_name') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Vehicle Code <span class="nova-text-danger">*</span></label>
                        <input type="text" name="vehicle_code" class="nova-input @error('vehicle_code') nova-invalid @enderror" value="{{ old('vehicle_code') }}" required>
                        @error('vehicle_code') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Plate لا. <span class="nova-text-danger">*</span></label>
                        <input type="text" name="plate_no" class="nova-input @error('plate_no') nova-invalid @enderror" value="{{ old('plate_no') }}" required>
                        @error('plate_no') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Barcode</label>
                        <input type="text" name="barcode" class="nova-input @error('barcode') nova-invalid @enderror" value="{{ old('barcode') }}">
                        @error('barcode') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Organization <span class="nova-text-danger">*</span></label>
                        <select name="organization_id" class="nova-select @error('organization_id') nova-invalid @enderror" required>
                            <option value="">Select Organization</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">Vehicle Type <span class="nova-text-danger">*</span></label>
                        <select name="vehicle_type_id" class="nova-select @error('vehicle_type_id') nova-invalid @enderror" required>
                            <option value="">Select Type</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}" {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="nova-col nova-col-md-3">
                    <div class="nova-form-group">
                        <label class="nova-label">Odometer</label>
                        <input type="number" step="0.01" name="odometer" class="nova-input" value="{{ old('odometer') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-3">
                    <div class="nova-form-group">
                        <label class="nova-label">Capacity</label>
                        <input type="number" step="0.01" name="capacity" class="nova-input" value="{{ old('capacity') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-3">
                    <div class="nova-form-group">
                        <label class="nova-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="nova-input" value="{{ old('amount') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-3">
                    <div class="nova-form-group">
                        <label class="nova-label">Volume</label>
                        <input type="number" step="0.01" name="volume" class="nova-input" value="{{ old('volume') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">Min Load QTY</label>
                        <input type="number" name="min_load_qty" class="nova-input" value="{{ old('min_load_qty') }}">
                    </div>
                </div>
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-check nova-mt-4">
                        <input class="nova-form-check-input" type="checkbox" name="inactive" value="1" id="inactiveSwitch" {{ old('inactive') ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="inactiveSwitch">غير نشط</label>
                        <small class="nova-text-muted d-block">Mark as inactive to disable this vehicle.</small>
                    </div>
                </div>
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('vehicles.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ Vehicle
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
