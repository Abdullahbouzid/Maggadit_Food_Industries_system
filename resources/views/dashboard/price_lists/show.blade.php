@extends('dashboard.partials.layout')

@section('title', $priceList->name)

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="dollar-sign" class="align-middle me-2"></i>
                            {{ $priceList->name }}
                        </h1>
                        <p class="text-muted">{{ $priceList->description }}</p>
                        <div class="mt-2">
                            @if($priceList->status === 'active')
                                <span class="badge bg-success">نشطة</span>
                            @else
                                <span class="badge bg-secondary">معطلة</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('price-lists.index') }}" class="btn btn-secondary btn-lg shadow-sm">
                            <i data-feather="arrow-right"></i> رجوع
                        </a>
                        <a href="{{ route('price-lists.versions.store', $priceList) }}" class="btn btn-primary btn-lg shadow-sm" onclick="event.preventDefault(); document.getElementById('create-version-form').submit();">
                            <i data-feather="plus-circle"></i> إنشاء تحديث جديد
                        </a>
                        <form id="create-version-form" action="{{ route('price-lists.versions.store', $priceList) }}" method="POST" class="d-none">
                            @csrf
                            <input type="hidden" name="effective_date" value="{{ date('Y-m-d') }}">
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <h5 class="mb-0">الإصدارات (التحديثات)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>تاريخ السريان</th>
                                        <th>الحالة</th>
                                        <th>المستخدم</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($priceList->versions as $version)
                                        <tr class="{{ $version->is_active ? 'table-success' : '' }}">
                                            <td class="ps-4 fw-semibold">{{ $version->id }}</td>
                                            <td><strong>{{ $version->effective_date->format('Y-m-d') }}</strong></td>
                                            <td>
                                                @if($version->is_active)
                                                    <span class="badge bg-success">نشط حالياً</span>
                                                @else
                                                    <span class="badge bg-secondary">سابق</span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $version->creator?->name ?? '—' }}</small></td>
                                            <td><small class="text-muted">{{ $version->created_at->format('Y-m-d H:i') }}</small></td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($version->is_active)
                                                        <a href="{{ route('price-lists.versions.edit', [$priceList, $version]) }}" class="btn btn-outline-warning" title="تعديل">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('price-lists.show', $priceList) }}?version={{ $version->id }}" class="btn btn-outline-info" title="عرض">
                                                            <i data-feather="eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="clock" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد إصدارات بعد</h5>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVersionModal">
                                                        <i data-feather="plus-circle"></i> إنشاء إصدار جديد
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <h5 class="mb-0">الأسعار الحالية (آخر إصدار)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>الصورة</th>
                                        <th>المنتج</th>
                                        <th>التصنيف</th>
                                        <th>العلامة التجارية</th>
                                        <th>نوع العبوة</th>
                                        <th>السعة</th>
                                        <th>السعر</th>
                                        <th>تاريخ آخر تعديل</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($versionItems as $item)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $item['id'] ?? '—' }}</td>
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
                                            <td><strong>{{ $item['name_ar'] ?: $item['name_en'] }}</strong></td>
                                            <td><small class="text-muted">{{ $item['category'] }}</small></td>
                                            <td><small class="text-muted">{{ $item['brand'] }}</small></td>
                                            <td><small class="text-muted">{{ $item['pack_type'] ?? 'قطعة' }}</small></td>
                                            <td><small class="text-muted">{{ $item['capacity'] ?? '—' }}</small></td>
                                            <td>
                                                @if($item['price'] !== null)
                                                    <span class="fw-bold text-success">{{ number_format($item['price'], 2) }} ر.س</span>
                                                @else
                                                    <span class="text-muted">غير مسجل</span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $item['effective_date'] }}</small></td>
                                            <td class="pe-4 text-end">
                                                @if($latestVersion && $latestVersion->is_active)
                                                    <a href="{{ route('price-lists.versions.edit', [$priceList, $latestVersion]) }}?item_id={{ $item['item_id'] }}&pack_id={{ $item['item_pack_id'] }}" class="btn btn-outline-warning btn-sm">
                                                        <i data-feather="edit"></i> تعديل
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="box" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد أسعار مسجلة</h5>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVersionModal">
                                                        <i data-feather="plus-circle"></i> إنشاء إصدار جديد
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal إنشاء إصدار جديد -->
<div class="modal fade" id="createVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إنشاء إصدار جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('price-lists.versions.store', $priceList) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">تاريخ السريان <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <small class="text-muted">سيتم نسخ الأسعار من آخر إصدار تلقائياً</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">إنشاء الإصدار</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($latestVersion && $latestVersion->is_active)
<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
        <h5 class="mb-0">إضافة منتجات جديدة للإصدار</h5>
    </div>
    <div class="card-body">
        <form id="add-item-form" method="POST" action="{{ route('price-lists.versions.update', [$priceList, $latestVersion]) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <select id="item-select" name="new_items[0][item_id]" class="form-select" required>
                    <option value="">اختر المنتج</option>
                    @foreach($itemsForSelect as $item)
                        <option value="{{ $item['id'] }}" data-packs="{{ json_encode($item['packs']) }}">
                            {{ $item['name_ar'] ?: $item['name_en'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="pack-select" name="new_items[0][item_pack_id]" class="form-select" disabled>
                    <option value="">اختر الوحدة</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="new_items[0][price]" class="form-control" placeholder="السعر" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">إضافة</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        const itemSelect = document.getElementById('item-select');
        const packSelect = document.getElementById('pack-select');

        if (itemSelect && packSelect) {
            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const packs = selectedOption.dataset.packs ? JSON.parse(selectedOption.dataset.packs) : [];

                packSelect.innerHTML = '<option value="">اختر الوحدة</option>';

                if (packs.length > 0) {
                    packs.forEach(function(pack) {
                        const option = document.createElement('option');
                        option.value = pack.id;
                        option.textContent = pack.name + (pack.uom ? ' (' + pack.uom + ')' : '');
                        packSelect.appendChild(option);
                    });
                    packSelect.disabled = false;
                } else {
                    packSelect.disabled = true;
                }
            });
        }
    });
</script>
@endpush
