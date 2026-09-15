@extends('dashboard.partials.layout')

@section('title', 'Warehouse التفاصيل: ' . $warehouse->code)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Warehouse: {{ $warehouse->code }}
        </h2>
        <p class="nova-page-subtitle">Detailed view من warehouse inventory</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouses.edit', $warehouse) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('warehouses.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع to List
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

{{-- ===== Statistics Cards ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
        <p class="nova-stat-label">عدد العبوات (Item Packs)</p>
        <p class="nova-stat-value">{{ $totalProducts }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-package"></i>
        </div>
        <p class="nova-stat-label">إجمالي الكميات (العبوات)</p>
        <p class="nova-stat-value">{{ number_format($totalQuantity, 3) }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-layers"></i>
        </div>
        <p class="nova-stat-label">نشط Batches</p>
        <p class="nova-stat-value">{{ $totalBatches }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon info">
            <i class="fas fa-tag"></i>
        </div>
        <p class="nova-stat-label">أنواع العبوات</p>
        <p class="nova-stat-value">{{ $totalPackTypes }}</p>
        <small class="nova-text-muted">إجمالي العبوات: {{ number_format($totalQuantityByPack, 3) }}</small>
    </div>
</div>


{{-- ===== Pack Statistics ===== --}}
@if($packStats->count())
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0">
            <i class="fas fa-chart-pie me-1"></i>
            إحصائيات الكميات حسب العبوة
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="pack-stats-grid">
            <div class="pack-chart-container">
                <canvas id="packChart"></canvas>
            </div>
            <div class="pack-details-container">
                <h6 class="nova-fw-bold nova-mb-3">تفاصيل العبوات</h6>
                <ul class="nova-list pack-details-list">
                    @foreach($packStats as $packStat)
                        <li class="nova-list-item nova-flex nova-justify-between nova-items-center">
                            <span class="pack-details-name">
                                <i class="fas fa-tag nova-text-info me-2"></i>
                                {{ $packStat['pack_name'] }}
                            </span>
                            <span class="nova-badge primary">
                                {{ number_format($packStat['total_pack_count'], 3) }} عبوة ({{ $packStat['percentage'] }}%)
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== بحث Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('warehouses.show', $warehouse) }}" id="searchForm" class="search-form">
            <div class="nova-form-group search-form-input">
                <label class="nova-label">بحث فوري</label>
                <div class="search-wrapper">
                    <span class="search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" id="searchInput" class="nova-input search-input"
                           placeholder="ابحث باسم المنتج، الكود، رقم الدفعة، العبوة..."
                           value="{{ request('search') }}" autocomplete="منf">
                </div>
            </div>
            <div class="search-form-actions">
                @if(request('search'))
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="nova-btn nova-btn-outline nova-btn-sm" id="clearبحث">
                        <i class="fas fa-times"></i> إلغاء الفلتر
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ===== Transfer Button ===== --}}
<div class="nova-row nova-mb-4">
    <div class="nova-col nova-col-12">
        <button type="button" class="nova-btn nova-btn-success nova-btn-lg nova-shadow-sm"
                id="transferBtn-{{ $warehouse->id }}" data-warehouse-id="{{ $warehouse->id }}">
            <i class="fas fa-paper-plane-top me-1"></i> نقل كميات من هذا المخزن
        </button>
        <form id="transferForm-{{ $warehouse->id }}"
              action="{{ route('warehouses.transfer.store-selection-from-warehouse', $warehouse->id) }}"
              method="POST" style="display: none;">
            @csrf
            <div id="selectedItemsContainer"></div>
        </form>
    </div>
</div>

{{-- ===== Products ===== --}}
<div class="nova-card">
    <div class="nova-card-header nova-flex nova-justify-between nova-items-center">
        <h5 class="nova-fw-bold mb-0">
            <i class="fas fa-grid"></i> Products in Warehouse
        </h5>
        <div class="nova-btn-group" role="group">
            <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-btn-active" id="btnCardعرض">
                <i class="fas fa-th-large"></i> Cards
            </button>
            <button type="button" class="nova-btn nova-btn-outline nova-btn-sm" id="btnTableعرض">
                <i class="fas fa-list"></i> Table
            </button>
        </div>
    </div>
    <div class="nova-card-body">
        {{-- ===== Card عرض ===== --}}
        <div id="cardعرض">
            @if($groupedProducts->count())
                <div class="products-grid" id="productCardsGrid">
                    @foreach($groupedProducts as $group)
                        <div class="product-card-wrapper" id="card-{{ $group['item_pack_id'] ?? $loop->index }}">
                            <div class="nova-product-card" data-item-pack-id="{{ $group['item_pack_id'] ?? '' }}">
                                {{-- الصورة --}}
                                <div class="nova-product-image">
                                    @if($group['image_url'])
                                        <img src="{{ $group['image_url'] }}" alt="{{ $group['item_name'] }}">
                                    @else
                                        <i class="fas fa-image nova-text-muted" style="font-size: 3rem;"></i>
                                    @endif
                                </div>

                                {{-- Body --}}
                                <div class="nova-product-body">
                                    <h5 class="nova-product-title">{{ $group['item_name'] }}</h5>
                                    @if($group['item_name_ar'])
                                        <p class="nova-product-subtitle">{{ $group['item_name_ar'] }}</p>
                                    @endif

                                    <div class="nova-product-meta">
                                        <span class="nova-badge info">{{ $group['pack_barcode'] }}</span>
                                        @if($group['pack_type'] || $group['pack_capacity'])
                                            <span class="nova-text-muted nova-fs-sm">
                                                {{ $group['pack_type'] }} @if($group['pack_capacity'])| {{ $group['pack_capacity'] }}@endif
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Stats Grid --}}
                                    <div class="product-stats-grid">
                                        <div class="nova-product-stat">
                                            <div class="nova-product-stat-label">عبوات</div>
                                            <div class="nova-product-stat-value nova-text-primary">
                                                {{ number_format($group['total_pack_count'], 3) }}
                                            </div>
                                        </div>
                                        <div class="nova-product-stat">
                                            <div class="nova-product-stat-label">قطع</div>
                                            <div class="nova-product-stat-value nova-text-success">
                                                {{ number_format($group['total_individual_units'], 0) }}
                                            </div>
                                        </div>
                                        <div class="nova-product-stat">
                                            <div class="nova-product-stat-label">وحدة/عبوة</div>
                                            <div class="nova-product-stat-value">
                                                {{ number_format($group['quantity_in_pack'], 2) }}
                                            </div>
                                        </div>
                                        <div class="nova-product-stat">
                                            <div class="nova-product-stat-label">دفعات</div>
                                            <div class="nova-product-stat-value">{{ $group['batches_count'] }}</div>
                                        </div>
                                    </div>

                                    {{-- Batches --}}
                                    @if($group['batches_count'] > 0)
                                        <button class="nova-btn nova-btn-outline nova-btn-sm nova-btn-info nova-btn-block nova-mb-2"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#batchList{{ $loop->index }}"
                                                aria-expanded="false">
                                            <i class="fas fa-list-ul me-1"></i> عرض الدفعات النشطة
                                        </button>
                                        <div class="nova-collapse" id="batchList{{ $loop->index }}">
                                            <ul class="nova-list small">
                                                @foreach($group['batches'] as $batch)
                                                    <li class="nova-list-item">
                                                        <a href="{{ route('production-batches.show', $batch['batch_id']) }}"
                                                           target="_blank" class="nova-text-primary">
                                                            {{ $batch['batch_number'] }}
                                                        </a>
                                                        <span class="nova-text-muted nova-fs-sm">
                                                            {{ number_format($batch['pack_count'], 3) }} عبوة
                                                            ({{ number_format($batch['individual_units'], 0) }} قطعة)
                                                            @if($batch['expiry_date'])
                                                                <span class="nova-text-muted">
                                                                    ({{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="nova-text-muted small nova-text-center nova-mb-2">لا توجد دفعات نشطة</p>
                                    @endif
                                </div>

                                {{-- Footer --}}
                                <div class="nova-product-footer">
                                    <label class="nova-form-check">
                                        <input type="checkbox" class="nova-form-check-input product-checkbox"
                                               value="{{ $group['item_pack_id'] ?? 'null' }}"
                                               data-batch-ids='@json($group['batch_item_ids'])'>
                                        <span class="nova-form-check-label">اختيار للنقل</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="nova-empty">
                    <div class="nova-empty-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="nova-empty-title">لا products found</h3>
                    <p class="nova-empty-text">Create your first product to get started.</p>
                </div>
            @endif
        </div>

        {{-- ===== Table عرض ===== --}}
        <div id="tableعرض" style="display: none;">
            @if($groupedProducts->count())
                <div class="nova-table-wrapper">
                    <div class="nova-table-scroll">
                        <table class="nova-table">
                            <thead>
                                <tr>
                                    <th class="nova-text-center" style="width: 40px;">
                                        <input type="checkbox" id="selectالكلProducts" class="nova-form-check-input">
                                    </th>
                                    <th>#</th>
                                    <th style="width: 80px;">الصورة</th>
                                    <th>Product</th>
                                    <th>Pack</th>
                                    <th>Packs Qty</th>
                                    <th>Units</th>
                                    <th>Units/Pack</th>
                                    <th>Batches</th>
                                    <th>Batch التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedProducts as $index => $group)
                                    <tr id="row-{{ $group['item_pack_id'] ?? $index }}">
                                        <td class="nova-text-center">
                                            <input type="checkbox" class="nova-form-check-input product-checkbox"
                                                   value="{{ $group['item_pack_id'] ?? 'null' }}"
                                                   data-batch-ids='@json($group['batch_item_ids'])'>
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if($group['image_url'])
                                                <div class="nova-table-cell-image">
                                                    <img src="{{ $group['image_url'] }}" alt="{{ $group['item_name'] }}">
                                                </div>
                                            @else
                                                <i class="fas fa-image nova-text-muted" style="font-size: 1.25rem;"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="nova-fw-semibold">{{ $group['item_name'] }}</strong>
                                            @if($group['item_name_ar'])
                                                <br><small class="nova-text-muted">{{ $group['item_name_ar'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="nova-badge info">{{ $group['pack_barcode'] }}</span>
                                            @if($group['pack_type'] || $group['pack_capacity'])
                                                <br>
                                                <small class="nova-text-muted">
                                                    {{ $group['pack_type'] }} @if($group['pack_capacity'])| {{ $group['pack_capacity'] }}@endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="nova-text-primary">
                                                {{ number_format($group['total_pack_count'], 3) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="nova-text-success">
                                                {{ number_format($group['total_individual_units'], 0) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($group['quantity_in_pack'], 2) }}</td>
                                        <td>{{ $group['batches_count'] }}</td>
                                        <td>
                                            @if($group['batches_count'] > 0)
                                                <button class="nova-btn nova-btn-sm nova-btn-outline nova-btn-info"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#tableBatchList{{ $loop->index }}"
                                                        aria-expanded="false">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </button>
                                                <div class="nova-collapse" id="tableBatchList{{ $loop->index }}">
                                                    <ul class="nova-list small">
                                                        @foreach($group['batches'] as $batch)
                                                            <li class="nova-list-item">
                                                                <a href="{{ route('production-batches.show', $batch['batch_id']) }}"
                                                                   target="_blank" class="nova-text-primary">
                                                                    {{ $batch['batch_number'] }}
                                                                </a>
                                                                <span class="nova-text-muted nova-fs-sm">
                                                                    {{ number_format($batch['pack_count'], 3) }} عبوة
                                                                    ({{ number_format($batch['individual_units'], 0) }} قطعة)
                                                                    @if($batch['expiry_date'])
                                                                        <span class="nova-text-muted">
                                                                            ({{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }})
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
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
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="nova-empty-title">لا products found</h3>
                    <p class="nova-empty-text">لا products match the search criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== حذف تأكيدation Modal ===== --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete warehouse
                <strong id="deleteName" class="nova-text-danger"></strong>?<br>
                لا يمكن التراجع عن هذا الإجراء.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteModal').classList.remove('show')">
                    إلغاء
                </button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* ============================================================
       Warehouse التفاصيل Table
       ============================================================ */
    .warehouse-details-table th {
        background: #f8f9fc;
        font-weight: 600;
        color: #5a5c69;
        vertical-align: middle;
    }

    .warehouse-details-table td {
        vertical-align: middle;
    }

    /* ============================================================
       Pack Statistics Grid
       ============================================================ */
    .pack-stats-grid {
        display: grid;
        grid-template-columns: 7fr 5fr;
        gap: 1rem;
        align-items: stretch;
    }

    .pack-chart-container {
        background: #f8f9fc;
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 280px;
    }

    .pack-chart-container canvas {
        max-height: 250px;
        width: 100% !important;
    }

    .pack-details-container {
        background: #f8f9fc;
        padding: 1rem;
        border-radius: 0.5rem;
        height: 100%;
        overflow-y: auto;
        max-height: 320px;
    }

    .pack-details-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pack-details-list .nova-list-item {
        padding: 0.5rem 0;
        border-bottom: 1px dashed #e3e6f0;
        font-size: 0.85rem;
    }

    .pack-details-list .nova-list-item:last-child {
        border-bottom: none;
    }

    .pack-details-name {
        display: flex;
        align-items: center;
    }

    /* ============================================================
       بحث Form
       ============================================================ */
    .search-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .search-form .nova-form-group {
        margin-bottom: 0;
    }

    .search-wrapper {
        position: relative;
        display: flex;
        align-items: stretch;
        width: 100%;
    }

    .search-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.875rem;
        background: var(--nova-surface-2);
        border: 1.5px solid var(--nova-border);
        border-end: none;
        border-radius: 10px 0 0 10px;
        color: var(--nova-text-4);
        flex-shrink: 0;
    }

    .search-input {
        border-radius: 0 10px 10px 0 !important;
        border-start: none !important;
        flex: 1;
        min-width: 0;
    }

    .search-form-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ============================================================
       Products Grid
       ============================================================ */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
        align-items: stretch;
    }

    .product-card-wrapper {
        display: flex;
        height: 100%;
    }

    /* ============================================================
       Product Card
       ============================================================ */
    .nova-product-card {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .nova-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        border-color: #d1d3e2;
    }

    .nova-product-card.selected {
        border-color: #1cc88a;
        box-shadow: 0 0 0 2px rgba(28, 200, 138, 0.25);
    }

    /* ===== الصورة - صورة كبيرة تملأ العرض ===== */
    .nova-product-image {
        width: 100%;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fc;
        overflow: hidden;
        flex-shrink: 0;
        position: relative;
    }

    .nova-product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        padding: 0;
        transition: transform 0.3s ease;
    }

    .nova-product-card:hover .nova-product-image img {
        transform: scale(1.05);
    }

    /* ===== Body - البيانات في المنتصف ===== */
    .nova-product-body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1rem;
        gap: 0.6rem;
    }

    .nova-product-title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.6em;
        text-align: center;
        width: 100%;
    }

    .nova-product-subtitle {
        font-size: 0.82rem;
        color: #858796;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-align: center;
        width: 100%;
    }

    .nova-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
        justify-content: center;
        min-height: 1.75rem;
        width: 100%;
    }

    /* ===== Stats Grid ===== */
    .product-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin-top: auto;
        width: 100%;
    }

    .nova-product-stat {
        background: #f8f9fc;
        border-radius: 0.35rem;
        padding: 0.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 3.5rem;
    }

    .nova-product-stat-label {
        font-size: 0.7rem;
        color: #858796;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 0.2rem;
        text-align: center;
    }

    .nova-product-stat-value {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.2;
        text-align: center;
    }

    /* ===== Footer ===== */
    .nova-product-footer {
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border-top: 1px solid #e3e6f0;
        margin-top: auto;
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .nova-product-footer .nova-form-check {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        margin: 0;
    }

    /* ===== قائمة الدفعات ===== */
    .nova-product-body .nova-collapse {
        max-height: 180px;
        overflow-y: auto;
        width: 100%;
        text-align: right;
    }

    .nova-product-body .nova-list-item {
        padding: 0.3rem 0;
        border-bottom: 1px dashed #e3e6f0;
        font-size: 0.78rem;
        text-align: right;
    }

    .nova-product-body .nova-list-item:last-child {
        border-bottom: none;
    }

    /* ============================================================
       Responsive
       ============================================================ */
    @media (max-width: 992px) {
        .pack-stats-grid {
            grid-template-columns: 1fr;
        }

        .pack-chart-container {
            min-height: 220px;
        }

        .pack-details-container {
            max-height: none;
        }
    }

    @media (max-width: 768px) {
        .search-form {
            grid-template-columns: 1fr;
        }

        .search-form-actions {
            justify-content: stretch;
        }

        .search-form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 0.85rem;
        }

        .nova-product-image {
            height: 170px;
        }

        .nova-product-title {
            font-size: 0.9rem;
        }

        .warehouse-details-table th {
            width: 40% !important;
        }
    }

    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }

        .nova-product-image {
            height: 200px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* ============================================================
           Toggle Card/Table عرض
           ============================================================ */
        const cardعرض = document.getElementById('cardعرض');
        const tableعرض = document.getElementById('tableعرض');
        const btnCard = document.getElementById('btnCardعرض');
        const btnTable = document.getElementById('btnTableعرض');

        function setعرض(mode) {
            if (mode === 'card') {
                cardعرض.style.display = 'block';
                tableعرض.style.display = 'none';
                btnCard.classList.add('nova-btn-active');
                btnTable.classList.remove('nova-btn-active');
            } else {
                cardعرض.style.display = 'none';
                tableعرض.style.display = 'block';
                btnTable.classList.add('nova-btn-active');
                btnCard.classList.remove('nova-btn-active');
            }
        }

        btnCard.addEventListener('click', function() { setعرض('card'); });
        btnTable.addEventListener('click', function() { setعرض('table'); });

        /* ============================================================
           Auto-search
           ============================================================ */
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timeout = null;

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    searchForm.submit();
                }, 400);
            });

            const clearBtn = document.getElementById('clearبحث');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventافتراضي();
                    window.location.href = this.href;
                });
            }
        }

        /* ============================================================
           Chart.js
           ============================================================ */
        const packLabels = @json($packStats->pluck('pack_name'));
        const packQuantities = @json($packStats->pluck('total_pack_count')->map(function($q) { return (float) $q; }));

        const colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#fd7e14', '#20c9a6', '#6610f2',
            '#6f42c1', '#e83e8c', '#fd7e14', '#20c9a6', '#6610f2'
        ];

        try {
            const canvas = document.getElementById('packChart');
            if (canvas && typeمن Chart !== 'undefined') {
                new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: packLabels,
                        datasets: [{
                            data: packQuantities,
                            backgroundColor: colors.slice(0, packLabels.length),
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.parsed || 0;
                                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return label + ': ' + value.toFixed(3) + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%',
                    }
                });
            }
        } catch (e) {
            console.error('Chart error:', e);
        }

        /* ============================================================
           Product Selection for Transfer
           ============================================================ */
        const transferForm = document.getElementById('transferForm-{{ $warehouse->id }}');
        const selectedItemsContainer = transferForm ? transferForm.querySelector('#selectedItemsContainer') : null;
        const selectالكلCheckbox = document.getElementById('selectالكلProducts');
        const productCheckboxes = document.querySelectorالكل('.product-checkbox');

        function updateSelectedItems() {
            const selectedBatchIds = [];
            productCheckboxes.forEach(cb => {
                if (cb.checked) {
                    let batchIds = [];
                    try {
                        const raw = cb.getAttribute('data-batch-ids');
                        const parsed = raw ? JSON.parse(raw) : null;
                        if (Array.isArray(parsed)) batchIds = parsed;
                    } catch (e) {
                        batchIds = [];
                    }
                    batchIds.forEach(id => {
                        if (!selectedBatchIds.includes(id)) {
                            selectedBatchIds.push(id);
                        }
                    });
                }
            });
            if (selectedItemsContainer) {
                selectedItemsContainer.innerHTML = '';
                selectedBatchIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_items[]';
                    input.value = id;
                    selectedItemsContainer.appendChild(input);
                });
            }
        }

        function updateCardSelection() {
            productCheckboxes.forEach(cb => {
                const card = cb.closest('.nova-product-card');
                if (card) {
                    if (cb.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                }
            });
        }

        productCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateSelectedItems();
                updateCardSelection();
            });
        });

        if (selectالكلCheckbox) {
            selectالكلCheckbox.addEventListener('change', function() {
                productCheckboxes.forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = this.checked;
                    }
                });
                updateSelectedItems();
                updateCardSelection();
            });
        }

        updateSelectedItems();
        updateCardSelection();

        /* ============================================================
           Transfer Button Handler
           ============================================================ */
        const transferBtn = document.getElementById('transferBtn-{{ $warehouse->id }}');
        if (transferBtn && transferForm) {
            transferBtn.addEventListener('click', function(e) {
                e.preventافتراضي();
                updateSelectedItems();
                const hiddenInputs = selectedItemsContainer
                    ? selectedItemsContainer.querySelectorالكل('input[name="selected_items[]"]')
                    : [];
                if (hiddenInputs.length === 0) {
                    alert('يرجى اختيار منتجات واحدة على الأقل للنقل.');
                    return;
                }
                transferForm.submit();
            });
        }

        /* ============================================================
           حذف تأكيدation
           ============================================================ */
        window.confirmحذف = function(id, name) {
            document.getElementById('deleteName').innerText = name;
            document.getElementById('deleteForm').action = '/warehouses/' + id;
            document.getElementById('deleteModal').classList.add('show');
        };

        /* ============================================================
           Auto-dismiss Alerts
           ============================================================ */
        setTimeout(function() {
            document.querySelectorالكل('.nova-alert').forEach(function(alert) {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
@endpush