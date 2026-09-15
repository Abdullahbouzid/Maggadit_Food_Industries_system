@extends('dashboard.partials.layout')

@section('title', 'إدارة العملاء')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-users" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            العملاء
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">إدارة حسابات العملاء والمنظمات</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('customers.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير
        </a>
        <a href="{{ route('customers.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> عميل جديد
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
        <ul style="margin: 0.25rem 0 0; padding-inline-start: 1.25rem;">
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
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">إجمالي العملاء</p>
        <p class="nova-stat-value">{{ $totalCustomers ?? $customers->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">بصورة شخصية</p>
        <p class="nova-stat-value">{{ $withAvatar ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-briefcase"></i>
        </div>
        <p class="nova-stat-label">بمنظمة نشطة</p>
        <p class="nova-stat-value">{{ $withActiveOrganization ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <p class="nova-stat-label">بدون منظمة</p>
        <p class="nova-stat-value">{{ $withoutOrganization ?? 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
            <i class="fas fa-upload"></i>
            استيراد العملاء
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.app.page__2') }}</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">العمود المطلوب: <strong>name, username, email</strong> (كلمة المرور اختيارية، الافتراضية "password123").<br>اختياري: <strong>active_organization_name</strong> (يجب أن يكون موجوداً).</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('customers.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تنزيل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Export Filter Section --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
            <i class="fas fa-download"></i>
            خيارات التصدير (فلاتر مخصصة)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('customers.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث (اسم/اسم مستخدم/بريد)</label>
                <input type="text" name="search" class="nova-input" placeholder="أدخل كلمة مفتاح...">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">المنظمة النشطة</label>
                <select name="organization_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Organization::pluck('name', 'id') as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير
                </button>
            </div>
        </form>
        <div class="nova-help-text" style="margin-top: 0.5rem;">استخدم هذه الفلاتر لتنزيل ملف إكسل يحتوي فقط على السجلات المطابقة.</div>
    </div>
</div>

{{-- Search Filter & Per Page --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('customers.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                    <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="الاسم، اسم المستخدم، البريد..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                </div>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">عدد السجلات لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">تطبيق</button>
                @if(request('search') || request('per_page'))
                    <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline">إعادة ضبط</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i> قائمة العملاء
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('customers.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة عميل
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الصورة الشخصية</th>
                    <th>المنظمة النشطة</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->username }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>
                        @if($customer->avatar)
                            <img src="{{ asset('uploads/customers/' . $customer->avatar) }}" alt="{{ $customer->name }}" class="nova-avatar nova-avatar-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <span class="nova-text-muted fst-italic">—</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->activeOrganization)
                            <span class="nova-badge success">{{ $customer->activeOrganization->name }}</span>
                        @else
                            <span class="nova-text-muted fst-italic">—</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('customers.show', $customer) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="حذف" onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
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
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">لا توجد عملاء</h3>
                            <p class="nova-empty-text">أنشئ أول عميل الآن.</p>
                            <a href="{{ route('customers.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> إنشاء عميل
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
                عرض {{ $customers->firstItem() ?? 0 }} إلى {{ $customers->lastItem() ?? 0 }} من أصل {{ $customers->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $customers->appends(request()->query())->links() }}
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
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف العميل <strong id="deleteName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
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
        document.getElementById('deleteForm').action = '/customers/' + id;
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
