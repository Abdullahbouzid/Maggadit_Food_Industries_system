@extends('dashboard.partials.layout')

@section('title', 'Item Image Details')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Item Image Details
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Review the selected item image and metadata.</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-images.edit', $itemImage) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('item-images.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back to List
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Item Image Details</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-row g-4">
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-muted">Item</label>
                    <div class="nova-fw-semibold">{{ $itemImage->item->name_en ?? $itemImage->item->name_ar ?? '—' }}</div>
                </div>
            </div>
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-muted">Sort Order</label>
                    <div class="nova-fw-semibold">{{ $itemImage->sort_order }}</div>
                </div>
            </div>
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-muted">Image</label>
                    <div class="border rounded nova-py-3 nova-px-2 nova-text-center">
                        <img src="{{ $itemImage->url }}" alt="{{ $itemImage->alt_text }}" class="img-fluid" style="max-height: 360px;">
                    </div>
                </div>
            </div>
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-muted">Alt Text</label>
                    <div class="nova-fw-semibold">{{ $itemImage->alt_text ?? '—' }}</div>
                </div>
            </div>
            <div class="nova-col nova-col-12">
                <div class="nova-form-actions">
                    <a href="{{ route('item-images.edit', $itemImage) }}" class="nova-btn nova-btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('item-images.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     Delete Confirmation Modal
     ============================================================ --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete item image
                <strong id="deleteName" class="nova-text-danger"></strong>?<br>
                This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteModal').classList.remove('show')">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete</button>
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
        document.getElementById('deleteForm').action = '/item-images/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
