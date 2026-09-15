@extends('dashboard.partials.layout')

@section('title', 'تعريفات الباكجات')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعريفات الباكجات (تعريفات باك)
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة وتعريف أنواع الباكجات القابلة لإعادة الاستخدام للمنتجات</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-operations.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-cog"></i> إدارة أنواع الباكجات
        </a>
        <a href="{{ route('pack-definitions.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة تعريف باكج جديد
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <div class="nova-flex nova-items-center nova-justify-between">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة تعريفات الباكجات ({{ $total }})
            </h3>
            <form method="GET" action="{{ route('pack-definitions.index') }}" class="nova-flex nova-gap-2" style="align-items: end;">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <form method="GET" action="{{ route('pack-definitions.index') }}" id="filterForm"><input type="text" name="search" class="nova-input" placeholder="بحث بالاسم، الكود، الباركود..." value="{{ request('search') }}" style="width: 240px;">
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <label class="nova-label">{{ __('messages.filter.per_page') }}</label>
                    <select name="per_page" class="nova-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.filter.all') }}</option>
                    </select>
                </div>
                <div class="nova-flex nova-gap-2" style="height: fit-content;">
                    <button type="submit" class="nova-btn nova-btn-primary">{{ __('messages.filter.apply') }}</button>
                    @if(request('search') || request('per_page'))
                        <a href="{{ route('pack-definitions.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                    @endif
                </div>
            </form>
                </div>
                <div class="nova-form-group" style="margin-bottom: 0;">
                    <select name="pack_type_id" class="nova-select" style="width: auto;">
                        <option value="">{{ __('messages.filter.all') }}</option>
                        @foreach($packTypes as $type)
                            <option value="{{ $type->id }}" {{ request('pack_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en ?? $type->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="height: fit-content;">
                    <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">{{ __('messages.filter.apply') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الكود</th>
                        <th>نوع الباكج</th>
                        <th>السعة</th>
                        <th>الكمية/عبوة</th>
                        <th>الباركود</th>
                        <th>الأبعاد (H×W×D)</th>
                        <th>الوزن</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packDefinitions as $packDef)
                    @php
                        $packType = $packDef->packType;
                        $packCapacity = $packDef->packCapacity;
                        $packTypeName = $packType?->name_ar ?? $packType?->name_en ?? '-';
                        $packCapacityValue = $packCapacity?->value ?? '-';
                        $packCapacityUom = $packCapacity?->uom?->code ?? '';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="nova-fw-semibold">{{ $packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? 'بدون اسم' }}</span>
                            @if($packDef->description)
                                <br><small class="nova-text-muted">{{ Str::limit($packDef->description, 50) }}</small>
                            @endif
                        </td>
                        <td><code>{{ $packDef->code ?? '-' }}</code></td>
                        <td><span class="nova-badge secondary">{{ $packTypeName }}</span></td>
                        <td>{{ $packCapacityValue }} {{ $packCapacityUom }}</td>
                        <td><span class="nova-badge primary">{{ $packDef->quantity_in_pack }}</span></td>
                        <td><code>{{ $packDef->barcode ?? '-' }}</code></td>
                        <td>
                            @if($packDef->height || $packDef->width || $packDef->depth)
                                {{ $packDef->height }}×{{ $packDef->width }}×{{ $packDef->depth }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $packDef->weight ?? '-' }}</td>
                        <td class="nova-text-end">
                            <div class="nova-table-actions">
                                <a href="{{ route('pack-definitions.edit', $packDef) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="{{ __('messages.modal.delete') }}" onclick="confirmDeletePackDef({{ $packDef->id }}, '{{ addslashes($packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? 'بدون اسم') }}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="nova-empty">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h3 class="nova-empty-title">{{ __('messages.dashboard.no_pack_definitions') }}</h3>
                                <p class="nova-empty-text">لا توجد تعريفات باكجات. قم بإنشاء أول تعريف.</p>
                                <a href="{{ route('pack-definitions.create') }}" class="nova-btn nova-btn-primary">
                                    <i class="fas fa-plus"></i> Create Pack Definition
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="nova-text-center nova-py-3">
            {{ $packDefinitions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- __('messages.modal.delete') تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deletePackDefModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">{{ __('messages.dashboard.confirm_deletion') }}</h4>
            <p class="nova-delete-modal-text">{{ __('messages.modal.are_you_sure') }} pack definition <strong id="deletePackDefName" class="nova-text-danger"></strong>?<br>{{ __('messages.modal.irreversible') }}.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deletePackDefModal').classList.remove('show')">{{ __('messages.dashboard.cancel') }}</button>
                <form id="deletePackDefForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.dashboard.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDeletePackDef = function(id, name) {
        document.getElementById('deletePackDefName').innerText = name;
        document.getElementById('deletePackDefForm').action = '/pack-definitions/' + id;
        document.getElementById('deletePackDefModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorالكل('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
