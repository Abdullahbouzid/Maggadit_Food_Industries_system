@extends('dashboard.partials.layout')

@section('title', 'لاtification التفاصيل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-eye" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            لاtification التفاصيل
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرض notification details and content</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.edit', $notification) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع to List
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-row g-4">
            <div class="nova-col nova-col-lg-8">
                <div class="nova-form-card">
                    <div class="nova-form-card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-bell"></i> لاtification التفاصيل
                        </h5>
                    </div>
                    <div class="nova-form-card-body">
                        <div class="nova-row g-3">
                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-heading me-1"></i> Subject (EN)
                                    </label>
                                    <p class="fw-semibold fs-5 mb-0">{{ $notification->subject_en }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-heading me-1"></i> العنوان (AR)
                                    </label>
                                    <p class="fw-semibold fs-5 mb-0">{{ $notification->subject_ar }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-align-left me-1"></i> Body (EN)
                                    </label>
                                    <p class="fw-semibold fs-6 mb-0">{{ $notification->body_en }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-align-left me-1"></i> المحتوى (AR)
                                    </label>
                                    <p class="fw-semibold fs-6 mb-0">{{ $notification->body_ar }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-4">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-bell me-1"></i> Channel
                                    </label>
                                    <p><span class="nova-badge info">{{ strtoupper($notification->channel) }}</span></p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-4">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-users-cog me-1"></i> Group
                                    </label>
                                    <p>{{ $notification->group ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-4">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-info-circle me-1"></i> الحالة
                                    </label>
                                    <p>
                                        @if($notification->is_sent)
                                            <span class="nova-badge success">Sent</span>
                                        @else
                                            <span class="nova-badge warning">معلق</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-users me-1"></i> Customers
                                    </label>
                                    <p>
@php
    $rawCustomers = $notification->customers;
    if (is_array($rawCustomers)) {
        $customers = $rawCustomers;
    } elseif (is_string($rawCustomers)) {
        $customers = json_decode($rawCustomers, true);
    } else {
        $customers = [];
    }
    if (!is_array($customers)) $customers = [];
@endphp
                                        @if($customers)
                                            {{ implode(', ', $customers) }}
                                        @else
                                            <span class="nova-text-muted">— الكل Customers —</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-calendar-alt me-1"></i> Scheduled At
                                    </label>
                                    <p>{{ $notification->scheduled_at ? $notification->scheduled_at->format('F d, Y \a\t h:i A') : '—' }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-clock me-1"></i> تاريخ الإنشاء
                                    </label>
                                    <p>{{ $notification->created_at->format('F d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>

                            <div class="nova-col nova-col-md-6">
                                <div class="nova-form-group">
                                    <label class="nova-label text-uppercase small fw-semibold">
                                        <i class="fas fa-history me-1"></i> الأخير Updated
                                    </label>
                                    <p>{{ $notification->updated_at->format('F d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nova-col nova-col-lg-4">
                <div class="nova-form-card">
                    <div class="nova-form-card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-image"></i> Media
                        </h5>
                    </div>
                    <div class="nova-form-card-body text-center">
                        @if($notification->image)
                            <img src="{{ asset('uploads/' . $notification->image) }}" alt="الصورة" class="nova-object-fit-cover" style="max-height: 200px; width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                        @endif
                        @if($notification->video)
                            <video controls class="nova-object-fit-cover w-100" style="border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                                <source src="{{ asset('uploads/' . $notification->video) }}" type="video/mp4">
                            </video>
                        @endif
                        @if(!$notification->image && !$notification->video)
                            <div class="nova-form-card" style="border: 2px dashed var(--nova-border); border-radius: 10px;">
                                <div class="nova-form-card-body">
                                    <i class="fas fa-image" style="width: 60px; height: 60px;" class="nova-text-muted"></i>
                                    <p class="nova-text-muted mt-2">لا media attached</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Additional إجراءات --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-body">
        <div class="nova-flex nova-gap-2">
            <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> رجوع to لاtifications
            </a>
            <a href="{{ route('notifications.edit', $notification) }}" class="nova-btn nova-btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmحذف({{ $notification->id }}, '{{ addslashes($notification->subject_en ?? 'لاtification') }}')">
                <i class="fas fa-trash-alt"></i> حذف
            </button>
        </div>
    </div>
</div>

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteلاtificationModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the notification <strong id="deleteلاtificationName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteلاtificationModal').classList.remove('show')">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف Forever</button>
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
        document.getElementById('deleteلاtificationName').innerText = name;
        document.getElementById('deleteForm').action = '/notifications/' + id;
        document.getElementById('deleteلاtificationModal').classList.add('show');
    };
});
</script>
@endpush
