@extends('dashboard.partials.layout')

@section('title', 'Edit Vehicle Type')

@section('content')
<div class="wrapper">
    <div class="main"><main class="content"><div class="container-fluid p-0">
        <div class="row mb-4"><div class="col-12"><h1 class="display-5 fw-bold"><i data-feather="edit"></i> Edit Vehicle Type: {{ $vehicleType->code }}</h1></div></div>
        <form action="{{ route('vehicle-types.update', $vehicleType) }}" method="POST">
            @csrf @method('PUT')
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="{{ old('code', $vehicleType->code) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Name (English)</label><input type="text" name="name_en" class="form-control" value="{{ old('name_en', $vehicleType->name_en) }}" required></div>
                        <div class="col-md-4"><label class="form-label">Name (Arabic)</label><input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $vehicleType->name_ar) }}" required></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $vehicleType->description) }}</textarea></div>
                        <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $vehicleType->sort_order) }}"></div>
                        <div class="col-md-4"><div class="form-check form-switch mt-4"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $vehicleType->is_active)?'checked':'' }}><label class="form-check-label" for="is_active">Active</label></div></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0"><button type="submit" class="btn btn-primary">Update</button> <a href="{{ route('vehicle-types.index') }}" class="btn btn-secondary">Cancel</a></div>
            </div>
        </form>
    </div></main></div>
</div>
@endsection