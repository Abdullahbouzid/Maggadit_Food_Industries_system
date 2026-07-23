@extends('dashboard.partials.layout')

@section('title', 'حركات المخازن والباتشات')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="activity" class="align-middle me-2"></i>
                            حركات المخازن والباتشات
                        </h1>
                        <p class="text-muted">عرض وإدارة جميع حركات النقل والتوزيع والاسترجاع</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- بطاقات الإحصائيات -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="list" class="text-primary" style="width: 24px;"></i>
                                    </div>
                                </div>
                                <div><h6 class="text-muted mb-1">إجمالي الحركات</h6><h2 class="mb-0 fw-bold">{{ $total }}</h2></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="arrow-down" class="text-success" style="width: 24px;"></i>
                                    </div>
                                </div>
                                <div><h6 class="text-muted mb-1">وارد (توزيع)</h6><h2 class="mb-0 fw-bold">{{ $inCount }}</h2></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="move" class="text-warning" style="width: 24px;"></i>
                                    </div>
                                </div>
                                <div><h6 class="text-muted mb-1">نقل بين المخازن</h6><h2 class="mb-0 fw-bold">{{ $transferCount }}</h2></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                        <i data-feather="alert-triangle" class="text-danger" style="width: 24px;"></i>
                                    </div>
                                </div>
                                <div><h6 class="text-muted mb-1">استرجاع صلاحية</h6><h2 class="mb-0 fw-bold">{{ $expiryCount }}</h2></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- نموذج الفلاتر -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('warehouse-movements.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">نوع الحركة</label>
                                <select name="movement_type" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($movementTypes as $key => $label)
                                        <option value="{{ $key }}" {{ request('movement_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">المخزن المصدر</label>
                                <select name="from_warehouse_id" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">المخزن الهدف</label>
                                <select name="to_warehouse_id" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">من تاريخ</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">إلى تاريخ</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i data-feather="filter"></i> فلتر</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- قائمة الحركات المجمعة -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> قائمة الحركات (مجموعة حسب الدفعة)</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('warehouse-movements.create') }}" class="btn btn-primary btn-sm">
                                <i data-feather="plus"></i> إضافة حركة
                            </a>
                            <a href="{{ route('warehouse-movements.export', request()->query()) }}" class="btn btn-success btn-sm">
                                <i data-feather="download"></i> تصدير Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>نوع الحركة</th>
                                        <th>رقم الدفعة</th>
                                        <th>المخزن المصدر</th>
                                        <th>المخزن الهدف</th>
                                        <th>تاريخ الحركة</th>
                                        <th>عدد المنتجات</th>
                                        <th>إجمالي الكمية</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupsPaginated as $group)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @php
                                                    $labels = ['in' => 'وارد', 'transfer' => 'نقل', 'expiry_return' => 'استرجاع'];
                                                    $colors = ['in' => 'success', 'transfer' => 'warning', 'expiry_return' => 'danger'];
                                                @endphp
                                                <span class="badge bg-{{ $colors[$group->movement_type] ?? 'secondary' }}">
                                                    {{ $labels[$group->movement_type] ?? $group->movement_type }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $group->batch_number }}</strong></td>
                                            <td>{{ $group->from_warehouse }}</td>
                                            <td>{{ $group->to_warehouse }}</td>
                                            <td>{{ $group->movement_date ? $group->movement_date->format('Y-m-d') : '-' }}</td>
                                            <td>{{ $group->items_count }}</td>
                                            <td>{{ number_format($group->total_quantity, 3) }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- نمرر معرف أول حركة في المجموعة --}}
                                                    <a href="{{ route('warehouse-movements.show', $group->first_movement->id) }}" class="btn btn-outline-info" title="عرض التفاصيل"><i data-feather="eye"></i></a>
                                                    <a href="{{ route('warehouse-movements.edit', $group->first_movement->id) }}" class="btn btn-outline-warning" title="تعديل"><i data-feather="edit"></i></a>
                                                    <form action="{{ route('warehouse-movements.destroy', $group->first_movement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"><i data-feather="trash-2"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted">لا توجد حركات مطابقة للفلاتر.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($groupsPaginated, 'links'))
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>{{ $groupsPaginated->appends(request()->query())->links() }}</div>
                                <div>
                                    <form method="GET" action="{{ route('warehouse-movements.index') }}" class="d-inline">
                                        <input type="hidden" name="per_page" value="all">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">عرض الكل</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection