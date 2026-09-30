@extends('dashboard.partials.layout')

@section('title', $priceList->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-dollar-sign" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            {{ $priceList->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">{{ $priceList->description }}</p>
        <div class="nova-flex nova-items-center nova-gap-2 nova-mt-2">
            @if($priceList->status === 'active')
                <span class="nova-badge success">نشطة</span>
            @else
                <span class="nova-badge secondary">معطلة</span>
            @endif
        </div>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('price-lists.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button type="submit" form="create-version-form" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إنشاء تحديث جديد
        </button>
        <form id="create-version-form" action="{{ route('price-lists.versions.store', $priceList) }}" method="POST" class="nova-d-none">
            @csrf
            <input type="hidden" name="effective_date" value="{{ date('Y-m-d') }}">
        </form>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title">الإصدارات (التحديثات)</h5>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th class="nova-text-start">#</th>
                        <th>تاريخ السريان</th>
                        <th>الحالة</th>
                        <th>المستخدم</th>
                        <th>تاريخ الإنشاء</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceList->versions as $version)
                        <tr>
                            <td class="nova-fw-semibold">{{ $version->id }}</td>
                            <td><span class="nova-fw-bold">{{ $version->effective_date->format('Y-m-d') }}</span></td>
                            <td>
                                @if($version->is_active)
                                    <span class="nova-badge success">نشط حالياً</span>
                                @else
                                    <span class="nova-badge secondary">سابق</span>
                                @endif
                            </td>
                            <td><small class="nova-text-muted">{{ $version->creator?->name ?? '—' }}</small></td>
                            <td><small class="nova-text-muted">{{ $version->created_at->format('Y-m-d H:i') }}</small></td>
                            <td class="nova-text-end">
                                @if($version->is_active)
                                    <a href="{{ route('price-lists.versions.edit', [$priceList, $version]) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <a href="{{ route('price-lists.show', $priceList) }}?version={{ $version->id }}" class="nova-icon-btn nova-btn-sm" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="nova-text-center" style="padding: 2rem;">
                                <div>
                                    <div class="nova-stat-icon secondary" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h5 class="nova-text-muted">لا توجد إصدارات بعد</h5>
                                    <button type="button" class="nova-btn nova-btn-primary nova-mt-2" onclick="document.getElementById('createVersionModal').classList.add('show')">
                                        <i class="fas fa-plus-circle"></i> إنشاء إصدار جديد
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

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="nova-card-title">الأسعار الحالية (آخر إصدار)</h5>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th class="nova-text-start">#</th>
                        <th>الصورة</th>
                        <th>المنتج</th>
                        <th>التصنيف</th>
                        <th>العلامة التجارية</th>
                        <th>نوع العبوة</th>
                        <th>السعة</th>
                        <th>السعر</th>
                        <th>تاريخ آخر تعديل</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versionItems as $item)
                        @php
                            $itemModel = \App\Models\Item::find($item['item_id']);
                            $imageUrl = $itemModel ? ($itemModel->main_image_url ?? null) : null;
                        @endphp
                        <tr>
                            <td class="nova-fw-semibold">{{ $item['id'] ?? '—' }}</td>
                            <td>
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item['name_ar'] }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="nova-flex nova-items-center nova-justify-center" style="width: 40px; height: 40px; background: var(--nova-surface-3); border-radius: 8px;">
                                        <span class="nova-text-muted">🖼️</span>
                                    </div>
                                @endif
                            </td>
                            <td><span class="nova-fw-bold">{{ $item['name_ar'] ?: $item['name_en'] }}</span></td>
                            <td><small class="nova-text-muted">{{ $item['category'] }}</small></td>
                            <td><small class="nova-text-muted">{{ $item['brand'] }}</small></td>
                            <td><small class="nova-text-muted">{{ $item['pack_type'] ?? 'قطعة' }}</small></td>
                            <td><small class="nova-text-muted">{{ $item['capacity'] ?? '—' }}</small></td>
                            <td>
                                @if($item['price'] !== null)
                                    <span class="nova-fw-bold" style="color: #16a34a;">{{ number_format($item['price'], 2) }} ر.س</span>
                                @else
                                    <span class="nova-text-muted">غير مسجل</span>
                                @endif
                            </td>
                            <td><small class="nova-text-muted">{{ $item['effective_date'] }}</small></td>
                            <td class="nova-text-end">
                                @if($latestVersion && $latestVersion->is_active)
                                    <a href="{{ route('price-lists.versions.edit', [$priceList, $latestVersion]) }}?item_id={{ $item['item_id'] }}&pack_id={{ $item['item_pack_id'] }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="nova-text-center" style="padding: 2rem;">
                                <div>
                                    <div class="nova-stat-icon secondary" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <h5 class="nova-text-muted">لا توجد أسعار مسجلة</h5>
                                    <button type="button" class="nova-btn nova-btn-primary nova-mt-2" onclick="document.getElementById('createVersionModal').classList.add('show')">
                                        <i class="fas fa-plus-circle"></i> إنشاء إصدار جديد
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

@if($latestVersion && $latestVersion->is_active)
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title">إضافة منتجات جديدة للإصدار</h5>
    </div>
    <div class="nova-card-body">
        <form id="add-item-form" method="POST" action="{{ route('price-lists.versions.update', [$priceList, $latestVersion]) }}" class="nova-row nova-gap-3">
            @csrf
            @method('PUT')
            <div class="nova-col">
                <div class="nova-form-group">
                    <select id="item-select" name="new_items[0][item_id]" class="nova-select" required>
                        <option value="">اختر المنتج</option>
                        @foreach($itemsForSelect as $item)
                            <option value="{{ $item['id'] }}" data-packs="{{ json_encode($item['packs']) }}">
                                {{ $item['name_ar'] ?: $item['name_en'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="nova-col">
                <div class="nova-form-group">
                    <select id="pack-select" name="new_items[0][item_pack_id]" class="nova-select" disabled>
                        <option value="">اختر الوحدة</option>
                    </select>
                </div>
            </div>
            <div class="nova-col">
                <div class="nova-form-group">
                    <input type="number" name="new_items[0][price]" class="nova-input" placeholder="السعر" step="0.01" min="0" required>
                </div>
            </div>
            <div class="nova-col nova-flex nova-items-end">
                <button type="submit" class="nova-btn nova-btn-primary">إضافة</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Create Version Modal --}}
<div class="nova-modal-backdrop" id="createVersionModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-modal-header">
            <h3 class="nova-modal-title">إنشاء إصدار جديد</h3>
            <button class="nova-modal-close" onclick="document.getElementById('createVersionModal').classList.remove('show')" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('price-lists.versions.store', $priceList) }}">
            @csrf
            <div class="nova-modal-body">
                <div class="nova-form-group">
                    <label class="nova-label">تاريخ السريان <span class="nova-text-danger">*</span></label>
                    <input type="date" name="effective_date" class="nova-input" value="{{ date('Y-m-d') }}" required>
                    <small class="nova-help-text">سيتم نسخ الأسعار من آخر إصدار تلقائياً</small>
                </div>
            </div>
            <div class="nova-modal-footer">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('createVersionModal').classList.remove('show')">إلغاء</button>
                <button type="submit" class="nova-btn nova-btn-primary">إنشاء الإصدار</button>
            </div>
        </form>
    </div>
</div>

{{-- ⛔️ تم حذف البطاقة المكررة "إضافة منتجات جديدة للإصدار" من هنا --}}

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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