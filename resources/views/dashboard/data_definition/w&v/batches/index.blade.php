@extends('dashboard.partials.layout')

@section('title', 'دفعات الإنتاج')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="package" class="align-middle me-2"></i>
                            دفعات الإنتاج
                        </h1>
                        <p class="text-muted">إدارة الدفعات المنتجة وتفاصيلها</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('production-batches.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إنشاء دفعة
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">إجمالي الدفعات</h6>
                                        <h2 class="mb-0">{{ $total }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="grid" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">نشطة</h6>
                                        <h2 class="mb-0">{{ $active }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="check-circle" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">منتهية الصلاحية</h6>
                                        <h2 class="mb-0">{{ $expired }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="clock" class="text-warning"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('production-batches.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">بحث</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="رقم الدفعة، ملاحظات..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                                    <option value="consumed" {{ request('status') == 'consumed' ? 'selected' : '' }}>مستهلكة</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">عدد الصفوف</label>
                                <select name="per_page" class="form-select">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">تصفية</button>
                                <a href="{{ route('production-batches.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> قائمة الدفعات</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>رقم الدفعة</th>
                                        <th>عدد المنتجات</th>
                                        <th>تاريخ الإنتاج</th>
                                        <th>تاريخ الانتهاء</th>
                                        <th>الحالة</th>
                                        <th>ملاحظات</th>
                                        <th class="pe-4 text-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $batch)
                                    <tr>
                                        <td class="ps-4 fw-semibold">
                                            {{ $batches instanceof \Illuminate\Pagination\LengthAwarePaginator ? $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() : $loop->iteration }}
                                        </td>
                                        <td><code>{{ $batch->batch_number }}</code></td>
                                        <td>{{ $batch->batch_items_count }}</td>
                                        <td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '-' }}</td>
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
                                        <td>{{ Str::limit($batch->notes, 30) }}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('production-batches.show', $batch) }}" class="btn btn-outline-info"><i data-feather="eye"></i></a>
                                                <a href="{{ route('production-batches.edit', $batch) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $batch->id }}, '{{ addslashes($batch->batch_number) }}')"><i data-feather="trash-2"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="package" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">لا توجد دفعات بعد</h5>
                                                <a href="{{ route('production-batches.create') }}" class="btn btn-primary"><i data-feather="plus-circle"></i> إنشاء أول دفعة</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($batches instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $batches->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i></div>
                <h4 class="fw-bold">تأكيد الحذف</h4>
                <p class="text-muted">هل أنت متأكد من حذف الدفعة <strong id="deleteItemName" class="text-danger"></strong>؟ هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        window.confirmDelete = function(id, name) {
            document.getElementById('deleteItemName').innerText = name;
            document.getElementById('deleteForm').action = '/production-batches/' + id;
            new bootstrap.Modal(document.getElementById('globalDeleteModal')).show();
        };
    });
</script>
@endpush