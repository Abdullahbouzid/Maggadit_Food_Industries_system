@extends('dashboard.partials.layout')

@section('title', 'إدارة الأجهزة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-microchip" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            الأجهزة
        </h2>
        <p class="nova-page-subtitle">إدارة أجهزة المنظمة والماركات والشركات</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('devices.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('devices.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة جهاز
        </a>
    </div>
</div>

{{-- Alert Messages --}}
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
@if(session('warning'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('warning') }}
    </div>
@endif
@if(session('import_errors'))
    <div class="nova-alert danger">
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;"><i class="fas fa-exclamation-circle"></i> أخطاء الاستيراد:</h6>
        <ul style="margin: 0.25rem 0 0; padding-left: 1.25rem;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-microchip"></i>
        </div>
        <p class="nova-stat-label">إجمالي الأجهزة</p>
        <p class="nova-stat-value">{{ $total ?? $devices->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشط</p>
        <p class="nova-stat-value">{{ $active ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-pause-circle"></i>
        </div>
        <p class="nova-stat-label">غير نشط</p>
        <p class="nova-stat-value">{{ $inactive ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-building"></i>
        </div>
        <p class="nova-stat-label">المنظمات</p>
        <p class="nova-stat-value">{{ $organizations->count() }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد الأجهزة
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('devices.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف إكسل (.xlsx, .xls, .csv)</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>name_en, name_ar, serial, organization_id, model_no, brand, is_inactive, purchase_date, warranty_expiry, remark</strong></div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('devices.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Main Filter --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('devices.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <input type="text" name="search" class="nova-input" placeholder="الاسم، الشيفرة الخاصة، الماركة..." value="{{ request('search') }}">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">المنظمة</label>
                <select name="organization_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">الحالة</label>
                <select name="status" class="nova-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">عدد السجلات لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10">10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">تطبيق</button>
                @if(request()->query())
                    <a href="{{ route('devices.index') }}" class="nova-btn nova-btn-outline">إعادة ضبط</a>
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
                <i class="fas fa-list"></i> قائمة الأجهزة
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('devices.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة جهاز
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>الشيفرة الخاصة</th>
                    <th>المنظمة</th>
                    <th>الماركة</th>
                    <th>الموديل</th>
                    <th>الحالة</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($devices->currentPage() - 1) * $devices->perPage() }}</td>
                    <td>{{ $device->name_en }}</td>
                    <td>{{ $device->name_ar }}</td>
                    <td><code>{{ $device->serial }}</code></td>
                    <td>{{ $device->organization?->name ?? '—' }}</td>
                    <td>{{ $device->brand ?: '—' }}</td>
                    <td>{{ $device->model_no ?: '—' }}</td>
                    <td>
                        @if($device->is_inactive)
                            <span class="nova-badge danger">غير نشط</span>
                        @else
                            <span class="nova-badge success">نشط</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('devices.show', $device) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('devices.edit', $device) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmDelete({{ $device->id }}, '{{ addslashes($device->serial) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد أجهزة</h3>
                            <p class="nova-empty-text">أنشئ أول جهاز الآن.</p>
                            <a href="{{ route('devices.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إضافة جهاز
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
                عرض {{ $devices->firstItem() ?? 0 }} إلى {{ $devices->lastItem() ?? 0 }} من أصل {{ $devices->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $devices->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف الجهاز <strong id="deleteName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
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
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmDelete = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/devices/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };

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
