@extends('dashboard.partials.layout')

@section('title', 'إدارة الفروع')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-store" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            إدارة الفروع
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">فروع العملاء</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('branches.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة فرع
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;">يرجى تصحيح الأخطاء التالية:</h6>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ============================================
     Quick Stats Cards (نفس نمط صفحة التفاصيل)
     ============================================ --}}
<div class="branch-stats-grid">
    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-primary">
            <i class="fas fa-store"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">إجمالي الفروع</span>
            <span class="branch-stat-value">{{ $branches->total() ?? 0 }}</span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-success">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">الفروع النشطة</span>
            <span class="branch-stat-value">{{ $branches->where('is_active', true)->count() }}</span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-muted">
            <i class="fas fa-circle-xmark"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">الفروع غير النشطة</span>
            <span class="branch-stat-value">{{ $branches->where('is_active', false)->count() }}</span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-info">
            <i class="fas fa-users"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">العملاء</span>
            <span class="branch-stat-value">{{ $customers->count() ?? 0 }}</span>
        </div>
    </div>
</div>

<div class="nova-card">
    {{-- Card Header: Filter --}}
    <div class="nova-card-header branches-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-list me-1"></i>
            قائمة الفروع
            <span class="branches-count-badge">{{ $branches->total() ?? 0 }}</span>
        </h3>
        <div class="nova-card-actions">
            <form method="GET" action="{{ route('branches.index') }}" class="branches-filter-form">
                <div class="branches-filter-wrap">
                    <i class="fas fa-filter branches-filter-icon"></i>
                    <select name="customer_id" onchange="this.form.submit()" class="nova-select branches-filter-select">
                        <option value="">— جميع العملاء —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Body: Table --}}
    <div class="nova-card-body" style="padding: 0;">
        @if(($branches ?? collect())->count() > 0)
            <div class="branches-table-wrap">
                <table class="branches-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>اسم الفرع</th>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>توقيت العمل</th>
                            <th>الحالة</th>
                            <th style="width: 130px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $index => $branch)
                            <tr>
                                <td class="branches-row-num">
                                    {{ $branches->firstItem() + $index }}
                                </td>
                                <td>
                                    <a href="{{ route('branches.show', $branch) }}" class="branches-name-link">
                                        <i class="fas fa-store"></i>
                                        {{ $branch->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('customers.show', $branch->customer) }}" class="branches-customer-link">
                                        <i class="fas fa-user"></i>
                                        {{ $branch->customer->name ?? '—' }}
                                    </a>
                                </td>
                                <td class="branches-cell-muted" dir="ltr">
                                    {{ $branch->phone ?: '—' }}
                                </td>
                                <td class="branches-cell-muted">
                                    @if($branch->working_hours_from && $branch->working_hours_to)
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($branch->working_hours_from)->format('H:i') }}
                                        —
                                        {{ \Carbon\Carbon::parse($branch->working_hours_to)->format('H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="branches-status {{ $branch->is_active ? 'branches-status-active' : 'branches-status-inactive' }}">
                                        <span class="branches-status-dot"></span>
                                        {{ $branch->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="branches-actions">
                                        <x-table-actions
                                            :view-url="route('branches.show', $branch)"
                                            :edit-url="route('branches.edit', $branch)"
                                            :delete-url="route('branches.destroy', $branch)"
                                            :delete-name="$branch->name"
                                            delete-modal="deleteBranchModal" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="branches-empty">
                <div class="branches-empty-icon">
                    <i class="fas fa-store-slash"></i>
                </div>
                <h4 class="branches-empty-title">لا توجد فروع مسجلة</h4>
                <p class="branches-empty-text">ابدأ بإضافة أول فرع لهذا النظام</p>
                <a href="{{ route('branches.create') }}" class="nova-btn nova-btn-primary">
                    <i class="fas fa-plus"></i> إضافة فرع جديد
                </a>
            </div>
        @endif
    </div>

    {{-- Card Footer: Pagination --}}
    @if(($branches ?? collect())->hasPages())
        <div class="branches-pagination-wrap">
            <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
                <div class="nova-text-muted nova-fs-sm">
                    عرض {{ $branches->firstItem() ?? 0 }} إلى {{ $branches->lastItem() ?? 0 }} من أصل {{ $branches->total() }} سجلاً
                </div>
                {{ $branches->links() }}
            </div>
        </div>
    @endif
</div>

<x-delete-modal modal-id="deleteBranchModal" />

@endsection

@push('styles')
<style>
    /* ============================================
       Quick Stats Cards (same as detail page)
       ============================================ */
    .branch-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .branch-stat-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid var(--nova-border, #e2e8f0);
        border-radius: var(--nova-radius-md, 0.75rem);
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .branch-stat-card:hover {
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .branch-stat-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .branch-stat-icon-primary { background: #dbeafe; color: #1d4ed8; }
    .branch-stat-icon-success { background: #dcfce7; color: #15803d; }
    .branch-stat-icon-warning { background: #fef3c7; color: #b45309; }
    .branch-stat-icon-info    { background: #e0f2fe; color: #0369a1; }
    .branch-stat-icon-muted   { background: #f1f5f9; color: #64748b; }

    .branch-stat-body {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .branch-stat-label {
        font-size: 0.75rem;
        color: var(--nova-text-muted, #64748b);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .branch-stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--nova-text, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ============================================
       Card Header + Filter
       ============================================ */
    .branches-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .branches-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 22px;
        padding: 0 0.5rem;
        margin-inline-start: 0.5rem;
        border-radius: 999px;
        background: var(--nova-primary, #2563eb);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        vertical-align: middle;
    }

    .branches-filter-form {
        margin: 0;
    }

    .branches-filter-wrap {
        position: relative;
    }

    .branches-filter-icon {
        position: absolute;
        top: 50%;
        inset-inline-start: 0.85rem;
        transform: translateY(-50%);
        color: var(--nova-text-muted, #94a3b8);
        font-size: 0.85rem;
        pointer-events: none;
    }

    .branches-filter-select {
        padding-inline-start: 2.25rem !important;
        min-width: 220px;
    }

    /* ============================================
       Table
       ============================================ */
    .branches-table-wrap {
        overflow-x: auto;
        border-radius: 0;
    }

    .branches-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .branches-table thead th {
        text-align: start;
        padding: 0.85rem 1rem;
        background: var(--nova-bg-soft, #f8fafc);
        color: var(--nova-text-muted, #64748b);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
        white-space: nowrap;
    }

    .branches-table tbody td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
        color: var(--nova-text, #1e293b);
        vertical-align: middle;
    }

    .branches-table tbody tr {
        transition: background 0.15s ease;
    }

    .branches-table tbody tr:hover {
        background: var(--nova-bg-soft, #f8fafc);
    }

    .branches-table tbody tr:last-child td {
        border-bottom: none;
    }

    .branches-row-num {
        color: var(--nova-text-muted, #94a3b8);
        font-weight: 600;
        font-size: 0.85rem;
    }

    .branches-name-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--nova-primary, #2563eb);
        text-decoration: none;
        transition: color 0.15s;
    }

    .branches-name-link:hover {
        color: var(--nova-primary-dark, #1d4ed8);
        text-decoration: underline;
    }

    .branches-customer-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--nova-text, #334155);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.15s;
    }

    .branches-customer-link:hover {
        color: var(--nova-primary, #2563eb);
    }

    .branches-cell-muted {
        color: var(--nova-text-muted, #64748b);
        font-size: 0.875rem;
    }

    /* ============================================
       Status Badge
       ============================================ */
    .branches-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .branches-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .branches-status-active {
        background: #dcfce7;
        color: #166534;
    }

    .branches-status-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    /* ============================================
       Actions
       ============================================ */
    .branches-actions {
        display: inline-flex;
        gap: 0.35rem;
        align-items: center;
    }

    .branches-actions .nova-btn {
        padding: 0.4rem 0.6rem;
    }

    /* ============================================
       Empty State
       ============================================ */
    .branches-empty {
        text-align: center;
        padding: 3.5rem 1.5rem;
    }

    .branches-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--nova-bg-soft, #f1f5f9);
        color: var(--nova-text-muted, #94a3b8);
        font-size: 2rem;
    }

    .branches-empty-title {
        margin: 0 0 0.35rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--nova-text, #1e293b);
    }

    .branches-empty-text {
        margin: 0 0 1.5rem;
        color: var(--nova-text-muted, #64748b);
        font-size: 0.9rem;
    }

    /* ============================================
       Pagination
       ============================================ */
    .branches-pagination-wrap {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--nova-border, #e2e8f0);
        display: flex;
        justify-content: center;
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 1024px) {
        .branch-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .branch-stats-grid {
            grid-template-columns: 1fr;
        }

        .branches-filter-select {
            min-width: 0;
            width: 100%;
        }

        .branches-filter-form,
        .branches-filter-wrap {
            width: 100%;
        }

        .branches-table thead th:nth-child(4),
        .branches-table thead th:nth-child(5),
        .branches-table tbody td:nth-child(4),
        .branches-table tbody td:nth-child(5) {
            display: none;
        }
    }
</style>
@endpush