@extends('dashboard.partials.layout')

@section('title', 'Create Device')

@section('content')
<div class="wrapper"><div class="main"><main class="content"><div class="container-fluid p-0">
    <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="plus-circle"></i> Create Device</h1></div></div>
    <form action="{{ route('devices.store') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name (English) <span class="text-danger">*</span></label><input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en') }}" required>@error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Name (Arabic) <span class="text-danger">*</span></label><input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>@error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Serial <span class="text-danger">*</span></label><input type="text" name="serial" class="form-control @error('serial') is-invalid @enderror" value="{{ old('serial') }}" required>@error('serial')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Organization <span class="text-danger">*</span></label><select name="organization_id" class="form-select @error('organization_id') is-invalid @enderror" required><option value="">Select Organization</option>@foreach($organizations as $org)<option value="{{ $org->id }}" {{ old('organization_id')==$org->id?'selected':'' }}>{{ $org->name }}</option>@endforeach</select>@error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">Model No</label><input type="text" name="model_no" class="form-control" value="{{ old('model_no') }}"></div>
                    <div class="col-md-4"><label class="form-label">Brand</label><select name="brand_id" class="form-select"><option value="">Select Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" {{ old('brand_id')==$brand->id?'selected':'' }}>{{ $brand->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',0) }}"></div>
                    <div class="col-md-6"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}"></div>
                    <div class="col-md-6"><label class="form-label">Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry') }}"></div>
                    <div class="col-12"><label class="form-label">Remark</label><textarea name="remark" class="form-control" rows="2">{{ old('remark') }}</textarea></div>
                    <div class="col-md-4"><div class="form-check form-switch mt-4"><input type="checkbox" name="is_inactive" value="1" class="form-check-input" id="inactive" {{ old('is_inactive')?'checked':'' }}><label class="form-check-label" for="inactive">Inactive</label></div></div>
                </div>
            </div>
            <div class="card-footer bg-white border-0"><button type="submit" class="btn btn-primary">Save</button> <a href="{{ route('devices.index') }}" class="btn btn-secondary">Cancel</a></div>
        </div>
    </form>
</div></main></div></div>
@endsection