@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الدفعة: ' . $batch->batch_number)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box-open" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل الدفعة: {{ $batch->batch_number }}
        </h2>
        <p class="nova-page-subtitle">عرض معلومات الدفعة والمنتجات المرتبطة بها</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.distribute', $batch) }}" class="nova-btn nova-btn-success nova-btn-sm">
            <i class="fas fa-share-alt"></i> توزيع
        </a>
        <a href="{{ route('production-batches.edit', $batch) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
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

{{-- ============================================================
     Batch Info + Products Grid
     ============================================================ --}}
<div class="batch-details-grid">
    {{-- ===== Batch Information ===== --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-info-circle me-1"></i> معلومات الدفعة
            </h5>
        </div>
        <div class="nova-card-body">
            @php
                $statusMap = [
                    'active' => ['label' => 'نشطة', 'color' => 'success', 'icon' => 'check-circle'],
                    'expired' => ['label' => 'منتهية', 'color' => 'warning', 'icon' => 'clock'],
                    'consumed' => ['label' => 'مستهلكة', 'color' => 'secondary', 'icon' => 'archive'],
                ];
                $status = $statusMap[$batch->status] ?? ['label' => $batch->status, 'color' => 'secondary', 'icon' => 'circle'];
            @endphp

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

                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-toggle-on info-icon"></i>
                        الحالة
                    </span>
                    <span class="info-value">
                        <span class="nova-badge {{ $status['color'] }}">
                            <i class="fas fa-{{ $status['icon'] }}"></i>
                            {{ $status['label'] }}
                        </span>
                    </span>
                </div>

                <div class="info-item info-item-block">
                    <span class="info-label">
                        <i class="fas fa-sticky-note info-icon"></i>
                        ملاحظات
                    </span>
                    <span class="info-value info-value-block">
                        {{ $batch->notes ?? '—' }}
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-plus-circle info-icon"></i>
                        تاريخ الإنشاء
                    </span>
                    <span class="info-value">
                        <i class="fas fa-calendar nova-text-muted"></i>
                        {{ $batch->created_at ? $batch?->created_at?->format('Y-m-d H:i') ?? '—' : '—' }}
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-history info-icon"></i>
                        آخر تحديث
                    </span>
                    <span class="info-value">
                        <i class="fas fa-calendar nova-text-muted"></i>
                        {{ $batch->updated_at ? $batch?->updated_at?->format('Y-m-d H:i') ?? '—' : '—' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Products Table ===== --}}
    <div class="nova-card">
        <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-shopping-bag me-1"></i> المنتجات في الدفعة
            </h5>
            @if($batch->batchItems->count())
                <span class="nova-badge info">
                    <i class="fas fa-boxes"></i>
                    {{ $batch->batchItems->count() }} منتج
                </span>
            @endif
        </div>
        <div class="nova-card-body">
            @if($batch->batchItems->count())
                <div class="nova-table-wrapper">
                    <div class="nova-table-scroll">
                        <table class="nova-table batch-products-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>المنتج</th>
                                    <th>العبوة</th>
                                    <th class="nova-text-end">الكمية المنتجة</th>
                                    <th class="nova-text-end">الموزعة</th>
                                    <th class="nova-text-end">المتبقية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->batchItems as $index => $item)
                                    <tr>
                                        <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong class="batch-product-name">
                                                {{ $item->item->name_en ?? 'غير معروف' }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($item->pack)
                                                <div class="batch-pack-info">
                                                    <code class="batch-pack-code">
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
                                        <td class="nova-text-end">
                                            <strong class="nova-text-primary">
                                                {{ number_format($item->quantity, 3) }}
                                            </strong>
                                        </td>
                                        <td class="nova-text-end">
                                            <span class="nova-text-warning">
                                                {{ number_format($item->transferred_quantity ?? 0, 3) }}
                                            </span>
                                        </td>
                                        <td class="nova-text-end">
                                            <span class="nova-text-success">
                                                {{ number_format($item->remaining_quantity ?? $item->quantity, 3) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="nova-empty">
                    <div class="nova-empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="nova-empty-title">لا توجد منتجات</h3>
                    <p class="nova-empty-text">لا توجد منتجات في هذه الدفعة.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ============================================================
     Distribution Movements
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
        <h5 class="nova-fw-bold mb-0">
            <i class="fas fa-exchange-alt me-1"></i> حركات التوزيع
        </h5>
        @php
            $movements = $batch->batchItems->flatMap(function ($item) {
                return $item->movements;
            })->sortByDesc('created_at');
        @endphp
        @if($movements->count())
            <span class="nova-badge primary">
                <i class="fas fa-list"></i>
                {{ $movements->count() }} حركة
            </span>
        @endif
    </div>
    <div class="nova-card-body">
        @if($movements->count())
            <div class="nova-table-wrapper">
                <div class="nova-table-scroll">
                    <table class="nova-table movements-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>المنتج</th>
                                <th>العبوة</th>
                                <th>المخزن المستهدف</th>
                                <th class="nova-text-end">الكمية</th>
                                <th>التاريخ</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $index => $movement)
                                <tr>
                                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong class="movement-product-name">
                                            {{ $movement->batchItem->item->name_en ?? 'غير معروف' }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($movement->batchItem->pack)
                                            <code class="movement-pack-code">
                                                {{ $movement->batchItem->pack->barcode }}
                                            </code>
                                        @else
                                            <span class="nova-text-muted">بدون عبوة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="movement-warehouse">
                                            <i class="fas fa-warehouse"></i>
                                            {{ $movement->toWarehouse->name_en ?? 'غير معروف' }}
                                        </span>
                                    </td>
                                    <td class="nova-text-end">
                                        <strong class="nova-text-primary">
                                            {{ number_format($movement->quantity, 3) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="movement-date">
                                            <i class="fas fa-calendar nova-text-muted"></i>
                                            {{ $movement->movement_date ? $movement->movement_date->format('Y-m-d') : ($movement->created_at ? $movement?->created_at?->format('Y-m-d') ?? '—' : '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($movement->notes)
                                            <span class="nova-text-muted nova-fs-sm" title="{{ $movement->notes }}">
                                                {{ Str::limit($movement->notes, 40) }}
                                            </span>
                                        @else
                                            <span class="nova-text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="nova-empty-title">لا توجد حركات توزيع</h3>
                <p class="nova-empty-text">لم يتم توزيع أي منتجات من هذه الدفعة بعد.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ============================================================
       Batch Details Grid
       ============================================================ */
    .batch-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        align-items: start;
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
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        transition: background 0.15s ease;
    }

    .info-item:hover {
        background: #f1f3f9;
    }

    .info-item-block {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #5a5c69;
        font-weight: 500;
        white-space: nowrap;
    }

    .info-icon {
        color: #b7b9cc;
        font-size: 0.8rem;
        width: 14px;
        text-align: center;
    }

    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #3a3b45;
        text-align: left;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .info-value-block {
        display: block;
        text-align: right;
        font-weight: 400;
        color: #5a5c69;
        line-height: 1.5;
        margin-top: 0.25rem;
    }

    .info-code {
        background: #f1f3f9;
        padding: 0.15rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.82rem;
        color: #5a5c69;
        font-weight: 600;
    }

    /* ============================================================
       Batch Products Table
       ============================================================ */
    .batch-products-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
        white-space: nowrap;
    }

    .batch-products-table td {
        vertical-align: middle;
    }

    .batch-product-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
    }

    .batch-pack-info {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .batch-pack-code {
        background: #f1f3f9;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.78rem;
        color: #5a5c69;
        width: fit-content;
    }

    /* ============================================================
       Movements Table
       ============================================================ */
    .movements-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
        white-space: nowrap;
    }

    .movements-table td {
        vertical-align: middle;
    }

    .movement-product-name {
        font-size: 0.875rem;
        color: var(--nova-text-1);
    }

    .movement-pack-code {
        background: #f1f3f9;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.78rem;
        color: #5a5c69;
    }

    .movement-warehouse {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        color: #5a5c69;
    }

    .movement-warehouse i {
        color: #b7b9cc;
    }

    .movement-date {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        color: #5a5c69;
        white-space: nowrap;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .batch-details-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .info-value {
            text-align: right;
            width: 100%;
        }

        .info-value-block {
            text-align: right;
        }
    }
</style>
@endpush