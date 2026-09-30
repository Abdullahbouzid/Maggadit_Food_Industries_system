@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الجهاز')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل الجهاز
        </h2>
        <p class="nova-page-subtitle">عرض كامل لمعلومات الجهاز {{ $device->serial }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('devices.edit', $device) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('devices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات الجهاز
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">المعرف</th>
                    <td>{{ $device->id }}</td>
                </tr>
                <tr>
                    <th>الاسم (إنجليزي)</th>
                    <td>
                        <div class="nova-flex nova-items-center gap-2">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <span>{{ $device->name_en }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>الاسم (عربي)</th>
                    <td>{{ $device->name_ar }}</td>
                </tr>
                <tr>
                    <th>الشيفرة الخاصة</th>
                    <td><code>{{ $device->serial }}</code></td>
                </tr>
                <tr>
                    <th>المنظمة</th>
                    <td>{{ $device->organization?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>الموديل</th>
                    <td>{{ $device->model_no ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الماركة</th>
                    <td>{{ $device->brand ?: '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الشراء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $device->purchase_date ? $device->purchase_date->format('d M Y') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>انتهاء الضمان</th>
                    <td>
                        <i class="fas fa-calendar-check nova-text-muted me-1"></i>
                        {{ $device->warranty_expiry ? $device->warranty_expiry->format('d M Y') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($device->is_inactive)
                            <span class="nova-badge danger">
                                <i class="fas fa-pause me-1"></i> غير نشط
                            </span>
                        @else
                            <span class="nova-badge success">
                                <i class="fas fa-play me-1"></i> نشط
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>الملاحظات</th>
                    <td>{{ $device->remark ?: '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $device->created_at ? $device->created_at->format('d M Y, h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $device->updated_at ? $device->updated_at->format('d M Y, h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('devices.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> العودة إلى القائمة
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('devices.edit', $device) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $device->id }}, '{{ addslashes($device->serial) }}')">
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
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/devices/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush
