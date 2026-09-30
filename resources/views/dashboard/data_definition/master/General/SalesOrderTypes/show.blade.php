@extends('dashboard.partials.layout')

@section('title', 'تفاصيل نوع طلب البيع')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل نوع طلب البيع
        </h2>
        <p class="nova-page-subtitle">عرض كامل لمعلومات {{ $salesOrderType->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('sales-order-types.edit', $salesOrderType) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> بيانات نوع طلب البيع
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">المعرف</th>
                    <td>{{ $salesOrderType->id }}</td>
                </tr>
                <tr>
                    <th>اسم نوع طلب البيع</th>
                    <td>
                        <div class="nova-flex nova-items-center gap-2">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <span>{{ $salesOrderType->name }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>الشيفرة</th>
                    <td>{{ $salesOrderType->code }}</td>
                </tr>
                <tr>
                    <th>نشط</th>
                    <td>
                        <span class="nova-badge {{ $salesOrderType->active ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $salesOrderType->active ? 'check' : 'x' }} me-1"></i>
                            {{ $salesOrderType->active ? 'نعم' : 'لا' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>افتراضي</th>
                    <td>
                        <span class="nova-badge {{ $salesOrderType->default ? 'accent' : 'secondary' }}">
                            <i class="fas fa-{{ $salesOrderType->default ? 'star' : 'x' }} me-1"></i>
                            {{ $salesOrderType->default ? 'نعم' : 'لا' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $salesOrderType->created_at ? $salesOrderType->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $salesOrderType->updated_at ? $salesOrderType->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('sales-order-types.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> العودة إلى القائمة
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('sales-order-types.edit', $salesOrderType) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $salesOrderType->id }}, '{{ addslashes($salesOrderType->name) }}')">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
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
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف <strong id="deleteName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
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
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/sales-order-types/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush
