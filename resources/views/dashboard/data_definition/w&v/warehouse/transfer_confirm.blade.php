@extends('dashboard.partials.layout')

@section('title', 'تأكيد نقل المنتجات إلى المخزن: ' . $targetWarehouse->name_en)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-check-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تأكيد نقل المنتجات إلى المخزن: {{ $targetWarehouse->code }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            الكميات المحددة ستنقل من المخزن المصدر: <strong>{{ $warehouse->name_en }}</strong>
            إلى المخزن الهدف: <strong>{{ $targetWarehouse->name_en }}</strong>
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouses.transfer.select', $warehouse) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back to Selection
        </a>
    </div>
</div>

{{-- ===== Alerts ===== --}}
@if ($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('warehouses.transfer.execute', $warehouse) }}" method="POST" id="transferForm">
    @csrf
    <input type="hidden" name="target_warehouse_id" value="{{ $targetWarehouse->id }}">

    {{-- ============================================================
         Section 1: Selected Products
         ============================================================ --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header nova-bg-light">
            <h6 class="nova-fw-bold mb-0">
                <i class="fas fa-list me-1"></i> المنتجات المختارة للنقل
            </h6>
        </div>
        <div class="nova-card-body">
            <div class="nova-table-wrapper">
                <div class="nova-table-scroll">
                    <table class="nova-table transfer-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>المنتج</th>
                                <th>الرمز</th>
                                <th>العبوة (Pack)</th>
                                <th class="nova-text-end">الكمية المتوفرة</th>
                                <th style="width: 140px;">الكمية للنقل</th>
                                <th class="nova-text-end" style="width: 100px;">النسبة</th>
                                <th style="width: 180px;">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalAvailable = 0; $totalToTransfer = 0; @endphp
                            @foreach($batchItems as $index => $item)
                                @php
                                    $available = $item->available_quantity;
                                    $totalAvailable += $available;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    {{-- Product Name --}}
                                    <td>
                                        <div class="product-cell">
                                            <strong class="product-cell-name">{{ $item->item->name_en }}</strong>
                                        </div>
                                    </td>

                                    {{-- Product Code --}}
                                    <td>
                                        <code class="product-code">{{ $item->item->code }}</code>
                                    </td>

                                    {{-- Pack Info --}}
                                    <td>
                                        @php
                                            $pack = $item->pack;
                                            $packType = $pack?->packType;
                                            $packCapacity = $pack?->packCapacity;
                                            $packTypeName = $packType?->name_ar ?? $packType?->name_en;
                                            $packTypeCode = $packType?->code;
                                            $packCapacityValue = $packCapacity?->value;
                                            $packCapacityUom = $packCapacity?->uom?->code;

                                            $packTypeBadge = match(true) {
                                                $packTypeCode === 'BOX_3X3' || str_contains((string) $packTypeName, 'صندوق 3') => 'warning',
                                                $packTypeCode === 'BOX_4X4' || str_contains((string) $packTypeName, 'صندوق 4') => 'info',
                                                $packTypeCode === 'BOX_6X6' || str_contains((string) $packTypeName, 'صندوق 6') => 'primary',
                                                $packTypeCode === 'PIECE' || str_contains((string) $packTypeName, 'قطعة') => 'secondary',
                                                default => 'dark',
                                            };

                                            $packIcon = match(true) {
                                                str_contains((string) $packTypeName, 'صندوق') || str_contains((string) $packTypeName, 'BOX') => 'box',
                                                str_contains((string) $packTypeName, 'قطعة') => 'circle',
                                                default => 'package',
                                            };
                                        @endphp

                                        <div class="pack-info">
                                            <span class="nova-badge {{ $packTypeBadge }} pack-type-badge">
                                                <i class="fas fa-{{ $packIcon }}"></i>
                                                {{ $packTypeName ?? 'قطعة' }}
                                            </span>

                                            @if($pack)
                                                <div class="pack-meta">
                                                    <span class="pack-meta-item">
                                                        <i class="fas fa-hash"></i>
                                                        <code>{{ $pack->barcode }}</code>
                                                    </span>

                                                    @if($packCapacityValue)
                                                        <span class="pack-meta-item">
                                                            <i class="fas fa-droplet"></i>
                                                            {{ $packCapacityValue }} {{ $packCapacityUom }}
                                                        </span>
                                                    @endif

                                                    @if($pack->quantity_in_pack > 0)
                                                        <span class="pack-meta-item pack-meta-info">
                                                            <i class="fas fa-layers"></i>
                                                            {{ $pack->quantity_in_pack }} وحدة في العبوة
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="nova-text-muted nova-fs-sm">بدون عبوة</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Available Quantity --}}
                                    <td class="nova-text-end">
                                        <strong class="nova-text-primary">
                                            {{ number_format($available, 3) }}
                                        </strong>
                                    </td>

                                    {{-- Transfer Quantity --}}
                                    <td>
                                        <input type="number"
                                               name="quantities[{{ $item->id }}]"
                                               class="nova-input nova-input-sm quantity-input"
                                               min="0" max="{{ $available }}" step="0.001"
                                               value="{{ $available }}" required>
                                        <input type="hidden" name="batch_item_ids[]" value="{{ $item->id }}">
                                    </td>

                                    {{-- Percentage --}}
                                    <td class="nova-text-end">
                                        <span class="nova-badge success percentage-display">0%</span>
                                    </td>

                                    {{-- Notes --}}
                                    <td>
                                        <input type="text"
                                               name="notes[{{ $item->id }}]"
                                               class="nova-input nova-input-sm"
                                               placeholder="ملاحظة اختيارية">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Section 2: Transfer Details & Summary
         ============================================================ --}}
    <div class="transfer-grid">
        {{-- Transfer Details --}}
        <div class="nova-card">
            <div class="nova-card-header nova-bg-light">
                <h6 class="nova-fw-bold mb-0">
                    <i class="fas fa-truck me-1"></i> تفاصيل النقل
                </h6>
            </div>
            <div class="nova-card-body">
                <div class="form-grid form-grid-2">
                    <div class="nova-form-group">
                        <label class="nova-label">المخزن المصدر</label>
                        <div class="readonly-field">
                            <i class="fas fa-warehouse readonly-field-icon"></i>
                            <span>{{ $warehouse->name_en }}</span>
                        </div>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">المخزن الهدف</label>
                        <div class="readonly-field readonly-field-success">
                            <i class="fas fa-warehouse readonly-field-icon"></i>
                            <span>{{ $targetWarehouse->name_en }}</span>
                        </div>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">تاريخ النقل <span class="nova-text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="nova-input"
                               value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">ملاحظات عامة</label>
                        <textarea name="general_notes" class="nova-input" rows="2"
                                  placeholder="ملاحظات عامة عن عملية النقل"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transfer Summary --}}
        <div class="nova-card">
            <div class="nova-card-header nova-bg-light">
                <h6 class="nova-fw-bold mb-0">
                    <i class="fas fa-chart-bar me-1"></i> ملخص النقل
                </h6>
            </div>
            <div class="nova-card-body">
                <div class="summary-list">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-warehouse"></i>
                            إجمالي الكمية المتوفرة في المصدر
                        </span>
                        <span class="summary-value" id="totalAvailableDisplay">0</span>
                    </div>

                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-paper-plane"></i>
                            إجمالي الكمية المحددة للنقل
                        </span>
                        <span class="summary-value nova-text-success" id="totalToTransferDisplay">0</span>
                    </div>

                    <div class="summary-item summary-item-highlight">
                        <span class="summary-label">
                            <i class="fas fa-percentage"></i>
                            النسبة المئوية الكلية المنقولة
                        </span>
                        <span class="summary-value nova-text-primary" id="percentageDisplay">0%</span>
                    </div>

                    <div class="summary-progress">
                        <div class="summary-progress-bar" id="summaryProgressBar" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Form Actions
         ============================================================ --}}
    <div class="form-actions form-actions-center">
        <button type="submit" class="nova-btn nova-btn-success nova-btn-lg nova-shadow-sm">
            <i class="fas fa-check-circle"></i> تأكيد وتنفيذ النقل
        </button>
        <a href="{{ route('warehouses.transfer.select', $warehouse) }}" class="nova-btn nova-btn-outline nova-btn-lg">
            <i class="fas fa-arrow-left"></i> تعديل الاختيار
        </a>
    </div>
</form>

@endsection

@push('styles')
<style>
    /* ============================================================
       Transfer Table
       ============================================================ */
    .transfer-table {
        min-width: 1000px;
    }

    .transfer-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
        white-space: nowrap;
    }

    .transfer-table td {
        vertical-align: middle;
    }

    /* ===== Product Cell ===== */
    .product-cell {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .product-cell-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
        line-height: 1.3;
    }

    .product-code {
        background: #f1f3f9;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.78rem;
        color: #5a5c69;
    }

    /* ===== Pack Info ===== */
    .pack-info {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .pack-type-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
    }

    .pack-type-badge i {
        width: 12px;
        height: 12px;
    }

    .pack-meta {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        font-size: 0.78rem;
        color: #858796;
    }

    .pack-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pack-meta-item i {
        width: 11px;
        font-size: 0.7rem;
        color: #b7b9cc;
    }

    .pack-meta-item code {
        background: #f1f3f9;
        padding: 0.05rem 0.35rem;
        border-radius: 0.2rem;
        font-size: 0.72rem;
        color: #5a5c69;
    }

    .pack-meta-info {
        color: var(--nova-info, #36b9cc);
        font-weight: 500;
    }

    /* ===== Quantity Input ===== */
    .quantity-input {
        text-align: center;
        font-weight: 600;
        color: var(--nova-primary);
    }

    /* ===== Percentage Badge ===== */
    .percentage-display {
        font-size: 0.8rem;
        min-width: 55px;
        display: inline-block;
        text-align: center;
    }

    /* ============================================================
       Transfer Grid (Details + Summary)
       ============================================================ */
    .transfer-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* ============================================================
       Form Grid System
       ============================================================ */
    .form-grid {
        display: grid;
        gap: 1.25rem;
    }

    .form-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid-span-2 {
        grid-column: span 2;
    }

    .form-grid .nova-form-group {
        margin-bottom: 0;
    }

    /* ============================================================
       Readonly Field (Source/Target Warehouse)
       ============================================================ */
    .readonly-field {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.875rem;
        background: #f8f9fc;
        border: 1.5px solid var(--nova-border);
        border-radius: 10px;
        font-weight: 600;
        color: #3a3b45;
        font-size: 0.875rem;
        min-height: 42px;
    }

    .readonly-field-icon {
        color: var(--nova-primary);
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .readonly-field-success {
        background: #f0fdf4;
        border-color: #86efac;
        color: #166534;
    }

    .readonly-field-success .readonly-field-icon {
        color: #16a34a;
    }

    /* ============================================================
       Summary List
       ============================================================ */
    .summary-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        gap: 0.75rem;
    }

    .summary-item-highlight {
        background: linear-gradient(135deg, #eef2ff 0%, #f8f9fc 100%);
        border: 1px solid #dbe4ff;
    }

    .summary-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #5a5c69;
        font-weight: 500;
    }

    .summary-label i {
        color: #b7b9cc;
        width: 16px;
        text-align: center;
    }

    .summary-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #3a3b45;
        white-space: nowrap;
    }

    /* ===== Progress Bar ===== */
    .summary-progress {
        width: 100%;
        height: 8px;
        background: #e3e6f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 0.25rem;
    }

    .summary-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #1cc88a 0%, #4e73df 100%);
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    /* ============================================================
       Form Actions
       ============================================================ */
    .form-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem 0;
        border-top: 1px solid var(--nova-border);
    }

    .form-actions-center {
        justify-content: center;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .transfer-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }

        .form-grid-span-2 {
            grid-column: span 1;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .summary-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .summary-value {
            font-size: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const totalAvailableDisplay = document.getElementById('totalAvailableDisplay');
    const totalToTransferDisplay = document.getElementById('totalToTransferDisplay');
    const percentageDisplay = document.getElementById('percentageDisplay');
    const summaryProgressBar = document.getElementById('summaryProgressBar');

    /* ============================================================
       Calculate Totals
       ============================================================ */
    function calculateTotals() {
        let totalAvailable = 0;
        let totalToTransfer = 0;

        quantityInputs.forEach((input) => {
            const available = parseFloat(input.getAttribute('max')) || 0;
            const quantity = parseFloat(input.value) || 0;
            totalAvailable += available;
            totalToTransfer += quantity;

            const percentageCell = input.closest('tr').querySelector('.percentage-display');
            if (percentageCell) {
                if (available > 0) {
                    const percentage = (quantity / available) * 100;
                    percentageCell.textContent = percentage.toFixed(1) + '%';

                    // Update badge color based on percentage
                    percentageCell.classList.remove('success', 'warning', 'danger', 'secondary');
                    if (percentage >= 100) {
                        percentageCell.classList.add('success');
                    } else if (percentage >= 50) {
                        percentageCell.classList.add('warning');
                    } else if (percentage > 0) {
                        percentageCell.classList.add('danger');
                    } else {
                        percentageCell.classList.add('secondary');
                    }
                } else {
                    percentageCell.textContent = '0%';
                    percentageCell.classList.remove('success', 'warning', 'danger');
                    percentageCell.classList.add('secondary');
                }
            }
        });

        totalAvailableDisplay.textContent = totalAvailable.toFixed(3);
        totalToTransferDisplay.textContent = totalToTransfer.toFixed(3);

        const overallPercentage = totalAvailable > 0 ? (totalToTransfer / totalAvailable) * 100 : 0;
        percentageDisplay.textContent = overallPercentage.toFixed(1) + '%';

        if (summaryProgressBar) {
            summaryProgressBar.style.width = Math.min(overallPercentage, 100) + '%';
        }
    }

    /* ============================================================
       Quantity Input Validation
       ============================================================ */
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max')) || 0;
            const value = parseFloat(this.value) || 0;

            if (value > max) {
                this.value = max;
            }

            if (value < 0) {
                this.value = 0;
            }

            calculateTotals();
        });

        calculateTotals();
    });

    /* ============================================================
       Form Submit Validation
       ============================================================ */
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        let hasValidQuantity = false;

        quantityInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            if (value > 0) {
                hasValidQuantity = true;
            }
        });

        if (!hasValidQuantity) {
            e.preventDefault();
            alert('يرجى تحديد كمية لنقلها على الأقل لمنتج واحد.');
        }
    });
});
</script>
@endpush