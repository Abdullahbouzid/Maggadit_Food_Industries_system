@extends('dashboard.partials.layout')

@section('title', 'عرض الفاتورة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-6">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="file-text" class="align-middle me-2"></i>
                            الفاتورة #{{ $invoice->invoice_number }}
                        </h1>
                    </div>
                    <div class="col-12 col-lg-6 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                            <i data-feather="edit"></i> تعديل
                        </a>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i data-feather="list"></i> القائمة
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted mb-1">العميل</h6>
                                <p class="mb-0">{{ $invoice->customer->name ?? 'غير محدد' }}</p>
                                <small class="text-muted">{{ $invoice->customer->email ?? '' }}</small>
                            </div>
                            <div class="col-md-3">
                                <h6 class="fw-bold text-muted mb-1">تاريخ الفاتورة</h6>
                                <p class="mb-0">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-3">
                                <h6 class="fw-bold text-muted mb-1">الحالة</h6>
                                <span class="badge bg-{{ $invoice->status == 'completed' ? 'success' : ($invoice->status == 'cancelled' ? 'danger' : 'warning text-dark') }}">
                                    {{ $invoice->status == 'completed' ? 'مكتملة' : ($invoice->status == 'cancelled' ? 'ملغاة' : 'معلقة') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="shopping-bag" class="me-2"></i> تفاصيل الأصناف</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>المنتج</th>
                                        <th class="text-center">الكمية</th>
                                        <th class="text-center">سعر الوحدة</th>
                                        <th class="text-center">الخصم</th>
                                        <th class="text-center">المجموع</th>
                                        <th>المخزن المصدر</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->items as $index => $item)
                                        @php
                                            $warehousesRaw = \App\Models\Warehouse::find($item->warehouse_id);
                                            $allWarehouses = \App\Models\Warehouse::where('is_active', true)->orderBy('name_en')->get();
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->item->images->first())
                                                        <img src="{{ asset('uploads/' . $item->item->images->first()->image_path) }}"
                                                             alt="{{ $item->item->name_ar }}" class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <span class="fw-bold">{{ $item->item->name_ar }}</span>
                                                        <small class="d-block text-muted">{{ $item->item->category->name_ar ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-center">
                                                {{ number_format($item->unit_price, 2) }} ر.س
                                                @if($item->offer_id && $item->original_unit_price)
                                                    <br><small class="text-muted line-through">{{ number_format($item->original_unit_price, 2) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->offer_id)
                                                    <span class="badge bg-danger">-{{ number_format($item->discount_amount, 2) }} ر.س</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">{{ number_format($item->subtotal, 2) }} ر.س</td>
                                            <td>
                                                @if($item->warehouse)
                                                    <span class="badge bg-info">{{ $item->warehouse->name }}</span>
                                                    @php
                                                        $availableStock = app(\App\Services\Warehouse\InvoiceService::class)
                                                            ->getAvailableStockForItemInWarehouse($item->item_id, $item->warehouse_id);
                                                    @endphp
                                                    <div class="mt-1">
                                                        <small class="text-muted">المتوفر: {{ number_format($availableStock, 2) }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button class="btn btn-sm btn-outline-info" title="عرض المواقع"
                                                        onclick="showItemWarehouses({{ $item->item_id }}, '{{ $item->item->name_ar }}')">
                                                    <i data-feather="package"></i> المخازن
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                لا توجد أصناف في هذه الفاتورة.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-end">
                            <div class="text-end">
                                <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">المجموع الفرعي:</td>
                                        <td class="fw-bold">{{ number_format($invoice->items->sum('subtotal') + $invoice->items->sum('discount_amount'), 2) }} ر.س</td>
                                    </tr>
                                    @php
                                        $totalDiscount = $invoice->items->sum('discount_amount');
                                    @endphp
                                    @if($totalDiscount > 0)
                                        <tr>
                                            <td class="text-success">إجمالي الخصم:</td>
                                            <td class="fw-bold text-success">-{{ number_format($totalDiscount, 2) }} ر.س</td>
                                        </tr>
                                    @endif
                                    <tr class="border-top">
                                        <td class="fw-bold">الإجمالي:</td>
                                        <td class="fw-bold fs-5 text-primary">{{ number_format($invoice->total_amount, 2) }} ر.س</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showItemWarehouses(itemId, itemName) {
    fetch(`/invoices/get-warehouses/${itemId}`)
        .then(res => res.json())
        .then(data => {
            let html = `<h6 class="fw-bold mb-3">${itemName}</h6>`;
            html += '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>المخزن</th><th class="text-end">الكمية المتوفرة</th></tr></thead><tbody>';
            if (data.length === 0) {
                html += '<tr><td colspan="2" class="text-center text-muted">لا توفر أي كمية في المخازن</td></tr>';
            } else {
                data.forEach(wh => {
                    html += `<tr><td>${wh.name || wh.name_en}</td><td class="text-end">${parseFloat(wh.quantity).toFixed(2)}</td></tr>`;
                });
            }
            html += '</tbody></table></div>';
            document.getElementById('warehousesModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('warehousesModal')).show();
        })
        .catch(() => alert('خطأ في تحميل المواقع'));
}
</script>

<div class="modal fade" id="warehousesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">المواقع المتاحة للمنتج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="warehousesModalBody">
            </div>
        </div>
    </div>
</div>
@endpush
