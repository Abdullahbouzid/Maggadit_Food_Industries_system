@extends('dashboard.partials.layout')

@section('title', 'Units من Measure (UOM) Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-layer-group" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Units من Measure (UOM)
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة وحدات القياس والرموز والأسماء المتعددة اللغات والتفعيل</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('uoms.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة جديد UOM
        </a>
        <a href="{{ route('uoms.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h6 class="nova-fw-bold"><i class="fas fa-exclamation-circle nova-me-1"></i> أخطاء الاستيراد:</h6>
        <ul class="nova-mb-0">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== Stats Cards ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="nova-stat-label">إجمالي وحدات القياس</p>
        <p class="nova-stat-value">{{ $totalUoms }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشط</p>
        <p class="nova-stat-value">{{ $activeCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-منf"></i>
        </div>
        <p class="nova-stat-label">غير نشط</p>
        <p class="nova-stat-value">{{ $inactiveCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">مع صورة</p>
        <p class="nova-stat-value">{{ $withImageCount }}</p>
    </div>
</div>

{{-- ===== Import Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-upload nova-me-2"></i> Import UOMs</h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('uoms.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="import-grid">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">ملف Excel</label>
                    <input type="file" name="import_file" class="nova-input" required>
                    <small class="nova-help-text">
                        الأعمدة المطلوبة | اختياري
                    </small>
                </div>
                <div class="import-actions">
                    <button type="submit" class="nova-btn nova-btn-success">
                        <i class="fas fa-upload"></i> استيراد البيانات
                    </button>
                    <a href="{{ route('uoms.template') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-file-text"></i> تحميل القالب
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== فلتر Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('uoms.index') }}" id="filterForm">
            <div class="filter-grid">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.search') }}</label>
                    <div class="nova-input-group">
                        <span class="nova-input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="searchInput" class="nova-input"
                               placeholder="Code, Name (E—R), Symbol..."
                               value="{{ request('search') }}" autocomplete="منf">
                    </div>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.status') }}</label>
                    <select name="status" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="">{{ __('messages.filter.all') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.filter.status_active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.filter.status_inactive') }}</option>
                    </select>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                    <select name="per_page" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> فلتر
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'per_page']))
                        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> إعادة تعيين
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة وحدات القياس
                @if($uoms->total())
                    <span class="nova-badge primary" style="margin-inline-start: 0.5rem;">{{ $uoms->total() }}</span>
                @endif
            </h3>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th class="nova-text-start">#</th>
                    <th>الكود</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>الرمز</th>
                    <th>الصورة</th>
                    <th>الحالة</th>
                    <th class="nova-text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uoms as $uom)
                <tr>
                    <td class="nova-fw-semibold">
                        {{ $loop->iteration + ($uoms->currentPage() - 1) * $uoms->perPage() }}
                    </td>
                    <td><code class="nova-code">{{ $uom->code }}</code></td>
                    <td><span class="nova-fw-bold">{{ $uom->name_en }}</span></td>
                    <td>{{ $uom->name_ar ?: '—' }}</td>
                    <td>
                        @if($uom->uom_symbol)
                            <span class="nova-badge info">{{ $uom->uom_symbol }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($uom->image)
                            <img src="{{ asset('uploads/' . $uom->image) }}"
                                 alt="{{ $uom->name_en }}"
                                 class="nova-table-thumb"
                                 loading="lazy">
                        @else
                            <span class="nova-text-muted nova-fs-sm">— لا الصورة —</span>
                        @endif
                    </td>
                    <td>
                        @if($uom->is_active)
                            <span class="nova-badge success">
                                <i class="fas fa-check-circle"></i> نشط
                            </span>
                        @else
                            <span class="nova-badge secondary">
                                <i class="fas fa-eye-منf"></i> غير نشط
                            </span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('uoms.show', $uom) }}"
                               class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('uoms.edit', $uom) }}"
                               class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="nova-icon-btn nova-btn-sm nova-text-danger"
                                    title="حذف"
                                    onclick="confirmحذف({{ $uom->id }}, '{{ addslashes($uom->name_en) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="nova-text-center" style="padding: 2.5rem 1rem;">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h5 class="nova-text-muted">لا توجد وحدات قياس</h5>
                            <p class="nova-text-muted nova-fs-sm">
                                @if(request()->anyFilled(['search', 'status']))
                                    يرجى تعديل معايير البحث أو الفلتر
                                @else
                                    ابدأ بإضافة أول وحدة قياس
                                @endif
                            </p>
                            @if(request()->anyFilled(['search', 'status']))
                                <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline nova-mt-2">
                                    <i class="fas fa-times"></i> {{ __('messages.filter.clear') }}
                                </a>
                            @else
                                <a href="{{ route('uoms.create') }}" class="nova-btn nova-btn-primary nova-mt-2">
                                    <i class="fas fa-plus-circle"></i> Add UOM
                                </a>
                            @endif
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
                عرض {{ $uoms->firstItem() ?? 0 }} to {{ $uoms->lastItem() ?? 0 }} من {{ $uoms->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $uoms->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== حذف تأكيدation Modal ===== --}}
<div class="nova-modal-backdrop" id="deleteUomModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete UOM
                <strong id="deleteUomName" class="nova-text-danger"></strong>?
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
    /* ===== Import Grid ===== */
    .import-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .import-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ===== فلتر Grid ===== */
    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ===== Table Thumb ===== */
    .nova-table-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 0.35rem;
        border: 1px solid #e3e6f0;
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
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .import-grid {
            grid-template-columns: 1fr;
        }
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== حذف Modal =====
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteUomName').innerText = name;
        document.getElementById('deleteForm').action = '/uoms/' + id;
        document.getElementById('deleteUomModal').classList.add('show');
    };

    window.closeحذفModal = function() {
        document.getElementById('deleteUomModal').classList.remove('show');
    };

    // إغلاق modal on backdrop click
    const modal = document.getElementById('deleteUomModal');
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

    // ===== Auto-search (debounced) =====
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let timeout = null;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    }

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