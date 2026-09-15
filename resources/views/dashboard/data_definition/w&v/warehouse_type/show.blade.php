@extends('dashboard.partials.layout')

@section('title', 'Warehouse Type التفاصيل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Warehouse Type: {{ $warehouseType->code }}
        </h2>
        <p class="nova-page-subtitle">عرض warehouse classification details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-types.edit', $warehouseType) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('warehouse-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Warehouse Type التفاصيل</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $warehouseType->id }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><span class="nova-badge secondary">{{ $warehouseType->code }}</span></td>
                </tr>
                <tr>
                    <th>Name (EN)</th>
                    <td>{{ $warehouseType->name_en }}</td>
                </tr>
                <tr>
                    <th>Name (AR)</th>
                    <td>{{ $warehouseType->name_ar ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الوصف</th>
                    <td>{{ $warehouseType->description ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($warehouseType->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> نشط</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> غير نشط</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ترتيب الفرز</th>
                    <td>{{ $warehouseType->sort_order ?? 0 }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $warehouseType->created_at ? $warehouseType->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>الأخير Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $warehouseType->updated_at ? $warehouseType->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('warehouse-types.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> رجوع to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('warehouse-types.edit', $warehouseType) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmحذف({{ $warehouseType->id }}, '{{ addslashes($warehouseType->name_en) }}')">
                    <i class="fas fa-trash-alt"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Warehouse Count --}}
@if($warehouseType->warehouses()->exists())
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-warehouse me-1"></i> Associated Warehouses</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name (EN)</th>
                        <th>Organization</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warehouseType->warehouses as $wh)
                    <tr>
                        <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                        <td><code>{{ $wh->code }}</code></td>
                        <td>{{ $wh->name_en }}</td>
                        <td>{{ $wh->organization?->name ?? '—' }}</td>
                        <td>
                            @if($wh->is_active)
                                <span class="nova-badge success">نشط</span>
                            @else
                                <span class="nova-badge secondary">غير نشط</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete warehouse type <strong id="deleteName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">إلغاء</button>
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
        document.getElementById('deleteForm').action = '/warehouse-types/' + id;
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
