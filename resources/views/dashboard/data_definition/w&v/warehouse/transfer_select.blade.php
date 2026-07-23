@extends('dashboard.partials.layout')

@section('title', 'اختيار منتجات للنقل من ' . $warehouse->name_en)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold">
                            <i data-feather="send" class="align-middle me-2"></i>
                            نقل كميات من {{ $warehouse->name_en }}
                        </h1>
                        <p class="text-muted">حدد المنتجات التي ترغب في نقلها، ثم اختر المخزن الهدف.</p>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('warehouses.transfer.store-selection', $warehouse) }}">
                    @csrf

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h6 class="fw-bold mb-0"><i data-feather="filter"></i> فلترة المنتجات</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="بحث (اسم, كود)" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="category_id" class="form-select">
                                        <option value="">جميع الفئات</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="brand_id" class="form-select">
                                        <option value="">جميع الماركات</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">فلتر</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i data-feather="list"></i> المنتجات المتوفرة</h6>
                            <div>
                                <label class="form-check-label">
                                    <input type="checkbox" id="selectAll" class="form-check-input"> تحديد الكل
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllCheckbox"></th>
                                            <th>#</th>
                                            <th>المنتج</th>
                                            <th>الكود</th>
                                            <th>الفئة</th>
                                            <th>الماركة</th>
                                            <th>رقم الدفعة</th>
                                            <th>العبوة</th>
                                            <th>الكمية المتوفرة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($batchItems as $index => $item)
                                            <tr>
                                                <td><input type="checkbox" name="selected_items[]" value="{{ $item->batch_item_id }}" class="item-checkbox"></td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->item_name }}</td>
                                                <td>{{ $item->item_code }}</td>
                                                <td>{{ $item->category }}</td>
                                                <td>{{ $item->brand }}</td>
                                                <td>{{ $item->batch_number }}</td>
                                                <td>{{ $item->pack_info }}</td>
                                                <td>{{ number_format($item->available_qty, 3) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center text-muted">لا توجد منتجات متوفرة في هذا المخزن.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h6 class="fw-bold mb-0"><i data-feather="home"></i> المخزن الهدف</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اختر المخزن الهدف <span class="text-danger">*</span></label>
                                    <select name="target_warehouse_id" class="form-select" required>
                                        <option value="">-- اختر --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name_en }} ({{ $wh->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i data-feather="check-circle"></i> تأكيد المنتجات والمتابعة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-secondary">
                            <i data-feather="arrow-left"></i> العودة للمخزن
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = [...checkboxes].every(c => c.checked);
                selectAll.checked = allChecked;
            });
        });
    });
</script>
@endpush
@endsection