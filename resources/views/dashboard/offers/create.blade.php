@extends('dashboard.partials.layout')

@section('title', 'إضافة عرض')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="plus-circle" class="me-2"></i>
                            إضافة عرض جديد
                        </h1>
                    </div>
                </div>

                <form action="{{ route('offers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">قائمة الأسعار المطبقة</label>
                                    <select name="price_list_id" id="price-list-select" class="form-control">
                                        <option value="">اختر قائمة الأسعار (افتراضي/الكل)</option>
                                        @foreach($priceLists as $pl)
                                            <option value="{{ $pl->id }}" {{ old('price_list_id') == $pl->id ? 'selected' : '' }}>
                                                {{ $pl->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">سيتم جلب آخر تحديث لسعر المنتج في القائمة المختارة</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">المنتج <span class="text-danger">*</span></label>
                                    <select name="item_id" id="item-select" class="form-control" required>
                                        <option value="">اختر المنتج</option>
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
                                    <div id="selected-item-info" class="mt-2 p-2 bg-light rounded d-none">
                                        <small class="text-muted">السعر الحالي: <strong id="current-price">0</strong> ر.س</small>
                                        <small class="text-muted d-block" id="current-pack"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 d-none" id="pack-select-container">
                                    <label class="form-label fw-semibold">العبوة <span class="text-danger">*</span></label>
                                    <select name="item_pack_id" id="pack-select" class="form-control">
                                        <option value="">اختر العبوة</option>
                                    </select>
                                    <div id="selected-pack-info" class="mt-2 p-2 bg-light rounded d-none">
                                        <small class="text-muted">سعر العبوة: <strong id="pack-price">0</strong> ر.س</small>
                                        <small class="text-muted d-block">الكمية المتوفرة: <strong id="pack-quantity">0</strong></small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">عنوان العرض</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="مثال: خصم نهاية الأسبوع">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">وصف العرض</label>
                                    <textarea name="description" rows="3" class="form-control" placeholder="وصف مختصر للعرض...">{{ old('description') }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">السعر القديم <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="old_price" id="old-price" class="form-control" value="{{ old('old_price') }}" required>
                                    <small id="price-source-info" class="text-primary font-weight-bold d-block mt-1"></small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">السعر الجديد <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="new_price" id="new-price" class="form-control" value="{{ old('new_price') }}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">نسبة الخصم (%)</label>
                                    <input type="number" step="0.01" name="discount_percentage" id="discount-percentage" class="form-control" value="{{ old('discount_percentage') }}" readonly>
                                    <div class="form-text">يُحسب تلقائياً</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ البداية <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ النهاية <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">صورة العرض</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="isActive">نشط</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i data-feather="save"></i> حفظ العرض
                                    </button>
                                    <a href="{{ route('offers.index') }}" class="btn btn-light btn-lg px-5 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.1/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 4px 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        if (typeof $.fn.select2 !== 'undefined') {
            $('#item-select').select2({
                placeholder: 'ابحث عن منتج...',
                allowClear: true,
                width: '100%'
            });
        }

        function fetchItemPrice() {
            const itemId = $('#item-select').val();
            const packId = $('#pack-select').val();
            const priceListId = $('#price-list-select').val();

            if (!itemId) return;

            $.ajax({
                url: "{{ route('offers.get_item_price') }}",
                data: {
                    item_id: itemId,
                    item_pack_id: packId,
                    price_list_id: priceListId
                },
                success: function(res) {
                    if (res.success && res.price > 0) {
                        $('#old-price').val(res.price);
                        $('#price-source-info').text('المصدر: ' + res.source + ' (' + res.price + ' ر.س)');
                        calculateDiscount();
                    }
                }
            });
        }

        $('#price-list-select').on('change', function() {
            fetchItemPrice();
        });

        $('#item-select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const pack = selectedOption.data('pack') || '';
            const packsData = selectedOption.data('packs');
            
            let packs = [];
            if (packsData) {
                try {
                    packs = typeof packsData === 'string' ? JSON.parse(packsData) : packsData;
                } catch (e) {
                    packs = [];
                }
            }
            
            const packSelect = $('#pack-select');
            packSelect.empty();
            
            if (packs.length > 0) {
                packSelect.append('<option value="">اختر العبوة</option>');
                
                packs.forEach(function(p) {
                    const packName = p.name || 'بدون اسم';
                    const packPrice = parseFloat(p.price) || 0;
                    const packQty = parseFloat(p.quantity) || 0;
                    packSelect.append('<option value="' + p.id + '" data-price="' + packPrice + '" data-quantity="' + packQty + '">' + packName + ' - ' + packPrice.toFixed(2) + ' ر.س</option>');
                });
                
                $('#pack-select-container').removeClass('d-none');
                packSelect.val(packs[0].id).trigger('change');
            } else {
                packSelect.append('<option value="" data-price="' + price + '" data-quantity="0">بدون عبوة</option>');
                $('#pack-select-container').removeClass('d-none');
                fetchItemPrice();
            }
            
            $('#current-pack').text(pack ? 'الوحدة: ' + pack : '');
        });

        $('#pack-select').on('change', function() {
            fetchItemPrice();
        });

        $('#new-price, #old-price').on('input change', function() {
            calculateDiscount();
        });

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
