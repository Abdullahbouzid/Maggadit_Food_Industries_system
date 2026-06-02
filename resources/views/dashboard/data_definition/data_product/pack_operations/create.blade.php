{{-- resources/views/dashboard/data_definition/data_product/pack_operations/create.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Add New Pack Operation')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="plus-circle"></i> Add New Pack Operation
                        </h1>
                        <p class="text-muted">Examples: "Sales Order Return +10", "Transfer +5", "Pack +20"</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('pack-operations.store') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Unique identifier (e.g., SOR10, TRF5)</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en') }}" required>
                                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Example: "Sales Order Return +10"</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
                                    @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">مثال: "مرتجعات أمر البيع +10"</div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order') }}">
                                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5"><i data-feather="save"></i> Save Operation</button>
                                    <a href="{{ route('pack-operations.index') }}" class="btn btn-secondary px-5">Cancel</a>
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