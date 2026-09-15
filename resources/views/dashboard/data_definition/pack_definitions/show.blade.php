@extends('dashboard.partials.layout')

@section('title', 'تفاصيل تعريف الباكج')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل تعريف الباكج
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرض معلومات وتعريفات الباكج</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('pack-definitions.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> __('messages.dashboard.back')
        </a>
        <a href="تعديل
        " class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-"></i> تعديل
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-row">
    <div class="nova-col">
        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title">معلومات التعريف</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الاسم</label>
                            <p class="nova-fw-semibold">{{ $packDefinition->name_ar ?? $packDefinition->name_en ?? $packDefinition->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الكود</label>
                            <p><code>{{ $packDefinition->code ?? '-' }}</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title">مواصفات العبوة</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">نوع الباكج</label>
                            <p><span class="nova-badge info">{{ $packDefinition->packType?->name_ar ?? $packDefinition->packType?->name_en ?? '-' }}</span></p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">سعة الباكج</label>
                            <p>{{ $packDefinition->packCapacity?->value ?? '-' }} {{ $packDefinition->packCapacity?->uom?->code ?? '' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الكمية في العبوة</label>
                            <p class="nova-fw-bold">{{ $packDefinition->quantity_in_pack }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الباركود</label>
                            <p><code>{{ $packDefinition->barcode ?? '-' }}</code></p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">فاصل التسلسل</label>
                            <p>{{ $packDefinition->serial_separator ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">معامل المعادلة</label>
                            <p>{{ $packDefinition->equivalency_factor ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title">الأبعاد والوزن</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الارتفاع</label>
                            <p>{{ $packDefinition->height ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">العرض</label>
                            <p>{{ $packDefinition->width ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">العمق</label>
                            <p>{{ $packDefinition->depth ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="nova-col">
                        <div class="nova-form-group">
                            <label class="nova-label nova-text-muted">الوزن</label>
                            <p>{{ $packDefinition->weight ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nova-col">
        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-cog me-2"></i> الإعدادات</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-form-check nova-mb-2">
                    <input class="nova-form-check-input" type="checkbox" {{ $packDefinition->is_weight_priced ? 'checked' : '' }} disabled>
                    <label class="nova-form-check-label">تسعير بالوزن</label>
                </div>
                <div class="nova-form-check nova-mb-2">
                    <input class="nova-form-check-input" type="checkbox" {{ $packDefinition->allow_fraction_in_qty ? 'checked' : '' }} disabled>
                    <label class="nova-form-check-label">السماح بكسور في الكمية</label>
                </div>
                <div class="nova-form-check nova-mb-2">
                    <input class="nova-form-check-input" type="checkbox" {{ $packDefinition->is_default_pack ? 'checked' : '' }} disabled>
                    <label class="nova-form-check-label">باكج افتراضي</label>
                </div>
            </div>
        </div>

        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-tag me-2"></i> عمليات الباكج</h5>
            </div>
            <div class="nova-card-body">
                @forelse($packDefinition->packOperations as $operation)
                    <span class="nova-badge primary me-1 mb-1">{{ $operation->name_en ?? $operation->name_ar }}</span>
                @empty
                    <p class="nova-text-muted" style="font-size: 0.875rem;">لا توجد عمليات</p>
                @endforelse
            </div>
        </div>

        <div class="nova-card nova-mb-4">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-layer-group me-2"></i> مجموعات الباكج</h5>
            </div>
            <div class="nova-card-body">
                @forelse($packDefinition->packGroups as $group)
                    <span class="nova-badge success me-1 mb-1">{{ $group->name_en ?? $group->name_ar }}</span>
                @empty
                    <p class="nova-text-muted" style="font-size: 0.875rem;">لا توجد مجموعات</p>
                @endforelse
            </div>
        </div>

        <div class="nova-card">
            <div class="nova-card-body">
                <div class="nova-flex nova-flex-column nova-gap-2">
                    <a href="{{ route('pack-definitions.edit', $packDefinition) }}" class="nova-btn nova-btn-primary">
                        <i class="fas fa-edit"></i> تعديل
                    </a>
                    <button type="button" class="nova-btn nova-btn-danger" onclick="confirmDeletePackDef({{ $packDefinition->id }}, '{{ addslashes($packDefinition->name_ar ?? $packDefinition->name_en ?? $packDefinition->name ?? 'بدون اسم') }}')">
                        <i class="fas fa-trash-alt"></i> حذف
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
