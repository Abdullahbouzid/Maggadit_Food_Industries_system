@extends('dashboard.partials.layout')

@section('title', 'Offers Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-tag" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Offers Management
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Create and manage offers on products with specific time periods</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('offers.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Add Offer
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
                Offers List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search offers...">
        </div>
        <div class="nova-toolbar-actions">
            <button type="button" class="nova-btn nova-btn-secondary nova-btn-sm">Export</button>
            <button type="button" class="nova-btn nova-btn-outline nova-btn-sm">Filter</button>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Old Price</th>
                    <th>New Price</th>
                    <th>Discount</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
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
                            <img src="{{ $offer->item->main_image_url }}" alt="Product image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
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
                            <span class="nova-badge success">Active</span>
                        @elseif(!$offer->is_active)
                            <span class="nova-badge secondary">Disabled</span>
                        @else
                            <span class="nova-badge warning text-dark">Expired</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('offers.show', $offer) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('offers.edit', $offer) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <x-table-actions
                                :view-url="null"
                                :edit-url="null"
                                :delete-url="route('offers.destroy', $offer)"
                                :delete-name="$offer->title ?? 'Offer'"
                                delete-modal="deleteOfferModal" />
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
                            <h3 class="nova-empty-title">No offers</h3>
                            <a href="{{ route('offers.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Add Offer
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

<x-delete-modal modal-id="deleteOfferModal" />