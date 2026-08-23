@extends('dashboard.partials.layout')

@section('title', 'Organization Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="eye" class="align-middle me-2" style="width: 32px; height: 32px;"></i>
                            Organization Details
                        </h1>
                        <p class="text-muted">View complete information of the selected organization.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-warning shadow-sm me-1">
                            <i data-feather="edit-2" class="align-middle me-1"></i> Edit
                        </a>
                        <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary shadow-sm">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> Back
                        </a>
                    </div>
                </div>

                <!-- Details Card -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="card-header bg-primary text-white py-3 px-4">
                                <h5 class="fw-bold mb-0">
                                    <i data-feather="info" class="align-middle me-1"></i>
                                    {{ $organization->name }}
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tr>
                                            <th class="bg-light" style="width: 35%;">ID</th>
                                            <td>{{ $organization->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Organization Name</th>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                        <i data-feather="building" class="text-primary" style="width: 16px;"></i>
                                                    </div>
                                                    {{ $organization->name }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Organization Code</th>
                                            <td><code class="text-primary">{{ $organization->code }}</code></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Parent Organization</th>
                                            <td>
                                                @if($organization->parent)
                                                    <i data-feather="arrow-up-right" class="text-muted me-1" style="width: 14px;"></i>
                                                    {{ $organization->parent->name }}
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-dark">Root Organization</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Organization Type</th>
                                            <td>{{ $organization->organization_type ?? '<span class="text-muted fst-italic">Not specified</span>' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">GLN (Global Location Number)</th>
                                            <td>
                                                @if($organization->gln)
                                                    <code>{{ $organization->gln }}</code>
                                                @else
                                                    <span class="text-muted fst-italic">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Created At</th>
                                            <td>
                                                <i data-feather="calendar" class="text-muted me-1" style="width: 14px;"></i>
                                                {{ $organization->created_at ? $organization->created_at->format('F d, Y h:i A') : '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Last Updated</th>
                                            <td>
                                                <i data-feather="clock" class="text-muted me-1" style="width: 14px;"></i>
                                                {{ $organization->updated_at ? $organization->updated_at->format('F d, Y h:i A') : '—' }}
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Contact Info Section -->
                                    <div class="mt-4 p-4 bg-light rounded-3">
                                        <h6 class="fw-bold mb-3">
                                            <i data-feather="phone" class="align-middle me-1"></i>
                                            بيانات التواصل
                                        </h6>
                                        <div class="row g-3">
                                            @if($organization->phone)
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">الهاتف</small>
                                                    <span class="fw-semibold">{{ $organization->phone }}</span>
                                                </div>
                                            @endif
                                            @if($organization->whatsapp)
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">الواتساب</small>
                                                    <span class="fw-semibold">{{ $organization->whatsapp }}</span>
                                                </div>
                                            @endif
                                            @if($organization->email)
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">البريد الإلكتروني</small>
                                                    <a href="mailto:{{ $organization->email }}" class="fw-semibold text-primary">{{ $organization->email }}</a>
                                                </div>
                                            @endif
                                            @if($organization->website)
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">الموقع الإلكتروني</small>
                                                    <a href="{{ $organization->website }}" target="_blank" class="fw-semibold text-primary">{{ $organization->website }}</a>
                                                </div>
                                            @endif
                                        @if($organization->address)
                                            <div class="col-12">
                                                <small class="text-muted d-block">العنوان</small>
                                                <span class="fw-semibold">{{ $organization->address }}</span>
                                            </div>
                                        @endif
                                        @if($organization->latitude && $organization->longitude)
                                            <div class="col-12 mt-2">
                                                <small class="text-muted d-block">الموقع على الخريطة</small>
                                                <div id="org-map-show" style="height: 250px; border-radius: 8px; z-index: 0;"></div>
                                            </div>
                                        @endif
                                            @if(!$organization->phone && !$organization->whatsapp && !$organization->email && !$organization->website && !$organization->address)
                                                <div class="col-12">
                                                    <span class="text-muted fst-italic">لم يتم تحديد بيانات التواصل</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $organization->forward_contact_messages_to_email ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $organization->forward_contact_messages_to_email ? 'مفعل' : 'معطل' }}
                                                </span>
                                                <span class="text-muted small">تحويل الرسائل إلى البريد</span>
                                                @if($organization->forward_contact_messages_to_email && $organization->contact_email_recipient)
                                                    <code class="small">{{ $organization->contact_email_recipient }}</code>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 py-3 px-4 text-end">
                                <a href="{{ route('organizations.index') }}" class="btn btn-secondary shadow-sm">
                                    <i data-feather="list" class="align-middle me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fc;
    }
    .badge.bg-secondary.bg-opacity-10 {
        background-color: rgba(108, 117, 125, 0.1) !important;
        color: #495057 !important;
    }
    #org-map-show {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        z-index: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        @if($organization->latitude && $organization->longitude)
            const showMap = L.map('org-map-show').setView([{{ $organization->latitude }}, {{ $organization->longitude }}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(showMap);
            L.marker([{{ $organization->latitude }}, {{ $organization->longitude }}]).addTo(showMap);
        @endif
    });
</script>
@endpush