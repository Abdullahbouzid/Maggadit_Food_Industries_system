@extends('dashboard.partials.layout')

@section('title', 'Warehouse Type Details')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Warehouse Type: {{ $warehouseType->code }}
        </h2>
        <p class="nova-page-subtitle">View warehouse classification details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('warehouse-types.edit', $warehouseType) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('warehouse-types.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> Warehouse Type Details</h5>
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
                    <th>Description</th>
                    <td>{{ $warehouseType->description ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($warehouseType->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> Active</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Sort Order</th>
                    <td>{{ $warehouseType->sort_order ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $warehouseType->created_at ? $warehouseType?->created_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $warehouseType->updated_at ? $warehouseType?->updated_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('warehouse-types.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> Back to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('warehouse-types.edit', $warehouseType) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $warehouseType->id }}, '{{ addslashes($warehouseType->name_en) }}')">
                    <i class="fas fa-trash-alt"></i> Delete
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
                        <th>Status</th>
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
                                <span class="nova-badge success">Active</span>
                            @else
                                <span class="nova-badge secondary">Inactive</span>
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

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete warehouse type <strong id="deleteName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">Cancel</button>
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
        document.getElementById('deleteForm').action = '/warehouse-types/' + id;
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
