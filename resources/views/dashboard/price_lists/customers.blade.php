@extends('dashboard.partials.layout')

@section('title', 'ربط قوائم الأسعار بالعملاء')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="users" class="align-middle me-2"></i>
                            ربط قوائم الأسعار بالعملاء
                        </h1>
                        <p class="text-muted">تحديد قائمة الأسعار لكل عميل</p>
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
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث عن عميل..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i data-feather="search"></i></button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <select name="price_list_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل القوائم</option>
                                    @foreach($priceLists as $list)
                                        <option value="{{ $list->id }}" {{ request('price_list_id') == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(request()->hasAny(['search', 'price_list_id']))
                                <div class="col-md-2">
                                    <a href="{{ route('price-lists.customers') }}" class="btn btn-sm btn-outline-secondary">
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
                                        <th>اسم المستخدم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>قائمة الأسعار</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $customer)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $customer->id }}</td>
                                            <td><strong>{{ $customer->name }}</strong></td>
                                            <td><small class="text-muted">{{ $customer->username }}</small></td>
                                            <td><small class="text-muted">{{ $customer->email }}</small></td>
                                            <td>
                                                @if($customer->priceList)
                                                    <span class="badge bg-info">{{ $customer->priceList->name }}</span>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updatePriceListModal{{ $customer->id }}">
                                                    <i data-feather="edit"></i> تعديل
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="users" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا يوجد عملاء</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $customers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@foreach($customers as $customer)
<!-- Modal تعديل قائمة الأسعار للعميل -->
<div class="modal fade" id="updatePriceListModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل قائمة الأسعار - {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customers.price-list.update', $customer) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">قائمة الأسعار</label>
                        <select name="price_list_id" class="form-select">
                            <option value="">— بدون قائمة —</option>
                            @foreach($priceLists as $list)
                                <option value="{{ $list->id }}" {{ $customer->price_list_id == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
