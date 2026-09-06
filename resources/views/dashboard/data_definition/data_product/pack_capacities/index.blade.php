@extends('dashboard.partials.layout')

@section('title', 'Pack Capacities')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="box" class="align-middle me-2"></i>
                            Pack Capacities
                        </h1>
                        <p class="text-muted">إدارة سعات الباكجات (مثال: 500g، 1kg، 250ml)</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="{{ route('pack-capacities.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة سعة باكج جديدة
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0">قائمة سعات الباكجات ({{ $total }})</h5>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('pack-capacities.index') }}" class="d-flex">
                                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="بحث بالقيمة، الاسم..." value="{{ request('search') }}">
                                    <select name="uom_id" class="form-select form-select-sm me-2" style="width: auto;">
                                        <option value="">كل الوحدات</option>
                                        @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ request('uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->name_en ?? $uom->name_ar }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">بحث</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>القيمة</th>
                                        <th>وحدة القياس</th>
                                        <th>الاسم (إنجليزي)</th>
                                        <th>الاسم (عربي)</th>
                                        <th>الوصف</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packCapacities as $capacity)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $capacity->value }}</strong></td>
                                            <td>
                                                <span class="badge bg-info">{{ $capacity->uom->name_en ?? $capacity->uom->name_ar ?? '-' }}</span>
                                                <small class="text-muted d-block">{{ $capacity->uom->uom_symbol ?? '' }}</small>
                                            </td>
                                            <td>{{ $capacity->name_en ?? '-' }}</td>
                                            <td>{{ $capacity->name_ar ?? '-' }}</td>
                                            <td>{{ $capacity->description ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('pack-capacities.edit', $capacity) }}" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <form action="{{ route('pack-capacities.destroy', $capacity) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i data-feather="inbox" class="mb-2" style="width: 40px;"></i>
                                                <p class="mb-0">لا توجد سعات باكجات. قم بإنشاء أول سعة.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $packCapacities->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
