@extends('dashboard.partials.layout')

@section('title', 'Create Banner')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2"><i data-feather="plus-circle"></i> Add New Banner</h1>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Title <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="home" {{ old('type') == 'home' ? 'selected' : '' }}>Home</option>
                                        <option value="category" {{ old('type') == 'category' ? 'selected' : '' }}>Category</option>
                                        <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>Product</option>
                                        <option value="promo" {{ old('type') == 'promo' ? 'selected' : '' }}>Promo</option>
                                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Image <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">URL <small class="text-muted">(optional)</small></label>
                                    <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}" placeholder="https://example.com">
                                    @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Order</label>
                                    <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}" min="0">
                                    @error('order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="hidden" name="show_in_customer_app" value="0">
                                        <input type="checkbox" name="show_in_customer_app" class="form-check-input" id="showInApp" value="1" {{ old('show_in_customer_app', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="showInApp">Show in Customer App</label>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Save Banner</button>
                                    <a href="{{ route('banners.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
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