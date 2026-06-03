@extends('dashboard.partials.layout')

@section('title', 'Edit Area')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit"></i> Edit Area: {{ $area->code }}
                        </h1>
                    </div>
                </div>

                <form action="{{ route('areas.update', $area) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $area->code) }}" required>
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $area->name_en) }}" required>
                                    @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $area->name_ar) }}" required>
                                    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State</label>
                                    <select name="state_id" class="form-select">
                                        <option value="">-- None --</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}" {{ old('state_id', $area->state_id) == $state->id ? 'selected' : '' }}>{{ $state->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $area->sort_order) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $area->description) }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $area->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('areas.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection