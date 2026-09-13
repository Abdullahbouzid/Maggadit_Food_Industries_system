@extends('dashboard.partials.layout')

@section('title', 'تعديل إصدار - ' . $priceList->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل إصدار - {{ $priceList->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">تاريخ السريان: {{ $version->effective_date->format('Y-m-d') }}</p>
        <div class="nova-flex nova-items-center nova-gap-2 nova-mt-2">
            @if($version->is_active)
                <span class="nova-badge success">نشط حالياً</span>
            @else
                <span class="nova-badge secondary">سابق</span>
            @endif
        </div>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('price-lists.show', $priceList) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button type="submit" form="version-form" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-save"></i> حفظ الإصدار
        </button>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<form id="version-form" method="POST" action="{{ route('price-lists.versions.update', [$priceList, $version]) }}">
    @csrf
    @method('PUT')
    <div class="nova-card">
        <div class="nova-card-body p-0">
            <div class="nova-table-scroll">
                <table class="nova-table">
                    <thead>
                        <tr>
                            <th class="nova-text-start">#</th>
                            <th>الصورة</th>
                            <th>المنتج</th>
                            <th>نوع العبوة</th>
                            <th>السعة</th>
                            <th>السعر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $counter = 1;
                        @endphp
                        @forelse($versionItems as $item)
                            @php
                                $itemModel = \App\Models\Item::find($item['item_id']);
                                $imageUrl = $itemModel ? ($itemModel->main_image_url ?? null) : null;
                            @endphp
                            <tr>
                                <td class="nova-fw-semibold">{{ $counter++ }}</td>
                                <td>
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item['name_ar'] }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="nova-flex nova-items-center nova-justify-center" style="width: 40px; height: 40px; background: var(--nova-surface-3); border-radius: 8px;">
                                            <span class="nova-text-muted">🖼️</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="nova-fw-bold">{{ $item['name_ar'] ?: $item['name_en'] }}</span>
                                    @if($item['is_new'] ?? false)
                                        <span class="nova-badge accent" style="margin-right: 0.5rem;">جديد</span>
                                    @endif
                                </td>
                                <td><small class="nova-text-muted">{{ $item['pack_type'] ?? 'قطعة' }}</small></td>
                                <td><small class="nova-text-muted">{{ $item['capacity'] ?? '—' }}</small></td>
                                <td>
                                    <input type="number"
                                           name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][price]"
                                           class="nova-input"
                                           style="width: 120px; display: inline-block;"
                                           value="{{ $item['price'] }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    <input type="hidden" name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][item_id]" value="{{ $item['item_id'] }}">
                                    <input type="hidden" name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][item_pack_id]" value="{{ $item['item_pack_id'] }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="nova-text-center" style="padding: 2rem;">
                                    <div>
                                        <div class="nova-stat-icon secondary" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <h5 class="nova-text-muted">لا توجد منتجات في هذا الإصدار</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
@endsection
@endsection
