@extends('dashboard.partials.layout')

@section('title', 'Edit Pack Group')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="edit-2"></i> Edit Pack Group</h1>
                        <p class="text-muted">Update: {{ $packGroup->name_en }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('pack-groups.update', $packGroup) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $packGroup->code) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $packGroup->sort_order) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $packGroup->name_en) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $packGroup->name_ar) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $packGroup->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr><button type="submit" class="btn btn-primary">Update</button>
                                    <a href="{{ route('pack-groups.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection