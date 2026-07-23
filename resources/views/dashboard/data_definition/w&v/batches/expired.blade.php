@extends('dashboard.partials.layout')

@section('title', 'الباتشات منتهية الصلاحية')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="alert-triangle" class="align-middle me-2"></i>
                            الباتشات منتهية الصلاحية
                        </h1>
                        <p class="text-muted">عرض وإدارة الباتشات التي انتهت صلاحيتها في المخازن</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- فلتر المخزن -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('production-batches.expired') }}" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اختر المخزن</label>
                                <select name="warehouse_id" class="form-select">
                                    <option value="">جميع المخازن</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name_en }} ({{ $warehouse->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary w-100"><i data-feather="filter"></i> فلتر</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($expiredStocks->count())
                    <form action="{{ route('production-batches.return-expired') }}" method="POST">
                        @csrf
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0"><i data-feather="list"></i> قائمة الباتشات منتهية الصلاحية</h5>
                                <div>
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i data-feather="archive"></i> استرجاع الكل إلى مخزن المهملات
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAll"></th>
                                                <th>#</th>
                                                <th>رقم الدفعة</th>
                                                <th>المنتج</th>
                                                <th>العبوة</th>
                                                <th>المخزن الحالي</th>
                                                <th>الكمية</th>
                                                <th>تاريخ الانتهاء</th>
                                                <th>عدد الأيام المنقضية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expiredStocks as $index => $stock)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="stock_ids[]" value="{{ $stock->id }}">
                                                    </td>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <a href="{{ route('production-batches.show', $stock->batchItem->batch_id) }}" target="_blank">
                                                            {{ $stock->batchItem->batch->batch_number }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $stock->batchItem->item->name_en ?? 'غير معروف' }}</td>
                                                    <td>{{ $stock->batchItem->pack ? $stock->batchItem->pack->barcode : 'بدون عبوة' }}</td>
                                                    <td>{{ $stock->warehouse->name_en ?? 'غير معروف' }}</td>
                                                    <td>{{ number_format($stock->quantity, 3) }}</td>
                                                    <td>{{ $stock->batchItem->batch->expiry_date ? $stock->batchItem->batch->expiry_date->format('Y-m-d') : '-' }}</td>
                                                    <td>
                                                        @php
                                                            $days = $stock->batchItem->batch->expiry_date ? now()->diffInDays($stock->batchItem->batch->expiry_date, false) : 0;
                                                        @endphp
                                                        <span class="badge bg-danger">{{ abs($days) }} يوم</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- اختيار مخزن المهملات -->
                        <div class="card border-0 shadow-sm rounded-4 mt-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="home"></i> مخزن استرجاع الباتشات منتهية الصلاحية</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">المخزن الهدف <span class="text-danger">*</span></label>
                                        <select name="destination_warehouse_id" class="form-select" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name_en }} ({{ $warehouse->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">تاريخ الاسترجاع</label>
                                        <input type="date" name="return_date" class="form-control" value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">ملاحظات</label>
                                        <input type="text" name="notes" class="form-control" placeholder="سبب الاسترجاع (اختياري)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center py-5">
                            <i data-feather="check-circle" style="width: 48px; height: 48px;" class="text-success mb-3"></i>
                            <h5 class="text-muted">لا توجد باتشات منتهية الصلاحية في المخازن.</h5>
                        </div>
                    </div>
                @endif

                <div class="row mt-4">
                    <div class="col-12">
                        <a href="{{ route('production-batches.index') }}" class="btn btn-secondary">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> العودة للقائمة
                        </a>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        // تحديد/إلغاء تحديد الكل
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('input[name="stock_ids[]"]').forEach(chk => {
                chk.checked = this.checked;
            });
        });
    });
</script>
@endpush