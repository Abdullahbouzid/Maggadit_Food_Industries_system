@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الحركة #' . $movement->id)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="info" class="align-middle me-2"></i>
                            تفاصيل الحركة (المجموعة)
                        </h1>
                        <p class="text-muted">عرض جميع المنتجات المنقولة في هذه الدفعة</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-light border-0 py-3 d-flex justify-content-between">
                                <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات الحركة</h6>
                                <a href="{{ route('warehouse-movements.export-detail', $movement->id) }}" class="btn btn-success btn-sm">
                                    <i data-feather="download"></i> تصدير التفاصيل (Excel)
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th style="width:30%">معرف الحركة</th><td>{{ $movement->id }}</td></tr>
                                    <tr><th>نوع الحركة</th>
                                        <td>
                                            @php
                                                $labels = ['in' => 'وارد (توزيع)', 'transfer' => 'نقل بين المخازن', 'expiry_return' => 'استرجاع صلاحية'];
                                                $colors = ['in' => 'success', 'transfer' => 'warning', 'expiry_return' => 'danger'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$movement->movement_type] ?? 'secondary' }}">
                                                {{ $labels[$movement->movement_type] ?? $movement->movement_type }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr><th>رقم الدفعة</th><td>{{ $movement->batchItem->batch->batch_number ?? '-' }}</td></tr>
                                    <tr><th>المخزن المصدر</th><td>{{ $movement->fromWarehouse->name_en ?? 'لا يوجد' }}</td></tr>
                                    <tr><th>المخزن الهدف</th><td>{{ $movement->toWarehouse->name_en ?? 'غير محدد' }}</td></tr>
                                    <tr><th>تاريخ الحركة</th><td>{{ $movement->movement_date ? $movement->movement_date->format('Y-m-d') : '-' }}</td></tr>
                                    <tr><th>الملاحظات</th><td>{{ $movement->notes ?? '-' }}</td></tr>
                                    <tr><th>عدد المنتجات</th><td>{{ $groupedMovements->count() }}</td></tr>
                                    <tr><th>إجمالي الكمية</th><td>{{ number_format($groupedMovements->sum('quantity'), 3) }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="settings"></i> إجراءات</h6>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('warehouse-movements.edit', $movement->id) }}" class="btn btn-warning w-100 mb-2"><i data-feather="edit"></i> تعديل</a>
                                <form action="{{ route('warehouse-movements.destroy', $movement->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"><i data-feather="trash-2"></i> حذف</button>
                                </form>
                                <hr>
                                <a href="{{ route('warehouse-movements.index') }}" class="btn btn-secondary w-100"><i data-feather="arrow-left"></i> العودة للقائمة</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- عرض المنتجات -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات المنقولة في هذه الدفعة</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>المنتج</th>
                                        <th>العبوة</th>
                                        <th>الكمية</th>
                                        <th>ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedMovements as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->batchItem->item->name_en ?? 'غير معروف' }} ({{ $item->batchItem->item->code ?? '' }})</td>
                                             <td>{{ $item->batchItem->pack ? $item->batchItem->pack->barcode . ' (Qty: ' . $item->batchItem->pack->quantity_in_pack . ')' : 'بدون عبوة' }}</td>
                                            <td>{{ number_format($item->quantity, 3) }}</td>
                                            <td>{{ $item->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">لا توجد منتجات مسجلة.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection