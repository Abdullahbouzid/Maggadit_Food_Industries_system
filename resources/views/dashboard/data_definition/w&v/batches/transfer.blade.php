@extends('dashboard.partials.layout')

@section('title', 'نقل الكميات: ' . $batch->batch_number)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-truck-moving" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            نقل الكميات من الدفعة: {{ $batch->batch_number }}
        </h2>
        <p class="nova-page-subtitle">انقل الكميات بين المخازن مع تسجيل الحركة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.show', $batch) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة لتفاصيل الدفعة
        </a>
    </div>
</div>

{{-- ===== Validation Errors ===== --}}
@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>يوجد أخطاء في الإدخال:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('production-batches.transfer.store', $batch) }}" method="POST">
    @csrf

    <div class="nova-row">
        {{-- Batch Info & Warehouses --}}
        <div class="nova-col nova-col-lg-4">
            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-info-circle me-1"></i> معلومات الدفعة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <table class="nova-table">
                        <tr>
                            <th>رقم الدفعة</th>
                            <td><code>{{ $batch->batch_number }}</code></td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنتاج</th>
                            <td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '—' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الانتهاء</th>
                            <td>{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '—' }}</td>
                        </tr>
                        <tr>
                            <th>الحالة</th>
                            <td>
                                @php
                                    $statusMap = [
                                        'active' => ['label' => 'نشطة', 'color' => 'success'],
                                        'expired' => ['label' => 'منتهية', 'color' => 'warning'],
                                        'consumed' => ['label' => 'مستهلكة', 'color' => 'secondary'],
                                    ];
                                @endphp
                                <span class="nova-badge {{ $statusMap[$batch->status]['color'] ?? 'secondary' }}">
                                    {{ $statusMap[$batch->status]['label'] ?? $batch->status }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="nova-card nova-mb-4">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-home me-1"></i> المخازن
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-form-group">
                        <label class="nova-label">المخزن المصدر <span class="nova-text-danger">*</span></label>
                        <select name="from_warehouse_id" id="from_warehouse_id"
                                class="nova-select @error('from_warehouse_id') nova-invalid @enderror" required>
                            <option value="">-- اختر --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name_en }} ({{ $warehouse->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('from_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">المخزن الهدف <span class="nova-text-danger">*</span></label>
                        <select name="to_warehouse_id" id="to_warehouse_id"
                                class="nova-select @error('to_warehouse_id') nova-invalid @enderror" required>
                            <option value="">-- اختر --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name_en }} ({{ $warehouse->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('to_warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">تاريخ النقل</label>
                        <input type="date" name="transfer_date" class="nova-input" value="{{ old('transfer_date', now()->toDateString()) }}">
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">ملاحظات</label>
                        <textarea name="notes" class="nova-input" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="nova-col nova-col-lg-8">
            <div class="nova-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-shopping-bag me-1"></i> المنتجات المراد نقلها
                    </h5>
                </div>
                <div class="nova-card-body">
                    @if($batch->batchItems->count())
                        <div class="nova-table-wrapper">
                            <div class="nova-table-scroll">
                                <table class="nova-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>المنتج</th>
                                            <th>العبوة</th>
                                            <th>الكمية الإجمالية</th>
                                            <th>الموزعة</th>
                                            <th>المتبقية</th>
                                            <th>الكمية في المصدر</th>
                                            <th>الكمية المراد نقلها</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        @foreach($batch->batchItems as $index => $item)
                                            @php
                                                $remaining = $item->quantity - $item->dispatched_quantity;
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->item->name_en ?? 'غير معروف' }}</td>
                                                <td>{{ $item->pack ? $item->pack->barcode . ' (كمية: ' . $item->pack->quantity_in_pack . ')' : 'بدون عبوة' }}</td>
                                                <td>{{ number_format($item->quantity, 3) }}</td>
                                                <td>{{ number_format($item->dispatched_quantity, 3) }}</td>
                                                <td>{{ number_format($remaining, 3) }}</td>
                                                <td id="stock-{{ $item->id }}">0</td>
                                                <td>
                                                    <input type="hidden" name="items[{{ $index }}][batch_item_id]" value="{{ $item->id }}">
                                                    <input type="number" step="0.001" min="0" max="{{ $remaining }}"
                                                           name="items[{{ $index }}][quantity]"
                                                           class="nova-input transfer-qty" value="0"
                                                           @if($remaining == 0) disabled @endif>
                                                    @if($remaining == 0)
                                                        <small class="nova-text-success">تم التوزيع بالكامل</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="nova-text-muted nova-text-center">لا توجد منتجات في هذه الدفعة للنقل.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Submit ===== --}}
    <div class="nova-flex nova-gap-2 nova-mt-4">
        <button type="submit" class="nova-btn nova-btn-success nova-btn-lg">
            <i class="fas fa-truck-moving"></i> نقل الكميات
        </button>
        <a href="{{ route('production-batches.show', $batch) }}" class="nova-btn nova-btn-secondary nova-btn-lg">إلغاء</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fromWarehouse = document.getElementById('from_warehouse_id');
        const batchId = {{ $batch->id }};

        function loadWarehouseStock(warehouseId) {
            if (!warehouseId) {
                document.querySelectorAll('[id^="stock-"]').forEach(el => {
                    el.textContent = '0';
                });
                return;
            }

            fetch(`/production-batches/${batchId}/warehouse-stock/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('[id^="stock-"]').forEach(el => {
                        const batchItemId = el.id.replace('stock-', '');
                        const qty = data[batchItemId] || 0;
                        el.textContent = qty.toFixed(3);
                        const input = el.closest('tr').querySelector('.transfer-qty');
                        if (input) {
                            input.setAttribute('max', qty);
                        }
                    });
                })
                .catch(error => console.error('خطأ في تحميل الأرصدة:', error));
        }

        fromWarehouse.addEventListener('change', function() {
            loadWarehouseStock(this.value);
        });

        // Update max when quantity input changes
        document.querySelectorAll('.transfer-qty').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.getAttribute('max'));
                const val = parseFloat(this.value);
                if (val > max) {
                    this.value = max;
                }
                if (val < 0) {
                    this.value = 0;
                }
            });
        });
    });
</script>
@endpush
