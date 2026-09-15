@extends('dashboard.partials.layout')

@section('title', 'دفعات الحركات')

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-boxes-stacked" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            دفعات الحركات
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة جميع حركات النقل والتوزيع والاسترجاع</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-movements.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إضافة حركة
        </a>
        <a href="{{ route('warehouse-movements.export', request()->query()) }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> تصدير إكسل
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

{{-- ===== Statistics ===== --}}
<div class="nova-row cols-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-list"></i>
        </div>
        <p class="nova-stat-label">إجمالي الحركات</p>
        <p class="nova-stat-value">{{ $total }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon success">
            <i class="fas fa-arrow-down"></i>
        </div>
        <p class="nova-stat-label">وارد (توزيع)</p>
        <p class="nova-stat-value">{{ $inCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <p class="nova-stat-label">نقل</p>
        <p class="nova-stat-value">{{ $transferCount }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="nova-stat-label">استرجاع منتهي الصلاحية</p>
        <p class="nova-stat-value">{{ $expiryCount }}</p>
    </div>
</div>

{{-- ===== فلتر Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('warehouse-movements.index') }}" class="filter-form">
            <div class="nova-form-group filter-item">
                <label class="nova-label">نوع الحركة</label>
                <select name="movement_type" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($movementTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('movement_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group filter-item">
                <label class="nova-label">المخزن المصدر</label>
                <select name="from_warehouse_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group filter-item">
                <label class="nova-label">المخزن الهدف</label>
                <select name="to_warehouse_id" class="nova-select">
                    <option value="">الكل</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nova-form-group filter-item">
                <label class="nova-label">منذ تاريخ</label>
                <input type="date" name="date_from" class="nova-input" value="{{ request('date_from') }}">
            </div>
            <div class="nova-form-group filter-item">
                <label class="nova-label">حتى تاريخ</label>
                <input type="date" name="date_to" class="nova-input" value="{{ request('date_to') }}">
            </div>
            <div class="nova-form-group filter-item filter-item-actions">
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <a href="{{ route('warehouse-movements.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-rotate-left"></i> إعادة ضبط
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ===== Movements Table ===== --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة الحركات (مجمعة حسب الدفعة)
            </h3>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نوع الحركة</th>
                    <th>رقم الدفعة</th>
                    <th>المخزن المصدر</th>
                    <th>المخزن الهدف</th>
                    <th>تاريخ الحركة</th>
                    <th>عدد المنتجات</th>
                    <th>إجمالي الكمية</th>
                    <th class="nova-text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupsPaginated as $group)
                <tr>
                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                    <td>
                        @php
                            $labels = ['in' => 'وارد', 'transfer' => 'نقل', 'expiry_return' => 'استرجاع منتهي'];
                            $colors = ['in' => 'success', 'transfer' => 'accent', 'expiry_return' => 'danger'];
                        @endphp
                        <span class="nova-badge {{ $colors[$group->movement_type] ?? 'secondary' }}">
                            {{ $labels[$group->movement_type] ?? $group->movement_type }}
                        </span>
                    </td>
                    <td><strong>{{ $group->batch_number }}</strong></td>
                    <td>{{ $group->from_warehouse ?? '—' }}</td>
                    <td>{{ $group->to_warehouse ?? '—' }}</td>
                    <td>{{ $group->movement_date ? $group->movement_date->format('Y-m-d') : '—' }}</td>
                    <td>{{ $group->items_count }}</td>
                    <td>{{ number_format($group->total_quantity, 3) }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('warehouse-movements.show', $group->first_movement->id) }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('warehouse-movements.edit', $group->first_movement->id) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف"
                                    onclick="confirmحذف({{ $group->first_movement->id }}, '{{ addslashes($group->batch_number) }}')">
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
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <h3 class="nova-empty-title">لا توجد حركات مخازن</h3>
                            <p class="nova-empty-text">أنشئ حركتك الأولى.</p>
                            <a href="{{ route('warehouse-movements.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus-circle"></i> إضافة حركة
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
                عرض {{ $groupsPaginated->firstItem() ?? 0 }} إلى {{ $groupsPaginated->lastItem() ?? 0 }} من أصل {{ $groupsPaginated->total() }} إدخال
            </div>
            <div class="nova-pagination">
                {{ $groupsPaginated->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
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
                هل أنت متأكد من رغبتك في حذف الحركة
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
    .filter-form {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1.2fr 1fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .filter-form .nova-form-group {
        margin-bottom: 0;
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

    @media (max-width: 1200px) {
        .filter-form {
            grid-template-columns: 1fr 1fr 1fr;
        }
        .filter-item-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.confirmحذف = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/warehouse-movements/' + id;
            document.getElementById('globalحذفModal').classList.add('show');
        };
    });
</script>
@endpush
