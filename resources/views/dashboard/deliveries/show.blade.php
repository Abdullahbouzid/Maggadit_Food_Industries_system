@extends('dashboard.partials.layout')

@section('title', __('messages.delivery_page.delivery_details'))

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-truck" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.delivery_page.delivery_details') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ $delivery->invoice?->invoice_number ?? $delivery->order?->order_number ?? '—' }}
        </p>
    </div>
    <div class="nova-flex nova-gap-2">
        <a href="{{ route('deliveries.index') }}" class="nova-btn">
            <i class="fas fa-arrow-right"></i> {{ __('messages.delivery_page.back') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

@php
    $stage = $delivery->current_stage instanceof \App\Enums\DeliveryStage
        ? $delivery->current_stage
        : \App\Enums\DeliveryStage::tryFrom((string) $delivery->current_stage);
    $badgeClass = match ($stage?->value ?? '') {
        'shipping' => 'nova-badge warning',
        'out_for_delivery' => 'nova-badge info',
        'unloading' => 'nova-badge info',
        'received' => 'nova-badge success',
        'cancelled' => 'nova-badge danger',
        default => 'nova-text-muted',
    };
@endphp

<div class="nova-row nova-gap-4">
    <div class="nova-col-8">
        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.delivery_page.delivery_details') }}</h3>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-4">
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.invoice_number') }}</div>
                        <div class="nova-fw-semibold">
                            @if($delivery->invoice)
                                <a href="{{ route('invoices.show', $delivery->invoice->id) }}">
                                    {{ $delivery->invoice->invoice_number }}
                                </a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.order_number') }}</div>
                        <div class="nova-fw-semibold">{{ $delivery->order?->order_number ?? '—' }}</div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.branch') }}</div>
                        <div class="nova-fw-semibold">
                            <i class="fas fa-store" style="margin-inline-end: 0.25rem;"></i>
                            {{ $delivery->order?->branch?->name ?? ($delivery->order ? $delivery->order->branch_id : '—') }}
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.customer') }}</div>
                        <div class="nova-fw-semibold">{{ $delivery->customer?->name ?? '—' }}</div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.driver') }}</div>
                        <div class="nova-fw-semibold">
                            {{ $delivery->driver?->name ?? __('messages.delivery_page.not_assigned') }}
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.vehicle') }}</div>
                        <div class="nova-fw-semibold">
                            @if($delivery->vehicle)
                                {{ $delivery->vehicle->vehicle_name }}
                                <span class="nova-text-muted nova-fs-sm">
                                    ({{ $delivery->vehicle->plate_no ?? __('messages.delivery_page.no_vehicle') }})
                                </span>
                            @else
                                {{ __('messages.delivery_page.not_assigned') }}
                            @endif
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.current_stage') }}</div>
                        <div>
                            @if($stage)
                                <span class="{{ $badgeClass }}">{{ $stage->label() }}</span>
                            @else
                                <span class="nova-text-muted">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.started_at') }}</div>
                        <div class="nova-fw-semibold">
                            {{ $delivery->started_at ? $delivery->started_at->format('Y-m-d H:i') : '—' }}
                        </div>
                    </div>
                    <div class="nova-col-6 nova-mb-3">
                        <div class="nova-text-muted nova-fs-sm">{{ __('messages.delivery_page.completed_at') }}</div>
                        <div class="nova-fw-semibold">
                            {{ $delivery->completed_at ? $delivery->completed_at->format('Y-m-d H:i') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.delivery_page.stage_timeline') }}</h3>
            </div>
            <div class="nova-card-body">
                @forelse($delivery->stageLogs->sortByDesc('created_at') as $log)
                    <div class="nova-timeline-item" style="padding: 0.75rem 0; border-bottom: 1px solid var(--nova-border);">
                        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
                            <div>
                                @php
                                    $logStage = \App\Enums\DeliveryStage::tryFrom((string) $log->stage);
                                    $logBadge = match ($logStage?->value ?? '') {
                                        'shipping' => 'nova-badge warning',
                                        'out_for_delivery' => 'nova-badge info',
                                        'unloading' => 'nova-badge info',
                                        'received' => 'nova-badge success',
                                        'cancelled' => 'nova-badge danger',
                                        default => 'nova-text-muted',
                                    };
                                @endphp
                                @if($logStage)
                                    <span class="{{ $logBadge }}">{{ $logStage->label() }}</span>
                                @else
                                    <span class="nova-text-muted">{{ $log->stage ?? '—' }}</span>
                                @endif
                                @if($log->notes)
                                    <div class="nova-text-muted nova-fs-sm" style="margin-top: 0.375rem;">
                                        {{ $log->notes }}
                                    </div>
                                @endif
                            </div>
                            <div class="nova-text-muted nova-fs-sm">
                                {{ $log->created_at?->format('Y-m-d H:i') }}
                                @if($log->updatedBy)
                                    &middot; {{ $log->updatedBy->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="nova-text-muted nova-py-4 nova-text-center">
                        {{ __('messages.delivery_page.stage_no_logs') }}
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="nova-col-4">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.delivery_page.advance_stage') }}</h3>
            </div>
            <div class="nova-card-body">
                @php $nextStage = $delivery->nextStage(); @endphp

                @if($delivery->canAdvance() && $nextStage)
                    <p class="nova-text-muted nova-fs-sm">
                        {{ __('messages.delivery_page.next_stage') }}:
                        <span class="nova-badge info">{{ $nextStage->label() }}</span>
                    </p>

                    <form method="POST" action="{{ route('deliveries.advance-stage', $delivery->id) }}">
                        @csrf
                        <div class="nova-form-group">
                            <label class="nova-label" for="advance_notes">
                                {{ __('messages.delivery_page.advance_note_label') }}
                            </label>
                            <textarea id="advance_notes" name="notes" rows="3" class="nova-textarea"
                                placeholder="{{ __('messages.delivery_page.advance_note_placeholder') }}"></textarea>
                            @error('notes')
                                <div class="nova-form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit"
                            class="nova-btn nova-btn-primary nova-w-100"
                            onclick="return confirm('{{ __('messages.delivery_page.advance_confirm') }}');">
                            <i class="fas fa-arrow-right"></i> {{ __('messages.delivery_page.advance_stage') }}
                        </button>
                    </form>
                @else
                    <p class="nova-text-muted nova-py-3 nova-text-center">
                        <i class="fas fa-circle-check" style="color: var(--nova-success); margin-bottom: 0.5rem; font-size: 1.5rem;"></i><br>
                        {{ __('messages.delivery_page.stage_none') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
