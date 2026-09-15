@extends('dashboard.partials.layout')

@section('title', 'توزيع الدفعة: ' . $batch->batch_number)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-share-alt" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            توزيع الدفعة: {{ $batch->batch_number }}
        </h2>
        <p class="nova-page-subtitle">قم بتوزيع المنتجات من هذه الدفعة إلى أحد المخازن</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.show', $batch) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة لتفاصيل الدفعة
        </a>
    </div>
</div>

{{-- ===== Validation خطأs ===== --}}
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

{{-- ===== Fully Distributed تحذير ===== --}}
@if(isset($isFullyDistributed) && $isFullyDistributed)
    <div class="nova-alert warning">
        <i class="fas fa-lock"></i>
        <strong>تم توزيع كافة كميات هذه الدفعة مسبقاً. لا يمكن التوزيع مرة أخرى.</strong>
    </div>
@endif

<form action="{{ route('production-batches.distribute.store', $batch) }}" method="POST">
    @csrf

    {{-- ============================================================
         Distribution Layout (Sidebar + Content)
         ============================================================ --}}
    <div class="distribution-layout">
        {{-- ===== Sidebar: Batch Info + Warehouse Selection ===== --}}
        <aside class="distribution-sidebar">
            {{-- Batch Info --}}
            <div class="nova-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-info-circle me-1"></i> معلومات الدفعة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-hashtag info-icon"></i>
                                رقم الدفعة
                            </span>
                            <span class="info-value">
                                <code class="info-code">{{ $batch->batch_number }}</code>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-industry info-icon"></i>
                                تاريخ الإنتاج
                            </span>
                            <span class="info-value">
                                {{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '—' }}
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-calendar-times info-icon"></i>
                                تاريخ الانتهاء
                            </span>
                            <span class="info-value">
                                {{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warehouse Selection --}}
            <div class="nova-card">
                <div class="nova-card-header">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-warehouse me-1"></i> المخزن المستهدف
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            اختر المخزن <span class="nova-text-danger">*</span>
                        </label>
                        <select name="warehouse_id" id="warehouse_id"
                                class="nova-select @error('warehouse_id') nova-invalid @enderror" required>
                            <option value="">-- اختر المخزن --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"
                                    {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name_en }} ({{ $warehouse->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <div class="nova-form-error">{{ $message }}</div> @enderror

                        <div class="nova-help-text">
                            <i class="fas fa-info-circle"></i>
                            سيتم تحميل الأرصدة الحالية في المخزن تلقائياً
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ===== Main Content: Products Table ===== --}}
        <main class="distribution-content">
            <div class="nova-card">
                <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
                    <h5 class="nova-fw-bold mb-0">
                        <i class="fas fa-shopping-bag me-1"></i> المنتجات المراد توزيعها
                    </h5>
                    @if($batch->batchItems->filter(function($i) { return $i->quantity - $i->dispatched_quantity > 0; })->count())
                        <span class="nova-badge info">
                            <i class="fas fa-boxes"></i>
                            {{ $batch->batchItems->filter(function($i) { return $i->quantity - $i->dispatched_quantity > 0; })->count() }} منتج
                        </span>
                    @endif
                </div>
                <div class="nova-card-body">
                    @if($batch->batchItems->filter(function($i) { return $i->quantity - $i->dispatched_quantity > 0; })->isلاtEmpty())
                        <div class="nova-table-wrapper">
                            <div class="nova-table-scroll">
                                <table class="nova-table distribution-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>المنتج</th>
                                            <th>العبوة</th>
                                            <th class="nova-text-end">الكمية الإجمالية</th>
                                            <th class="nova-text-end">الموزعة سابقاً</th>
                                            <th class="nova-text-end">المتبقية</th>
                                            <th class="nova-text-end">في المخزن</th>
                                            <th style="width: 160px;">الكمية المراد توزيعها</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        @foreach($batch->batchItems as $index => $item)
                                            @php
                                                $remaining = $item->quantity - $item->dispatched_quantity;
                                                $isLocked = $remaining <= 0;
                                            @endphp
                                            @if(!$isLocked)
                                                <tr id="row-{{ $item->id }}">
                                                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>

                                                    {{-- Product --}}
                                                    <td>
                                                        <strong class="distribution-product-name">
                                                            {{ $item->item->name_en ?? 'غير معروف' }}
                                                        </strong>
                                                    </td>

                                                    {{-- Pack --}}
                                                    <td>
                                                        @if($item->pack)
                                                            <div class="distribution-pack-info">
                                                                <code class="distribution-pack-code">
                                                                    {{ $item->pack->barcode }}
                                                                </code>
                                                                <small class="nova-text-muted">
                                                                    كمية: {{ $item->pack->quantity_in_pack }}
                                                                </small>
                                                            </div>
                                                        @else
                                                            <span class="nova-text-muted">بدون عبوة</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Quantity --}}
                                                    <td class="nova-text-end">
                                                        <span class="nova-text-muted">
                                                            {{ number_format($item->quantity, 3) }}
                                                        </span>
                                                    </td>

                                                    {{-- Dispatched --}}
                                                    <td class="nova-text-end">
                                                        <span class="nova-text-warning">
                                                            {{ number_format($item->dispatched_quantity, 3) }}
                                                        </span>
                                                    </td>

                                                    {{-- Remaining --}}
                                                    <td class="nova-text-end">
                                                        <strong class="nova-text-primary">
                                                            {{ number_format($remaining, 3) }}
                                                        </strong>
                                                    </td>

                                                    {{-- Stock in Warehouse --}}
                                                    <td class="nova-text-end">
                                                        <span id="stock-{{ $item->id }}" class="stock-badge">0</span>
                                                    </td>

                                                    {{-- Distribute Quantity Input --}}
                                                    <td>
                                                        <input type="hidden"
                                                               name="items[{{ $index }}][batch_item_id]"
                                                               value="{{ $item->id }}">
                                                        <input type="number" step="0.001" min="0"
                                                               max="{{ $remaining }}"
                                                               name="items[{{ $index }}][quantity]"
                                                               class="nova-input distribute-qty"
                                                               value="0" required>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="nova-empty-title">تم التوزيع بالكامل</h3>
                            <p class="nova-empty-text">
                                جميع المنتجات في هذه الدفعة تم توزيعها بالكامل. لا يمكن إضافة توزيعات جديدة.
                            </p>
                        </div>
                    @endif

                    @if($batch->batchItems->isEmpty())
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد منتجات</h3>
                            <p class="nova-empty-text">لا توجد منتجات في هذه الدفعة لتوزيعها.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    {{-- ============================================================
         Form إجراءات
         ============================================================ --}}
    @if(!isset($isFullyDistributed) || !$isFullyDistributed)
        <div class="form-actions">
            <button type="submit" class="nova-btn nova-btn-success nova-btn-lg">
                <i class="fas fa-share-alt"></i> توزيع
            </button>
            <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-secondary nova-btn-lg">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    @else
        <div class="form-actions">
            <a href="{{ route('production-batches.show', $batch) }}" class="nova-btn nova-btn-secondary nova-btn-lg">
                <i class="fas fa-arrow-right"></i> العودة لتفاصيل الدفعة
            </a>
        </div>
    @endif
