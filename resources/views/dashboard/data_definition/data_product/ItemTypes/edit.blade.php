@extends('dashboard.partials.layout')

@section('title', 'Edit Item Type: ' . $itemType->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit-2"></i> Edit Item Type
                        </h1>
                        <p class="text-muted">Update details for <strong>{{ $itemType->name }}</strong></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('item-types.update', $itemType) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $itemType->code) }}" required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $itemType->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $itemType->description) }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Current Image</label>
                                    @if($itemType->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $itemType->image) }}" alt="{{ $itemType->name }}" width="100" class="rounded border">
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
                                        <option value="1" {{ old('is_active', $itemType->is_active) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $itemType->is_active) ? '' : 'selected' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $itemType->sort_order) }}">
                                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Update Item Type</button>
                                    <a href="{{ route('item-types.index') }}" class="btn btn-secondary px-5">Cancel</a>
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