@extends('dashboard.partials.layout')

@section('title', 'Item Type Details: ' . $itemType->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Item Type Details
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Viewing information for <strong>{{ $itemType->name }}</strong></p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('item-types.edit', $itemType) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to List
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Item Type Details</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $itemType->id }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><span class="nova-badge secondary">{{ $itemType->code }}</span></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><strong class="nova-fw-semibold">{{ $itemType->name }}</strong></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $itemType->description ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Image</th>
                    <td>
                        @if($itemType->image)
                            <img src="{{ asset('uploads/' . $itemType->image) }}" alt="{{ $itemType->name }}" class="nova-rounded" style="max-height: 150px; max-width: 150px; object-fit: cover;">
                        @else
                            <span class="nova-text-muted">— No Image —</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($itemType->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> Active</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Sort Order</th>
                    <td>{{ $itemType->sort_order ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $itemType->created_at ? $itemType?->created_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $itemType->updated_at ? $itemType?->updated_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('item-types.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> Back to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('item-types.edit', $itemType) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $itemType->id }}, '{{ addslashes($itemType->name) }}')">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteItemTypeModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete item type <strong id="deleteItemTypeName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteItemTypeModal').classList.remove('show')">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Permanently</button>
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
        document.getElementById('deleteItemTypeName').innerText = name;
        document.getElementById('deleteForm').action = '/item-types/' + id;
        document.getElementById('deleteItemTypeModal').classList.add('show');
    };
});
</script>
@endpush
