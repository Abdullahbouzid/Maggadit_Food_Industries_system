@extends('dashboard.partials.layout')

@section('title', 'Edit Offer')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Edit Offer
        </h2>
    </div>
</div>

<form action="{{ route('offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title">
                <i class="fas fa-edit"></i>
                Offer Details
            </h2>
        </div>
        <div class="nova-card-body">
            <div class="nova-row cols-2">
                <div class="nova-form-group">
                    <label class="nova-label">Product <span class="nova-text-danger">*</span></label>
                    <select name="item_id" id="item-select" class="nova-select" required>
                        <option value="">Select product</option>
                        @foreach($items as $item)
                            <option value="{{ $item['id'] }}" 
                                            data-price="{{ $item['price'] }}"
                                            data-packs="{{ json_encode($item['packs']) }}"
                                            data-name="{{ $item['name'] }}"
                                            data-pack="{{ $item['pack_type'] }}"
                                            {{ old('item_id', $offer->item_id) == $item['id'] ? 'selected' : '' }}>
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
                    <label class="nova-label">Pack <span class="nova-text-danger">*</span></label>
                    <select name="item_pack_id" id="pack-select" class="nova-select">
                        <option value="">Select pack</option>
                    </select>
                    <div id="selected-pack-info" class="nova-alert info" style="display: none; margin-top: 0.5rem;">
                        <small class="nova-text-muted">Pack price: <strong id="pack-price">0</strong> SAR</small>
                        <small class="nova-text-muted d-block">Quantity: <strong id="pack-quantity">0</strong></small>
                    </div>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Offer Title</label>
                    <input type="text" name="title" class="nova-input" value="{{ old('title', $offer->title) }}" placeholder="Example: Weekend Discount">
                </div>
            </div>
            <div class="nova-form-group">
                <label class="nova-label">Description</label>
                <textarea name="description" rows="3" class="nova-textarea" placeholder="Brief description for the offer...">{{ old('description', $offer->description) }}</textarea>
            </div>
            <div class="nova-row cols-3">
                <div class="nova-form-group">
                    <label class="nova-label">Old Price <span class="nova-text-danger">*</span></label>
                    <input type="number" step="0.01" name="old_price" id="old-price" class="nova-input" value="{{ old('old_price', $offer->old_price) }}" required>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">New Price <span class="nova-text-danger">*</span></label>
                    <input type="number" step="0.01" name="new_price" id="new-price" class="nova-input" value="{{ old('new_price', $offer->new_price) }}" required>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">Discount (%)</label>
                    <input type="number" step="0.01" name="discount_percentage" id="discount-percentage" class="nova-input" value="{{ old('discount_percentage', $offer->discount_percentage) }}" readonly>
                    <div class="nova-form-help">Calculated automatically</div>
                </div>
            </div>
            <div class="nova-row cols-2">
                <div class="nova-form-group">
                    <label class="nova-label">Start Date <span class="nova-text-danger">*</span></label>
                    <input type="datetime-local" name="start_date" class="nova-input" value="{{ old('start_date', $offer->start_date?->format('Y-m-d\\TH:i')) }}" required>
                </div>
                <div class="nova-form-group">
                    <label class="nova-label">End Date <span class="nova-text-danger">*</span></label>
                    <input type="datetime-local" name="end_date" class="nova-input" value="{{ old('end_date', $offer->end_date?->format('Y-m-d\\TH:i')) }}" required>
                </div>
            </div>
            <div class="nova-form-group">
                <label class="nova-label">Offer Image</label>
                <input type="file" name="image" class="nova-input" accept="image/*">
                @if($offer->image)
                    <img src="{{ asset('storage/' . $offer->image) }}" alt="Current offer image" class="nova-mt-2 rounded" style="max-height: 150px;">
                @endif
            </div>
            <div class="nova-form-group">
                <label class="nova-switch">
                    <input type="checkbox" name="is_active" class="nova-switch-input" id="isActive" value="1" {{ $offer->is_active ? 'checked' : '' }}>
                    <span class="nova-switch-slider"></span>
                    <span class="nova-fw-semibold">Active</span>
                </label>
            </div>
        </div>
        <div class="nova-card-footer">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="{{ route('offers.index') }}" class="nova-btn nova-btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        $('#item-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const pack = selectedOption.data('pack') || '';
            let packs = [];
            try { packs = JSON.parse(selectedOption.data('packs') || '[]'); } catch (e) { packs = []; }
            
            $('#pack-select-container').addClass('nova-hidden');
            $('#pack-select').val('');
            
            if (packs.length > 0) {
                const packSelect = $('#pack-select');
                packSelect.empty().append('<option value="">Select pack</option>');
                packs.forEach(function(p) {
                    packSelect.append('<option value="' + p.id + '" data-price="' + (p.price || 0) + '" data-quantity="' + (p.quantity || 0) + '">' + (p.name || '') + ' - ' + (p.price || 0) + ' SAR</option>');
                });
                $('#pack-select-container').removeClass('nova-hidden');
                packSelect.val(packs[0].id).trigger('change');
            } else if (price > 0) {
                $('#old-price').val(price);
                $('#current-price').text(price.toFixed(2));
                $('#selected-item-info').removeClass('nova-hidden');
            }
            $('#current-pack').text(pack ? 'Unit: ' + pack : '');
            calculateDiscount();
        });

        $('#pack-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const quantity = parseFloat(selectedOption.data('quantity')) || 0;
            if (price > 0) {
                $('#old-price').val(price);
                $('#pack-price').text(price.toFixed(2));
                $('#pack-quantity').text(quantity);
                $('#selected-pack-info').removeClass('nova-hidden');
                $('#selected-item-info').removeClass('nova-hidden');
            } else {
                $('#old-price').val('');
                $('#selected-pack-info').addClass('nova-hidden');
            }
            calculateDiscount();
        });

        function calculateDiscount() {
            const oldPrice = parseFloat($('#old-price').val()) || 0;
            const newPrice = parseFloat($('#new-price').val()) || 0;
            if (oldPrice > 0 && newPrice > 0 && newPrice < oldPrice) {
                const discount = ((oldPrice - newPrice) / oldPrice) * 100;
                $('#discount-percentage').val(discount.toFixed(2));
            } else { $('#discount-percentage').val(''); }
        }
    });
</script>
@endpush