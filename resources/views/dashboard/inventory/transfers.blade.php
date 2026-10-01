@extends('dashboard.partials.layout')

@section('title', __('messages.inventory_page.transfers_title'))

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-truck-ramp-box" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.inventory_page.transfers_title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.inventory_page.transfers_subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('inventory.alerts') }}" class="nova-btn">
            <i class="fas fa-triangle-exclamation"></i> {{ __('messages.inventory_page.alerts_title') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

<div class="nova-row nova-gap-4">
    <div class="nova-col-4">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.inventory_page.new_movement') }}</h3>
            </div>
            <div class="nova-card-body">
                <form method="POST" action="{{ route('inventory.transfers.store') }}">
                    @csrf

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.inventory_page.movement_type') }}</label>
                        <select name="movement_type" class="nova-select" id="movement-type" required>
                            <option value="transfer">{{ __('messages.inventory_page.type_transfer') }}</option>
                            <option value="out">{{ __('messages.inventory_page.type_dispense') }}</option>
                        </select>
                    </div>

                    <div class="nova-form-group" id="batch-item-group">
                        <label class="nova-label">{{ __('messages.inventory_page.alerts_batch') }}</label>
                        <select name="batch_item_id" class="nova-select">
                            <option value="">{{ __('messages.inventory_page.select_batch') }}</option>
                            @foreach ($batchItems as $batchItem)
                                <option value="{{ $batchItem->id }}">
                                    {{ $batchItem->item?->name_ar ?: ($batchItem->item?->name_en ?: 'منتج') }}
                                    — {{ $batchItem->batch?->batch_number ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                        @error('batch_item_id')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group d-none" id="pack-group">
                        <label class="nova-label">{{ __('messages.inventory_page.alerts_pack') }}</label>
                        <select name="item_pack_id" class="nova-select">
                            <option value="">{{ __('messages.inventory_page.select_pack') }}</option>
                            @foreach ($packs as $pack)
                                <option value="{{ $pack->id }}">
                                    {{ $pack->item?->name_ar ?: ($pack->item?->name_en ?: 'منتج') }}
                                    — {{ $pack->packType?->name ?? $pack->id }}
                                </option>
                            @endforeach
                        </select>
                        @error('item_pack_id')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                        <p class="nova-text-muted nova-fs-sm" style="margin-top: 0.375rem;">
                            {{ __('messages.inventory_page.fifo_hint') }}
                        </p>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.inventory_page.from_warehouse') }}</label>
                        <select name="from_warehouse_id" class="nova-select" required>
                            <option value="">{{ __('messages.inventory_page.select_warehouse') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name_ar ?: $warehouse->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('from_warehouse_id')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group" id="to-warehouse-group">
                        <label class="nova-label">{{ __('messages.inventory_page.to_warehouse') }}</label>
                        <select name="to_warehouse_id" class="nova-select">
                            <option value="">{{ __('messages.inventory_page.select_warehouse') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name_ar ?: $warehouse->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_warehouse_id')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.inventory_page.quantity') }}</label>
                        <input type="number" name="quantity" step="0.001" min="0.001" class="nova-input" required>
                        @error('quantity')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.inventory_page.notes') }}</label>
                        <input type="text" name="notes" class="nova-input">
                    </div>

                    <button type="submit" class="nova-btn nova-btn-primary nova-w-100">
                        <i class="fas fa-check"></i> {{ __('messages.inventory_page.save_movement') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="nova-col-8">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.inventory_page.movements_history') }}</h3>
            </div>
            <div class="nova-card-body">
                <div class="table-responsive">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.inventory_page.col_date') }}</th>
                                <th>{{ __('messages.inventory_page.movement_type') }}</th>
                                <th>{{ __('messages.inventory_page.alerts_product') }}</th>
                                <th>{{ __('messages.inventory_page.alerts_pack') }}</th>
                                <th>{{ __('messages.inventory_page.alerts_batch') }}</th>
                                <th>{{ __('messages.inventory_page.from_warehouse') }}</th>
                                <th>{{ __('messages.inventory_page.to_warehouse') }}</th>
                                <th>{{ __('messages.inventory_page.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr>
                                    <td>{{ $movement->movement_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>
                                        @if ($movement->movement_type === 'transfer')
                                            <span class="nova-badge info">{{ __('messages.inventory_page.type_transfer') }}</span>
                                        @else
                                            <span class="nova-badge danger">{{ __('messages.inventory_page.type_dispense') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $movement->batchItem?->item?->name_ar ?: ($movement->batchItem?->item?->name_en ?: '—') }}</td>
                                    <td>{{ $movement->batchItem?->pack?->packType?->name ?? '—' }}</td>
                                    <td><span class="nova-badge info">{{ $movement->batchItem?->batch?->batch_number ?? '—' }}</span></td>
                                    <td>{{ $movement->fromWarehouse?->name_ar ?: ($movement->fromWarehouse?->name_en ?: '—') }}</td>
                                    <td>{{ $movement->toWarehouse?->name_ar ?: ($movement->toWarehouse?->name_en ?: '—') }}</td>
                                    <td class="nova-fw-semibold">
                                        {{ rtrim(rtrim(number_format((float) $movement->quantity, 3), '0'), '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="nova-text-center nova-py-4">
                                        <p class="nova-text-muted">{{ __('messages.inventory_page.no_movements') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // A transfer moves a specific batch; a dispense picks batches by FIFO, so
    // the batch selector is irrelevant there.
    const typeSelect = document.getElementById('movement-type');
    const batchGroup = document.getElementById('batch-item-group');
    const packGroup = document.getElementById('pack-group');
    const toGroup = document.getElementById('to-warehouse-group');

    function syncFields() {
        const isTransfer = typeSelect.value === 'transfer';
        batchGroup.classList.toggle('d-none', !isTransfer);
        toGroup.classList.toggle('d-none', !isTransfer);
        packGroup.classList.toggle('d-none', isTransfer);
        batchGroup.querySelector('select').required = isTransfer;
        packGroup.querySelector('select').required = !isTransfer;
    }

    typeSelect.addEventListener('change', syncFields);
    syncFields();
</script>
@endpush
