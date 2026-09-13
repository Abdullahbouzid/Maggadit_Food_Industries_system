@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الحركة #' . $movement->id)

@section('content')
    {{-- ===== Action Bar ===== --}}
    <div class="nova-actionbar">
        <div>
            <h2 class="nova-actionbar-title">
                <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
                تفاصيل الحركة: {{ $movement->id }}
            </h2>
            <p class="nova-page-subtitle">لوحة معلومات شاملة للحركة المخزنية</p>
        </div>
        <div class="nova-actionbar-actions">
            <a href="{{ route('warehouse-movements.export-detail', $movement->id) }}"
               class="nova-btn nova-btn-success nova-btn-sm">
                <i class="fas fa-download"></i> تصدير التفاصيل (إكسل)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="nova-alert success" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
        $type = $movementTypes[$movement->movement_type] ?? [
            'label' => $movement->movement_type,
            'color' => 'secondary',
        ];
        $totalQty = $groupedMovements->sum('quantity');
        $avgQty   = $groupedMovements->count() > 0 ? $groupedMovements->avg('quantity') : 0;
        $maxQty   = $groupedMovements->count() > 0 ? $groupedMovements->max('quantity') : 0;
    @endphp

    {{-- ===== KPI Cards ===== --}}
    <div class="nova-kpi-grid">
        <div class="nova-kpi-card">
            <div class="nova-kpi-icon kpi-blue">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="nova-kpi-content">
                <span class="nova-kpi-label">عدد المنتجات</span>
                <h3 class="nova-kpi-value">{{ $groupedMovements->count() }}</h3>
                <span class="nova-kpi-trend">
                    <i class="fas fa-arrow-up"></i> منتج
                </span>
            </div>
        </div>

        <div class="nova-kpi-card">
            <div class="nova-kpi-icon kpi-green">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="nova-kpi-content">
                <span class="nova-kpi-label">إجمالي الكمية</span>
                <h3 class="nova-kpi-value">{{ number_format($totalQty, 3) }}</h3>
                <span class="nova-kpi-trend">
                    <i class="fas fa-chart-line"></i> إجمالي
                </span>
            </div>
        </div>

        <div class="nova-kpi-card">
            <div class="nova-kpi-icon kpi-purple">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="nova-kpi-content">
                <span class="nova-kpi-label">متوسط الكمية</span>
                <h3 class="nova-kpi-value">{{ number_format($avgQty, 3) }}</h3>
                <span class="nova-kpi-trend">
                    <i class="fas fa-equals"></i> لكل منتج
                </span>
            </div>
        </div>

        <div class="nova-kpi-card">
            <div class="nova-kpi-icon kpi-orange">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="nova-kpi-content">
                <span class="nova-kpi-label">أقصى كمية</span>
                <h3 class="nova-kpi-value">{{ number_format($maxQty, 3) }}</h3>
                <span class="nova-kpi-trend">
                    <i class="fas fa-crown"></i> الأعلى
                </span>
            </div>
        </div>
    </div>

    {{-- ===== Info Grid ===== --}}
    <div class="nova-dash-grid">

        {{-- Movement Info --}}
        <div class="nova-dash-card">
            <div class="nova-dash-card-header">
                <div class="nova-dash-card-icon">
                     <i class="fas fa-info-circle"></i>
                </div>
                <h5>معلومات الحركة</h5>
            </div>
            <div class="nova-dash-card-body">
                <ul class="nova-dash-list">
                    <li>
                        <span class="nova-dash-list-label">
                            <i class="fas fa-hashtag"></i> المعرف
                        </span>
                        <span class="nova-dash-list-value">#{{ $movement->id }}</span>
                    </li>
                    <li>
                        <span class="nova-dash-list-label">
                            <i class="fas fa-exchange-alt"></i> النوع
                        </span>
                        <span class="nova-badge {{ $type['color'] }}">{{ $type['label'] }}</span>
                    </li>
                    <li>
                        <span class="nova-dash-list-label">
                            <i class="fas fa-layer-group"></i> الدفعة
                        </span>
                        <span class="nova-dash-list-value">
                            {{ optional(optional($movement->batchItem)->batch)->batch_number ?? '—' }}
                        </span>
                    </li>
                    <li>
                        <span class="nova-dash-list-label">
                            <i class="fas fa-calendar-alt"></i> التاريخ
                        </span>
                        <span class="nova-dash-list-value">
                            {{ optional($movement->movement_date)->format('Y-m-d') ?? '—' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Warehouses --}}
        <div class="nova-dash-card">
            <div class="nova-dash-card-header">
                <div class="nova-dash-card-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h5>مسار النقل</h5>
            </div>
            <div class="nova-dash-card-body">
                <div class="nova-dash-warehouse from">
                    <div class="nova-dash-wh-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="nova-dash-wh-info">
                        <span>من مخزن</span>
                        <strong>{{ optional($movement->fromWarehouse)->name_en ?? 'لا يوجد' }}</strong>
                    </div>
                </div>

                <div class="nova-dash-wh-divider">
                    <i class="fas fa-arrow-down"></i>
                </div>

                <div class="nova-dash-warehouse to">
                    <div class="nova-dash-wh-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="nova-dash-wh-info">
                        <span>إلى مخزن</span>
                        <strong>{{ optional($movement->toWarehouse)->name_en ?? 'غير محدد' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="nova-dash-card">
            <div class="nova-dash-card-header">
                <div class="nova-dash-card-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <h5>ملاحظات</h5>
            </div>
            <div class="nova-dash-card-body">
                <p class="nova-dash-notes">
                    {{ $movement->notes ?: 'لا توجد ملاحظات مسجلة على هذه الحركة.' }}
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="nova-dash-card">
            <div class="nova-dash-card-header">
                <div class="nova-dash-card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h5>الإجراءات</h5>
            </div>
            <div class="nova-dash-card-body">
                <div class="nova-dash-actions">
                    <a href="{{ route('warehouse-movements.edit', $movement->id) }}"
                       class="nova-dash-action edit">
                        <i class="fas fa-edit"></i>
                        <span>تعديل الحركة</span>
                    </a>
                    <button type="button"
                            class="nova-dash-action delete"
                            data-delete-id="{{ $movement->id }}"
                            data-delete-name="الحركة #{{ $movement->id }}"
                            onclick="confirmDelete(this.dataset.deleteId, this.dataset.deleteName)">
                        <i class="fas fa-trash-alt"></i>
                        <span>حذف الحركة</span>
                    </button>
                    <a href="{{ route('warehouse-movements.index') }}"
                       class="nova-dash-action back">
                        <i class="fas fa-arrow-right"></i>
                        <span>العودة للقائمة</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Products Table ===== --}}
    <div class="nova-card">
        <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
            <h5 class="nova-fw-bold mb-0">
                <i class="fas fa-shopping-bag me-1"></i>
                المنتجات المنقولة في هذه الدفعة
            </h5>
            <span class="nova-badge primary">
                {{ $groupedMovements->count() }} منتج / {{ number_format($totalQty, 3) }}
            </span>
        </div>
        <div class="nova-card-body nova-p-0">
            <div class="nova-table-wrapper">
                <table class="nova-table nova-table-striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>المنتج</th>
                            <th>العبوة</th>
                            <th class="nova-text-center" style="width: 130px;">الكمية</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedMovements as $item)
                            @php
                                $batchItem = $item->batchItem;
                                $product   = optional($batchItem)->item;
                                $pack      = optional($batchItem)->pack;
                            @endphp
                            <tr>
                                <td>
                                    <span class="nova-row-index">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="nova-product-cell">
                                        <div class="nova-product-avatar">
                                            {{ mb_substr(optional($product)->name_en ?? '?', 0, 1) }}
                                        </div>
                                        <div class="nova-product-info">
                                            <strong>{{ optional($product)->name_en ?? 'غير معروف' }}</strong>
                                            @if(optional($product)->code)
                                                <span class="nova-text-muted">
                                                    كود: {{ $product->code }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($pack)
                                        <div class="nova-pack-cell">
                                            <i class="fas fa-box"></i>
                                            <span>{{ $pack->barcode }}</span>
                                            <small class="nova-text-muted">
                                                ({{ $pack->quantity_in_pack }})
                                            </small>
                                        </div>
                                    @else
                                        <span class="nova-text-muted">بدون عبوة</span>
                                    @endif
                                </td>
                                <td class="nova-text-center">
                                    <span class="nova-qty-badge">
                                        {{ number_format($item->quantity, 3) }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->notes)
                                        <span class="nova-note-badge">
                                            <i class="fas fa-comment"></i>
                                            {{ $item->notes }}
                                        </span>
                                    @else
                                        <span class="nova-text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="nova-text-center nova-text-muted">
                                    لا توجد منتجات مسجلة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Delete Modal ===== --}}
    <div class="nova-modal-backdrop" id="globalDeleteModal" tabindex="-1" aria-hidden="true" role="dialog">
        <div class="nova-modal" role="document">
            <div class="nova-delete-modal">
                <div class="nova-delete-modal-icon">
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                </div>
                <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
                <p class="nova-delete-modal-text">
                    هل أنت متأكد من حذف الحركة
                    <strong id="deleteItemName" class="nova-text-danger"></strong>؟<br>
                    لا يمكن التراجع.
                </p>
                <div class="nova-delete-modal-actions">
                    <button type="button" class="nova-btn nova-btn-outline" data-modal-close>إلغاء</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="nova-btn nova-btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* ===== KPI Grid ===== */
    .nova-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .nova-kpi-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--nova-card-bg, #fff);
        border: 1px solid var(--nova-border, #e5e7eb);
        border-radius: 16px;
        transition: transform .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }

    .nova-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
        opacity: .6;
    }

    .nova-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0,0,0,.08);
    }

    .nova-kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .kpi-blue   { background: rgba(59,130,246,.12);  color: #3b82f6; }
    .kpi-green  { background: rgba(34,197,94,.12);   color: #22c55e; }
    .kpi-purple { background: rgba(139,92,246,.12);  color: #8b5cf6; }
    .kpi-orange { background: rgba(245,158,11,.12);  color: #f59e0b; }

    .nova-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .nova-kpi-label {
        font-size: 0.78rem;
        color: #6b7280;
        font-weight: 500;
    }

    .nova-kpi-value {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
        color: #111827;
    }

    .nova-kpi-trend {
        font-size: 0.72rem;
        color: #6b7280;
    }

    .nova-kpi-trend i {
        color: #22c55e;
    }

    /* ===== Dashboard Grid ===== */
    .nova-dash-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .nova-dash-card {
        background: var(--nova-card-bg, #fff);
        border: 1px solid var(--nova-border, #e5e7eb);
        border-radius: 16px;
        overflow: hidden;
        transition: box-shadow .2s;
    }

    .nova-dash-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,.06);
    }

    .nova-dash-card-header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 1rem;
        border-bottom: 1px solid var(--nova-border, #e5e7eb);
        background: linear-gradient(135deg, rgba(59,130,246,.04), rgba(139,92,246,.04));
    }

    .nova-dash-card-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .nova-dash-card-header h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .nova-dash-card-body {
        padding: 1rem;
    }

    /* ===== List ===== */
    .nova-dash-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nova-dash-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.55rem 0;
        border-bottom: 1px dashed var(--nova-border, #e5e7eb);
        font-size: 0.87rem;
    }

    .nova-dash-list li:last-child {
        border-bottom: 0;
    }

    .nova-dash-list-label {
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .nova-dash-list-label i {
        color: #3b82f6;
        font-size: 0.8rem;
    }

    .nova-dash-list-value {
        font-weight: 600;
        color: #111827;
    }

    /* ===== Warehouses ===== */
    .nova-dash-warehouse {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem;
        border-radius: 12px;
    }

    .nova-dash-warehouse.from {
        background: rgba(239,68,68,.06);
        border: 1px solid rgba(239,68,68,.15);
    }

    .nova-dash-warehouse.to {
        background: rgba(59,130,246,.06);
        border: 1px solid rgba(59,130,246,.15);
    }

    .nova-dash-wh-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
    }

    .nova-dash-warehouse.from .nova-dash-wh-icon {
        background: linear-gradient(135deg, #ef4444, #f97316);
    }

    .nova-dash-warehouse.to .nova-dash-wh-icon {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    }

    .nova-dash-wh-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .nova-dash-wh-info span {
        font-size: 0.72rem;
        color: #6b7280;
    }

    .nova-dash-wh-info strong {
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nova-dash-wh-divider {
        text-align: center;
        color: #6b7280;
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }

    /* ===== Notes ===== */
    .nova-dash-notes {
        margin: 0;
        font-size: 0.88rem;
        color: #6b7280;
        line-height: 1.7;
        padding: 0.85rem;
        background: rgba(245,158,11,.06);
        border-right: 3px solid #f59e0b;
        border-radius: 8px;
    }

    /* ===== Dash Actions ===== */
    .nova-dash-actions {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .nova-dash-action {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        width: 100%;
    }

    .nova-dash-action:hover {
        transform: translateX(-4px);
    }

    .nova-dash-action.edit {
        background: rgba(245,158,11,.1);
        color: #f59e0b;
    }

    .nova-dash-action.delete {
        background: rgba(239,68,68,.1);
        color: #ef4444;
    }

    .nova-dash-action.back {
        background: rgba(107,114,128,.1);
        color: #6b7280;
    }

    .nova-dash-action i {
        width: 20px;
        text-align: center;
    }

    /* ===== Products Table ===== */
    .nova-p-0 { padding: 0 !important; }

    .nova-table-striped tbody tr:nth-child(even) {
        background: rgba(0,0,0,.015);
    }

    .nova-row-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(59,130,246,.1);
        color: #3b82f6;
        font-weight: 700;
        font-size: 0.78rem;
    }

    .nova-product-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .nova-product-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .nova-product-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .nova-product-info strong {
        font-size: 0.9rem;
    }

    .nova-product-info span {
        font-size: 0.75rem;
    }

    .nova-pack-cell {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
    }

    .nova-pack-cell i {
        color: #6b7280;
    }

    .nova-qty-badge {
        display: inline-block;
        padding: 4px 14px;
        background: linear-gradient(135deg, rgba(59,130,246,.1), rgba(139,92,246,.1));
        color: #3b82f6;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .nova-note-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        background: rgba(245,158,11,.1);
        color: #d97706;
        border-radius: 6px;
        font-size: 0.78rem;
    }

    /* ===== Modal ===== */
    .nova-modal-backdrop { display: none; }
    .nova-modal-backdrop.show { display: flex; }

    @media (max-width: 768px) {
        .nova-kpi-value { font-size: 1.15rem; }
        .nova-kpi-icon  { width: 46px; height: 46px; font-size: 1.15rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        'use strict';

        const modal         = document.getElementById('globalDeleteModal');
        const deleteForm    = document.getElementById('deleteForm');
        const deleteName    = document.getElementById('deleteItemName');
        const baseDeleteUrl = @json(url('/warehouse-movements'));

        function openModal()  { modal.classList.add('show');    modal.setAttribute('aria-hidden', 'false'); }
        function closeModal() { modal.classList.remove('show'); modal.setAttribute('aria-hidden', 'true');  }

        window.confirmDelete = function (id, name) {
            deleteName.textContent = name;
            deleteForm.action = baseDeleteUrl + '/' + id;
            openModal();
        };

        modal.addEventListener('click', function (e) {
            if (e.target === modal || e.target.matches('[data-modal-close]')) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });
    })();
</script>
@endpush