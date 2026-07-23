@extends('dashboard.partials.layout')

@section('title', 'تحديد كميات النقل من ' . $warehouse->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="edit" class="align-middle me-2"></i>
                            تحديد كميات النقل
                        </h1>
                        <p class="text-muted">أدخل الكمية المطلوب نقلها لكل منتج من المخزن <strong>{{ $warehouse->name_en }}</strong> إلى <strong>{{ $targetWarehouse->name_en }}</strong>.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('warehouses.transfer.execute', $warehouse) }}">
                    @csrf

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات المختارة</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>المنتج</th>
                                            <th>العبوة</th>
                                            <th>رقم الدفعة</th>
                                            <th>الكمية المتوفرة</th>
                                            <th>الكمية المراد نقلها</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($batchItems as $index => $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->item->name_en ?? $item->item->name_ar }}</td>
                                                <td>{{ $item->pack ? $item->pack->barcode . ' (Qty: ' . $item->pack->quantity . ')' : 'بدون عبوة' }}</td>
                                                <td>{{ $item->batch->batch_number }}</td>
                                                <td>{{ number_format($item->available_quantity, 3) }}</td>
                                                <td>
                                                    <input type="number" name="quantities[{{ $item->id }}]" 
                                                           class="form-control qty-input" 
                                                           step="0.001" min="0.001" 
                                                           max="{{ $item->available_quantity }}" 
                                                           placeholder="الكمية" required>
                                                    @error("quantities.{$item->id}")
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted">لم يتم اختيار أي منتج.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات إضافية</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">تاريخ النقل</label>
                                    <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', now()->toDateString()) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ملاحظات</label>
                                    <input type="text" name="notes" class="form-control" placeholder="سبب النقل (اختياري)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="check-circle"></i> تأكيد النقل
                            </button>
                            <a href="{{ route('warehouses.transfer.select', $warehouse) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">الرجوع</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        // التحقق من أن الكمية لا تتجاوز المتوفرة
        document.querySelectorAll('.qty-input').forEach(input => {
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
@endsection