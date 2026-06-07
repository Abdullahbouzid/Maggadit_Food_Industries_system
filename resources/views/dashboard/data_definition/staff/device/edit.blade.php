@extends('dashboard.partials.layout')

@section('title', 'Edit Device')

@section('content')
<div class="wrapper"><div class="main"><main class="content"><div class="container-fluid p-0">
    <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="edit"></i> Edit Device: {{ $device->serial }}</h1></div></div>
    <form action="{{ route('devices.update', $device) }}" method="POST">
        @csrf @method('PUT')
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label>Name (EN) *</label><input type="text" name="name_en" class="form-control" value="{{ old('name_en', $device->name_en) }}" required></div>
                    <div class="col-md-6"><label>Name (AR) *</label><input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $device->name_ar) }}" required></div>
                    <div class="col-md-6"><label>Serial *</label><input type="text" name="serial" class="form-control" value="{{ old('serial', $device->serial) }}" required></div>
                    <div class="col-md-6"><label>Organization *</label><select name="organization_id" class="form-select" required><option value="">Select</option>@foreach($organizations as $org)<option value="{{ $org->id }}" {{ old('organization_id', $device->organization_id)==$org->id?'selected':'' }}>{{ $org->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label>Model No</label><input type="text" name="model_no" class="form-control" value="{{ old('model_no', $device->model_no) }}"></div>
                    <div class="col-md-4"><label>Brand</label><select name="brand_id" class="form-select"><option value="">Select</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" {{ old('brand_id', $device->brand_id)==$brand->id?'selected':'' }}>{{ $brand->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $device->sort_order) }}"></div>
                    <div class="col-md-6"><label>Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', optional($device->purchase_date)->format('Y-m-d')) }}"></div>
                    <div class="col-md-6"><label>Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry', optional($device->warranty_expiry)->format('Y-m-d')) }}"></div>
                    <div class="col-12"><label>Remark</label><textarea name="remark" class="form-control" rows="2">{{ old('remark', $device->remark) }}</textarea></div>
                    <div class="col-md-4"><div class="form-check form-switch"><input type="checkbox" name="is_inactive" value="1" id="inactive" class="form-check-input" {{ old('is_inactive', $device->is_inactive)?'checked':'' }}><label for="inactive" class="form-check-label">Inactive</label></div></div>
                </div>
            </div>
            <div class="card-footer"><button type="submit" class="btn btn-primary">Update</button> <a href="{{ route('devices.index') }}" class="btn btn-secondary">Cancel</a></div>
        </div>
    </form>
</div></main></div></div>
@endsection