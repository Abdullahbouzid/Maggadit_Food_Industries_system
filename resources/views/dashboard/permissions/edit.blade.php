@extends('dashboard.partials.layout')

@section('title', '{{ __('messages.app.page__1') }}')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__2') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('messages.app.page__4') }}
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf
            @method('PUT')
            <div class="nova-form-group">
                <label class="nova-label">{{ __('messages.app.page__6') }}</label>
                <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="nova-input @error('name') nova-invalid @enderror" required>
                @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
            </div>
            <div class="nova-flex nova-items-center nova-gap-2">
                <a href="{{ route('permissions.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.app.page__5') }}</a>
                <button type="submit" class="nova-btn nova-btn-primary">{{ __('messages.app.page__3') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
