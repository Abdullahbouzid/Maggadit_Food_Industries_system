@extends('dashboard.partials.layout')

@section('title', 'Item Pack Details')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Item Pack Details
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            Viewing packaging information for
            @if($itemPack->barcode)
                <strong>{{ $itemPack->barcode }}</strong>
            @else
                <strong>Pack #{{ $itemPack->id }}</strong>
            @endif
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-packs.edit', $itemPack) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
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
@if($itemPack->updated_at)
    <div class="nova-alert info" style="display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-clock"></i>
        آخر تحديث: {{ $itemPack->updated_at->diffForHumans() }}
        <span class="nova-text-muted">({{ $itemPack->updated_at->format('Y-m-d H:i') }})</span>
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
                <label class="detail-label">Item</label>
                <p class="detail-value">
                    @if($itemPack->item)
                        <a href="{{ route('items.show', $itemPack->item) }}"
                           class="nova-text-primary nova-fw-bold">
                            {{ $itemPack->item->name_en ?? $itemPack->item->name_ar ?? '—' }}
                        </a>
                        @if($itemPack->item->code)
                            <br><small class="nova-text-muted">{{ $itemPack->item->code }}</small>
                        @endif
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Pack Type</label>
                <p class="detail-value">
                    @if($itemPack->packType)
                        <span class="nova-badge info">
                            {{ $itemPack->packType->name_en ?? $itemPack->packType->name_ar ?? '—' }}
                        </span>
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Pack Capacity</label>
                <p class="detail-value">
                    @if($itemPack->packCapacity)
                        <span class="nova-badge secondary">
                            {{ $itemPack->packCapacity->value ?? $itemPack->packCapacity->formatted_value ?? '—' }}
                            {{ $itemPack->packCapacity->uom->uom_symbol ?? $itemPack->packCapacity->uom->code ?? '' }}
                        </span>
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Quantity in Pack</label>
                <p class="detail-value">
                    <span class="nova-badge primary">
                        {{ rtrim(rtrim(number_format($itemPack->quantity_in_pack, 3, '.', ''), '0'), '.') }}
                    </span>
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Barcode</label>
                <p class="detail-value">
                    @if($itemPack->barcode)
                        <code class="nova-code">{{ $itemPack->barcode }}</code>
                    @else
                        <span class="nova-text-muted">—</span>
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Serial Separator</label>
                <p class="detail-value">{{ $itemPack->serial_separator ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Equivalency Factor</label>
                <p class="detail-value">{{ $itemPack->equivalency_factor ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Sort Order</label>
                <p class="detail-value">{{ $itemPack->sort_order ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Dimensions & Weight ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-ruler me-2"></i> Dimensions & Weight</h5>
    </div>
    <div class="nova-card-body">
        <div class="detail-grid detail-grid-4">
            <div class="detail-item">
                <label class="detail-label">Height</label>
                <p class="detail-value">{{ $itemPack->height ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Width</label>
                <p class="detail-value">{{ $itemPack->width ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Depth</label>
                <p class="detail-value">{{ $itemPack->depth ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label class="detail-label">Weight</label>
                <p class="detail-value">{{ $itemPack->weight ?? '—' }}</p>
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
                $itemPack->is_default_pack      ? ['label' => 'Default Pack',       'class' => 'success', 'icon' => 'check-circle'] : null,
                $itemPack->is_weight_priced     ? ['label' => 'Weight Priced',      'class' => 'success', 'icon' => 'weight-hanging'] : null,
                $itemPack->allow_fraction_in_qty? ['label' => 'Allow Fraction Qty', 'class' => 'info',    'icon' => 'divide'] : null,
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
            <p class="nova-text-muted mb-0">— No flags set —</p>
        @endif
    </div>
</div>

{{-- ===== Pack Operations ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title">
            <i class="fas fa-tag me-2"></i> Pack Operations
            @if($itemPack->packOperations->count())
                <span class="nova-badge primary">{{ $itemPack->packOperations->count() }}</span>
            @endif
        </h5>
    </div>
    <div class="nova-card-body">
        @if($itemPack->packOperations->count())
            <div class="nova-flex nova-flex-wrap nova-gap-2">
                @foreach($itemPack->packOperations as $op)
                    <span class="nova-badge secondary">
                        <i class="fas fa-cog"></i>
                        {{ $op->name_en ?? $op->name_ar ?? '—' }}
                        @if($op->code)
                            <small class="nova-text-muted">({{ $op->code }})</small>
                        @endif
                    </span>
                @endforeach
            </div>
        @else
            <p class="nova-text-muted mb-0">— No operations —</p>
        @endif
    </div>
</div>

{{-- ===== Pack Groups ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title">
            <i class="fas fa-layer-group me-2"></i> Pack Groups
            @if($itemPack->packGroups->count())
                <span class="nova-badge primary">{{ $itemPack->packGroups->count() }}</span>
            @endif
        </h5>
    </div>
    <div class="nova-card-body">
        @if($itemPack->packGroups->count())
            <div class="nova-flex nova-flex-wrap nova-gap-2">
                @foreach($itemPack->packGroups as $group)
                    <span class="nova-badge secondary">
                        <i class="fas fa-layer-group"></i>
                        {{ $group->name_en ?? $group->name_ar ?? '—' }}
                        @if($group->code)
                            <small class="nova-text-muted">({{ $group->code }})</small>
                        @endif
                    </span>
                @endforeach
            </div>
        @else
            <p class="nova-text-muted mb-0">— No groups —</p>
        @endif
    </div>
</div>

{{-- ===== Actions ===== --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="detail-actions">
            <a href="{{ route('item-packs.edit', $itemPack) }}" class="nova-btn nova-btn-primary">
                <i class="fas fa-edit"></i> Edit Pack
            </a>
            <a href="{{ route('item-packs.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button type="button"
                    class="nova-btn nova-btn-danger"
                    onclick="confirmDelete({{ $itemPack->id }}, '{{ addslashes($itemPack->barcode ?? 'Item Pack #' . $itemPack->id) }}')">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ===== Delete Confirmation Modal ===== --}}
<div class="nova-modal-backdrop" id="deleteItemPackModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete item pack
                <strong id="deleteItemPackName" class="nova-text-danger"></strong>?
                <br>This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete Permanently
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

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
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

    /* ===== Actions ===== */
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
        .detail-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .detail-grid-4 {
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

    // ===== Delete Modal =====
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteItemPackName').innerText = name;
        document.getElementById('deleteForm').action = '/item-packs/' + id;
        document.getElementById('deleteItemPackModal').classList.add('show');
    };

    window.closeDeleteModal = function() {
        document.getElementById('deleteItemPackModal').classList.remove('show');
    };

    // Close modal on backdrop click
    const modal = document.getElementById('deleteItemPackModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDeleteModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeDeleteModal();
        }
    });

    // ===== Auto-dismiss alerts =====
    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush