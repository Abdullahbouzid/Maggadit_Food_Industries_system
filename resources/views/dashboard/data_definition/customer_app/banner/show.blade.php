@extends('dashboard.partials.layout')

@section('title', 'Banner التفاصيل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-image" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Banner التفاصيل
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرض full information about this banner</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('banners.edit', $banner) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
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
    <div class="nova-card-body">
        <div class="nova-row g-4">

            {{-- الصورة --}}
            <div class="nova-col nova-col-md-4 text-center">
                <div class="nova-form-group">
                    @if($banner->image)
                        <img src="{{ asset('uploads/' . $banner->image) }}" alt="{{ $banner->title }}" class="nova-object-fit-cover" style="max-height: 250px; width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    @else
                        <div class="nova-form-card" style="border: 2px dashed var(--nova-border); border-radius: 10px;">
                            <div class="nova-form-card-body">
                                <i class="fas fa-image" style="width: 64px; height: 64px;" class="text-muted"></i>
                                <p class="nova-text-muted mt-2">لا image uploaded</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- التفاصيل --}}
            <div class="nova-col nova-col-md-8">
                <div class="nova-row g-3">

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-heading me-1"></i> Title
                            </label>
                            <p class="fw-semibold fs-5 mb-0">{{ $banner->title ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-tag me-1"></i> Type
                            </label>
                            <p class="fw-semibold fs-5 mb-0"><span class="nova-badge info">{{ ucfirst($banner->type) }}</span></p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-link me-1"></i> URL
                            </label>
                            <p class="fw-semibold fs-5 mb-0">
                                @if($banner->url)
                                    <a href="{{ $banner->url }}" target="_blank" class="nova-text-primary">{{ $banner->url }}</a>
                                @else
                                    <span class="nova-text-muted">—</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-sort-numeric-down me-1"></i> Order
                            </label>
                            <p class="fw-semibold fs-5 mb-0">{{ $banner->order }}</p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-eye me-1"></i> Show in Customer App
                            </label>
                            <p class="fw-semibold fs-5 mb-0">
                                @if($banner->show_in_customer_app)
                                    <span class="nova-badge success">Visible</span>
                                @else
                                    <span class="nova-badge secondary">Hidden</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-clock me-1"></i> تاريخ الإنشاء
                            </label>
                            <p class="fw-semibold fs-5 mb-0">{{ $banner->created_at->format('Y-m-d h:i A') }}</p>
                        </div>
                    </div>

                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label text-uppercase small fw-semibold">
                                <i class="fas fa-history me-1"></i> الأخير Updated
                            </label>
                            <p class="fw-semibold fs-5 mb-0">{{ $banner->updated_at->diffForHumans() }}</p>
                        </div>
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
            <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> رجوع to Banners
            </a>
            <a href="{{ route('banners.edit', $banner) }}" class="nova-btn nova-btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmحذف({{ $banner->id }}, '{{ addslashes($banner->title ?? 'Banner') }}')">
                <i class="fas fa-trash-alt"></i> حذف
            </button>
        </div>
    </div>
</div>

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteBannerModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the banner <strong id="deleteBannerName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteBannerModal').classList.remove('show')">إلغاء</button>
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
        document.getElementById('deleteBannerName').innerText = name;
        document.getElementById('deleteForm').action = '/banners/' + id;
        document.getElementById('deleteBannerModal').classList.add('show');
    };
});
</script>
@endpush
