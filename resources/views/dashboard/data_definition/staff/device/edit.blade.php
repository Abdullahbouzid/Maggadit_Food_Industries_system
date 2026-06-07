@extends('dashboard.partials.layout')

@section('title', 'Edit Device')

@section('content')
<div class="wrapper">
    <div class="main"><main class="content"><div class="container-fluid p-0">
        <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="edit"></i> Edit Device: {{ $device->serial }}</h1></div></div>
        <form action="{{ route('devices.update', $device) }}" method="POST">
            @csrf @method('PUT')
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name (English) <span class="text-danger">*</span></label><input type="text" name="name_en" class="form-control" value="{{ old('name_en', $device->name_en) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Name (Arabic) <span class="text-danger">*</span></label><input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $device->name_ar) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Serial Number <span class="text-danger">*</span></label><input type="text" name="serial" class="form-control" value="{{ old('serial', $device->serial) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Organization <span class="text-danger">*</span></label><select name="organization_id" class="form-select" required>@foreach($organizations as $org)<option value="{{ $org->id }}" {{ old('organization_id', $device->organization_id)==$org->id?'selected':'' }}>{{ $org->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Model No</label><input type="text" name="model_no" class="form-control" value="{{ old('model_no', $device->model_no) }}"></div>
                        <div class="col-md-4"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" value="{{ old('brand', $device->brand) }}"></div>
                        <div class="col-md-4"><div class="form-check form-switch mt-4"><input type="checkbox" name="is_inactive" value="1" class="form-check-input" id="is_inactive" {{ old('is_inactive', $device->is_inactive)?'checked':'' }}><label class="form-check-label" for="is_inactive">Inactive</label></div></div>
                        <div class="col-md-6"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $device->purchase_date?->format('Y-m-d')) }}"></div>
                        <div class="col-md-6"><label class="form-label">Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry', $device->warranty_expiry?->format('Y-m-d')) }}"></div>
                        <div class="col-12"><label class="form-label">Remark</label><textarea name="remark" class="form-control" rows="3">{{ old('remark', $device->remark) }}</textarea></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0"><button type="submit" class="btn btn-primary">Update</button> <a href="{{ route('devices.index') }}" class="btn btn-secondary">Cancel</a></div>
            </div>
        </form>
    </div></main></div>
</div>
@endsection