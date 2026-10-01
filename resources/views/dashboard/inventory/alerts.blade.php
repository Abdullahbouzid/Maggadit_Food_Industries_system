@extends('dashboard.partials.layout')

@section('title', __('messages.inventory_page.alerts_title'))

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-triangle-exclamation" style="color: var(--nova-danger); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.inventory_page.alerts_title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.inventory_page.alerts_subtitle') }}
        </p>
    </div>
    <div class="nova-flex nova-gap-2">
        <form method="POST" action="{{ route('inventory.alerts.notify') }}">
            @csrf
            <input type="hidden" name="warehouse_id" value="{{ $filters['warehouse_id'] ?? '' }}">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-bell"></i> {{ __('messages.inventory_page.raise_alerts') }}
            </button>
        </form>
        <a href="{{ route('inventory.index') }}" class="nova-btn">
            <i class="fas fa-warehouse"></i> {{ __('messages.inventory_page.title') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_low_stock') }}</div>
                <div class="nova-kpi-value" style="color: var(--nova-danger);">{{ $lowCount }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_expiring') }}</div>
                <div class="nova-kpi-value" style="color: var(--nova-warning);">{{ $expiringCount }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_total_positions') }}</div>
                <div class="nova-kpi-value">{{ $rows->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('inventory.alerts') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 200px;">
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
                <div class="nova-form-group" style="min-width: 200px;">
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
                <div class="nova-form-group">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.delivery_page.apply') }}
                    </button>
                </div>
                <div class="nova-form-group">
                    <a href="{{ route('inventory.alerts') }}" class="nova-btn">
                        {{ __('messages.delivery_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="nova-card">
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
                    @forelse ($rows as $row)
                        <tr style="{{ $row->is_low ? 'background: var(--nova-danger-bg, #fef2f2);' : '' }}">
                            <td>
                                <div class="nova-fw-semibold">{{ $row->name }}</div>
                                <div class="nova-text-muted nova-fs-sm">{{ $row->code }}</div>
                            </td>
                            <td>{{ $row->pack_type_id ? __('messages.inventory_page.alerts_pack_id', ['id' => $row->pack_type_id]) : '—' }}</td>
                            <td>
                                <span class="nova-badge info">{{ $row->batch_number }}</span>
                            </td>
                            <td>{{ $row->expiry_date ? \Carbon\Carbon::parse($row->expiry_date)->format('Y-m-d') : '—' }}</td>
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
                    @empty
                        <tr>
                            <td colspan="7" class="nova-text-center nova-py-4">
                                <p class="nova-text-muted">{{ __('messages.inventory_page.alerts_empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
