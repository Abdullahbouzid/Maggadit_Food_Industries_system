@extends('dashboard.partials.layout')

@section('title', 'تفاصيل الفرع: ' . $branch->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-store" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل الفرع: {{ $branch->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            <i class="fas fa-user"></i> {{ $branch->customer->name }}
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('branches.edit', $branch) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('branches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- ============================================
     Quick Stats Cards
     ============================================ --}}
<div class="branch-stats-grid">
    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-{{ $branch->is_active ? 'success' : 'muted' }}">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">الحالة</span>
            <span class="branch-stat-value">
                <span class="branches-status {{ $branch->is_active ? 'branches-status-active' : 'branches-status-inactive' }}">
                    <span class="branches-status-dot"></span>
                    {{ $branch->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-primary">
            <i class="fas fa-phone"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">الهاتف</span>
            <span class="branch-stat-value" dir="ltr">
                {{ $branch->phone ?: '—' }}
            </span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">توقيت العمل</span>
            <span class="branch-stat-value">
                @if($branch->working_hours_from && $branch->working_hours_to)
                    {{ \Carbon\Carbon::parse($branch->working_hours_from)->format('H:i') }}
                    —
                    {{ \Carbon\Carbon::parse($branch->working_hours_to)->format('H:i') }}
                @else
                    —
                @endif
            </span>
        </div>
    </div>

    <div class="branch-stat-card">
        <div class="branch-stat-icon branch-stat-icon-info">
            <i class="fas fa-user"></i>
        </div>
        <div class="branch-stat-body">
            <span class="branch-stat-label">العميل</span>
            <a href="{{ route('customers.show', $branch->customer) }}" class="branch-stat-value branch-stat-link">
                {{ $branch->customer->name }}
            </a>
        </div>
    </div>
</div>

{{-- ============================================
     Info + Map (two columns)
     ============================================ --}}
<div class="branch-detail-grid">
    {{-- Info Card --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-info-circle me-1"></i>
                معلومات الفرع
            </h3>
        </div>
        <div class="nova-card-body" style="padding: 0;">
            <table class="branch-info-table">
                <tbody>
                    <tr>
                        <th>
                            <i class="fas fa-store"></i> اسم الفرع
                        </th>
                        <td class="branch-info-strong">{{ $branch->name }}</td>
                    </tr>
                    <tr>
                        <th>
                            <i class="fas fa-user"></i> العميل
                        </th>
                        <td>
                            <a href="{{ route('customers.show', $branch->customer) }}" class="branch-info-link">
                                {{ $branch->customer->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <i class="fas fa-phone"></i> الهاتف
                        </th>
                        <td dir="ltr" style="text-align: start;">
                            {{ $branch->phone ?: '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <i class="fas fa-map-marker-alt"></i> العنوان
                        </th>
                        <td>
                            {{ $branch->address ?: '—' }}
                        </td>
                    </tr>
                    @if($branch->latitude && $branch->longitude)
                        <tr>
                            <th>
                                <i class="fas fa-crosshairs"></i> الإحداثيات
                            </th>
                            <td>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $branch->latitude }},{{ $branch->longitude }}"
                                   target="_blank"
                                   class="branch-info-link"
                                   dir="ltr"
                                   style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                    {{ $branch->latitude }}, {{ $branch->longitude }}
                                </a>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <th>
                            <i class="fas fa-clock"></i> توقيت العمل
                        </th>
                        <td>
                            @if($branch->working_hours_from && $branch->working_hours_to)
                                {{ \Carbon\Carbon::parse($branch->working_hours_from)->format('H:i') }}
                                —
                                {{ \Carbon\Carbon::parse($branch->working_hours_to)->format('H:i') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <i class="fas fa-calendar-plus"></i> تاريخ الإنشاء
                        </th>
                        <td class="branch-info-muted">
                            {{ $branch->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <i class="fas fa-calendar-check"></i> آخر تحديث
                        </th>
                        <td class="branch-info-muted">
                            {{ $branch->updated_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Map Card --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-map-marked-alt me-1"></i>
                الموقع على الخريطة
            </h3>
            @if($branch->latitude && $branch->longitude)
                <div class="nova-card-actions">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $branch->latitude }},{{ $branch->longitude }}"
                       target="_blank"
                       class="nova-btn nova-btn-outline nova-btn-sm">
                        <i class="fas fa-external-link-alt"></i> فتح في خرائط جوجل
                    </a>
                </div>
            @endif
        </div>
        <div class="nova-card-body" style="padding: 0;">
            @if($branch->latitude && $branch->longitude)
                <div id="branchMap" class="branch-map"></div>
            @else
                <div class="branch-map-empty">
                    <div class="branch-map-empty-icon">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                    <h4 class="branch-map-empty-title">لا توجد إحداثيات</h4>
                    <p class="branch-map-empty-text">لم يتم تسجيل موقع جغرافي لهذا الفرع بعد</p>
                    <a href="{{ route('branches.edit', $branch) }}" class="nova-btn nova-btn-outline nova-btn-sm">
                        <i class="fas fa-location-crosshairs"></i> إضافة الموقع
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================
       Quick Stats Cards
       ============================================ */
    .branch-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .branch-stat-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid var(--nova-border, #e2e8f0);
        border-radius: var(--nova-radius-md, 0.75rem);
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .branch-stat-card:hover {
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .branch-stat-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .branch-stat-icon-primary { background: #dbeafe; color: #1d4ed8; }
    .branch-stat-icon-success { background: #dcfce7; color: #15803d; }
    .branch-stat-icon-warning { background: #fef3c7; color: #b45309; }
    .branch-stat-icon-info    { background: #e0f2fe; color: #0369a1; }
    .branch-stat-icon-muted   { background: #f1f5f9; color: #64748b; }

    .branch-stat-body {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .branch-stat-label {
        font-size: 0.75rem;
        color: var(--nova-text-muted, #64748b);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .branch-stat-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--nova-text, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .branch-stat-link {
        text-decoration: none;
        color: var(--nova-primary, #2563eb);
        transition: color 0.15s;
    }

    .branch-stat-link:hover {
        text-decoration: underline;
    }

    /* ============================================
       Detail Grid (Info + Map)
       ============================================ */
    .branch-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    /* ============================================
       Info Table
       ============================================ */
    .branch-info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .branch-info-table tbody tr {
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
    }

    .branch-info-table tbody tr:last-child {
        border-bottom: none;
    }

    .branch-info-table th,
    .branch-info-table td {
        padding: 0.9rem 1.25rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .branch-info-table th {
        text-align: start;
        width: 35%;
        background: var(--nova-bg-soft, #f8fafc);
        color: var(--nova-text-muted, #475569);
        font-weight: 600;
        white-space: nowrap;
        border-inline-end: 1px solid var(--nova-border, #e2e8f0);
    }

    .branch-info-table th i {
        width: 18px;
        margin-inline-end: 0.5rem;
        color: var(--nova-primary, #2563eb);
    }

    .branch-info-table td {
        color: var(--nova-text, #1e293b);
    }

    .branch-info-strong {
        font-weight: 700;
        color: var(--nova-text, #0f172a) !important;
    }

    .branch-info-muted {
        color: var(--nova-text-muted, #64748b) !important;
        font-size: 0.85rem !important;
    }

    .branch-info-link {
        color: var(--nova-primary, #2563eb);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s;
    }

    .branch-info-link:hover {
        color: var(--nova-primary-dark, #1d4ed8);
        text-decoration: underline;
    }

    /* ============================================
       Status Badge (re-used from index)
       ============================================ */
    .branches-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .branches-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .branches-status-active   { background: #dcfce7; color: #166534; }
    .branches-status-inactive { background: #f1f5f9; color: #64748b; }

    /* ============================================
       Map
       ============================================ */
    .branch-map {
        width: 100%;
        height: 420px;
        border-radius: 0 0 var(--nova-radius-md, 0.75rem) var(--nova-radius-md, 0.75rem);
    }

    .branch-map-empty {
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .branch-map-empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--nova-bg-soft, #f1f5f9);
        color: var(--nova-text-muted, #94a3b8);
        font-size: 1.85rem;
    }

    .branch-map-empty-title {
        margin: 0 0 0.35rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--nova-text, #1e293b);
    }

    .branch-map-empty-text {
        margin: 0 0 1.25rem;
        color: var(--nova-text-muted, #64748b);
        font-size: 0.875rem;
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 1024px) {
        .branch-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .branch-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .branch-stats-grid {
            grid-template-columns: 1fr;
        }

        .branch-map {
            height: 300px;
        }

        .branch-info-table th {
            width: 45%;
        }
    }
</style>
@endpush

@push('scripts')
@if($branch->latitude && $branch->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = {{ $branch->latitude }};
        const lng = {{ $branch->longitude }};

        const map = L.map('branchMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng])
            .addTo(map)
            .bindPopup(@json($branch->name))
            .openPopup();

        // Fix tile rendering if container was hidden
        setTimeout(() => map.invalidateSize(), 200);
    });
</script>
@endif
@endpush