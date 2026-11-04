@extends('dashboard.partials.layout')

@section('title', __('messages.inventory_page.movements_title'))

@section('content')

{{-- Hero Header --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-exchange-alt" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.inventory_page.movements_title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.inventory_page.movements_subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('inventory.index') }}" class="nova-btn nova-btn-outline">
            <i class="fas fa-arrow-right"></i> {{ __('messages.inventory_page.back_to_inventory') }}
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('inventory.movements') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 200px;">
                    <label class="nova-label">{{ __('messages.inventory_page.filter_type') }}</label>
                    <select name="type" class="nova-select">
                        <option value="">{{ __('messages.inventory_page.type_all') }}</option>
                        @foreach(\App\Enums\InventoryMovementType::cases() as $type)
                            <option value="{{ $type->value }}" {{ (request('type') ?? '') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
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
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.inventory_page.col_date') }}</label>
                    <input type="date" name="date_from" class="nova-input" value="{{ request('date_from') }}">
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">&nbsp;</label>
                    <input type="date" name="date_to" class="nova-input" value="{{ request('date_to') }}">
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.inventory_page.apply') }}
                    </button>
                    <a href="{{ route('inventory.movements') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.inventory_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Type Tabs --}}
<div class="nova-status-tabs">
    <a href="{{ route('inventory.movements', array_merge(request()->except(['type', 'page']), ['type' => null])) }}"
       class="nova-status-tab {{ empty(request('type')) ? 'active' : '' }}">
        {{ __('messages.inventory_page.type_all') }}
        <span class="nova-badge">{{ $typeCounts->total ?? $movements->count() }}</span>
    </a>
    @foreach(\App\Enums\InventoryMovementType::cases() as $type)
        <a href="{{ route('inventory.movements', array_merge(request()->except(['type', 'page']), ['type' => $type->value])) }}"
           class="nova-status-tab {{ (request('type') ?? '') === $type->value ? 'active' : '' }}">
            {{ $type->label() }}
            <span class="nova-badge">{{ $typeCounts[$type->value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Movements Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.inventory_page.col_date') }}</th>
                        <th>{{ __('messages.inventory_page.col_type') }}</th>
                        <th>{{ __('messages.inventory_page.col_item') }}</th>
                        <th>{{ __('messages.inventory_page.col_pack') }}</th>
                        <th>{{ __('messages.inventory_page.col_warehouse') }}</th>
                        <th>{{ __('messages.inventory_page.col_quantity') }}</th>
                        <th>{{ __('messages.inventory_page.col_balance_after') }}</th>
                        <th>{{ __('messages.inventory_page.col_reference') }}</th>
                        <th>{{ __('messages.inventory_page.col_created_by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $movement->id }}</span></td>
                        <td>{{ $movement->created_at ? \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') : '—' }}</td>
                        <td>
                            @php
                                $type = \App\Enums\InventoryMovementType::tryFrom($movement->type);
                                $badgeClass = match ($type?->value ?? '') {
                                    'in' => 'nova-badge-success',
                                    'out' => 'nova-badge-danger',
                                    'reserve' => 'nova-badge-warning',
                                    'release' => 'nova-badge-accent',
                                    'adjustment' => 'nova-badge-info',
                                    'damage' => 'nova-badge-danger',
                                    'return_good' => 'nova-badge-success',
                                    'return_damaged' => 'nova-badge-warning',
                                    default => '',
                                };
                            @endphp
                            @if($type)
                                <span class="nova-badge {{ $badgeClass }}">{{ $type->label() }}</span>
                            @else
                                <span class="nova-text-muted">{{ $movement->type ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $movement->item->name_ar ?? $movement->item->name_en ?? '-' }}</td>
                        <td>{{ $movement->pack->display_name ?? '-' }}</td>
                        <td>{{ $movement->warehouse->name_ar ?? $movement->warehouse->name_en ?? '-' }}</td>
                        <td class="nova-text-end">{{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 3) }}</td>
                        <td class="nova-text-end">{{ number_format($movement->balance_after, 3) }}</td>
                        <td>
                            @if($movement->reference_type && $movement->reference_id)
                                {{ $movement->reference_type }} #{{ $movement->reference_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $movement->created_by ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-exchange-alt" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.inventory_page.no_movements') }}</p>
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
                    {{ __('messages.inventory_page.showing_count', ['count' => $movements->count()]) }}
                </div>
                <div class="nova-pagination">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
