@extends('dashboard.partials.layout')

@section('title', 'Pack Capacities')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-box" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Pack Capacities
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة سعات الباكجات (مثال: 500g، 1kg، 250ml)</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-capacities.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة سعة باكج جديدة
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-header">
        <div class="nova-flex nova-items-center nova-justify-between">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة سعات الباكجات ({{ $total }})
            </h3>
            <form method="GET" action="{{ route('pack-capacities.index') }}" class="nova-flex nova-gap-2">
                <form method="GET" action="{{ route('pack-capacities.index') }}" id="filterForm"><input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.search') }}" value="{{ request('search') }}" style="width: 200px;">
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
                        <a href="{{ route('pack-capacities.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                    @endif
                </div>
            </form>
                <select name="uom_id" class="nova-select" style="width: auto;">
                    <option value="">{{ __('messages.filter.all') }}</option>
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->id }}" {{ request('uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->name_en ?? $uom->name_ar }}</option>
                    @endforeach
                </select>
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">{{ __('messages.filter.apply') }}</button>
            </form>
        </div>
    </div>
    <div class="nova-card-body nova-p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>القيمة</th>
                        <th>وحدة القياس</th>
                        <th>الاسم (إنجليزي)</th>
                        <th>الاسم (عربي)</th>
                        <th>الوصف</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packCapacities as $capacity)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="nova-fw-bold">{{ $capacity->value }}</span></td>
                            <td>
                                <span class="nova-badge info">{{ $capacity->uom->name_en ?? $capacity->uom->name_ar ?? '-' }}</span>
                                <small class="nova-text-muted nova-block">{{ $capacity->uom->uom_symbol ?? '' }}</small>
                            </td>
                            <td>{{ $capacity->name_en ?? '-' }}</td>
                            <td>{{ $capacity->name_ar ?? '-' }}</td>
                            <td>{{ $capacity->description ?? '-' }}</td>
                            <td class="nova-text-end">
                                <div class="nova-table-actions">
                                    <a href="{{ route('pack-capacities.edit', $capacity) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذفCapacity({{ $capacity->id }}, '{{ addslashes($capacity->name_en ?? $capacity->name_ar ?? $capacity->value) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="nova-text-center" style="padding: 2rem;">
                                <div>
                                    <div class="nova-stat-icon secondary" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h5 class="nova-text-muted">لا توجد سعات باكجات. قم بإنشاء أول سعة.</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="nova-text-center nova-py-3">
            {{ $packCapacities->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deleteCapacityModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete pack capacity <strong id="deleteCapacityName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteCapacityModal').classList.remove('show')">إلغاء</button>
                <form id="deleteCapacityForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
});

window.confirmحذفCapacity = function(id, name) {
    document.getElementById('deleteCapacityName').innerText = name;
    document.getElementById('deleteCapacityForm').action = '/pack-capacities/' + id;
    document.getElementById('deleteCapacityModal').classList.add('show');
};
</script>
@endpush
