@extends('dashboard.partials.layout')

@section('title', 'دفعات الإنتاج')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box-open" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            دفعات الإنتاج
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة الدفعات المنتجة وتفاصيلها</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('production-batches.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إنشاء دفعة
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

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid"></i>
        </div>
        <p class="nova-stat-label">إجمالي الدفعات</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشطة</p>
        <p class="nova-stat-value">{{ $active }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-hourglass-end"></i>
        </div>
        <p class="nova-stat-label">منتهية الصلاحية</p>
        <p class="nova-stat-value">{{ $expired }}</p>
    </div>
</div>

{{-- ===== فلتر Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('production-batches.index') }}" class="filter-form">
            {{-- بحث --}}
            <div class="nova-form-group filter-item filter-item-search">
                <label class="nova-label">{{ __('messages.filter.search') }}</label>
                <div class="filter-search-wrapper">
                    <span class="filter-search-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input filter-search-input"
                           placeholder="رقم الدفعة، ملاحظات..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- الحالة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">{{ __('messages.filter.status') }}</label>
                <select name="status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                    <option value="consumed" {{ request('status') == 'consumed' ? 'selected' : '' }}>مستهلكة</option>
                </select>
            </div>

            {{-- لكل صفحة --}}
            <div class="nova-form-group filter-item">
                <label class="nova-label">حسب الصفحة</label>
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
                    <i class="fas fa-filter"></i> {{ __('messages.filter.apply') }}
                </button>
                <a href="{{ route('production-batches.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-rotate-left"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Data Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة الدفعات
            </h3>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الدفعة</th>
                    <th>عدد المنتجات</th>
                    <th>تاريخ الإنتاج</th>
                    <th>تاريخ الانتهاء</th>
                    <th>الحالة</th>
                    <th>ملاحظات</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $batches instanceمن \Illuminate\Pagination\LengthAwarePaginator ? $loop->iteration + ($batches->currentصفحة() - 1) * $batches->perصفحة() : $loop->iteration }}
                    </td>
                    <td><code>{{ $batch->batch_number }}</code></td>
                    <td>{{ $batch->batch_items_count }}</td>
                    <td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '—' }}</td>
                    <td>{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '—' }}</td>
                    <td>
                        @php
                            $statusMap = [
                                'active' => ['label' => 'نشطة', 'color' => 'success'],
                                'expired' => ['label' => 'منتهية', 'color' => 'warning'],
                                'consumed' => ['label' => 'مستهلكة', 'color' => 'secondary'],
                            ];
                        @endphp
                        <span class="nova-badge {{ $statusMap[$batch->status]['color'] ?? 'secondary' }}">
                            {{ $statusMap[$batch->status]['label'] ?? $batch->status }}
                        </span>
                    </td>
                    <td>{{ Str::limit($batch->notes, 30) }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('production-batches.show', $batch) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('production-batches.edit', $batch) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف"
                                    onclick="confirmحذف({{ $batch->id }}, '{{ addslashes($batch->batch_number) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد دفعات بعد</h3>
                            <p class="nova-empty-text">أنشئ دفعتك الأولى.</p>
                            <a href="{{ route('production-batches.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> إنشاء دفعة
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches instanceمن \Illuminate\Pagination\LengthAwarePaginator)
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                عرض {{ $batches->firstItem() ?? 0 }} إلى {{ $batches->lastItem() ?? 0 }} من أصل {{ $batches->total() }} إدخال
            </div>
            <div class="nova-pagination">
                {{ $batches->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ===== حذف تأكيدation Modal ===== --}}
<div class="nova-modal-backdrop" id="globalحذفModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                هل أنت متأكد من رغبتك في حذف الدفعة
                <strong id="deleteItemName" class="nova-text-danger"></strong>؟<br>
                لا يمكن التراجع عن هذا الإجراء.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('globalحذفModal').classList.remove('show')">
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
<style>
    /* ===== Import Form ===== */
    .import-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    /* ===== فلتر Form ===== */
    .filter-form {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    /* بحث Input Wrapper */
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

    /* فلتر إجراءات */
    .filter-item-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-item-actions .nova-btn {
        white-space: nowrap;
    }

    /* ===== حذف Modal ===== */
    .nova-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }

    .nova-modal-backdrop.show {
        display: flex;
    }

    .nova-modal {
        background: #fff;
        border-radius: 1rem;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .nova-delete-modal {
        padding: 2rem;
        text-align: center;
    }

    .nova-delete-modal-icon {
        width: 64px;
        height: 64px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .nova-delete-modal-icon i {
        font-size: 1.75rem;
        color: #dc2626;
    }

    .nova-delete-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .nova-delete-modal-text {
        color: #6b7280;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .nova-delete-modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
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

        .filter-item {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .filter-item-search {
            grid-column: span 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perصفحةSelect = document.querySelector('select[name="per_page"]');
        if (perصفحةSelect) perصفحةSelect.addEventListener('change', () => document.getElementById('filterForm').submit());

        window.confirmحذف = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/production-batches/' + id;
            document.getElementById('globalحذفModal').classList.add('show');
        };
    });
</script>
@endpush
