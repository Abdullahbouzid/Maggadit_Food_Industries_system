@extends('dashboard.partials.layout')

@section('title', 'Edit Item Image')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold"><i data-feather="edit-2"></i> Edit Item Image</h1>
                        <p class="text-muted">Update the image details for this item.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('item-images.update', $itemImage) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Item <span class="text-danger">*</span></label>
                                    <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                        <option value="">Select item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ old('item_id', $itemImage->item_id) == $item->id ? 'selected' : '' }}>{{ $item->name_en ?? $item->name_ar }} ({{ $item->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $itemImage->sort_order) }}">
                                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Current Image</label>
                                    <div class="border rounded p-2">
                                        <img src="{{ $itemImage->url }}" alt="{{ $itemImage->alt_text }}" class="img-fluid" style="max-width: 180px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Upload New Image</label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Alt Text</label>
                                    <input type="text" name="alt_text" class="form-control @error('alt_text') is-invalid @enderror" value="{{ old('alt_text', $itemImage->alt_text) }}">
                                    @error('alt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5">Update Image</button>
                                    <a href="{{ route('item-images.index') }}" class="btn btn-secondary px-5">Cancel</a>
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
