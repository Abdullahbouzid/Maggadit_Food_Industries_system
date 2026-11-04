@extends('dashboard.partials.layout')

@section('title', 'Price Lists')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-dollar-sign" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Price Lists
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create and manage price lists for customers</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('price-lists.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Add Price List
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Price Lists
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search price lists...">
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Created By</th>
                    <th class="nova-text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($priceLists as $priceList)
                <tr>
                    <td>{{ $priceList->id }}</td>
                    <td><span class="nova-fw-bold">{{ $priceList->name }}</span></td>
                    <td><small class="nova-text-muted">{{ Str::limit($priceList->description, 50) }}</small></td>
                    <td>
                        @if($priceList->status === 'active')
                            <span class="nova-badge success">Active</span>
                        @else
                            <span class="nova-badge secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <span class="nova-badge secondary">{{ $priceList->items_count ?? 0 }}</span>
                    </td>
                    <td><small class="nova-text-muted">{{ $priceList->creator?->name ?? '—' }}</small></td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('price-lists.show', $priceList) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('price-lists.edit', $priceList) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('price-lists.toggle-status', $priceList) }}" method="POST" class="nova-delete-form">
                                @csrf
                                <button type="submit" class="nova-icon-btn nova-btn-sm {{ $priceList->status === 'active' ? 'nova-text-warning' : 'nova-text-primary' }}" title="{{ $priceList->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $priceList->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete('{{ $priceList->id }}', '{{ addslashes($priceList->name) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="nova-empty-title">No price lists found</h3>
                            <p class="nova-empty-text">Create your first price list to get started.</p>
                            <a href="{{ route('price-lists.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Add Price List
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-pagination">
            {{ $priceLists->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete price list <strong id="deleteName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/price-lists/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush