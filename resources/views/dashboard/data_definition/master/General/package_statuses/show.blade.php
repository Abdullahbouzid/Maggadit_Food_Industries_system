@extends('dashboard.partials.layout')

@section('title', 'تفاصيل حالة الطرد')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل حالة الطرد
        </h2>
        <p class="nova-page-subtitle">عرض كامل لمعلومات {{ $packageState->status_name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('package-statuses.edit', $packageState) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('package-statuses.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات حالة الطرد
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">المعرف</th>
                    <td>{{ $packageState->id }}</td>
                </tr>
                <tr>
                    <th>اسم الحالة</th>
                    <td>
                        <div class="nova-flex nova-items-center gap-2">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <span>{{ $packageState->status_name }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>المنظمة</th>
                    <td>{{ $packageState->organization->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $packageState->created_at ? $packageState->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $packageState->updated_at ? $packageState->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>

            {{-- الحالة Properties --}}
            <div class="nova-card nova-mt-4">
                <div class="nova-card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cog"></i> خصائص الحالة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-row g-3">
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->resellable ? 'success' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->resellable ? 'check' : 'x' }} me-1"></i>
                                قابل لإعادة البيع: {{ $packageState->resellable ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->expired ? 'danger' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->expired ? 'check' : 'x' }} me-1"></i>
                                منتهية الصلاحية: {{ $packageState->expired ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->default ? 'accent' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->default ? 'check' : 'x' }} me-1"></i>
                                افتراضي: {{ $packageState->default ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->lpo_required ? 'warning' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->lpo_required ? 'check' : 'x' }} me-1"></i>
                                مطلوب LPO: {{ $packageState->lpo_required ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->is_related_to_cn_request ? 'primary' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->is_related_to_cn_request ? 'check' : 'x' }} me-1"></i>
                                مرتبط بـ CN: {{ $packageState->is_related_to_cn_request ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                        <div class="nova-col nova-col-md-4">
                            <span class="nova-badge {{ $packageState->is_price_editable ? 'info' : 'secondary' }}">
                                <i class="fas fa-{{ $packageState->is_price_editable ? 'check' : 'x' }} me-1"></i>
                                قابل لتحرير السعر: {{ $packageState->is_price_editable ? 'نعم' : 'لا' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('package-statuses.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> العودة إلى القائمة
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('package-statuses.edit', $packageState) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmحذف({{ $packageState->id }}, '{{ addslashes($packageState->status_name) }}')">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
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
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف <strong id="deleteName" class="nova-text-danger"></strong>؟<br>لا يمكن التراجع عن هذا الإجراء.</p>
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
        document.getElementById('deleteForm').action = '/package-statuses/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush
