{{-- resources/views/admin/permissions/edit.blade.php --}}
@extends('dashboard.partials.layout')
@section('title', 'Edit Permission')
@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold"><i data-feather="edit-2"></i> Edit Permission</h1>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form method="POST" action="{{ route('permissions.update', $permission) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Permission Name</label>
                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection