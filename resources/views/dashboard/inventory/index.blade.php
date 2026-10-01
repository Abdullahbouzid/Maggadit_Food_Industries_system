@extends('dashboard.partials.layout')

@section('title', __('messages.inventory_page.title'))

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-warehouse" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.inventory_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.inventory_page.subtitle') }}
        </p>
    </div>
    <div class="nova-flex nova-gap-2">
        <a href="{{ route('inventory.alerts') }}" class="nova-btn">
            <i class="fas fa-triangle-exclamation"></i> {{ __('messages.inventory_page.alerts_title') }}
        </a>
        <a href="{{ route('inventory.movements') }}" class="nova-btn">
            <i class="fas fa-arrows-rotate"></i> {{ __('messages.inventory_page.movements_title') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

{{-- KPI Cards --}}
<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_warehouses') }}</div>
                <div class="nova-kpi-value">{{ $byWarehouse->count() }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card nova-kpi-danger">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_low_stock') }}</div>
                <div class="nova-kpi-value nova-text-danger">{{ $lowStockCount }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card nova-kpi-info">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_expiring') }}</div>
                <div class="nova-kpi-value nova-text-info">{{ $expiringCount }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('inventory.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 220px;">
                    <label class="nova-label">{{ __('messages.inventory_page.filter_warehouse') }}</label>
                    <select name="warehouse_id" class="nova-select">
                        <option value="">{{ __('messages.inventory_page.all_warehouses') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ (string) ($filters['warehouse_id'] ?? '') === (string) $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name_ar ?: $warehouse->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="min-width: 220px;">
                    <label class="nova-label">{{ __('messages.inventory_page.filter_item') }}</label>
                    <select name="item_id" class="nova-select">
                        <option value="">{{ __('messages.inventory_page.all_items') }}</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}"
                                {{ (string) ($filters['item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                {{ $item->name_ar ?: $item->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group nova-flex nova-items-center nova-gap-2">
                    <label class="nova-form-check nova-flex nova-items-center nova-gap-2">
                        <input type="checkbox" name="only_low" value="1" {{ request('only_low') ? 'checked' : '' }}>
                        <span>{{ __('messages.inventory_page.filter_low_stock') }}</span>
                    </label>
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.inventory_page.apply') }}
                    </button>
                    <a href="{{ route('inventory.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.inventory_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- One card per warehouse, one row per batch --}}
@forelse ($byWarehouse as $group)
    @php $warehouse = $group['warehouse']; @endphp

    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-warehouse" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
                {{ $warehouse?->name_ar ?: ($warehouse?->name_en ?: __('messages.inventory_page.unknown_warehouse')) }}
            </h3>
            <div class="nova-flex nova-gap-2 nova-items-center">
                @if ($group['low_count'] > 0)
                    <span class="nova-badge danger">
                        {{ $group['low_count'] }} {{ __('messages.inventory_page.status_low') }}
                    </span>
                @endif
                @if ($group['expired_count'] > 0)
                    <span class="nova-badge warning">
                        {{ $group['expired_count'] }} {{ __('messages.inventory_page.status_expired') }}
                    </span>
                @endif
                <span class="nova-badge info">
                    {{ __('messages.inventory_page.kpi_available') }}:
                    {{ rtrim(rtrim(number_format($group['total_available'], 2), '0'), '.') }}
                </span>
            </div>
        </div>
        <div class="nova-card-body">
            <div class="table-responsive">
                <table class="nova-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.inventory_page.alerts_product') }}</th>
                            <th>{{ __('messages.inventory_page.alerts_pack') }}</th>
                            <th>{{ __('messages.inventory_page.alerts_batch') }}</th>
                            <th>{{ __('messages.inventory_page.alerts_expiry') }}</th>
                            <th>{{ __('messages.inventory_page.alerts_available') }}</th>
                            <th>{{ __('messages.inventory_page.alerts_threshold') }}</th>
                            <th class="nova-text-end">{{ __('messages.inventory_page.alerts_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['rows'] as $row)
                            <tr style="{{ $row->is_low ? 'background: var(--nova-danger-bg, #fef2f2);' : '' }}">
                                <td>
                                    <div class="nova-fw-semibold">{{ $row->name }}</div>
                                    <div class="nova-text-muted nova-fs-sm">{{ $row->code }}</div>
                                </td>
                                <td>{{ $row->item_pack_id ?? '—' }}</td>
                                <td><span class="nova-badge info">{{ $row->batch_number }}</span></td>
                                <td>
                                    {{ $row->expiry_date ? \Carbon\Carbon::parse($row->expiry_date)->format('Y-m-d') : '—' }}
                                </td>
                                <td class="nova-fw-semibold">
                                    {{ rtrim(rtrim(number_format((float) $row->available_quantity, 2), '0'), '.') }}
                                </td>
                                <td class="nova-text-muted">
                                    {{ rtrim(rtrim(number_format((float) $row->low_stock_threshold, 2), '0'), '.') }}
                                </td>
                                <td class="nova-text-end">
                                    @if ($row->is_expired)
                                        <span class="nova-badge danger">{{ __('messages.inventory_page.status_expired') }}</span>
                                    @elseif ($row->is_low)
                                        <span class="nova-badge danger">{{ __('messages.inventory_page.status_low') }}</span>
                                    @else
                                        <span class="nova-badge success">{{ __('messages.inventory_page.status_ok') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@empty
    <div class="nova-card">
        <div class="nova-card-body nova-text-center nova-py-4">
            <div class="nova-empty">
                <i class="fas fa-warehouse" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                <p>{{ __('messages.inventory_page.empty') }}</p>
            </div>
        </div>
    </div>
@endforelse

@endsection
