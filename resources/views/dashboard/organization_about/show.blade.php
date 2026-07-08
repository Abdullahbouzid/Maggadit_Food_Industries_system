@extends('dashboard.partials.layout')

@section('title', 'تفاصيل نبذة المنظمة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="info" class="align-middle me-2"></i>
                            تفاصيل نبذة المنظمة
                        </h1>
                        <p class="text-muted">عرض كامل لمحتوى نبذة المنظمة</p>
                    </div>
                </div>

                {{-- Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Organization --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">المنظمة</h6>
                                <p class="fs-5">{{ $organizationAbout->organization->name ?? '—' }}</p>
                            </div>

                            {{-- Title --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">العنوان الرئيسي</h6>
                                <p class="fs-5">{{ $organizationAbout->title ?? '—' }}</p>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <h6 class="fw-bold text-muted">الوصف / القصة</h6>
                                <p class="fs-6">{{ $organizationAbout->description ?? '—' }}</p>
                            </div>

                            {{-- Vision --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">الرؤية</h6>
                                <p class="fs-5">{{ $organizationAbout->vision ?? '—' }}</p>
                            </div>

                            {{-- Mission --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">الرسالة</h6>
                                <p class="fs-5">{{ $organizationAbout->mission ?? '—' }}</p>
                            </div>

                            {{-- Values --}}
                            <div class="col-12">
                                <h6 class="fw-bold text-muted">القيم الأساسية</h6>
                                @php $values = json_decode($organizationAbout->values ?? '[]', true); @endphp
                                @if($values && count($values) > 0)
                                    <ul class="list-unstyled">
                                        @foreach($values as $value)
                                            <li><i data-feather="check-circle" class="text-success me-2"></i> {{ $value }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">—</p>
                                @endif
                            </div>

                            {{-- Statistics --}}
                            <div class="col-12">
                                <h6 class="fw-bold text-muted">الإحصائيات</h6>
                                @php $stats = json_decode($organizationAbout->statistics ?? '{}', true); @endphp
                                @if($stats && count($stats) > 0)
                                    <div class="row">
                                        @foreach($stats as $key => $value)
                                            @php
                                                // Prepare display value for statistics
                                                if (is_array($value)) {
                                                    // If array of scalars, join with comma; otherwise JSON encode
                                                    $filtered = array_filter($value, fn($v) => is_scalar($v));
                                                    if (count($filtered) === count($value)) {
                                                        $displayValue = implode(', ', $filtered);
                                                    } else {
                                                        $displayValue = json_encode($value, JSON_UNESCAPED_UNICODE);
                                                    }
                                                } else {
                                                    $displayValue = $value;
                                                }
                                            @endphp
                                            <div class="col-md-3">
                                                <div class="bg-light p-3 rounded-4 text-center">
                                                    <div class="fs-2 fw-bold">{{ is_numeric($displayValue) ? number_format($displayValue) : $displayValue }}</div>
                                                    <div class="text-muted">{{ $key }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">—</p>
                                @endif
                            </div>

                            {{-- Images --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">صورة الشعار</h6>
                                @if($organizationAbout->image_url)
                                    <img src="{{ $organizationAbout->image_url }}" alt="الشعار" class="img-fluid rounded-4" style="max-height: 200px;">
                                @else
                                    <p class="text-muted">—</p>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted">صورة الغلاف</h6>
                                @if($organizationAbout->cover_image_url)
                                    <img src="{{ $organizationAbout->cover_image_url }}" alt="الغلاف" class="img-fluid rounded-4" style="max-height: 200px; width: 100%; object-fit: cover;">
                                @else
                                    <p class="text-muted">—</p>
                                @endif
                            </div>

                            {{-- Status --}}
                            <div class="col-12">
                                <h6 class="fw-bold text-muted">الحالة</h6>
                                @if($organizationAbout->is_active)
                                    <span class="badge bg-success fs-6">نشط</span>
                                @else
                                    <span class="badge bg-secondary fs-6">غير نشط</span>
                                @endif
                            </div>

                            {{-- Timestamps --}}
                            <div class="col-12">
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">تاريخ الإنشاء: {{ $organizationAbout->created_at->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <small class="text-muted">آخر تحديث: {{ $organizationAbout->updated_at->format('Y-m-d H:i') }}</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="col-12">
                                <a href="{{ route('organization-about.edit', $organizationAbout) }}" class="btn btn-warning btn-lg px-5">
                                    <i data-feather="edit"></i> تعديل
                                </a>
                                <a href="{{ route('organization-about.index') }}" class="btn btn-light btn-lg px-5 ms-2">
                                    <i data-feather="arrow-left"></i> رجوع
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