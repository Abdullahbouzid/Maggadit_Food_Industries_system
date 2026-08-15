@extends('dashboard.partials.layout')

@section('title', 'اختيار المنتجات للنقل من المخزن: ' . $warehouse->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="check-square" class="align-middle me-2"></i>
                            اختيار المنتجات للنقل من المخزن: {{ $warehouse->code }}
                        </h1>
                        <p class="text-muted">اختر المنتجات التي ترغب في نقلها من المخزن المصدر {{ $warehouse->name_en }}, ثم اختر المخزن الهدف وحدد الكميات في الخطوة التالية.</p>
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

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="filter"></i> تصفية المنتجات</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('warehouses.transfer.select', $warehouse) }}" method="GET" id="filterForm">
                                    <div class="mb-3">
                                        <label class="form-label">بحث بالمنتج أو الكود أو الدفعة</label>
                                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ابحث عن producto...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">التصنيف</label>
                                        <select name="category_id" class="form-select">
                                            <option value="">جميع التصنيفات</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">العلامة التجارية</label>
                                        <select name="brand_id" class="form-select">
                                            <option value="">جميع العلامات</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-sm">تطبيق التصفية</button>
                                        <a href="{{ route('warehouses.transfer.select', $warehouse) }}" class="btn btn-outline-secondary btn-sm ms-2">إعادة تعيين</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0"><i data-feather="shopping-bag"></i> المنتجات المتوفرة في المخزن المصدر</h6>
                                <div>
                                    <span class="text-muted">إجمالي المنتجات المتوفرة: {{ $batchItems->count() }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($batchItems->isEmpty())
                                    <p class="text-center text-muted py-4">لا توجد منتجات متوفرة في هذا المخزن مع الشروط المحددة.</p>
                                @else
                                    <form action="{{ route('warehouses.transfer.store-selection', $warehouse) }}" method="POST" id="selectionForm">
                                        @csrf
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                                        <th>المنتج</th>
                                                        <th>الرمز</th>
                                                        <th>التصنيف</th>
                                                        <th>العلامة التجارية</th>
                                                        <th>الدفعة</th>
                                                        <th>العبوة</th>
                                                        <th class="text-end">الكمية المتوفرة</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($batchItems as $item)
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="checkbox" name="selected_items[]" value="{{ $item->batch_item_id }}" class="form-check-input item-checkbox" {{ $item->available_qty > 0 ? '' : 'disabled' }}>
                                                            </td>
                                                            <td>{{ $item->item_name }}</td>
                                                            <td>{{ $item->item_code }}</td>
                                                            <td>{{ $item->category }}</td>
                                                            <td>{{ $item->brand }}</td>
                                                            <td>{{ $item->batch_number }}</td>
                                                            <td>{{ $item->pack_info }}</td>
                                                            <td class="text-end">{{ number_format($item->available_qty, 3) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        @if($batchItems->isNotEmpty())
                                            <div class="mt-3 text-center">
                                                <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                                    <i data-feather="move"></i> متابعة لتحديد المخزن الهدف
                                                </button>
                                            </div>
                                        @endif
                                    </form>
                                @endif
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
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();

    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const filterForm = document.getElementById('filterForm');

    // Function to update select all state
    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked:not(:disabled)').length;
        const totalCount = document.querySelectorAll('.item-checkbox:not(:disabled)').length;
        selectAllCheckbox.checked = (totalCount > 0 && checkedCount === totalCount);
        selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < totalCount);
    }

    // Handle select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = this.checked;
            }
        });
        updateSelectAllState();
    });

    // Handle individual checkbox changes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
        });
    });

    // Initialize
    updateSelectAllState();

    // Submit filter form on change
    const filterInputs = filterForm.querySelectorAll('select, input[type="text"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });

    // Focus search input if it has a value
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && searchInput.value) {
        searchInput.focus();
    }
});
</script>
@endpush