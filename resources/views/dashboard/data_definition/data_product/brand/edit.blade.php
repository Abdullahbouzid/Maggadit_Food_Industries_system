@extends('dashboard.partials.layout')

@section('title', 'Edit Brand: ' . $brand->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit"></i> Edit Brand
                        </h1>
                        <p class="text-muted">Edit brand details: {{ $brand->name }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $brand->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Logo</label>
                                @if($brand->logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('uploads/' . $brand->logo) }}" alt="{{ $brand->name }}" width="100" class="rounded border shadow-sm">
                                    </div>
                                @else
                                    <p class="text-muted">No logo uploaded yet.</p>
                                @endif
                                <label for="logo" class="form-label fw-semibold">Change Logo (Optional)</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="show_in_customer_app" name="show_in_customer_app" value="1" {{ old('show_in_customer_app', $brand->show_in_customer_app) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="show_in_customer_app">Show in Customer App</label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i data-feather="save"></i> Update</button>
                                <a href="{{ route('brands.index') }}" class="btn btn-light px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection