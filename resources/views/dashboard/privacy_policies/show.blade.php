@extends('dashboard.partials.layout')

@section('title', 'عرض السياسة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="shield" class="align-middle me-2"></i>
                            {{ $privacyPolicy->title ?? ($privacyPolicy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة') }}
                        </h1>
                        <p class="text-muted">
                            @if($privacyPolicy->organization)
                                المنظمة: {{ $privacyPolicy->organization->name }}
                            @else
                                محتوى عام
                            @endif
                        </p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('privacy-policies.edit', $privacyPolicy) }}" class="btn btn-warning">
                            <i data-feather="edit"></i> تعديل
                        </a>
                        <a href="{{ route('privacy-policies.index') }}" class="btn btn-secondary">
                            <i data-feather="list"></i> القائمة
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                @if($privacyPolicy->type == 'privacy_policy')
                                    <i data-feather="shield" class="me-2"></i> سياسة الخصوصية
                                @else
                                    <i data-feather="file-text" class="me-2"></i> شروط الخدمة
                                @endif
                            </h5>
                            <span class="badge bg-{{ $privacyPolicy->is_active ? 'success' : 'secondary' }}">
                                {{ $privacyPolicy->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="prose max-w-none">
                            {!! nl2br(e($privacyPolicy->content)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
