{{-- resources/views/admin/permissions/create.blade.php --}}
@extends('dashboard.partials.layout')
@section('title', 'Create Permission')
@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold"><i data-feather="key"></i> Create New Permission</h1>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form method="POST" action="{{ route('permissions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Permission Name (e.g., edit_articles)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Use snake_case, prefix with resource name if needed.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection