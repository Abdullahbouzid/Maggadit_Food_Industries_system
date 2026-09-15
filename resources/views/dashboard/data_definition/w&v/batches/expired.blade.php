@extends('dashboard.partials.layout')

@section('title', 'دفعات منتهية الصلاحية')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-hourglass-end" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            دفعات منتهية الصلاحية
        </h2>
        <p class="nova-page-subtitle">عرض وإدارة الدفعات التي انتهت صلاحيتها في المخازن</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة للقائمة
        </a>
    </div>
</div>

{{-- ===== Alerts ===== --}}
@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

{{-- ===== Warehouse فلتر ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('production-batches.expired') }}" class="filter-form">
            <div class="nova-form-group filter-item">
                <label class="nova-label">اختر المخزن</label>
                <select name="warehouse_id" class="nova-select">
                    <option value="">جميع المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name_en }} ({{ $warehouse->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group filter-item">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> تصفية
                </button>
            </div>
        </form>
    </div>
</div>

@if($expiredStocks->count())
<form action="{{ route('production-batches.return-expired') }}" method="POST">
    @csrf
    {{-- ===== منتهي Batches Table ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-list"></i> قائمة الدفعات منتهية الصلاحية
            </h5>
            <button type="submit" class="nova-btn nova-btn-danger nova-btn-sm">
                <i class="fas fa-archive"></i> استرجاع الكل إلى مخزن المهملات
            </button>
        </div>
        <div class="nova-card-body">
            <div class="nova-table-wrapper">
                <div class="nova-table-scroll">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectالكل" class="nova-form-check-input"></th>
                                <th>#</th>
                                <th>رقم الدفعة</th>
                                <th>المنتج</th>
                                <th>العبوة</th>
                                <th>المخزن الحالي</th>
                                <th>الكمية</th>
                                <th>تاريخ الانتهاء</th>
                                <th>عدد الأيام المنقضية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiredStocks as $index => $stock)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="stock_ids[]" value="{{ $stock->id }}" class="nova-form-check-input">
                                    </td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('production-batches.show', $stock->batchItem->batch_id) }}" target="_blank" class="nova-text-primary nova-fw-semibold">
                                            {{ $stock->batchItem->batch->batch_number }}
                                        </a>
                                    </td>
                                    <td>{{ $stock->batchItem->item->name_en ?? 'غير معروف' }}</td>
                                    <td>{{ $stock->batchItem->pack ? $stock->batchItem->pack->barcode : 'بدون عبوة' }}</td>
                                    <td>{{ $stock->warehouse->name_en ?? 'غير معروف' }}</td>
                                    <td>{{ number_format($stock->quantity, 3) }}</td>
                                    <td>{{ $stock->batchItem->batch->expiry_date ? $stock->batchItem->batch->expiry_date->format('Y-m-d') : '—' }}</td>
                                    <td>
                                        @php
                                            $days = $stock->batchItem->batch->expiry_date ? now()->diffInDays($stock->batchItem->batch->expiry_date, false) : 0;
                                        @endphp
                                        <span class="nova-badge danger">{{ abs($days) }} يوم</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Destination Warehouse ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-home me-1"></i> مخزن استرجاع الدفعات منتهية الصلاحية
            </h5>
        </div>
        <div class="nova-card-body">
            <div class="nova-form-row">
                <div class="nova-form-group nova-form-col">
                    <label class="nova-label">المخزن الهدف <span class="nova-text-danger">*</span></label>
                    <select name="destination_warehouse_id" class="nova-select" required>
                        <option value="">-- اختر --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name_en }} ({{ $warehouse->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group nova-form-col">
                    <label class="nova-label">تاريخ الاسترجاع</label>
                    <input type="date" name="return_date" class="nova-input" value="{{ now()->toDateString() }}">
                </div>
                <div class="nova-form-group nova-form-col nova-form-col-full">
                    <label class="nova-label">ملاحظات</label>
                    <input type="text" name="notes" class="nova-input" placeholder="سبب الاسترجاع (اختياري)">
                </div>
            </div>
            <div class="nova-form-row nova-mt-3">
                <button type="submit" class="nova-btn nova-btn-danger">
                    <i class="fas fa-archive"></i> استرجاع المحدد
                </button>
            </div>
        </div>
    </div>
    </form>
@else
    {{-- ===== Empty State ===== --}}
    <div class="nova-card nova-mb-4">
        <div class="nova-card-body text-center py-5">
            <div class="nova-empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="nova-empty-title">لا توجد دفعات منتهية الصلاحية في المخازن.</h3>
        </div>
    </div>
@endif

{{-- ===== رجوع Button ===== --}}
<div class="nova-mt-3">
    <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-secondary">
        <i class="fas fa-arrow-right"></i> العودة للقائمة
    </a>
</div>
@endsection

@push('styles')
<style>
    .filter-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
    }

    .filter-item-actions {
        display: flex;
        align-items: flex-end;
    }

    .nova-form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .nova-form-col-full {
        grid-column: 1 / -1;
    }

    .nova-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--nova-text-4);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select/Deselect الكل
        document.getElementById('selectالكل')?.addEventListener('change', function() {
            document.querySelectorالكل('input[name="stock_ids[]"]').forEach(chk => {
                chk.checked = this.checked;
            });
        });
    });
</script>
@endpush
