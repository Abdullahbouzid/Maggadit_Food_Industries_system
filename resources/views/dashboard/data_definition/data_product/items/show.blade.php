@extends('dashboard.partials.layout')

@section('title', 'Item التفاصيل')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Item التفاصيل
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            عرضing information for
            <strong>{{ $item->name_en }}</strong>
            @if($item->name_ar)
                ({{ $item->name_ar }})
            @endif
            @if($item->code)
                <span class="nova-badge info" style="margin-inline-start: 0.5rem;">{{ $item->code }}</span>
            @endif
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('items.edit', $item) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع to List
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

{{-- ===== Meta Info ===== --}}
@if($item->updated_at)
    <div class="nova-alert info" style="display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-clock"></i>
        آخر تحديث: {{ $item->updated_at->diffForHumans() }}
        <span class="nova-text-muted">({{ $item->updated_at->format('Y-m-d H:i') }})</span>
    </div>
@endif

{{-- ===== Basic Information ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-info-circle me-2"></i> Basic Information</h5>
    </div>
    <div class="nova-card-body">
        <div class="detail-grid detail-grid-4">
            <div class="detail-item">
                <label class="detail-label">Code</label>
                <p class="detail-value"><code class="nova-code">{{ $item->code }}</code></p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Sequence</label>
                <p class="detail-value">{{ $item->sequence ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">الاسم بالإنجليزي</label>
                <p class="detail-value nova-fw-bold">{{ $item->name_en }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">الاسم بالعربي</label>
                <p class="detail-value">{{ $item->name_ar ?: '—' }}</p>
            </div>
            <div class="detail-item detail-item-full">
                <label class="detail-label">Definition</label>
                <p class="detail-value">{{ $item->definition ?: '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Classification ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-sitemap me-2"></i> Classification</h5>
    </div>
    <div class="nova-card-body">
        <div class="detail-grid detail-grid-5">
            <div class="detail-item">
                <label class="detail-label">نوع العنصر</label>
                <p class="detail-value">
                    @if($item->itemType)
                        <span class="nova-badge secondary">{{ $item->itemType->name_en ?? $item->itemType->name }}</span>
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">الفئة</label>
                <p class="detail-value">
                    @if($item->category)
                        <span class="nova-badge secondary">{{ $item->category->name_en ?? $item->category->name }}</span>
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Brand</label>
                <p class="detail-value">{{ $item->brand->name_en ?? $item->brand->name ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Model</label>
                <p class="detail-value">{{ $item->model->name_en ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Supplier</label>
                <p class="detail-value">{{ $item->supplier->name_en ?? $item->supplier->name ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Tolerances & Stock ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-sliders-h me-2"></i> Tolerances & Stock</h5>
    </div>
    <div class="nova-card-body">
        <div class="detail-grid detail-grid-5">
            <div class="detail-item">
                <label class="detail-label">Shelf Life</label>
                <p class="detail-value">
                    {{ $item->shelf_life_time ?? '—' }}
                    @if($item->shelf_life_time)
                        <small class="nova-text-muted">days</small>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Van Stock Tolerance</label>
                <p class="detail-value">{{ $item->van_stock_tolerance ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Storage Tolerance</label>
                <p class="detail-value">{{ $item->storage_tolerance ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Weight Tolerance</label>
                <p class="detail-value">{{ $item->weight_tolerance ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">منخفض Stock Qty</label>
                <p class="detail-value">
                    @if($item->low_stock_qty !== null)
                        <span class="nova-badge warning">{{ $item->low_stock_qty }}</span>
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Flags ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-flag me-2"></i> Flags</h5>
    </div>
    <div class="nova-card-body">
        @php
            $flags = array_filter([
                $item->is_inactive       ? ['label' => 'غير نشط',            'class' => 'secondary', 'icon' => 'eye-slash'] : null,
                $item->is_new            ? ['label' => 'جديد',                 'class' => 'primary',   'icon' => 'star'] : null,
                $item->is_price_protected? ['label' => 'Price Protected',     'class' => 'warning',   'icon' => 'lock'] : null,
                $item->is_power_sku      ? ['label' => 'Power SKU',           'class' => 'accent',    'icon' => 'bolt'] : null,
                $item->is_default_batch  ? ['label' => 'افتراضي Batch',       'class' => 'info',      'icon' => 'layer-group'] : null,
                $item->is_temp_credit    ? ['label' => 'Temp Credit',         'class' => 'info',      'icon' => 'credit-card'] : null,
                $item->is_batch_priced   ? ['label' => 'Batch Priced',        'class' => 'primary',   'icon' => 'tags'] : null,
                $item->is_initiative     ? ['label' => 'Initiative',          'class' => 'accent',    'icon' => 'rocket'] : null,
                $item->force_default_pack? ['label' => 'Force افتراضي Pack',  'class' => 'warning',   'icon' => 'box'] : null,
                $item->has_serial_number ? ['label' => 'Has Serial',          'class' => 'success',   'icon' => 'barcode'] : null,
                $item->required_by_liter ? ['label' => 'By Liter',            'class' => 'info',      'icon' => 'flask'] : null,
                $item->required_by_weight? ['label' => 'By Weight',           'class' => 'warning',   'icon' => 'weight'] : null,
            ]);
        @endphp

        @if(count($flags))
            <div class="nova-flex nova-flex-wrap nova-gap-2">
                @foreach($flags as $flag)
                    <span class="nova-badge {{ $flag['class'] }}">
                        <i class="fas fa-{{ $flag['icon'] }}"></i> {{ $flag['label'] }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="nova-text-muted mb-0">— لا flags set —</p>
        @endif
    </div>
</div>

{{-- ===== Packs ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title">
            <i class="fas fa-boxes me-2"></i> Packs
            @if($item->packs->count())
                <span class="nova-badge primary">{{ $item->packs->count() }}</span>
            @endif
        </h5>
    </div>
    <div class="nova-card-body nova-p-0">
        @if($item->packs->count())
            <div class="nova-table-scroll">
                <table class="nova-table">
                    <thead>
                        <tr>
                            <th>وحدة القياس</th>
                            <th>الكمية</th>
                            <th>الباركود</th>
                            <th>عمليات الباكج</th>
                            <th>مجموعات الباكج</th>
                            <th class="nova-text-center">افتراضي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->packs as $pack)
                        <tr>
                            <td>
                                {{ $pack->packCapacity?->uom->name_en ?? '-' }}
                                @if($pack->packCapacity?->uom->name_ar)
                                    / {{ $pack->packCapacity->uom->name_ar }}
                                @endif
                            </td>
                            <td>
                                <span class="nova-badge info">{{ $pack->quantity_in_pack }}</span>
                            </td>
                            <td>
                                @if($pack->barcode)
                                    <code class="nova-code">{{ $pack->barcode }}</code>
                                @else
                                    <span class="nova-text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pack->packOperations->count())
                                    <div class="nova-flex nova-flex-wrap nova-gap-1">
                                        @foreach($pack->packOperations as $op)
                                            <span class="nova-badge secondary">
                                                {{ $op->name_en ?? $op->name_ar }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="nova-text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($pack->packGroups->count())
                                    <div class="nova-flex nova-flex-wrap nova-gap-1">
                                        @foreach($pack->packGroups as $group)
                                            <span class="nova-badge secondary">
                                                {{ $group->name_en ?? $group->name_ar }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="nova-text-muted">—</span>
                                @endif
                            </td>
                            <td class="nova-text-center">
                                @if($pack->is_default_pack)
                                    <span class="nova-badge success">
                                        <i class="fas fa-check-circle"></i> نعم
                                    </span>
                                @else
                                    <span class="nova-badge secondary">لا</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-state-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h5 class="nova-text-muted">لا packs defined</h5>
                <p class="nova-text-muted nova-fs-sm">This item doesn't have any packs yet.</p>
            </div>
        @endif
    </div>
</div>

{{-- ===== الصورةs ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-image me-2"></i> الصورةs</h5>
    </div>
    <div class="nova-card-body">
        @if($item->image || $item->images->count())
            <div class="images-grid">
                @if($item->image)
                    <div class="image-item image-main">
                        <span class="image-badge">Main</span>
                        <img src="{{ asset('uploads/' . $item->image) }}"
                             alt="{{ $item->name_en }}"
                             loading="lazy">
                    </div>
                @endif
                @foreach($item->images as $img)
                    <div class="image-item">
                        <img src="{{ $img->url }}"
                             alt="{{ $item->name_en }} - additional"
                             loading="lazy">
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 2rem 1rem;">
                <div class="empty-state-icon">
                    <i class="fas fa-image"></i>
                </div>
                <h5 class="nova-text-muted">لا images available</h5>
                <p class="nova-text-muted nova-fs-sm">This item doesn't have any images yet.</p>
            </div>
        @endif
    </div>
</div>

{{-- ===== إجراءات ===== --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="detail-actions">
            <a href="{{ route('items.edit', $item) }}" class="nova-btn nova-btn-primary">
                <i class="fas fa-edit"></i> تعديل Item
            </a>
            <a href="{{ route('items.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> رجوع to List
            </a>
            <button type="button"
                    class="nova-btn nova-btn-danger"
                    onclick="confirmحذف({{ $item->id }}, '{{ addslashes($item->name_en) }}')">
                <i class="fas fa-trash-alt"></i> حذف
            </button>
        </div>
    </div>
</div>

{{-- ===== حذف تأكيدation Modal ===== --}}
<div class="nova-modal-backdrop" id="deleteItemModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete
                <strong id="deleteItemName" class="nova-text-danger"></strong>?
                <br>لا يمكن التراجع عن هذا الإجراء.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeحذفModal()">
                    إلغاء
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">
                        <i class="fas fa-trash-alt"></i> الحذف النهائي
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ===== Detail Grid ===== */
    .detail-grid {
        display: grid;
        gap: 1rem;
    }

    .detail-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .detail-grid-5 {
        grid-template-columns: repeat(5, 1fr);
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-item-full {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #858796;
        font-weight: 600;
    }

    .detail-value {
        font-size: 0.9rem;
        color: #2e2f37;
        margin: 0;
        word-break: break-word;
    }

    /* ===== Code Style ===== */
    .nova-code {
        background: #f8f9fc;
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        color: #4e73df;
        border: 1px solid #e3e6f0;
    }

    /* ===== الصورةs Grid ===== */
    .images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.85rem;
    }

    .image-item {
        position: relative;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid #e3e6f0;
        background: #f8f9fc;
        aspect-ratio: 1 / 1;
    }

    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.2s ease;
    }

    .image-item:hover img {
        transform: scale(1.05);
    }

    .image-main {
        border-color: #4e73df;
        box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.15);
    }

    .image-badge {
        position: absolute;
        top: 0.4rem;
        left: 0.4rem;
        background: #4e73df;
        color: #fff;
        font-size: 0.7rem;
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        font-weight: 600;
        z-index: 2;
    }

    /* ===== Empty State ===== */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #858796;
        margin-bottom: 0.5rem;
    }

    /* ===== إجراءات ===== */
    .detail-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    /* ===== Modal ===== */
    .nova-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .nova-modal-backdrop.show {
        display: flex;
    }

    .nova-modal-backdrop .nova-modal {
        max-width: 480px;
        width: 100%;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        animation: modalFadeIn 0.2s ease;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nova-delete-modal {
        padding: 1.75rem;
        text-align: center;
    }

    .nova-delete-modal-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: rgba(231, 74, 59, 0.1);
        color: #e74a3b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .nova-delete-modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .nova-delete-modal-text {
        color: #858796;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .nova-delete-modal-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .detail-grid-5 {
            grid-template-columns: repeat(3, 1fr);
        }
        .detail-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .detail-grid-4,
        .detail-grid-5 {
            grid-template-columns: 1fr;
        }
        .detail-actions {
            flex-direction: column;
        }
        .detail-actions .nova-btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== حذف Modal =====
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteItemName').innerText = name;
        document.getElementById('deleteForm').action = '/items/' + id;
        document.getElementById('deleteItemModal').classList.add('show');
    };

    window.closeحذفModal = function() {
        document.getElementById('deleteItemModal').classList.remove('show');
    };

    // إغلاق modal on backdrop click
    const modal = document.getElementById('deleteItemModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeحذفModal();
            }
        });
    }

    // إغلاق modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeحذفModal();
        }
    });

    // ===== Auto-dismiss alerts =====
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