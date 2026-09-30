@extends('dashboard.partials.layout')

@section('title', 'Edit Permission')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Edit Permission
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update permission details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf
            @method('PUT')
            <div class="nova-form-group">
                <label class="nova-label">Permission Name</label>
                <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="nova-input @error('name') nova-invalid @enderror" required>
                @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
            </div>
            <div class="nova-flex nova-items-center nova-gap-2">
                <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline">Cancel</a>
                <button type="submit" class="nova-btn nova-btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
