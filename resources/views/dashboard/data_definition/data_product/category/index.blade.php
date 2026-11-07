@extends('dashboard.partials.layout')

@section('title', 'إدارة الفئات')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-folder" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Categories
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage categories, codes, divisions, and visibility</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('categories.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة جديد
        </a>
        <a href="{{ route('categories.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير Table
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

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid"></i>
        </div>
        <p class="nova-stat-label">إجمالي الفئات</p>
        <p class="nova-stat-value">{{ $totalCategories }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-eye"></i>
        </div>
        <p class="nova-stat-label">Shown in App</p>
        <p class="nova-stat-value">{{ $shownInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-منf"></i>
        </div>
        <p class="nova-stat-label">Hidden in App</p>
        <p class="nova-stat-value">{{ $hiddenInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">مع صورة</p>
        <p class="nova-stat-value">{{ $withImage }}</p>
    </div>
</div>

{{-- ===== Import Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h6 class="nova-fw-bold mb-0">
            <i class="fas fa-upload"></i> Import Categories
        </h6>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="nova-form-group import-form-input">
                <label class="nova-label">ملف Excel</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">
                    مطلوب columns: <strong>code, name, division</strong>.
                    اختياري: image, show_in_customer_app, sort_order.
                </div>
            </div>
            <div class="import-form-actions">
                <button type="submit" class="nova-btn nova-btn-success">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
                <a href="{{ route('categories.template') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-file-text"></i> تحميل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
     فلتر Section
     ============================================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('categories.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">بحث (Code/Name)</label>
                <input type="text" name="search" class="nova-input filter-search-input"
                       placeholder="Code or name..."
                       value="{{ request('search') }}">
            </div>

            {{-- Division --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Division</label>
                <select name="division" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div }}" {{ request('division') == $div ? 'selected' : '' }}>{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Show الحالة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">Show الحالة</label>
                <select name="show_status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="shown" {{ request('show_status') == 'shown' ? 'selected' : '' }}>Shown</option>
                    <option value="hidden" {{ request('show_status') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                </select>
            </div>

            {{-- لكل صفحة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                </select>
            </div>

            {{-- إجراءات --}}
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> فلتر
                </button>
                @if(request()->anyFilled(['search', 'division', 'show_status', 'per_page']))
                    <a href="{{ route('categories.index') }}" class="nova-btn nova-btn-outline">
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
                <i class="fas fa-list"></i> قائمة الفئات
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('categories.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus-circle"></i> Add الفئة
            </a>
        </div>
    </div>

    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Division</th>
                    <th>الصورة</th>
                    <th>Show in App</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoriesPaginated as $cat)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($categoriesPaginated->currentPage() - 1) * $categoriesPaginated->perPage() }}
                    </td>
                    <td>
                        <code class="table-code">{{ $cat->code }}</code>
                    </td>
                    <td>
                        <span class="nova-fw-semibold">{{ $cat->name }}</span>
                    </td>
                    <td>{{ $cat->division }}</td>
                    <td>
                        @if($cat->image)
                            <img src="{{ asset('uploads/' . $cat->image) }}" alt="{{ $cat->name }}" width="50" height="50" style="object-fit: cover; border: 1px solid var(--nova-border); border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                        @else
                            <span class="nova-text-muted" style="font-style: italic;">— لا الصورة —</span>
                        @endif
                    </td>
                    <td>
                        @if($cat->show_in_customer_app)
                            <span class="nova-badge success">
                                <i class="fas fa-check"></i> Visible
                            </span>
                        @else
                            <span class="nova-badge secondary">
                                <i class="fas fa-x"></i> Hidden
                            </span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <x-table-actions
                            :view-url="route('categories.show', $cat)"
                            :edit-url="route('categories.edit', $cat)"
                            :delete-url="route('categories.destroy', $cat)"
                            :delete-name="$cat->name"
                            delete-modal="globalحذفModal" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-folder-open" style="width: 48px; height: 48px;"></i>
                            </div>
                            <h3 class="nova-empty-title">لا categories found</h3>
                            <p class="nova-empty-text">You can add a new category using the add button.</p>
                            <a href="{{ route('categories.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> Add الفئة
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
                عرض {{ $categoriesPaginated->firstItem() ?? 0 }} to {{ $categoriesPaginated->lastItem() ?? 0 }} من {{ $categoriesPaginated->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $categoriesPaginated->appends(request()->query())->links() }}
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
    .import-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .import-form .nova-form-group {
        margin-bottom: 0;
    }

    .import-form-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
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

    .table-code {
        background: #f1f3f9;
        padding: 0.15rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        color: #5a5c69;
        font-weight: 500;
    }

    @media (max-width: 992px) {
        .filter-form {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 3;
        }

        .filter-item-actions {
            grid-column: span 3;
            justify-content: flex-end;
        }
    }

    @media (max-width: 768px) {
        .import-form {
            grid-template-columns: 1fr;
        }

        .import-form-actions {
            flex-direction: column;
            width: 100%;
        }

        .import-form-actions .nova-btn {
            width: 100%;
            justify-content: center;
        }

        .filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-item-search {
            grid-column: span 2;
        }

        .filter-item-actions {
            grid-column: span 2;
            justify-content: stretch;
        }

        .filter-item-actions .nova-btn {
            flex: 1;
            justify-content: center;
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
