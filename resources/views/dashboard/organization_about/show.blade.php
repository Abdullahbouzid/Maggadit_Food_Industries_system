@extends('dashboard.partials.layout')

@section('title', 'تفاصيل نبذة المنظمة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تفاصيل نبذة المنظمة
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرض كامل لمحتوى نبذة المنظمة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organization-about.edit', $organizationAbout) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('organization-about.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-row g-4">
            {{-- Organization --}}
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-building me-1"></i> المنظمة
                    </label>
                    <p class="nova-fw-semibold fs-5 mb-0">{{ $organizationAbout->organization->name ?? '—' }}</p>
                </div>
            </div>

            {{-- Title --}}
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-heading me-1"></i> العنوان الرئيسي
                    </label>
                    <p class="nova-fw-semibold fs-5 mb-0">{{ $organizationAbout->title ?? '—' }}</p>
                </div>
            </div>

            {{-- Description --}}
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-align-left me-1"></i> الوصف / القصة
                    </label>
                    <p class="nova-fw-semibold fs-6 mb-0">{{ $organizationAbout->description ?? '—' }}</p>
                </div>
            </div>

            {{-- Vision --}}
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-bullseye me-1"></i> الرؤية
                    </label>
                    <p class="nova-fw-semibold fs-5 mb-0">{{ $organizationAbout->vision ?? '—' }}</p>
                </div>
            </div>

            {{-- Mission --}}
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-target me-1"></i> الرسالة
                    </label>
                    <p class="nova-fw-semibold fs-5 mb-0">{{ $organizationAbout->mission ?? '—' }}</p>
                </div>
            </div>

            {{-- Values --}}
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-list me-1"></i> القيم الأساسية
                    </label>
                    @php
                        $values = $organizationAbout->values;
                        if (!is_array($values) && is_string($organizationAbout->values ?? null)) {
                            $values = json_decode($organizationAbout->values, true) ?: [];
                        }
                        $values = is_array($values) ? $values : [];
                    @endphp
                    @if(!empty($values))
                        <ul class="nova-list">
                            @foreach($values as $value)
                                <li>
                                    <i class="fas fa-check-circle nova-text-success me-2"></i> {{ $value }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="nova-text-muted">—</p>
                    @endif
                </div>
            </div>

            {{-- Statistics (المعدلة) --}}
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-chart-bar me-1"></i> الإحصائيات
                    </label>
                    @php
                        $stats = [];
                        if (!empty($organizationAbout->statistics)) {
                            $raw = is_array($organizationAbout->statistics) ? $organizationAbout->statistics : json_decode($organizationAbout->statistics, true);
                            if (is_array($raw)) {
                                $i = 0;
                                $count = count($raw);
                                while ($i < $count) {
                                    $item = $raw[$i];
                                    if (isset($item['key']) && isset($item['value'])) {
                                        $stats[] = ['key' => $item['key'], 'value' => $item['value']];
                                        $i++;
                                    } elseif (isset($item['key']) && !isset($item['value']) && isset($raw[$i+1]['value'])) {
                                        $stats[] = ['key' => $item['key'], 'value' => $raw[$i+1]['value']];
                                        $i += 2;
                                    } elseif (isset($item['value']) && !isset($item['key']) && isset($raw[$i-1]['key'])) {
                                        $i++;
                                    } else {
                                        $i++;
                                    }
                                }
                            }
                        }
                    @endphp
                    @if(!empty($stats))
                        <div class="nova-row g-3 mt-2">
                            @foreach($stats as $stat)
                                @php
                                    $key = $stat['key'] ?? '';
                                    $value = $stat['value'] ?? '';
                                    $icon = 'fas fa-chart-simple';
                                    if (str_contains(strtolower($key), 'عام') || str_contains(strtolower($key), 'سنة') || str_contains(strtolower($key), 'year')) $icon = 'fas fa-calendar-alt';
                                    elseif (str_contains(strtolower($key), 'منتج') || str_contains(strtolower($key), 'product')) $icon = 'fas fa-box';
                                    elseif (str_contains(strtolower($key), 'عميل') || str_contains(strtolower($key), 'customer')) $icon = 'fas fa-users';
                                    elseif (str_contains(strtolower($key), 'شريك') || str_contains(strtolower($key), 'partner')) $icon = 'fas fa-handshake';
                                    elseif (str_contains(strtolower($key), 'طلب') || str_contains(strtolower($key), 'order')) $icon = 'fas fa-shopping-cart';
                                @endphp
                                <div class="nova-col nova-col-md-3 nova-col-sm-6">
                                    <div class="nova-stat nova-text-center" style="padding: 1rem; background: var(--nova-surface-2); border-radius: 10px;">
                                        <div class="nova-stat-value" style="font-size: 1.5rem; color: var(--nova-primary); margin: 0;">
                                            {{ is_numeric($value) ? number_format($value) : $value }}
                                        </div>
                                        <div class="nova-stat-label nova-flex nova-items-center nova-justify-center nova-gap-1">
                                            <i class="{{ $icon }}" style="color: var(--nova-secondary);"></i>
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="nova-text-muted">—</p>
                    @endif
                </div>
            </div>

            {{-- Images --}}
            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-image me-1"></i> صورة الشعار
                    </label>
                    @if($organizationAbout->image_url)
                        <img src="{{ $organizationAbout->image_url }}" alt="الشعار" class="nova-object-fit-cover" style="max-height: 200px; width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    @else
                        <p class="nova-text-muted">—</p>
                    @endif
                </div>
            </div>

            <div class="nova-col nova-col-md-6">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-images me-1"></i> صورة الغلاف
                    </label>
                    @if($organizationAbout->cover_image_url)
                        <img src="{{ $organizationAbout->cover_image_url }}" alt="الغلاف" class="nova-object-fit-cover" style="max-height: 200px; width: 100%; border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                    @else
                        <p class="nova-text-muted">—</p>
                    @endif
                </div>
            </div>

            {{-- Status --}}
            <div class="nova-col nova-col-12">
                <div class="nova-form-group">
                    <label class="nova-label nova-text-uppercase small nova-fw-semibold">
                        <i class="fas fa-info-circle me-1"></i> الحالة
                    </label>
                    @if($organizationAbout->is_active)
                        <span class="nova-badge success fs-6">نشط</span>
                    @else
                        <span class="nova-badge secondary fs-6">غير نشط</span>
                    @endif
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="nova-col nova-col-12">
                <div class="nova-flex nova-justify-between nova-flex-wrap nova-gap-2" style="border-top: 1px solid var(--nova-border-light); padding-top: 1rem;">
                    <small class="nova-text-muted">
                        <i class="fas fa-clock me-1"></i> تاريخ الإنشاء: {{ $organizationAbout->created_at->format('Y-m-d H:i') }}
                    </small>
                    <small class="nova-text-muted">
                        <i class="fas fa-history me-1"></i> آخر تحديث: {{ $organizationAbout->updated_at->format('Y-m-d H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-body">
        <div class="nova-flex nova-gap-2">
            <a href="{{ route('organization-about.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('organization-about.edit', $organizationAbout) }}" class="nova-btn nova-btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmDelete({{ $organizationAbout->id }}, '{{ addslashes($organizationAbout->title ?? 'About Entry') }}')">
                <i class="fas fa-trash-alt"></i> حذف
            </button>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete <strong id="deleteName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/organization-about/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush

<style>
.nova-object-fit-cover {
    object-fit: cover;
}
</style>
