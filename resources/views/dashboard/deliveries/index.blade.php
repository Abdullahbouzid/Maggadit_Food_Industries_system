@extends('dashboard.partials.layout')

@section('title', __('messages.delivery_page.title'))

@section('content')

{{-- Hero Header --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-truck" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.delivery_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.delivery_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('deliveries.create') }}" class="nova-btn nova-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.delivery_page.add_delivery') }}
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('deliveries.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 180px;">
                    <label class="nova-label">{{ __('messages.delivery_page.filter_stage') }}</label>
                    <select name="stage" class="nova-select">
                        <option value="">{{ __('messages.delivery_page.stage_all') }}</option>
                        @foreach(\App\Enums\DeliveryStage::cases() as $stage)
                            <option value="{{ $stage->value }}" {{ (request('stage') ?? '') === $stage->value ? 'selected' : '' }}>
                                {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="min-width: 180px;">
                    <label class="nova-label">{{ __('messages.delivery_page.filter_driver') }}</label>
                    <select name="driver_id" class="nova-select">
                        <option value="">{{ __('messages.delivery_page.all_drivers') }}</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ (request('driver_id') ?? '') === (string) $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group">
                    <input type="date" name="date_from" class="nova-input" value="{{ request('date_from') }}">
                </div>
                <div class="nova-form-group">
                    <input type="date" name="date_to" class="nova-input" value="{{ request('date_to') }}">
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.delivery_page.apply') }}
                    </button>
                    <a href="{{ route('deliveries.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.delivery_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Stage Tabs --}}
<div class="nova-status-tabs">
    <a href="{{ route('deliveries.index', array_merge(request()->except(['stage', 'page']), ['stage' => null])) }}"
       class="nova-status-tab {{ empty(request('stage')) ? 'active' : '' }}">
        {{ __('messages.delivery_page.stage_all') }}
        <span class="nova-badge">{{ $stageCounts['total'] ?? $deliveries->count() }}</span>
    </a>
    @foreach(\App\Enums\DeliveryStage::cases() as $stage)
        <a href="{{ route('deliveries.index', array_merge(request()->except(['stage', 'page']), ['stage' => $stage->value])) }}"
           class="nova-status-tab {{ (request('stage') ?? '') === $stage->value ? 'active' : '' }}">
            {{ $stage->label() }}
            <span class="nova-badge">{{ $stageCounts[$stage->value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Deliveries Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.delivery_page.col_invoice') }}</th>
                        <th>{{ __('messages.delivery_page.col_customer') }}</th>
                        <th>{{ __('messages.delivery_page.col_driver') }}</th>
                        <th>{{ __('messages.delivery_page.col_stage') }}</th>
                        <th>{{ __('messages.delivery_page.col_started') }}</th>
                        <th>{{ __('messages.delivery_page.col_completed') }}</th>
                        <th class="nova-text-end">{{ __('messages.delivery_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $delivery->id }}</span></td>
                        <td>{{ $delivery->invoice?->invoice_number ?? '—' }}</td>
                        <td>{{ $delivery->invoice?->customer->name ?? '—' }}</td>
                        <td>
                            @if($delivery->driver)
                                <span class="nova-badge">{{ $delivery->driver->name ?? '—' }}</span>
                            @else
                                <span class="nova-text-muted">—</span>
                            @endif
                        </td>
                        <td>
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
                            @if($stage)
                                <span class="nova-badge {{ $badgeClass }}">{{ $stage->label() }}</span>
                            @else
                                <span class="nova-text-muted">{{ $delivery->current_stage ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $delivery->started_at ? \Carbon\Carbon::parse($delivery->started_at)->format('Y-m-d') : '—' }}</td>
                        <td>{{ $delivery->completed_at ? \Carbon\Carbon::parse($delivery->completed_at)->format('Y-m-d') : '—' }}</td>
                        <td class="nova-text-end">
                            <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                @if($delivery->canAdvance())
                                    <a href="javascript:void(0)" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.delivery_page.advance_stage') }}">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                @endif
                                <a href="javascript:void(0)" class="nova-icon-btn nova-btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-truck" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.delivery_page.empty') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="nova-card-footer">
            <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
                <div class="nova-text-muted nova-fs-sm">
                    {{ __('messages.delivery_page.showing_count', ['count' => $deliveries->count()]) }}
                </div>
                <div class="nova-pagination">
                    {{ $deliveries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection