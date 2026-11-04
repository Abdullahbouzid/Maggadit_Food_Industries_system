@extends('dashboard.partials.layout')

@section('title', 'تفاصيل المنطقة: ' . $area->code)

@section('content')
{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            المنطقة: {{ $area->code }}
        </h2>
        <p class="nova-page-subtitle">عرض تفصيلي للمنطقة الجغرافية</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('areas.edit', $area) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('areas.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> عودة للقائمة
        </a>
    </div>
</div>

{{-- ===== Alerts ===== --}}
@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- ===== Area Details ===== --}}
<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="nova-fw-bold mb-0">
            <i class="fas fa-file-alt me-1"></i> تفاصيل المنطقة
        </h5>
    </div>
    <div class="nova-card-body">
        <table class="nova-table">
            <tbody>
                <tr>
                    <th style="width: 180px;">الكود</th>
                    <td><code>{{ $area->code }}</code></td>
                </tr>
                <tr>
                    <th>الاسم (إنجليزي)</th>
                    <td>{{ $area->name_en }}</td>
                </tr>
                <tr>
                    <th>الاسم (عربي)</th>
                    <td>{{ $area->name_ar }}</td>
                </tr>
                <tr>
                    <th>الولاية</th>
                    <td>{{ $area->state?->name_en ?? '—' }}</td>
                </tr>
                <tr>
                    <th>الوصف</th>
                    <td>{{ $area->description ?? '—' }}</td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($area->is_active)
                            <span class="nova-badge success">نشط</span>
                        @else
                            <span class="nova-badge secondary">غير نشط</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ترتيب الفرز</th>
                    <td>{{ $area->sort_order }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>{{ $area?->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>{{ $area?->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <a href="{{ route('areas.edit', $area) }}" class="nova-btn nova-btn-warning">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('areas.index') }}" class="nova-btn nova-btn-secondary">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>
</div>
@endsection
