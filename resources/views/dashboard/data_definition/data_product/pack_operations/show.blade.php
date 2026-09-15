@extends('dashboard.partials.layout')

@section('title', 'Pack Operation التفاصيل: ' . $packOperation->name_en)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Pack Operation: {{ $packOperation->code }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرضing information for {{ $packOperation->name_en }} ({{ $packOperation->name_ar }})</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-operations.edit', $packOperation) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('pack-operations.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع to List
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Pack Operation التفاصيل</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $packOperation->id }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><code class="table-code">{{ $packOperation->code }}</code></td>
                </tr>
                <tr>
                    <th>الاسم بالإنجليزي</th>
                    <td>{{ $packOperation->name_en }}</td>
                </tr>
                <tr>
                    <th>الاسم بالعربي</th>
                    <td>{{ $packOperation->name_ar }}</td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($packOperation->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> نشط</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> غير نشط</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ترتيب الفرز</th>
                    <td>{{ $packOperation->sort_order ?? '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $packOperation->created_at ? $packOperation->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>الأخير Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $packOperation->updated_at ? $packOperation->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
        <div class="nova-form-actions nova-mt-4">
            <a href="{{ route('pack-operations.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> رجوع to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('pack-operations.edit', $packOperation) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmحذف({{ $packOperation->id }}, '{{ addslashes($packOperation->name_en) }}')">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     حذف تأكيدation Modal
     ============================================================ --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete pack operation
                <strong id="deleteName" class="nova-text-danger"></strong>?<br>
                لا يمكن التراجع عن هذا الإجراء.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteModal').classList.remove('show')">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmحذف = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/pack-operations/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorالكل('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
