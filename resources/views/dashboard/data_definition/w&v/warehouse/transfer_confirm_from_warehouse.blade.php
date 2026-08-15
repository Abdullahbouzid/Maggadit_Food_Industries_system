@extends('dashboard.partials.layout')

@section('title', 'تأكيد نقل المنتجات من المخزن: ' . $warehouse->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="check-circle" class="align-middle me-2"></i>
                            تأكيد نقل المنتجات من المخزن: {{ $warehouse->code }}
                        </h1>
                        <p class="text-muted">اختر المخزن الهدف وحدد الكميات للنقل من المخزن المصدر: <strong>{{ $warehouse->name_en }}</strong></p>
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i data-feather="alert-circle" class="me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('warehouses.transfer.execute', $warehouse) }}" method="POST" id="transferForm">
                    @csrf

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h6 class="fw-bold mb-0"><i data-feather="list"></i> المنتجات المختارة للنقل</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>المنتج</th>
                                            <th>الرمز</th>
                                            <th>الكمية المتوفرة في المصدر</th>
                                            <th>الكمية للنقل</th>
                                            <th>النسبة المئوية</th>
                                            <th>ملاحظات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalAvailable = 0; $totalToTransfer = 0; @endphp
                                        @foreach($batchItems as $index => $item)
                                            @php
                                                $available = $item->available_quantity;
                                                $totalAvailable += $available;
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->item->name_en }}</td>
                                                <td>{{ $item->item->code }}</td>
                                                <td class="text-end">{{ number_format($available, 3) }}</td>
                                                <td>
                                                    <input type="number" name="quantities[{{ $item->id }}]" class="form-control form-control-sm quantity-input" min="0" max="{{ $available }}" step="0.001" value="{{ $available }}" required>
                                                    <input type="hidden" name="batch_item_ids[]" value="{{ $item->id }}">
                                                </td>
                                                <td class="text-end percentage-display">0%</td>
                                                <td>
                                                    <input type="text" name="notes[{{ $item->id }}]" class="form-control form-control-sm" placeholder="ملاحظة اختيارية">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="truck"></i> تفاصيل النقل</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">المخزن المصدر</label>
                                                <input type="text" class="form-control" value="{{ $warehouse->name_en }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">المخزن الهدف</label>
                                                <select name="target_warehouse_id" class="form-select form-select-sm" required>
                                                    <option value="">اختر المخزن الهدف</option>
                                                    @foreach($warehouses as $wh)
                                                        <option value="{{ $wh->id }}">{{ $wh->name_en }} ({{ $wh->code }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">تاريخ النقل</label>
                                                <input type="date" name="transfer_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ملاحظات عامة</label>
                                                <textarea name="general_notes" class="form-control" rows="2" placeholder="ملاحظات عامة عن عملية النقل"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="bar-chart-2"></i> ملخص النقل</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">إجمالي الكمية المتوفرة في المصدر</label>
                                        <input type="text" class="form-control" id="totalAvailableDisplay" readonly value="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">إجمالي الكمية المحددة للنقل</label>
                                        <input type="text" class="form-control" id="totalToTransferDisplay" readonly value="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">النسبة المئوية للكلية المنقولة</label>
                                        <input type="text" class="form-control" id="percentageDisplay" readonly value="0%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                            <i data-feather="check-circle"></i> تأكيد وتنفيذ النقل
                        </button>
                        <a href="{{ route('warehouses.transfer.select', $warehouse) }}" class="btn btn-secondary btn-lg ms-2">
                            <i data-feather="arrow-left"></i> تعديل الاختيار
                        </a>
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

    // حساب totals والنسب المئوية
    function calculateTotals() {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        let totalAvailable = 0;
        let totalToTransfer = 0;

        quantityInputs.forEach((input, index) => {
            const available = parseFloat(input.getAttribute('max')) || 0;
            const quantity = parseFloat(input.value) || 0;
            totalAvailable += available;
            totalToTransfer += quantity;

            // تحديث النسبة المئوية لهذا المنتج
            const percentageCell = input.closest('tr').querySelector('.percentage-display');
            if (available > 0) {
                const percentage = (quantity / available) * 100;
                percentageCell.textContent = percentage.toFixed(1) + '%';
            } else {
                percentageCell.textContent = '0%';
            }
        });

        // تحديث الإجماليات
        document.getElementById('totalAvailableDisplay').textContent = totalAvailable.toFixed(3);
        document.getElementById('totalToTransferDisplay').textContent = totalToTransfer.toFixed(3);
        const overallPercentage = totalAvailable > 0 ? (totalToTransfer / totalAvailable) * 100 : 0;
        document.getElementById('percentageDisplay').textContent = overallPercentage.toFixed(1) + '%';
    }

    // إضافة مستمعي التغيير لجميع حقول الكمية
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            // التأكد من أن القيمة لا تتجاوز الحد الأقصى
            const max = parseFloat(this.getAttribute('max')) || 0;
            const value = parseFloat(this.value) || 0;
            if (value > max) {
                this.value = max;
            }
            calculateTotals();
        });

        // تهيئة القيم الابتدائية
        calculateTotals();
    });

    // معالجة النموذج قبل الإرسال للتأكد من صحة البيانات
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        let hasValidQuantity = false;

        quantityInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            if (value > 0) {
                hasValidQuantity = true;
            }
        });

        if (!hasValidQuantity) {
            e.preventDefault();
            alert('يرجى تحديد كمية لنقلها على الأقل لمنتج واحد.');
        }
    });
});
</script>
@endpush