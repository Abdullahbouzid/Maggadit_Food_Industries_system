@extends('dashboard.partials.layout')

@section('title', 'إدارة الاستبيانات')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">الاستبيانات</h1>
                <p class="text-muted small mb-0">إدارة استبيانات تطبيق العملاء</p>
            </div>
            <a href="{{ route('surveys.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i data-feather="plus" style="width:16px;height:16px"></i>
                إنشاء استبيان جديد
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <div class="alert-message">{{ session('success') }}</div>
        </div>
    @endif

    <div class="row">
        @forelse($surveys as $survey)
        <div class="col-12 col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0" style="border-radius:16px; overflow:hidden;">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" 
                     style="background: {{ $survey->is_active ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : '#f1f5f9' }}; border:none;">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: {{ $survey->is_active ? 'white' : '#64748b' }}">
                            {{ $survey->title }}
                        </h5>
                        <small style="color: {{ $survey->is_active ? 'rgba(255,255,255,0.75)' : '#94a3b8' }}">
                            {{ $survey->questions->count() }} سؤال  •  {{ $survey->estimated_minutes }} دقائق
                        </small>
                    </div>
                    <span class="badge" style="background: {{ $survey->is_active ? 'rgba(255,255,255,0.2)' : '#e2e8f0' }}; color: {{ $survey->is_active ? 'white' : '#64748b' }}; border-radius:20px; padding:6px 12px; font-size:0.75rem;">
                        {{ $survey->is_active ? '● نشط' : '○ غير نشط' }}
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <p class="text-muted small mb-3">{{ $survey->description ?? 'لا يوجد وصف' }}</p>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <div class="p-2 rounded-3" style="background:#f0f4ff;">
                                <div class="fw-bold text-primary fs-5">{{ $survey->responses_count }}</div>
                                <small class="text-muted">رد</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded-3" style="background:#f0fdf4;">
                                <div class="fw-bold text-success fs-5">{{ $survey->questions->count() }}</div>
                                <small class="text-muted">سؤال</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded-3" style="background:#fff7ed;">
                                <div class="fw-bold text-warning fs-5">{{ $survey->estimated_minutes }}</div>
                                <small class="text-muted">دقيقة</small>
                            </div>
                        </div>
                    </div>

                    @if($survey->starts_at || $survey->ends_at)
                    <div class="d-flex gap-3 small text-muted mb-3">
                        @if($survey->starts_at)
                        <span><i data-feather="calendar" style="width:13px"></i> يبدأ: {{ $survey->starts_at->format('Y-m-d') }}</span>
                        @endif
                        @if($survey->ends_at)
                        <span><i data-feather="clock" style="width:13px"></i> ينتهي: {{ $survey->ends_at->format('Y-m-d') }}</span>
                        @endif
                    </div>
                    @endif

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('surveys.show', $survey) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                            <i data-feather="bar-chart-2" style="width:13px"></i> النتائج
                        </a>
                        <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                            <i data-feather="edit" style="width:13px"></i> تعديل
                        </a>
                        <form action="{{ route('surveys.toggle', $survey) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $survey->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} d-flex align-items-center gap-1">
                                <i data-feather="{{ $survey->is_active ? 'pause-circle' : 'play-circle' }}" style="width:13px"></i>
                                {{ $survey->is_active ? 'إيقاف' : 'تفعيل' }}
                            </button>
                        </form>
                        <form action="{{ route('surveys.destroy', $survey) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الاستبيان وجميع ردوده؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                <i data-feather="trash-2" style="width:13px"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-3">
                    <small class="text-muted">أُنشئ في {{ $survey->created_at->format('Y/m/d H:i') }}</small>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5" style="border-radius:16px;">
                <div class="card-body">
                    <i data-feather="clipboard" style="width:60px;height:60px;color:#cbd5e1;" class="mb-3"></i>
                    <h5 class="text-muted">لا توجد استبيانات بعد</h5>
                    <p class="text-muted small">أنشئ استبيانك الأول لجمع آراء العملاء</p>
                    <a href="{{ route('surveys.create') }}" class="btn btn-primary">
                        <i data-feather="plus" style="width:16px"></i> إنشاء استبيان
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
