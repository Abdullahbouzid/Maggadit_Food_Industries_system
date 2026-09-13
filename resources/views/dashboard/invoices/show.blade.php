@extends('dashboard.partials.layout')

@section('title', 'عرض الفاتورة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-file-text" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            الفاتورة #{{ $invoice->invoice_number }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Invoice details and items</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('invoices.edit', $invoice) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('invoices.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <div class="nova-row nova-gap-3">
            <div class="nova-col">
                <h6 class="nova-fw-bold nova-text-muted" style="margin-bottom: 0.25rem;">العميل</h6>
                <p class="mb-0">{{ $invoice->customer->name ?? 'غير محدد' }}</p>
                <small class="nova-text-muted">{{ $invoice->customer->email ?? '' }}</small>
            </div>
            <div class="nova-col">
                <h6 class="nova-fw-bold nova-text-muted" style="margin-bottom: 0.25rem;">تاريخ الفاتورة</h6>
                <p class="mb-0">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
            </div>
            <div class="nova-col">
                <h6 class="nova-fw-bold nova-text-muted" style="margin-bottom: 0.25rem;">الحالة</h6>
                <span class="nova-badge {{ $invoice->status == 'completed' ? 'success' : ($invoice->status == 'cancelled' ? 'danger' : 'accent') }}">
                    {{ $invoice->status == 'completed' ? 'مكتملة' : ($invoice->status == 'cancelled' ? 'ملغاة' : 'معلقة') }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-header">
            <h5 class="nova-card-title"><i class="fas fa-shopping-bag" style="margin-left: 0.5rem;"></i> تفاصيل الأصناف</h5>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th class="nova-text-center">الكمية</th>
                        <th class="nova-text-center">سعر الوحدة</th>
                        <th class="nova-text-center">الخصم</th>
                        <th class="nova-text-center">المجموع</th>
                        <th>المخزن المصدر</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                        @php
                            $warehousesRaw = \App\Models\Warehouse::find($item->warehouse_id);
                            $allWarehouses = \App\Models\Warehouse::where('is_active', true)->orderBy('name_en')->get();
                        @endphp
                        <tr>
                            <td class="nova-fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <div class="nova-flex nova-items-center">
                                    @if($item->item->images->first())
                                        <img src="{{ asset('uploads/' . $item->item->images->first()->image_path) }}"
                                             alt="{{ $item->item->name_ar }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover; margin-left: 0.5rem;">
                                    @endif
                                    <div>
                                        <span class="nova-fw-bold">{{ $item->item->name_ar }}</span>
                                        <small class="nova-text-muted d-block">{{ $item->item->category->name_ar ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="nova-text-center">{{ $item->quantity }}</td>
                            <td class="nova-text-center">
                                {{ number_format($item->unit_price, 2) }} ر.س
                                @if($item->offer_id && $item->original_unit_price)
                                    <br><small class="nova-text-muted" style="text-decoration: line-through;">{{ number_format($item->original_unit_price, 2) }}</small>
                                @endif
                            </td>
                            <td class="nova-text-center">
                                @if($item->offer_id)
                                    <span class="nova-badge danger">-{{ number_format($item->discount_amount, 2) }} ر.س</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="nova-text-center nova-fw-bold">{{ number_format($item->subtotal, 2) }} ر.س</td>
                            <td>
                                @if($item->warehouse)
                                    <span class="nova-badge secondary">{{ $item->warehouse->name }}</span>
                                    @php
                                        $availableStock = app(\App\Services\Warehouse\InvoiceService::class)
                                            ->getAvailableStockForItemInWarehouse($item->item_id, $item->warehouse_id);
                                    @endphp
                                    <div class="nova-mt-1">
                                        <small class="nova-text-muted">المتوفر: {{ number_format($availableStock, 2) }}</small>
                                    </div>
                                @else
                                    <span class="nova-text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td class="nova-text-end">
                                <button class="nova-btn nova-btn-outline nova-btn-sm" title="عرض المواقع"
                                        onclick="showItemWarehouses({{ $item->item_id }}, '{{ $item->item->name_ar }}')">
                                    <i class="fas fa-box"></i> المخازن
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="nova-text-center" style="padding: 2rem;">
                                <span class="nova-text-muted">لا توجد أصناف في هذه الفاتورة.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-end">
            <div class="nova-text-end">
                <table class="nova-table nova-table-borderless" style="max-width: 300px;">
                    <tbody>
                        <tr>
                            <td class="nova-text-muted">المجموع الفرعي:</td>
                            <td class="nova-fw-bold">{{ number_format($invoice->items->sum('subtotal') + $invoice->items->sum('discount_amount'), 2) }} ر.س</td>
                        </tr>
                        @php
                            $totalDiscount = $invoice->items->sum('discount_amount');
                        @endphp
                        @if($totalDiscount > 0)
                            <tr>
                                <td class="nova-text-success">إجمالي الخصم:</td>
                                <td class="nova-fw-bold nova-text-success">-{{ number_format($totalDiscount, 2) }} ر.س</td>
                            </tr>
                        @endif
                        <tr style="border-top: 1px solid var(--nova-border);">
                            <td class="nova-fw-bold">الإجمالي:</td>
                            <td class="nova-fw-bold" style="font-size: 1.25rem; color: var(--nova-primary);">{{ number_format($invoice->total_amount, 2) }} ر.س</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
        
@endsection

@push('scripts')
<script>
function showItemWarehouses(itemId, itemName) {
    fetch(`/invoices/get-warehouses/${itemId}`)
        .then(res => res.json())
        .then(data => {
            let html = `<h6 class="nova-fw-bold nova-mb-3">${itemName}</h6>`;
            html += '<div class="nova-table-scroll"><table class="nova-table"><thead><tr><th>المخزن</th><th class="nova-text-end">الكمية المتوفرة</th></tr></thead><tbody>';
            if (data.length === 0) {
                html += '<tr><td colspan="2" class="nova-text-center nova-text-muted">لا توفر أي كمية في المخازن</td></tr>';
            } else {
                data.forEach(wh => {
                    html += `<tr><td>${wh.name || wh.name_en}</td><td class="nova-text-end">${parseFloat(wh.quantity).toFixed(2)}</td></tr>`;
                });
            }
            html += '</tbody></table></div>';
            document.getElementById('warehousesModalBody').innerHTML = html;
            document.getElementById('warehousesModal').classList.add('show');
        })
        .catch(() => alert('خطأ في تحميل المواقع'));
}
</script>

<div class="nova-modal-backdrop" id="warehousesModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-modal-header">
            <h5 class="nova-modal-title">المواقع المتوفرة للمنتج</h5>
            <button class="nova-modal-close" onclick="document.getElementById('warehousesModal').classList.remove('show')" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="nova-modal-body" id="warehousesModalBody">
        </div>
    </div>
</div>
@endpush
