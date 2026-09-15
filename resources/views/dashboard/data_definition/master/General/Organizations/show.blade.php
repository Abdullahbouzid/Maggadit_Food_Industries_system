@extends('dashboard.partials.layout')

@section('title', 'Organization التفاصيل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-eye" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل المنظمة
        </h2>
        <p class="nova-page-subtitle">عرض كامل لمعلومات {{ $organization->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organizations.edit', $organization) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('organizations.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
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
    <div class="nova-card-header" style="background: var(--nova-primary); color: white;">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-building"></i> {{ $organization->name }}
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">المعرف</th>
                    <td class="nova-fw-semibold">{{ $organization->id }}</td>
                </tr>
                <tr>
                    <th>اسم المنظمة</th>
                    <td>
                        <div class="nova-flex nova-items-center gap-2">
                            <div class="nova-stat-icon primary" style="width: 32px; height: 32px;">
                                <i class="fas fa-building" style="width: 16px;"></i>
                            </div>
                            <span>{{ $organization->name }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>شيفرة المنظمة</th>
                    <td>
                        <span class="nova-badge secondary">{{ $organization->code }}</span>
                    </td>
                </tr>
                <tr>
                    <th>المنظمة الأصلية</th>
                    <td>
                        @if($organization->parent)
                            <i class="fas fa-sitemap nova-text-muted me-1"></i>
                            {{ $organization->parent->name }}
                        @else
                            <span class="nova-badge secondary">منظمة رئيسية</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>نوع المنظمة</th>
                    <td>{{ $organization->organization_type ?: '<span class="nova-text-muted fst-italic">غير محدد</span>' }}</td>
                </tr>
                <tr>
                    <th>رقم الموقع العالمي (GLN)</th>
                    <td>
                        @if($organization->gln)
                            <code class="nova-text-primary">{{ $organization->gln }}</code>
                        @else
                            <span class="nova-text-muted fst-italic">غير متوفر</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $organization->created_at ? $organization->created_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $organization->updated_at ? $organization->updated_at->format('F d, Y h:i A') : '—' }}
                    </td>
                </tr>
            </table>

            {{-- Contact Info --}}
            <div class="nova-card nova-mt-4">
                <div class="nova-card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-phone"></i> بيانات التواصل
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div class="nova-row g-3">
                        @if($organization->phone)
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small text-uppercase">الهاتف</label>
                                <p class="nova-fw-semibold mb-0">{{ $organization->phone }}</p>
                            </div>
                        @endif
                        @if($organization->whatsapp)
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small text-uppercase">الواتساب</label>
                                <p class="nova-fw-semibold mb-0">{{ $organization->whatsapp }}</p>
                            </div>
                        @endif
                        @if($organization->email)
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small text-uppercase">البريد الإلكتروني</label>
                                <a href="mailto:{{ $organization->email }}" class="nova-text-primary fw-semibold">{{ $organization->email }}</a>
                            </div>
                        @endif
                        @if($organization->website)
                            <div class="nova-col nova-col-md-6">
                                <label class="nova-label small text-uppercase">الموقع الإلكتروني</label>
                                <a href="{{ $organization->website }}" target="_blank" class="nova-text-primary fw-semibold">{{ $organization->website }}</a>
                            </div>
                        @endif
                        @if($organization->address)
                            <div class="nova-col nova-col-12">
                                <label class="nova-label small text-uppercase">العنوان</label>
                                <p class="nova-fw-semibold mb-0">{{ $organization->address }}</p>
                            </div>
                        @endif
                        @if(!$organization->phone && !$organization->whatsapp && !$organization->email && !$organization->website && !$organization->address)
                            <div class="nova-col nova-col-12">
                                <span class="nova-text-muted fst-italic">لم يتم تحديد بيانات التواصل</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Email Forwarding --}}
            <div class="nova-card nova-mt-4">
                <div class="nova-card-body">
                    <div class="nova-flex nova-items-center gap-2">
                        <span class="nova-badge {{ $organization->forward_contact_messages_to_email ? 'success' : 'secondary' }}">
                            {{ $organization->forward_contact_messages_to_email ? 'مفعل' : 'معطل' }}
                        </span>
                        <span class="nova-text-muted small">تحويل الرسائل إلى البريد</span>
                        @if($organization->forward_contact_messages_to_email && $organization->contact_email_recipient)
                            <code class="small">{{ $organization->contact_email_recipient }}</code>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Map --}}
            @if($organization->latitude && $organization->longitude)
            <div class="nova-card nova-mt-4">
                <div class="nova-card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-map-marked-alt"></i> الموقع على الخريطة
                    </h5>
                </div>
                <div class="nova-card-body">
                    <div id="org-map-show" style="height: 250px; border-radius: 8px;"></div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-end nova-gap-2">
            <a href="{{ route('organizations.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> العودة إلى القائمة
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($organization->latitude && $organization->longitude)
        const showMap = L.map('org-map-show').setعرض([{{ $organization->latitude }}, {{ $organization->longitude }}], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; مفتوحStreetMap contributors',
            maxZoom: 19,
        }).addTo(showMap);
        L.marker([{{ $organization->latitude }}, {{ $organization->longitude }}]).addTo(showMap);
    @endif
});
</script>
@endpush
