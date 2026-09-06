@extends('dashboard.partials.layout')

@section('title', 'إدارة قوائم الأسعار')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="dollar-sign" class="align-middle me-2"></i>
                            إدارة قوائم الأسعار
                        </h1>
                        <p class="text-muted">إنشاء وإدارة قوائم أسعار مخصصة للعملاء</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('price-lists.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة قائمة أسعار
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث عن قائمة أسعار..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i data-feather="search"></i></button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل الحالات</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشطة</option>
                                </select>
                            </div>
                            @if(request()->hasAny(['search', 'status']))
                                <div class="col-md-2">
                                    <a href="{{ route('price-lists.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i data-feather="x"></i> مسح
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>الحالة</th>
                                        <th>عدد المنتجات</th>
                                        <th>المستخدم الذي أنشأها</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($priceLists as $priceList)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $priceList->id }}</td>
                                            <td><strong>{{ $priceList->name }}</strong></td>
                                            <td><small class="text-muted">{{ Str::limit($priceList->description, 50) }}</small></td>
                                            <td>
                                                @if($priceList->status === 'active')
                                                    <span class="badge bg-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-secondary">معطلة</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info">{{ $priceList->items_count ?? 0 }}</span></td>
                                            <td><small class="text-muted">{{ $priceList->creator?->name ?? '—' }}</small></td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('price-lists.show', $priceList) }}" class="btn btn-outline-info" title="عرض"><i data-feather="eye"></i></a>
                                                    <a href="{{ route('price-lists.edit', $priceList) }}" class="btn btn-outline-warning" title="تعديل"><i data-feather="edit"></i></a>
                                                    <form action="{{ route('price-lists.toggle-status', $priceList) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-{{ $priceList->status === 'active' ? 'warning' : 'success' }}" title="{{ $priceList->status === 'active' ? 'تعطيل' : 'تفعيل' }}">
                                                            <i data-feather="{{ $priceList->status === 'active' ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('price-lists.destroy', $priceList) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف قائمة الأسعار؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف"><i data-feather="trash-2"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="dollar-sign" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد قوائم أسعار</h5>
                                                    <a href="{{ route('price-lists.create') }}" class="btn btn-primary">
                                                        <i data-feather="plus-circle"></i> إضافة قائمة أسعار
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $priceLists->links('pagination::bootstrap-5') }}
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
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
