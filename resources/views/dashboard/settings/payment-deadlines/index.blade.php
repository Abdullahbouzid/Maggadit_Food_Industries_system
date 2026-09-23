@extends('dashboard.partials.layout')

@section('title', __('messages.payment_deadline_page.title'))

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-clock" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.payment_deadline_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.payment_deadline_page.subtitle') }}
        </p>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <p class="nova-text-muted">{{ __('messages.payment_deadline_page.empty_state') }}</p>
    </div>
</div>
@endsection
