@extends('dashboard.partials.layout')

@section('title', 'Item Models')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-microchip" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Item Models
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage product models</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة جديد
        </a>
        <a href="{{ route('item-models.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
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

<div class="nova-row cols-3">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-microchip"></i>
        </div>
        <p class="nova-stat-label">Total</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
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

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-upload"></i> Import Item Models
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('item-models.import') }}" method="POST" enctype="multipart/form-data" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="flex: 1 1 20rem; margin-bottom: 0;">
                <label class="nova-label">Import File</label>
                <input type="file" name="import_file" class="nova-input" required>
            </div>
            <div class="nova-form-group" style="flex: 1 1 9rem; margin-bottom: 0;">
                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">Import</button>
            </div>
            <div class="nova-form-group" style="flex: 1 1 10rem; margin-bottom: 0;">
                <a href="{{ route('item-models.template') }}" class="nova-btn nova-btn-outline nova-w-100" style="margin-top: 0.35rem;">Template</a>
            </div>
        </form>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('item-models.index') }}" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            <div class="nova-form-group" style="flex: 1 1 17rem; margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <input type="text" name="search" class="nova-input" placeholder="بحث..." value="{{ request('search') }}">
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
                <label class="nova-label">لكل صفحة</label>
                <select name="per_page" class="nova-select" onchange="this.form.submit()">
                    <option value="10">10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 9rem; margin-bottom: 0;">
                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">فلتر</button>
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-microchip"></i> قائمة نماذج العنصر
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Model
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name (EN)</th>
                    <th>Name (AR)</th>
                    <th>الحالة</th>
                    <th>Sort</th>
                    <th style="text-align: right;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($models as $m)
                <tr>
                    <td>{{ $loop->iteration + ($models->currentصفحة() - 1) * $models->perصفحة() }}</td>
                    <td><code>{{ $m->code }}</code></td>
                    <td>{{ $m->name_en }}</td>
                    <td>{{ $m->name_ar }}</td>
                    <td>
                        @if($m->is_active)
                            <span class="nova-badge success">نشط</span>
                        @else
                            <span class="nova-badge secondary">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $m->sort_order ?? '-' }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                <a href="{{ route('item-models.show', $m) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('item-models.edit', $m) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذف({{ $m->id }}, '{{ addslashes($m->name_en) }}')">
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
                                <i class="fas fa-microchip" style="width: 48px; height: 48px;"></i>
                            </div>
                            <h3 class="nova-empty-title">لا سجل</h3>
                            <p class="nova-empty-text">Add an item model to get started.</p>
                <a href="{{ route('item-models.create') }}" class="nova-btn nova-btn-primary">
                    <i class="fas fa-plus"></i> Add Model
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
