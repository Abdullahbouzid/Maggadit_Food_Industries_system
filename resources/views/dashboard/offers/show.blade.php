@extends('dashboard.partials.layout')

@section('title', 'عرض العرض')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-tag" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ $offer->{{ __('messages.app.page_') }} ?? 'عرض' }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">
            <i class="fas fa-cube"></i> عرض على: {{ $offer->item?->name_ar ?: $offer->item?->name_en }}
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('offers.edit', $offer) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('offers.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-list"></i> القائمة
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
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-info-circle"></i> تفاصيل العرض
            </h5>
            @if($offer->is_active && $offer->start_date <= now() && $offer->end_date >= now())
                <span class="nova-badge success">نشط</span>
            @else
                <span class="nova-badge secondary">غير نشط</span>
            @endif
        </div>
    </div>
    <div class="nova-card-body">
        <div class="nova-row g-4">
            <div class="nova-col nova-col-lg-8 mx-auto">
                <table class="nova-table nova-table-borderless">
                    <tr>
                        <th class="nova-col nova-col-md-4">المنتج</th>
                        <td>
                            {{ $offer->item?->name_ar ?: $offer->item?->name_en }}
                            @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                                <span class="nova-badge info ms-1">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</span>
                            @endif
                            @if($offer->itemPack)
                                <br><small class="nova-text-muted">العبوة: {{ $offer->itemPack->packCapacity?->uom->name_ar ?? $offer->itemPack->packCapacity?->uom->name_en ?? '' }}</small>
                            @endif
                        </td>
                    </tr>
                    @if($offer->{{ __('messages.app.page_') }})
                        <tr>
                            <th>العنوان</th>
                            <td>{{ $offer->{{ __('messages.app.page_') }} }}</td>
                        </tr>
                    @endif
                    @if($offer->description)
                        <tr>
                            <th>الوصف</th>
                            <td>{{ $offer->description }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>السعر القديم</th>
                        <td><s class="nova-text-muted">{{ number_format($offer->old_price, 2) }} ر.س</s></td>
                    </tr>
                    <tr>
                        <th>السعر الجديد</th>
                        <td class="nova-fw-bold nova-text-success">{{ number_format($offer->new_price, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>نسبة الخصم</th>
                        <td><span class="nova-badge danger">{{ $offer->formatted_discount ?? number_format($offer->discount_percentage, 0) . '%' }}</span></td>
                    </tr>
                    <tr>
                        <th>تاريخ البداية</th>
                        <td>{{ $offer->start_date->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ النهاية</th>
                        <td>{{ $offer->end_date->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>

                @if($offer->image)
                    <div class="text-center mt-4">
                        <img src="{{ asset('storage/' . $offer->image) }}" alt="صورة العرض" class="nova-object-fit-cover" style="max-height: 300px; max-width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    </div>
                @elseif($offer->item && $offer->item->main_image_url)
                    <div class="text-center mt-4">
                        <img src="{{ $offer->item->main_image_url }}" alt="صورة المنتج" class="nova-object-fit-cover" style="max-height: 300px; max-width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Additional Actions --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-body">
        <div class="nova-flex nova-gap-2">
            <a href="{{ route('offers.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> {{ __('messages.app.page__5') }}
            </a>
            <a href="{{ route('offers.edit', $offer) }}" class="nova-btn nova-btn-warning">
                <i class="fas fa-edit"></i> {{ __('messages.app.page__6') }}
            </a>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmDelete({{ $offer->id }}, '{{ addslashes($offer->{{ __('messages.app.page_') }} ?? 'Offer') }}')">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteOfferModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__1') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the offer <strong id="deleteOfferName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteOfferModal').classList.remove('show')">{{ __('messages.app.page__3') }}</button>
                <form id="deleteOfferForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__4') }}</button>
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
        document.getElementById('deleteOfferName').innerText = name;
        document.getElementById('deleteOfferForm').action = '/offers/' + id;
        document.getElementById('deleteOfferModal').classList.add('show');
    };
});
</script>
@endpush
