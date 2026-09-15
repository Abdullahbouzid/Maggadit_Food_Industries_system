@extends('dashboard.partials.layout')

@section('title', 'Pack Types')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-layer-group" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            Pack Types
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">إدارة أنواع الباكجات (صندوق 3×3، قطعة، إلخ)</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-types.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus-circle"></i> إضافة نوع باكج جديد
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-header">
        <div class="nova-flex nova-items-center nova-justify-between">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                قائمة أنواع الباكجات ({{ $total }})
            </h3>
            <form method="GET" action="{{ route('pack-types.index') }}" class="nova-flex nova-gap-2">
                <form method="GET" action="{{ route('pack-types.index') }}" id="filterForm"><input type="text" name="search" class="nova-input" placeholder="{{ __('messages.filter.search') }}" value="{{ request('search') }}" style="width: 220px;">
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
                        <a href="{{ route('pack-types.index') }}" class="nova-btn nova-btn-outline">{{ __('messages.filter.reset') }}</a>
                    @endif
                </div>
            </form>
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
                        <th>الاسم</th>
                        <th>الاسم (إنجليزي)</th>
                        <th>الكود</th>
                        <th>عدد القطع</th>
                        <th>الوصف</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packTypes as $packType)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="nova-fw-bold">{{ $packType->name_ar ?? $packType->name ?? '-' }}</span></td>
                            <td>{{ $packType->name_en ?? '-' }}</td>
                            <td><code>{{ $packType->code ?? '-' }}</code></td>
                            <td>{{ $packType->pieces_per_pack ?? '-' }}</td>
                            <td>{{ $packType->description ?? '-' }}</td>
                            <td class="nova-text-end">
                                <div class="nova-table-actions">
                                    <a href="{{ route('pack-types.edit', $packType) }}" class="nova-icon-btn nova-btn-sm" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" title="حذف" onclick="confirmحذفPackType({{ $packType->id }}, '{{ addslashes($packType->name_ar ?? $packType->name ?? 'بدون اسم') }}')">
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
                                    <h5 class="nova-text-muted">لا توجد أنواع باكجات. قم بإنشاء أول نوع.</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="nova-text-center nova-py-3">
            {{ $packTypes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- حذف تأكيدation Modal (لاva) --}}
<div class="nova-modal-backdrop" id="deletePackTypeModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">تأكيد الحذف</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete pack type <strong id="deletePackTypeName" class="nova-text-danger"></strong>?<br>لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deletePackTypeModal').classList.remove('show')">إلغاء</button>
                <form id="deletePackTypeForm" method="POST" action="">
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

window.confirmحذفPackType = function(id, name) {
    document.getElementById('deletePackTypeName').innerText = name;
    document.getElementById('deletePackTypeForm').action = '/pack-types/' + id;
    document.getElementById('deletePackTypeModal').classList.add('show');
};
</script>
@endpush
