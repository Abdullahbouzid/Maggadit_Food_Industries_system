@extends('dashboard.partials.layout')

@section('title', 'إدارة أنواع طلبات البيع')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
             <i class="fas fa-shopping-cart" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Sales Order Types
        </h2>
        <p class="nova-page-subtitle">Manage sales order types, codes, and default settings</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('sales-order-types.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير Table
        </a>
        <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Create
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
<div class="nova-row cols-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-grid-2"></i>
        </div>
        <p class="nova-stat-label">Total Types</p>
        <p class="nova-stat-value">{{ $total ?? $salesOrderTypes->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="nova-stat-label">نشط Types</p>
        <p class="nova-stat-value">{{ $activeCount ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-x-circle"></i>
        </div>
        <p class="nova-stat-label">غير نشط Types</p>
        <p class="nova-stat-value">{{ $inactiveCount ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-star"></i>
        </div>
        <p class="nova-stat-label">افتراضي Type</p>
        <p class="nova-stat-value">{{ $defaultCount ?? 0 }}</p>
    </div>
</div>

{{-- Import Section --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-upload"></i>
            Import Sales Order Types
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('sales-order-types.import') }}" method="POST" enctype="multipart/form-data" class="nova-row cols-3" style="align-items: end;">
            @csrf
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">ملف Excel</label>
                <input type="file" name="import_file" class="nova-input" required>
                <div class="nova-help-text">مطلوب columns: <strong>name, code, active, default</strong>. نشط/افتراضي: نعم/لا or 1/0 or true/false.</div>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-upload"></i> استيراد البيانات
                </button>
            </div>
            <div style="height: fit-content;">
                <a href="{{ route('sales-order-types.template') }}" class="nova-btn nova-btn-outline nova-btn-block">
                    <i class="fas fa-file-alt"></i> تحميل القالب
                </a>
            </div>
        </form>
    </div>
</div>

{{-- تصدير Options (Custom فلترs) --}}
<div class="nova-card mb-4">
    <div class="nova-card-header">
        <h2 class="nova-card-title" style="margin: 0;">
            <i class="fas fa-download"></i>
            تصدير Options (Custom فلترs)
        </h2>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('sales-order-types.export') }}" method="GET" class="nova-row cols-3" style="align-items: end;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث (name/code)</label>
                <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.search') }}"">
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.status') }}</label>
                <select name="active_status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="active">{{ __('messages.filter.status_active') }}</option>
                    <option value="inactive">{{ __('messages.filter.status_inactive') }}</option>
                </select>
            </div>
            <div style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-block">
                    <i class="fas fa-file-excel"></i> تصدير فلترed
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Main فلتر (for table) --}}
<div class="nova-card mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form id="filterForm" method="GET" action="{{ route('sales-order-types.index') }}" class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">بحث (name/code)</label>
                <div class="nova-input-group" style="display: flex; gap: 0;">
                     <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.code') }}" value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                </div>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">{{ __('messages.filter.status') }}</label>
                <select name="active_status" class="nova-select">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    <option value="active" {{ request('active_status') == 'active' ? 'selected' : '' }}>{{ __('messages.filter.status_active') }}</option>
                    <option value="inactive" {{ request('active_status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.filter.status_inactive') }}</option>
                </select>
            </div>
            <div class="nova-form-group" style="margin-bottom: 0;">
                <label class="nova-label">Rows لكل صفحة</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="height: fit-content;">
                <button type="submit" class="nova-btn nova-btn-primary">فلتر</button>
                @if(request('search') || request('active_status') || request('per_page'))
                    <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline">إعادة تعيين</a>
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
                <i class="fas fa-list"></i>
                قائمة أنواع طلبات البيع
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Create Type
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>الحالة</th>
                    <th>افتراضي</th>
                    <th style="text-align: right;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesOrderTypes as $type)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($salesOrderTypes->currentPage() - 1) * $salesOrderTypes->perPage() }}</td>
                    <td>{{ $type->name }}</td>
                    <td><span class="nova-badge secondary">{{ $type->code }}</span></td>
                    <td>
                        @if($type->active)
                            <span class="nova-badge success">نشط</span>
                        @else
                            <span class="nova-badge danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        @if($type->default)
                            <span class="nova-badge accent"><i class="fas fa-star"></i> افتراضي</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                <a href="{{ route('sales-order-types.show', $type) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('sales-order-types.edit', $type) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                    <i class="fas fa-edit"></i>
                </a>
                <x-table-actions
                    :view-url="null"
                    :edit-url="null"
                    :delete-url="route('sales-order-types.destroy', $type)"
                    :delete-name="$type->name"
                    delete-modal="globalحذفModal" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-database" style="width: 40px; height: 40px;"></i>
                            </div>
                            <h5 class="nova-text-muted">لا sales order types found</h5>
                            <a href="{{ route('sales-order-types.create') }}" class="nova-btn nova-btn-primary">Create one</a>
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
                عرض {{ $salesOrderTypes->firstItem() ?? 0 }} إلى {{ $salesOrderTypes->lastItem() ?? 0 }} من أصل {{ $salesOrderTypes->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $salesOrderTypes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

<x-delete-modal modal-id="globalحذفModal" />
