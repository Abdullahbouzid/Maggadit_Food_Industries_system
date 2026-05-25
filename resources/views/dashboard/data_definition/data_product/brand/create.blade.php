@extends('dashboard.partials.layout')

@section('title', 'Add New Brand')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="plus-circle"></i> Add Brand
                        </h1>
                        <p class="text-muted">Enter the new brand details</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label fw-semibold">Logo (Optional)</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                                <div class="form-text">Preferred size 200×200, max 2MB.</div>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="show_in_customer_app" name="show_in_customer_app" value="1" {{ old('show_in_customer_app') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="show_in_customer_app">Show in Customer App</label>
                                <div class="form-text">If enabled, this brand will appear in the customer app.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i data-feather="save"></i> Save</button>
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