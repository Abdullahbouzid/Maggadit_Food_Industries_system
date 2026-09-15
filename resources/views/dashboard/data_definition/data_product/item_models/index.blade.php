@extends('dashboard.partials.layout')

@section('title', 'نماذج العناصر')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-microchip" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            نماذج العناصر
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة نماذج العناصر المستخدمة في المنتجات</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة نموذج
        </a>
        <a href="{{ route('item-models.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin: 0; padding-inline-start: 1.25rem;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics --}}
<div class="nova-row cols-3 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-microchip"></i>
        </div>
        <p class="nova-stat-label">إجمالي النماذج</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشط</p>
        <p class="nova-stat-value">{{ $active }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-circle-pause"></i>
        </div>
        <p class="nova-stat-label">غير نشط</p>
        <p class="nova-stat-value">{{ $inactive }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-upload"></i> استيراد نماذج العناصر
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('item-models.import') }}" method="POST" enctype="multipart/form-data" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="flex: 1 1 20rem; margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>code, name_en, name_ar, is_active, sort_order</strong></div>
            </div>
            <div class="nova-form-group" style="flex: 1 1 9rem; margin-bottom: 0;">
                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">إستيراد</button>
            </div>
            <div class="nova-form-group" style="flex: 1 1 10rem; margin-bottom: 0;">
                <a href="{{ route('item-models.template') }}" class="nova-btn nova-btn-outline nova-w-100" style="margin-top: 0.35rem;">تنزيل القالب</a>
            </div>
        </form>
    </div>
</div>

{{-- تصدير فلتر --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-download"></i> خيارات التصدير (فلاتر مخصصة)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('item-models.export') }}" method="GET" class="nova-row cols-3 nova-gap-3">
            <div class="nova-form-group">
                <label class="nova-label">بحث</label>
                <input type="text" name="search" class="nova-input" placeholder="كود أو اسم...">
            </div>
            <div class="nova-form-group">
                <label class="nova-label">الحالة</label>
                <select name="status" class="nova-select">
                    <option value="">الكل</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>
            <div class="nova-form-group" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Main فلتر --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('item-models.index') }}" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            <div class="nova-form-group" style="flex: 1 1 17rem; margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                    <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="البحث بالكود أو الاسم..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                </div>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">الحالة</label>
                <select name="status" class="nova-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 10rem; margin-bottom: 0;">
                <label class="nova-label">عدد السجلات لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10">10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">تطبيق</button>
                @if(request()->query())
                    <a href="{{ route('item-models.index') }}" class="nova-btn nova-btn-outline">إعادة ضبط</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i> قائمة نماذج العناصر
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة نموذج
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الشيفرة</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>الحالة</th>
                    <th>ترتيب الفرز</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($models as $model)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($models->currentصفحة() - 1) * $models->perصفحة() }}</td>
                    <td><code>{{ $model->code }}</code></td>
                    <td>{{ $model->name_en }}</td>
                    <td>{{ $model->name_ar }}</td>
                    <td>
                        @if($model->is_active)
                            <span class="nova-badge success">نشط</span>
                        @else
                            <span class="nova-badge secondary">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $model->sort_order ?? '—' }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('item-models.show', $model) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('item-models.edit', $model) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذف({{ $model->id }}, '{{ addslashes($model->name_en) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد نماذج منتجات</h3>
                            <p class="nova-empty-text">أنشئ أول نموذج الآن.</p>
                            <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء نموذج
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
                عرض {{ $models->firstItem() ?? 0 }} إلى {{ $models->lastItem() ?? 0 }} من أصل {{ $models->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $models->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف نوع المنتج <strong id="deleteName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف نهائي</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/item-models/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush
