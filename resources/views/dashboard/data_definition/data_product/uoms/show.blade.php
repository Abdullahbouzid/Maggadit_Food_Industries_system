@extends('dashboard.partials.layout')

@section('title', 'UOM التفاصيل: ' . $uom->name_en)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            UOM التفاصيل
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرضing information for <strong>{{ $uom->name_en }} ({{ $uom->name_ar }})</strong></p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('uoms.edit', $uom) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع to List
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> UOM التفاصيل</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $uom->id }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><span class="nova-badge secondary">{{ $uom->code }}</span></td>
                </tr>
                <tr>
                    <th>Symbol</th>
                    <td><code>{{ $uom->uom_symbol ?? '—' }}</code></td>
                </tr>
                <tr>
                    <th>Name (EN)</th>
                    <td><strong class="nova-fw-semibold">{{ $uom->name_en }}</strong></td>
                </tr>
                <tr>
                    <th>Name (AR)</th>
                    <td>{{ $uom->name_ar ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الوصف</th>
                    <td>{{ $uom->description ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الصورة</th>
                    <td>
                        @if($uom->image)
                            <img src="{{ asset('uploads/' . $uom->image) }}" alt="{{ $uom->name_en }}" class="nova-rounded" style="max-height: 150px; max-width: 150px; object-fit: cover;">
                        @else
                            <span class="nova-text-muted">— لا الصورة —</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($uom->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> نشط</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> غير نشط</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ترتيب الفرز</th>
                    <td>{{ $uom->sort_order ?? 0 }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $uom->created_at ? $uom->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>الأخير Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $uom->updated_at ? $uom->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('uoms.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> رجوع to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('uoms.edit', $uom) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmحذف({{ $uom->id }}, '{{ addslashes($uom->name_en) }}')">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteUomModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete UOM <strong id="deleteUomName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteUomModal').classList.remove('show')">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">الحذف النهائي</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    window.confirmحذف = function(id, name) {
        document.getElementById('deleteUomName').innerText = name;
        document.getElementById('deleteForm').action = '/uoms/' + id;
        document.getElementById('deleteUomModal').classList.add('show');
    };
});
</script>
@endpush
@endsection