</form>
@endsection

@push('styles')
<style>
    /* ============================================================
       Distribution Layout (Sidebar + Content)
       ============================================================ */
    .distribution-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 1.5rem;
    }

    .distribution-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: sticky;
        top: 1rem;
    }

    /* ============================================================
       Info List (Batch Information)
       ============================================================ */
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0.6rem 0.875rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        transition: background 0.15s ease;
    }

    .info-item:hover {
        background: #f1f3f9;
    }

    .info-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        color: #5a5c69;
        font-weight: 500;
        white-space: nowrap;
    }

    .info-icon {
        color: #b7b9cc;
        font-size: 0.75rem;
        width: 14px;
        text-align: center;
    }

    .info-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #3a3b45;
        text-align: left;
    }

    .info-code {
        background: #f1f3f9;
        padding: 0.15rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        color: #5a5c69;
        font-weight: 600;
    }

    /* ============================================================
       Distribution Table
       ============================================================ */
    .distribution-table {
        min-width: 900px;
    }

    .distribution-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
        white-space: nowrap;
    }

    .distribution-table td {
        vertical-align: middle;
    }

    .distribution-product-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
    }

    .distribution-pack-info {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .distribution-pack-code {
        background: #f1f3f9;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.78rem;
        color: #5a5c69;
        width: fit-content;
    }

    /* ===== Stock Badge ===== */
    .stock-badge {
        display: inline-block;
        min-width: 50px;
        padding: 0.2rem 0.5rem;
        background: #eef2ff;
        border: 1px solid #dbe4ff;
        border-radius: 0.35rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--nova-primary);
        text-align: center;
    }

    /* ===== Distribute Quantity Input ===== */
    .distribute-qty {
        text-align: center;
        font-weight: 600;
        color: var(--nova-primary);
    }

    /* ============================================================
       Form إجراءات
       ============================================================ */
    .form-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem 0;
        border-top: 1px solid var(--nova-border);
    }

    /* ============================================================
       Help Text
       ============================================================ */
    .nova-help-text {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.4rem;
        font-size: 0.78rem;
        color: #858796;
    }

    .nova-help-text i {
        color: #b7b9cc;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .distribution-layout {
            grid-template-columns: 1fr;
        }

        .distribution-sidebar {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .info-value {
            text-align: right;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const warehouseSelect = document.getElementById('warehouse_id');
        const batchId = {{ $batch->id }};

        /* ============================================================
           Load Warehouse Stock via AJAX
           ============================================================ */
        function loadWarehouseStock(warehouseId) {
            if (!warehouseId) {
                // إعادة تعيين stock badges to 0
                document.querySelectorالكل('[id^="stock-"]').forEach(el => {
                    el.textContent = '0';
                });
                return;
            }

            fetch(`/production-batches/${batchId}/warehouse-stock/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelectorالكل('[id^="stock-"]').forEach(el => {
                        const batchItemId = el.id.replace('stock-', '');
                        const qty = data[batchItemId] || 0;
                        el.textContent = parseFloat(qty).toFixed(3);
                    });
                })
                .catch(error => {
                    console.error('خطأ في تحميل الأرصدة:', error);
                });
        }

        /* ============================================================
           Warehouse Change Listener
           ============================================================ */
        if (warehouseSelect) {
            warehouseSelect.addEventListener('change', function() {
                loadWarehouseStock(this.value);
            });

            const initialWarehouse = warehouseSelect.value;
            if (initialWarehouse) {
                loadWarehouseStock(initialWarehouse);
            }
        }

        /* ============================================================
           Distribute Quantity Input Validation
           ============================================================ */
        document.querySelectorالكل('.distribute-qty').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.getAttribute('max')) || 0;
                const val = parseFloat(this.value) || 0;

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