@extends('dashboard.partials.layout')

@section('title', 'Edit Banner')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit-2" class="align-middle me-2"></i>
                            Edit Banner
                        </h1>
                        <p class="text-muted">Update banner details, image, and visibility settings</p>
                    </div>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Edit Form --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form action="{{ route('banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">

                                {{-- Title --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Title <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Type --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="home" {{ old('type', $banner->type) == 'home' ? 'selected' : '' }}>Home</option>
                                        <option value="category" {{ old('type', $banner->type) == 'category' ? 'selected' : '' }}>Category</option>
                                        <option value="product" {{ old('type', $banner->type) == 'product' ? 'selected' : '' }}>Product</option>
                                        <option value="promo" {{ old('type', $banner->type) == 'promo' ? 'selected' : '' }}>Promo</option>
                                        <option value="custom" {{ old('type', $banner->type) == 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Current Image --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Current Image</label>
                                    <div class="mb-2">
                                        @if($banner->image)
                                            <img src="{{ asset('uploads/' . $banner->image) }}" alt="{{ $banner->title }}" width="120" height="120" class="rounded border shadow-sm object-fit-cover">
                                            <div class="mt-1">
                                                <span class="text-muted small">{{ basename($banner->image) }}</span>
                                            </div>
                                        @else
                                            <p class="text-muted fst-italic">No image uploaded</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- New Image --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Replace Image <small class="text-muted">(optional, leave empty to keep current)</small></label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- URL --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">URL <small class="text-muted">(optional)</small></label>
                                    <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url', $banner->url) }}" placeholder="https://example.com">
                                    @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Order --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Order</label>
                                    <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $banner->order) }}" min="0">
                                    @error('order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Show in App --}}
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="hidden" name="show_in_customer_app" value="0">
                                        <input type="checkbox" name="show_in_customer_app" class="form-check-input" id="showInApp" value="1" {{ old('show_in_customer_app', $banner->show_in_customer_app) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="showInApp">Show in Customer App</label>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Update Banner</button>
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

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .object-fit-cover { object-fit: cover; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        // Auto-dismiss alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (typeof bootstrap !== 'undefined') {
                    let bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    });
</script>
@endpush