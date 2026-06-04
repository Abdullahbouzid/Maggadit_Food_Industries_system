{{-- resources/views/dashboard/data_definition/data_product/uoms/edit.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Edit UOM: ' . $uom->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit-2"></i> Edit UOM
                        </h1>
                        <p class="text-muted">Update details for <strong>{{ $uom->name_en }} / {{ $uom->name_ar }}</strong></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('uoms.update', $uom) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $uom->code) }}" required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Symbol (e.g., kg, m, pc)</label>
                                    <input type="text" name="uom_symbol" class="form-control @error('uom_symbol') is-invalid @enderror" value="{{ old('uom_symbol', $uom->uom_symbol) }}">
                                    @error('uom_symbol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $uom->name_en) }}" required>
                                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $uom->name_ar) }}" required>
                                    @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $uom->description) }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Current Image</label>
                                    @if($uom->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('uploads/' . $uom->image) }}" alt="{{ $uom->name_en }}" width="100" class="rounded border">
                                        </div>
                                    @else
                                        <p class="text-muted">No image</p>
                                    @endif
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    <div class="form-text">Leave empty to keep current image. Max 2MB.</div>
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" {{ old('is_active', $uom->is_active) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $uom->is_active) ? '' : 'selected' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $uom->sort_order) }}">
                                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Update UOM</button>
                                    <a href="{{ route('uoms.index') }}" class="btn btn-secondary px-5">Cancel</a>
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