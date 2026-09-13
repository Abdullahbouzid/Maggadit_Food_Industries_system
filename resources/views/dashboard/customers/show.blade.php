@extends('dashboard.partials.layout')

@section('title', 'تفاصيل العميل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل العميل
        </h2>
        <p class="nova-page-subtitle">عرض كامل لمعلومات {{ $customer->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('customers.edit', $customer) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
            <i class="fas fa-file-alt"></i> بيانات العميل
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-row nova-gap-4" style="align-items: start;">
            {{-- Avatar --}}
            <div class="nova-col nova-col-md-4" style="text-align: center;">
                @if($customer->avatar)
                    <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="nova-avatar nova-avatar-xl" style="width: 160px; height: 160px; object-fit: cover; border: 4px solid var(--nova-surface-2); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                @else
                    <div class="nova-stat-icon primary" style="width: 160px; height: 160px; font-size: 3rem; margin: 0 auto; border: 4px solid var(--nova-surface-2); box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 50%;">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="nova-col nova-col-md-8">
                <div class="nova-table-scroll">
                    <table class="nova-table">
                        <tr>
                            <th style="width: 30%;">الاسم</th>
                            <td class="nova-fw-semibold">{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <th>اسم المستخدم</th>
                            <td>{{ $customer->username }}</td>
                        </tr>
                        <tr>
                            <th>البريد الإلكتروني</th>
                            <td>
                                <i class="fas fa-envelope nova-text-muted me-1"></i>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>المنظمة النشطة</th>
                            <td>
                                @if($customer->activeOrganization)
                                    <span class="nova-badge success">{{ $customer->activeOrganization->name }}</span>
                                @else
                                    <span class="nova-text-muted fst-italic">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>جميع المنظمات</th>
                            <td>
                                @if($customer->organizations->count())
                                    @foreach($customer->organizations as $org)
                                        <span class="nova-badge secondary">{{ $org->name }}</span>
                                    @endforeach
                                @else
                                    <span class="nova-text-muted fst-italic">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>قائمة الأسعار</th>
                            <td>{{ $customer->priceList?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنشاء</th>
                            <td>
                                <i class="fas fa-calendar nova-text-muted me-1"></i>
                                {{ $customer->created_at ? $customer->created_at->format('d M Y, h:i A') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>آخر تحديث</th>
                            <td>
                                <i class="fas fa-history nova-text-muted me-1"></i>
                                {{ $customer->updated_at ? $customer->updated_at->format('d M Y, h:i A') : '—' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> العودة إلى القائمة
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
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
            <p class="nova-delete-modal-text">هل أنت متأكد أنك تريد حذف العميل <strong id="deleteName" class="nova-text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
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
        document.getElementById('deleteForm').action = '/customers/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush
