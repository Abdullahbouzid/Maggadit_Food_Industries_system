@extends('dashboard.partials.layout')

@section('title', 'إدارة العروض')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="tag" class="align-middle me-2"></i>
                            إدارة العروض
                        </h1>
                        <p class="text-muted">إنشاء وإدارة العروض على المنتجات مع تحديد الفترة الزمنية</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('offers.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة عرض
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
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث عن عرض..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i data-feather="search"></i></button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل الحالات</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشطة</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                                </select>
                            </div>
                            @if(request()->hasAny(['search', 'status']))
                                <div class="col-md-2">
                                    <a href="{{ route('offers.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                            <th>الصورة</th>
                                            <th>المنتج</th>
                                            <th>الوحدة</th>
                                            <th>السعر القديم</th>
                                            <th>السعر الجديد</th>
                                            <th>الخصم</th>
                                            <th>الفترة</th>
                                            <th>الحالة</th>
                                            <th class="pe-4 text-end">إجراءات</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    @forelse($offers as $offer)
                                        <tr class="{{ $offer->is_active && $offer->start_date <= now() && $offer->end_date >= now() ? '' : 'table-secondary' }}">
                                            <td class="ps-4 fw-semibold">{{ $offer->id }}</td>
                                            <td>
                                                @if($offer->image)
                                                    <img src="{{ asset('storage/' . $offer->image) }}" alt="صورة العرض" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @elseif($offer->item && $offer->item->main_image_url)
                                                    <img src="{{ $offer->item->main_image_url }}" alt="صورة المنتج" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <span class="text-muted">🖼️</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $offer->item?->name_ar ?: $offer->item?->name_en }}</strong>
                                                @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                                                    <br><small class="text-muted">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</small>
                                                @endif
                                                @if($offer->itemPack)
                                                    <br><small class="text-muted">العبوة: {{ $offer->itemPack->packCapacity?->uom->name_ar ?? $offer->itemPack->packCapacity?->uom->name_en ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                                                    <span class="badge bg-info">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td><s class="text-muted">{{ number_format($offer->old_price, 2) }}</s></td>
                                            <td class="fw-bold text-success">{{ number_format($offer->new_price, 2) }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $offer->formatted_discount ?? number_format($offer->discount_percentage, 0) . '%' }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $offer->start_date->format('Y-m-d') }}<br>
                                                    إلى {{ $offer->end_date->format('Y-m-d') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($offer->is_active && $offer->start_date <= now() && $offer->end_date >= now())
                                                    <span class="badge bg-success">نشط</span>
                                                @elseif(!$offer->is_active)
                                                    <span class="badge bg-secondary">معطل</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">منتهي</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('offers.show', $offer) }}" class="btn btn-outline-info" title="عرض"><i data-feather="eye"></i></a>
                                                    <a href="{{ route('offers.edit', $offer) }}" class="btn btn-outline-warning" title="تعديل"><i data-feather="edit"></i></a>
                                                    <form action="{{ route('offers.destroy', $offer) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا العرض؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف"><i data-feather="trash-2"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="tag" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد عروض</h5>
                                                    <a href="{{ route('offers.create') }}" class="btn btn-primary">
                                                        <i data-feather="plus-circle"></i> إضافة عرض
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
                        {{ $offers->links('pagination::bootstrap-5') }}
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
