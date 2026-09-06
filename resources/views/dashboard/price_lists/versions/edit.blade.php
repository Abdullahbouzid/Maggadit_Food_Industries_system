@extends('dashboard.partials.layout')

@section('title', 'تعديل إصدار - ' . $priceList->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit" class="align-middle me-2"></i>
                            تعديل إصدار - {{ $priceList->name }}
                        </h1>
                        <p class="text-muted">تاريخ السريان: {{ $version->effective_date->format('Y-m-d') }}</p>
                        @if($version->is_active)
                            <span class="badge bg-success">نشط حالياً</span>
                        @else
                            <span class="badge bg-secondary">سابق</span>
                        @endif
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('price-lists.show', $priceList) }}" class="btn btn-secondary btn-lg shadow-sm">
                            <i data-feather="arrow-right"></i> رجوع
                        </a>
                        <button type="submit" form="version-form" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="save"></i> حفظ الإصدار
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="version-form" method="POST" action="{{ route('price-lists.versions.update', [$priceList, $version]) }}">
                    @csrf
                    @method('PUT')
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>الصورة</th>
                                            <th>المنتج</th>
                                            <th>نوع العبوة</th>
                                            <th>السعة</th>
                                            <th>السعر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $counter = 1;
                                        @endphp
                                        @forelse($versionItems as $item)
                                            <tr>
                                                <td class="ps-4 fw-semibold">{{ $counter++ }}</td>
                                                <td>
                                                    @php
                                                        $itemModel = \App\Models\Item::find($item['item_id']);
                                                        $imageUrl = $itemModel ? ($itemModel->main_image_url ?? null) : null;
                                                    @endphp
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item['name_ar'] }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <span class="text-muted">🖼️</span>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $item['name_ar'] ?: $item['name_en'] }}</strong>
                                                    @if($item['is_new'] ?? false)
                                                        <span class="badge bg-warning ms-1">جديد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $item['pack_type'] ?? 'قطعة' }}</small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $item['capacity'] ?? '—' }}</small>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][price]"
                                                           class="form-control form-control-sm"
                                                           style="width: 120px; display: inline-block;"
                                                           value="{{ $item['price'] }}"
                                                           step="0.01"
                                                           min="0"
                                                           required>
                                                    <input type="hidden" name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][item_id]" value="{{ $item['item_id'] }}">
                                                    <input type="hidden" name="items[{{ $item['item_id'] }}_{{ $item['item_pack_id'] ?? 'null' }}][item_pack_id]" value="{{ $item['item_pack_id'] }}">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="py-4">
                                                        <i data-feather="box" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                        <h5 class="text-muted">لا توجد منتجات في هذا الإصدار</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
