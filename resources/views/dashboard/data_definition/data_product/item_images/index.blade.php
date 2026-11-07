@extends('dashboard.partials.layout')

@section('title', 'Item الصورةs')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-image" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Item الصورةs
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage product images used in item definitions.</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-images.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> Add الصورة
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
@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>أخطاء الاستيراد:</strong>
            <ul class="nova-mb-0 nova-mt-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ============================================================
     فلتر Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('item-images.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">{{ __('messages.filter.search') }}</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="بحث by item or code..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- لكل صفحة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                </select>
            </div>

            {{-- إجراءات --}}
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> فلتر
                </button>
                @if(request('search') || request('per_page'))
                    <a href="{{ route('item-images.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-rotate-left"></i> إعادة تعيين
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     Data Table
     ============================================================ --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i> قائمة صور العنصر
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('item-images.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus-circle"></i> Add الصورة
            </a>
        </div>
    </div>

    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Item</th>
                    <th>الصورة</th>
                    <th>Alt Text</th>
                    <th>ترتيب الفرز</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $image)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($images->currentPage() - 1) * $images->perPage() }}
                    </td>
                    <td>{{ $image->item->name_en ?? $image->item->name_ar ?? '—' }}</td>
                    <td>
                        <img src="{{ $image->url }}" alt="{{ $image->alt_text }}" style="max-width: 100px; border: 1px solid var(--nova-border); border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    </td>
                    <td>{{ $image->alt_text ?? '—' }}</td>
                    <td>{{ $image->sort_order }}</td>
<td class="nova-text-end">
                        <x-table-actions
                            :view-url="route('item-images.show', $image)"
                            :edit-url="route('item-images.edit', $image)"
                            :delete-url="route('item-images.destroy', $image)"
                            :delete-name="$image->alt_text ?? $image->item->name_en ?? 'image'"
                            delete-modal="globalحذفModal" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-image" style="width: 48px; height: 48px;"></i>
                            </div>
                            <h3 class="nova-empty-title">لا item images found</h3>
                            <p class="nova-empty-text">Add an item image to get started.</p>
                            <a href="{{ route('item-images.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> Add الصورة
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                عرض {{ $images->firstItem() ?? 0 }} إلى {{ $images->lastItem() ?? 0 }} من أصل {{ $images->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $images->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<x-delete-modal modal-id="globalحذفModal" />
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* ============================================================
           Auto-submit on per_page change
           ============================================================ */
        const perصفحةSelect = document.querySelector('select[name="per_page"]');
        if (perصفحةSelect) {
            perصفحةSelect.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        }

/* ============================================================
           Auto-dismiss Alerts
           ============================================================ */
         setTimeout(() => {
            document.querySelectorالكل('.nova-alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
@endpush

@push('styles')
<style>
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .filter-search-wrapper {
        position: relative;
        display: flex;
        align-items: stretch;
        width: 100%;
    }

    .filter-search-icon {
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

    .filter-search-input {
        border-radius: 0 10px 10px 0 !important;
        border-start: none !important;
        flex: 1;
        min-width: 0;
    }

    .filter-item-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-item-actions .nova-btn {
        white-space: nowrap;
    }

    @media (max-width: 992px) {
        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 2;
        }

        .filter-item-actions {
            grid-column: span 2;
            justify-content: flex-end;
        }
    }

    @media (max-width: 480px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .filter-item-search,
        .filter-item-actions {
            grid-column: span 1;
        }
    }
</style>
@endpush
