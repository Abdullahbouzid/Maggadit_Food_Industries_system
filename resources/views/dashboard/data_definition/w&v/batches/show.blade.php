@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الدفعة: ' . $batch->batch_number)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="package" class="align-middle me-2"></i>
                            تفاصيل الدفعة: {{ $batch->batch_number }}
                        </h1>
                        <p class="text-muted">عرض معلومات الدفعة والمنتجات المرتبطة بها</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات الدفعة</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%;">رقم الدفعة</th>
                                        <td><code>{{ $batch->batch_number }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإنتاج</th>
                                        <td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الانتهاء</th>
                                        <td>{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>الحالة</th>
                                        <td>
                                            @php
                                                $statusMap = [
                                                    'active' => ['label' => 'نشطة', 'class' => 'success'],
                                                    'expired' => ['label' => 'منتهية', 'class' => 'warning'],
                                                    'consumed' => ['label' => 'مستهلكة', 'class' => 'secondary'],
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusMap[$batch->status]['class'] ?? 'secondary' }}">
                                                {{ $statusMap[$batch->status]['label'] ?? $batch->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>ملاحظات</th>
                                        <td>{{ $batch->notes ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإنشاء</th>
                                        <td>{{ $batch->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>آخر تحديث</th>
                                        <td>{{ $batch->updated_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات في الدفعة</h6>
                            </div>
                            <div class="card-body">
                                @if($batch->batchItems->count())
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                       <!-- في الأعلى بجانب أزرار التعديل -->
                                        <a href="{{ route('production-batches.distribute', $batch->id) }}" class="btn btn-primary btn-lg shadow-sm ms-2">
                                            <i data-feather="share-2"></i> توزيع
                                        </a>

                                        <!-- في جدول المنتجات -->
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>المنتج</th>
                                                <th>العبوة</th>
                                                <th>الكمية المنتجة</th>
                                                <th>المرحلة</th>
                                                <th>المتبقية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($batch->batchItems as $index => $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->item->name_en ?? 'غير معروف' }}</td>
                                                    <td>{{ $item->pack ? $item->pack->barcode . ' (Qty: ' . $item->pack->quantity . ')' : 'بدون عبوة' }}</td>
                                                    <td>{{ number_format($item->quantity, 3) }}</td>
                                                    <td>{{ number_format($item->transferred_quantity ?? 0, 3) }}</td>
                                                    <td>{{ number_format($item->remaining_quantity ?? $item->quantity, 3) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center">لا توجد منتجات في هذه الدفعة.</p>
                                @endif
                              
                            </div>
                           
                        </div>
                    </div>
                    <!-- حركات التوزيع -->
<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-light border-0 py-3">
        <h6 class="fw-bold mb-0"><i data-feather="activity"></i> حركات التوزيع</h6>
    </div>
    <div class="card-body">
        @php
            $movements = $batch->batchItems->flatMap(function ($item) {
                return $item->movements;
            })->sortByDesc('created_at');
        @endphp
        @if($movements->count())
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>العبوة</th>
                            <th>المخزن المستهدف</th>
                            <th>الكمية</th>
                            <th>التاريخ</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $index => $movement)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $movement->batchItem->item->name_en ?? 'غير معروف' }}</td>
                                <td>{{ $movement->batchItem->pack ? $movement->batchItem->pack->barcode : 'بدون عبوة' }}</td>
                                <td>{{ $movement->toWarehouse->name_en ?? 'غير معروف' }}</td>
                                <td>{{ number_format($movement->quantity, 3) }}</td>
                                <td>{{ $movement->movement_date ? $movement->movement_date->format('Y-m-d') : $movement->created_at->format('Y-m-d') }}</td>
                                <td>{{ $movement->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center">لا توجد حركات توزيع لهذه الدفعة.</p>
        @endif
    </div>
</div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('production-batches.index') }}" class="btn btn-secondary btn-lg shadow-sm">
                            <i data-feather="arrow-left"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('production-batches.edit', $batch) }}" class="btn btn-warning btn-lg shadow-sm ms-2">
                            <i data-feather="edit"></i> تعديل
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection