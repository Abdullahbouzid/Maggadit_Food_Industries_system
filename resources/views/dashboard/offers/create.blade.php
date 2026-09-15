@extends('dashboard.partials.layout')

@section('title', 'Add Offer')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-plus-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
    </div>
</div>

<form action="{{ route('offers.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-{{ __('messages.app.page_') }}">
                <i class="fas fa-edit"></i>
                {{ __('messages.app.page__2') }}
            </h2>
        </div>
        <div class="nova-card-body">
            <div class="nova-row cols-2">
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.dashboard.price_lists') }} <span class="nova-text-danger">*</span></label>
                    <select name="price_list_id" id="price-list-select" class="nova-select">
                        <option value="">{{ __('messages.app.page__18') }}</option>
                        @foreach($priceLists as $pl)
                            <option value="{{ $pl->id }}" {{ old('price_list_id') == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="nova-form-help">{{ __('messages.dashboard.calculated_automatically') }}</div>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.dashboard.product') }} <span class="nova-text-danger">*</span></label>
                    <select name="item_id" id="item-select" class="nova-select" required>
                        <option value="">{{ __('messages.app.page__19') }}</option>
                        @foreach($items as $item)
                            <option value="{{ $item['id'] }}" 
                                            data-price="{{ $item['price'] }}"
                                            data-packs="{{ json_encode($item['packs']) }}"
                                            data-name="{{ $item['name'] }}"
                                            data-pack="{{ $item['pack_type'] }}"
                                            {{ old('item_id') == $item['id'] ? 'selected' : '' }}>
                                {{ $item['full_name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="selected-item-info" class="nova-alert info" style="display: none; margin-top: 0.5rem;">
                        <small class="nova-text-muted">Current price: <strong id="current-price">0</strong> SAR</small>
                        <small class="nova-text-muted d-block" id="current-pack"></small>
                    </div>
                </div>
                <div class="nova-form-group nova-hidden" id="pack-select-container">
                    <label class="nova-label">{{ __('messages.dashboard.pack') }} <span class="nova-text-danger">*</span></label>
                    <select name="item_pack_id" id="pack-select" class="nova-select">
                        <option value="">{{ __('messages.app.page__20') }}</option>
                    </select>
                    <div id="selected-pack-info" class="nova-alert info" style="display: none; margin-top: 0.5rem;">
                        <label class="nova-label">{{ __('messages.dashboard.pack') }} price: <strong id="pack-price">0</strong> SAR</label>
                        <small class="nova-text-muted d-block">Quantity: <strong id="pack-quantity">0</strong></small>
                    </div>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.app.page__9') }}</label>
                    <input type="text" name="{{ __('messages.app.page_') }}" class="nova-input" value="{{ old('{{ __('messages.app.page_') }}') }}" placeholder="Example: Weekend Discount">
                </div>
            </div>
            <div class="nova-form-group">
                <label class="nova-label">{{ __('messages.app.page__10') }}</label>
                <textarea name="description" rows="3" class="nova-textarea" placeholder="Brief description for the offer...">{{ old('description') }}</textarea>
            </div>
            <div class="nova-row cols-3">
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.dashboard.old_price') }} <span class="nova-text-danger">*</span></label>
                    <input type="number" step="0.01" name="old_price" id="old-price" class="nova-input" value="{{ old('old_price') }}" required>
                    <small class="nova-help-text" id="price-source-info"></small>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.dashboard.new_price') }} <span class="nova-text-danger">*</span></label>
                    <input type="number" step="0.01" name="new_price" id="new-price" class="nova-input" value="{{ old('new_price') }}" required>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">{{ __('messages.app.page__13') }}</label>
                    <input type="number" step="0.01" name="discount_percentage" id="discount-percentage" class="nova-input" value="{{ old('discount_percentage') }}" readonly>
                    <div class="nova-form-help">{{ __('messages.dashboard.calculated_automatically') }}</div>
                </div>
            </div>
            <div class="nova-row cols-2">
                <div class="nova-form-group">
                    <label class="nova-label">Start Date <span class="nova-text-danger">*</span></label>
                    <input type="datetime-local" name="start_date" class="nova-input" value="{{ old('start_date') }}" required>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">End Date <span class="nova-text-danger">*</span></label>
                    <input type="datetime-local" name="end_date" class="nova-input" value="{{ old('end_date') }}" required>
                </div>
            </div>
            <div class="nova-form-group">
                <label class="nova-label">{{ __('messages.app.page__16') }}</label>
                <input type="file" name="image" class="nova-input" accept="image/*">
            </div>
            <div class="nova-form-group">
                <label class="nova-switch">
                    <input type="checkbox" name="is_active" class="nova-switch-input" id="is{{ __('messages.app.page__3') }}" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                    <span class="nova-switch-slider"></span>
                    <span class="nova-fw-semibold">{{ __('messages.app.page__3') }}</span>
                </label>
            </div>
        </div>
        <div class="nova-card-footer">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-save"></i> {{ __('messages.app.page__4') }}
            </button>
            <a href="{{ route('offers.index') }}" class="nova-btn nova-btn-secondary">
                <i class="fas fa-times"></i> {{ __('messages.app.page__5') }}
            </a>
        </div>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.1/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px; padding: 4px 12px; border: 1.5px solid var(--nova-border); border-radius: 10px;
        background: var(--nova-surface); color: var(--nova-text); font-size: 0.95rem;
    }
    .select2-container--default .select2-selection--single:focus {
        border-color: var(--nova-primary);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#item-select').select2({ placeholder: 'Search product...', allowClear: true, width: '100%' });
        }

        function fetchItemPrice() {
            const itemId = $('#item-select').val();
            const packId = $('#pack-select').val();
            const priceListId = $('#price-list-select').val();
            if (!itemId) return;
            $.ajax({
                url: "{{ route('offers.get_item_price') }}",
                data: { item_id: itemId, item_pack_id: packId, price_list_id: priceListId },
                success: function(res) {
                    if (res.success && res.price > 0) {
                        $('#old-price').val(res.price);
                        $('#price-source-info').text('Source: ' + res.source + ' (' + res.price + ' SAR)');
                        calculateDiscount();
                    }
                }
            });
        }

        $('#price-list-select').on('change', function() { fetchItemPrice(); });

        $('#item-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const pack = selectedOption.data('pack') || '';
            const packsData = selectedOption.data('packs');
            let packs = [];
            if (packsData) {
                try { packs = typeof packsData === 'string' ? JSON.parse(packsData) : packsData; } catch(e) { packs = []; }
            }
            const packSelect = $('#pack-select');
            packSelect.empty();
            if (packs.length > 0) {
                packSelect.append('<option value="">{{ __('messages.app.page__20') }}</option>');
                packs.forEach(function(p) {
                    packSelect.append('<option value="' + p.id + '" data-price="' + (p.price || 0) + '" data-quantity="' + (p.quantity || 0) + '">{{ __('messages.app.page__22') }}</option>');
                });
                $('#pack-select-container').removeClass('nova-hidden');
                packSelect.val(packs[0].id).trigger('change');
            } else {
                packSelect.append('<option value="" data-price="' + price + '" data-quantity="0">{{ __('messages.app.page__23') }}</option>');
                $('#pack-select-container').removeClass('nova-hidden');
                fetchItemPrice();
            }
            $('#current-pack').text(pack ? 'Unit: ' + pack : '');
        });

        $('#pack-select').on('change', function() { fetchItemPrice(); });
        $('#new-price, #old-price').on('input change', function() { calculateDiscount(); });

        function calculateDiscount() {
            const oldPrice = parseFloat($('#old-price').val()) || 0;
            const newPrice = parseFloat($('#new-price').val()) || 0;
            if (oldPrice > 0 && newPrice > 0 && newPrice < oldPrice) {
                const discount = ((oldPrice - newPrice) / oldPrice) * 100;
                $('#discount-percentage').val(discount.toFixed(2));
            } else {
                $('#discount-percentage').val('');
            }
        }
    });
</script>
@endpush