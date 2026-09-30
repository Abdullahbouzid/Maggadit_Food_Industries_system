@extends('dashboard.partials.layout')

@section('title', 'Create Permission')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-key" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create New Permission
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Add a new permission to the system</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <div class="nova-form-group">
                <label class="nova-label">Permission Name (e.g., edit_articles)</label>
                <input type="text" name="name" class="nova-input @error('name') nova-invalid @enderror" required>
                @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                <small class="nova-help-text">Use snake_case, prefix with resource name if needed.</small>
            </div>
            <div class="nova-flex nova-items-center nova-gap-2">
                <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline">Cancel</a>
                <button type="submit" class="nova-btn nova-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
