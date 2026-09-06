@extends('dashboard.partials.layout')

@section('title', 'عرض العرض')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="tag" class="align-middle me-2"></i>
                            {{ $offer->title ?? 'عرض' }}
                        </h1>
                        <p class="text-muted">عرض على: {{ $offer->item?->name_ar ?: $offer->item?->name_en }}</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('offers.edit', $offer) }}" class="btn btn-warning">
                            <i data-feather="edit"></i> تعديل
                        </a>
                        <a href="{{ route('offers.index') }}" class="btn btn-secondary">
                            <i data-feather="list"></i> القائمة
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                            <div class="card-header bg-primary text-white py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">تفاصيل العرض</h5>
                                    <span class="badge bg-{{ $offer->is_active && $offer->start_date <= now() && $offer->end_date >= now() ? 'success' : 'secondary' }}">
                                        {{ $offer->is_active && $offer->start_date <= now() && $offer->end_date >= now() ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-borderless mb-4">
                                    <tr>
                                        <th class="w-25">المنتج</th>
                                        <td>
                                            {{ $offer->item?->name_ar ?: $offer->item?->name_en }}
                                            @if($offer->item && $offer->item->packs->first() && $offer->item->packs->first()->packCapacity?->uom)
                                                <span class="badge bg-info ms-1">{{ $offer->item->packs->first()->packCapacity?->uom->name_ar ?? $offer->item->packs->first()->packCapacity?->uom->name_en }}</span>
                                            @endif
                                            @if($offer->itemPack)
                                                <br><small class="text-muted">العبوة: {{ $offer->itemPack->packCapacity?->uom->name_ar ?? $offer->itemPack->packCapacity?->uom->name_en ?? '' }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($offer->title)
                                        <tr>
                                            <th>العنوان</th>
                                            <td>{{ $offer->title }}</td>
                                        </tr>
                                    @endif
                                    @if($offer->description)
                                        <tr>
                                            <th>الوصف</th>
                                            <td>{{ $offer->description }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>السعر القديم</th>
                                        <td><s class="text-muted">{{ number_format($offer->old_price, 2) }} ر.س</s></td>
                                    </tr>
                                    <tr>
                                        <th>السعر الجديد</th>
                                        <td class="fw-bold text-success">{{ number_format($offer->new_price, 2) }} ر.س</td>
                                    </tr>
                                    <tr>
                                        <th>نسبة الخصم</th>
                                        <td><span class="badge bg-danger">{{ $offer->formatted_discount ?? number_format($offer->discount_percentage, 0) . '%' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ البداية</th>
                                        <td>{{ $offer->start_date->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ النهاية</th>
                                        <td>{{ $offer->end_date->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>

                                @if($offer->image)
                                    <div class="text-center">
                                        <img src="{{ asset('storage/' . $offer->image) }}" alt="صورة العرض" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                                    </div>
                                @elseif($offer->item && $offer->item->main_image_url)
                                    <div class="text-center">
                                        <img src="{{ $offer->item->main_image_url }}" alt="صورة المنتج" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                                    </div>
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
    if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
