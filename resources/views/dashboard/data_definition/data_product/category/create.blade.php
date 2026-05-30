@extends('dashboard.partials.layout')

@section('title', 'Add New Category')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="plus-circle"></i> Add Category
                        </h1>
                        <p class="text-muted">Create a new product category</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label fw-semibold">Category Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="division" class="form-label fw-semibold">Division <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('division') is-invalid @enderror" id="division" name="division" value="{{ old('division') }}" required>
                                @error('division')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">Image (Optional)</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                <div class="form-text">Preferred size 200x200, max 2MB.</div>
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="show_in_customer_app" name="show_in_customer_app" value="1" {{ old('show_in_customer_app') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="show_in_customer_app">Show in Customer App</label>
                                <div class="form-text">If enabled, this category will appear in the customer app.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i data-feather="save"></i> Save</button>
                                <a href="{{ route('categories.index') }}" class="btn btn-light px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection