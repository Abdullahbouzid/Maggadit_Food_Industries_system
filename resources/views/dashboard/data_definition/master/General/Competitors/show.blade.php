@extends('dashboard.partials.layout')

@section('title', 'Competitor التفاصيل')

@section('content')
@php
    $typeLabels = ['direct' => 'Direct', 'indirect' => 'Indirect', 'potential' => 'Potential'];
@endphp

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Competitor: {{ $competitor->name }}
        </h2>
        <p class="nova-page-subtitle">عرض competitor details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('competitors.edit', $competitor) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('competitors.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold nova-mb-0"><i class="fas fa-file-alt nova-me-1"></i> Competitor التفاصيل</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $competitor->id }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $competitor->name }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><span class="nova-badge secondary">{{ $competitor->code }}</span></td>
                </tr>
                <tr>
                    <th>Organization</th>
                    <td>{{ $competitor->organization?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Competitor Type</th>
                    <td>
                        @if($competitor->competitor_type && isset($typeLabels[$competitor->competitor_type]))
                            <span class="nova-badge secondary">{{ $typeLabels[$competitor->competitor_type] }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>GLN</th>
                    <td>{{ $competitor->gln ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Contact Person</th>
                    <td>{{ $competitor->contact_person ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $competitor->phone ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $competitor->email ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $competitor->address ?: '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted nova-me-1"></i>
                        {{ $competitor->created_at ? $competitor->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>الأخير Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted nova-me-1"></i>
                        {{ $competitor->updated_at ? $competitor->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('competitors.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> رجوع to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('competitors.edit', $competitor) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmحذف({{ $competitor->id }}, '{{ addslashes($competitor->name) }}')">
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
            <p class="nova-delete-modal-text">Are you sure you want to delete competitor <strong id="deleteName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
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
        document.getElementById('deleteForm').action = '/competitors/' + id;
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
