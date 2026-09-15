@extends('dashboard.partials.layout')

@section('title', 'Offers')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-tag" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('offers.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.app.page__14') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- ===== Filter Section ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('offers.index') }}" id="filterForm">
            <div class="nova-row cols-3" style="align-items: end; gap: 0.75rem;">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.search') }}</label>
                    <div class="nova-input-group" style="display: flex; gap: 0;">
                        <span class="nova-input-group-text" style="display: flex; align-items: center; padding: 0 0.875rem; background: var(--nova-surface-2); border: 1.5px solid var(--nova-border); border-end: none; border-radius: 10px 0 0 10px; color: var(--nova-text-4);">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.placeholder_offers') }}" value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-start: none;">
                    </div>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                    <select name="per_page" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                    </select>
                </div>
                <div class="nova-flex nova-gap-2" style="height: fit-content;">
                    <button type="submit" class="nova-btn nova-btn-primary">{{ __('messages.filter.apply') }}</button>
                    @if(request('search') || request('per_page'))
                        <a href="{{ route('offers.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.app.page__2') }}
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="{{ __('messages.filter.placeholder_offers') }}">
        </div>
        <div class="nova-toolbar-actions">
            <button type="button" class="nova-btn nova-btn-secondary nova-btn-sm">{{ __('messages.app.page__10') }}</button>
            <button type="button" class="nova-btn nova-btn-outline nova-btn-sm">{{ __('messages.app.page__11') }}</button>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__16') }}</th>
                    <th>{{ __('messages.app.page__17') }}</th>
                    <th>{{ __('messages.app.page__18') }}</th>
                    <th>{{ __('messages.app.page__19') }}</th>
                    <th>{{ __('messages.app.page__20') }}</th>
                    <th>{{ __('messages.app.page__21') }}</th>
                    <th>{{ __('messages.app.page__22') }}</th>
                    <th>{{ __('messages.app.page__23') }}</th>
                    <th style="text-align: right;">{{ __('messages.app.page__24') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offers as $offer)
                <tr class="{{ $offer->is_active && $offer->start_date <= now() && $offer->end_date >= now() ? '' : 'table-secondary' }}">
                    <td>{{ $offer->id }}</td>
                    <td>
                        @if($offer->image)
                            <img src="{{ asset('storage/' . $offer->image) }}" alt="Offer image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        @elseif($offer->item && $offer->item->main_image_url)
                            <img src="{{ $offer->item->main_image_url }}" alt="{{ __('messages.app.page__17') }} image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <span class="text-muted">🖼️</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $offer->item?->name_ar ?: $offer->item?->name_en }}</strong>
                        @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                            <br><small class="nova-text-muted">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</small>
                        @endif
                        @if($offer->itemPack)
                            <br><small class="nova-text-muted">Pack: {{ $offer->itemPack->packCapacity?->uom->name_ar ?? $offer->itemPack->packCapacity?->uom->name_en ?? '' }}</small>
                        @endif
                    </td>
                    <td>
                        @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                            <span class="nova-badge secondary">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</span>
                        @else
                            <span class="nova-text-muted">—</span>
                        @endif
                    </td>
                    <td><s class="nova-text-muted">{{ number_format($offer->old_price, 2) }}</s></td>
                    <td class="nova-fw-bold nova-text-success">{{ number_format($offer->new_price, 2) }}</td>
                    <td>
                        <span class="nova-badge danger">{{ $offer->formatted_discount ?? number_format($offer->discount_percentage, 0) . '%' }}</span>
                    </td>
                    <td>
                        <small class="nova-text-muted">
                            {{ $offer->start_date->format('Y-m-d') }}<br>
                            to {{ $offer->end_date->format('Y-m-d') }}
                        </small>
                    </td>
                    <td>
                        @if($offer->is_active && $offer->start_date <= now() && $offer->end_date >= now())
                            <span class="nova-badge success">{{ __('messages.app.page__7') }}</span>
                        @elseif(!$offer->is_active)
                            <span class="nova-badge secondary">{{ __('messages.app.page__8') }}</span>
                        @else
                            <span class="nova-badge warning text-dark">{{ __('messages.app.page__9') }}</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('offers.show', $offer) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('offers.edit', $offer) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="Delete" onclick="confirmDelete({{ $offer->id }}, '{{ addslashes($offer->{{ __('messages.app.page_') }} ?? 'Offer') }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">{{ __('messages.app.page__3') }}</h3>
                            <a href="{{ route('offers.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.app.page__14') }}
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
                Showing {{ $offers->firstItem() ?? 0 }} to {{ $offers->lastItem() ?? 0 }} of {{ $offers->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $offers->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteOfferModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the offer <strong id="deleteOfferName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteOfferModal').classList.remove('show')">{{ __('messages.app.page__12') }}</button>
                <form id="deleteOfferForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__13') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteOfferName').innerText = name;
        document.getElementById('deleteOfferForm').action = '/offers/' + id;
        document.getElementById('deleteOfferModal').classList.add('show');
    };
});
</script>
@endpush