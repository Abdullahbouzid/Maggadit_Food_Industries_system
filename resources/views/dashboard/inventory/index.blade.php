@extends('dashboard.partials.layout')

@section('title', __('messages.inventory_page.title'))

@section('content')

{{-- Hero Header --}}
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
</div>

{{-- KPI Cards --}}
<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_total_items') }}</div>
                <div class="nova-kpi-value">{{ $inventories->count() }}</div>
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
                <div class="nova-kpi-label">{{ __('messages.inventory_page.kpi_available') }}</div>
                <div class="nova-kpi-value nova-text-info">{{ number_format($inventories->sum('available_quantity'), 3) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('inventory.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 250px;">
                    <label class="nova-label">{{ __('messages.inventory_page.search_placeholder') }}</label>
                    <input type="text" name="search" class="nova-input" value="{{ request('search') }}" placeholder="{{ __('messages.inventory_page.search_placeholder') }}">
                </div>
                <div class="nova-form-group" style="min-width: 200px;">
                    <label class="nova-label">{{ __('messages.inventory_page.filter_warehouse') }}</label>
                    <select name="warehouse_id" class="nova-select">
                        <option value="">{{ __('messages.inventory_page.all_warehouses') }}</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ (request('warehouse_id') ?? '') === (string) $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name_ar ?? $warehouse->name_en ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group nova-flex nova-items-center nova-gap-2">
                    <label class="nova-form-check nova-flex nova-items-center nova-gap-2">
                        <input type="checkbox" name="low_stock_only" value="1" {{ request('low_stock_only') ? 'checked' : '' }}>
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

{{-- Inventory Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.inventory_page.col_item') }}</th>
                        <th>{{ __('messages.inventory_page.col_pack') }}</th>
                        <th>{{ __('messages.inventory_page.col_warehouse') }}</th>
                        <th>{{ __('messages.inventory_page.col_available') }}</th>
                        <th>{{ __('messages.inventory_page.col_reserved') }}</th>
                        <th>{{ __('messages.inventory_page.col_damaged') }}</th>
                        <th>{{ __('messages.inventory_page.col_reorder_level') }}</th>
                        <th>{{ __('messages.inventory_page.col_status') }}</th>
                        <th class="nova-text-end">{{ __('messages.inventory_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $inventory->id }}</span></td>
                        <td>{{ $inventory->item->name_ar ?? $inventory->item->name_en ?? '-' }}</td>
                        <td>{{ $inventory->pack->display_name ?? '-' }}</td>
                        <td>{{ $inventory->warehouse->name_ar ?? $inventory->warehouse->name_en ?? '-' }}</td>
                        <td class="nova-text-end">{{ number_format($inventory->available_quantity, 3) }}</td>
                        <td class="nova-text-end">{{ number_format($inventory->reserved_quantity, 3) }}</td>
                        <td class="nova-text-end">{{ number_format($inventory->damaged_quantity, 3) }}</td>
                        <td class="nova-text-end">{{ number_format($inventory->reorder_level, 3) }}</td>
                        <td>
                            @php
                                $isLow = $inventory->available_quantity <= $inventory->reorder_level;
                                $badgeClass = $isLow ? 'nova-badge-danger' : 'nova-badge-success';
                                $statusLabel = $isLow ? __('messages.inventory_page.status_low') : __('messages.inventory_page.status_ok');
                            @endphp
                            <span class="nova-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="nova-text-end">
                            <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                <a href="{{ route('inventory.movements', $inventory->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.inventory_page.view_movements') }}">
                                    <i class="fas fa-arrows-rotate"></i>
                                </a>
                                <a href="{{ route('inventory.adjust', $inventory->id) }}" class="nova-icon-btn nova-btn-sm nova-btn-primary" title="{{ __('messages.inventory_page.adjust') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-warehouse" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.inventory_page.empty') }}</p>
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
                    {{ __('messages.inventory_page.showing_count', ['count' => $inventories->count()]) }}
                </div>
                <div class="nova-pagination">
                    {{ $inventories->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
