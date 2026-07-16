@extends('dashboard.partials.layout')

@section('title', 'توزيع الدفعة: ' . $batch->batch_number)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="send" class="align-middle me-2"></i>
                            توزيع الدفعة: {{ $batch->batch_number }}
                        </h1>
                        <p class="text-muted">قم بتوزيع المنتجات من هذه الدفعة إلى أحد المخازن</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i data-feather="alert-circle" class="me-2"></i>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('production-batches.distribute.store', $batch) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات الدفعة</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr><th>رقم الدفعة</th><td><code>{{ $batch->batch_number }}</code></td></tr>
                                        <tr><th>تاريخ الإنتاج</th><td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '-' }}</td></tr>
                                        <tr><th>تاريخ الانتهاء</th><td>{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '-' }}</td></tr>
                                        <tr><th>الحالة</th><td><span class="badge bg-success">{{ ucfirst($batch->status) }}</span></td></tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="home"></i> المخزن المستهدف</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="warehouse_id" class="form-label fw-semibold">اختر المخزن <span class="text-danger">*</span></label>
                                        <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                    {{ $warehouse->name_en }} ({{ $warehouse->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات المراد توزيعها</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>المنتج</th>
                                                    <th>العبوة</th>
                                                    <th>الكمية الإجمالية</th>
                                                    <th>الموزعة سابقاً</th>
                                                    <th>المتبقية</th>
                                                    <th>الكمية في المخزن</th>
                                                    <th>الكمية المراد توزيعها</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTableBody">
                                                @foreach($batch->batchItems as $index => $item)
                                                    @php
                                                        $remaining = $item->quantity - $item->dispatched_quantity;
                                                    @endphp
                                                    <tr id="row-{{ $item->id }}">
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item->item->name_en ?? 'غير معروف' }}</td>
                                                        <td>{{ $item->pack ? $item->pack->barcode . ' (Qty: ' . $item->pack->quantity . ')' : 'بدون عبوة' }}</td>
                                                        <td>{{ number_format($item->quantity, 3) }}</td>
                                                        <td>{{ number_format($item->dispatched_quantity, 3) }}</td>
                                                        <td class="fw-bold @if($remaining == 0) text-success @else text-primary @endif">
                                                            {{ number_format($remaining, 3) }}
                                                        </td>
                                                        <td id="stock-{{ $item->id }}">0</td>
                                                        <td>
                                                            <input type="hidden" name="items[{{ $index }}][batch_item_id]" value="{{ $item->id }}">
                                                            <input type="number" step="0.001" min="0" max="{{ $remaining }}" name="items[{{ $index }}][quantity]" class="form-control distribute-qty" value="0" @if($remaining == 0) disabled @endif>
                                                            @if($remaining == 0)
                                                                <small class="text-success">تم التوزيع بالكامل</small>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if($batch->batchItems->isEmpty())
                                        <p class="text-muted text-center">لا توجد منتجات في هذه الدفعة لتوزيعها.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="send"></i> توزيع
                            </button>
                            <a href="{{ route('production-batches.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        // دالة جلب الأرصدة
        function loadWarehouseStock(warehouseId) {
            if (!warehouseId) return;
            const batchId = {{ $batch->id }};
            fetch(`/production-batches/${batchId}/warehouse-stock/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    // تحديث عمود الكمية في المخزن لكل منتج
                    document.querySelectorAll('[id^="stock-"]').forEach(el => {
                        const batchItemId = el.id.replace('stock-', '');
                        const qty = data[batchItemId] || 0;
                        el.textContent = qty.toFixed(3);
                    });
                })
                .catch(error => console.error('خطأ في تحميل الأرصدة:', error));
        }

        // عند تغيير المخزن
        document.getElementById('warehouse_id').addEventListener('change', function() {
            loadWarehouseStock(this.value);
        });

        // تحميل الأرصدة إذا كان هناك مخزن محدد مسبقاً (عند تحميل الصفحة)
        const initialWarehouse = document.getElementById('warehouse_id').value;
        if (initialWarehouse) {
            loadWarehouseStock(initialWarehouse);
        }

        // التحقق من الكمية المدخلة لا تتجاوز المتبقية
        document.querySelectorAll('.distribute-qty').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.getAttribute('max'));
                const val = parseFloat(this.value);
                if (val > max) {
                    this.value = max;
                }
                if (val < 0) {
                    this.value = 0;
                }
            });
        });
    });
</script>
@endpush