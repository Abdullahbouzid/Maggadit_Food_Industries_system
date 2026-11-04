@extends('dashboard.partials.layout')

@section('title', trans('messages.payment_deadline_page.create'))

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ trans('messages.payment_deadline_page.create') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ trans('messages.payment_deadline_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> {{ trans('messages.payment_deadline_page.warning_cancel') }}
        </a>
    </div>
</div>

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="nova-alert danger">
        <ul class="nova-mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('payment-deadlines.store') }}" class="nova-col-8">
    @csrf

    @include('dashboard.settings.payment-deadlines._form', ['setting' => null])
</form>

@endsection
