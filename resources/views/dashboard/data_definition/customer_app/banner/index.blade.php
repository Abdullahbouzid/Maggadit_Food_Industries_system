@extends('dashboard.partials.layout')

@section('title', 'Banners Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-image" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Banners
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage banners, images, and links for customer app</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('banners.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Add New
        </a>
        <a href="{{ route('banners.export', request()->query()) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-download"></i> Export Table
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-row cols-2 cols-lg-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-image"></i>
        </div>
        <p class="nova-stat-label">Total Banners</p>
        <p class="nova-stat-value">{{ $totalBanners }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-eye"></i>
        </div>
        <p class="nova-stat-label">Shown in App</p>
        <p class="nova-stat-value">{{ $shownInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-eye-slash"></i>
        </div>
        <p class="nova-stat-label">Hidden in App</p>
        <p class="nova-stat-value">{{ $hiddenInApp }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-layers"></i>
        </div>
        <p class="nova-stat-label">Distinct Types</p>
        <p class="nova-stat-value">{{ $typesCount }}</p>
    </div>
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form id="filterForm" method="GET" action="{{ route('banners.index') }}" class="nova-flex nova-flex-wrap nova-gap-3" style="align-items: end;">
            <div class="nova-form-group" style="flex: 1 1 16rem; margin-bottom: 0;">
                <label class="nova-label">Search (Title / Type)</label>
                <div class="nova-input-group">
                    <span class="nova-input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="nova-input" placeholder="Search..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">Type</label>
                <select name="type" class="nova-select">
                    <option value="">All Types</option>
                    <option value="home" {{ request('type') == 'home' ? 'selected' : '' }}>Home</option>
                    <option value="category" {{ request('type') == 'category' ? 'selected' : '' }}>Category</option>
                    <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Product</option>
                    <option value="promo" {{ request('type') == 'promo' ? 'selected' : '' }}>Promo</option>
                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div class="nova-form-group" style="flex: 1 1 11rem; margin-bottom: 0;">
                <label class="nova-label">Records per page</label>
                <select name="per_page" class="nova-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="nova-flex nova-gap-2" style="flex: 1 1 12rem; align-items: end;">
                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">Filter</button>
                @if(request('search') || request('type') || request('per_page'))
                    <a href="{{ route('banners.index') }}" class="nova-btn nova-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Banners List
            </h3>
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('banners.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Banner
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>URL</th>
                    <th>Show in App</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bannersPaginated as $banner)
                <tr>
                    <td>{{ $loop->iteration + ($bannersPaginated->currentPage() - 1) * $bannersPaginated->perPage() }}</td>
                    <td>
                        @if($banner->image)
                            <img src="{{ asset('uploads/' . $banner->image) }}" alt="{{ $banner->title }}" width="60" height="60" class="nova-object-fit-cover" style="border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                        @else
                            <span class="nova-text-muted" style="font-style: italic;">— No Image —</span>
                        @endif
                    </td>
                    <td>{{ $banner->title ?? '—' }}</td>
                    <td><span class="nova-badge primary">{{ ucfirst($banner->type) }}</span></td>
                    <td>
                        @if($banner->url)
                            <a href="{{ $banner->url }}" target="_blank" class="nova-text-primary" style="display: inline-block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $banner->url }}</a>
                        @else
                            <span class="nova-text-muted" style="font-style: italic;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($banner->show_in_customer_app)
                            <span class="nova-badge success">Visible</span>
                        @else
                            <span class="nova-badge secondary">Hidden</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('banners.show', $banner) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('banners.edit', $banner) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="Delete" onclick="confirmDelete({{ $banner->id }}, '{{ addslashes($banner->title ?? 'Banner') }}')">
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
                                <i class="fas fa-image"></i>
                            </div>
                            <h3 class="nova-empty-title">No banners found</h3>
                            <p class="nova-empty-text">You can add a new banner using the add button.</p>
                            <a href="{{ route('banners.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Add Banner
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                Showing {{ $bannersPaginated->firstItem() ?? 0 }} to {{ $bannersPaginated->lastItem() ?? 0 }} of {{ $bannersPaginated->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $bannersPaginated->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteBannerModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the banner <strong id="deleteBannerName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteBannerModal').classList.remove('show')">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    window.confirmDelete = function(id, name) {
        document.getElementById('deleteBannerName').innerText = name;
        document.getElementById('deleteForm').action = '/banners/' + id;
        document.getElementById('deleteBannerModal').classList.add('show');
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
