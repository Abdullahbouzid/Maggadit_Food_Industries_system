@extends('dashboard.partials.layout')

@section('title', 'عرض السؤال')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-question" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            عرض السؤال
        </h2>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('faqs.edit', $faq->id) }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('faqs.index') }}" class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <dl class="nova-dl">
            <dt class="nova-fw-semibold fs-5 mb-0">التصنيف</dt>
            <dd class="mb-3">{{ $faq->category ?? '—' }}</dd>

            <dt class="nova-fw-semibold fs-5 mb-0">السؤال</dt>
            <dd class="mb-3">{{ $faq->question }}</dd>

            <dt class="nova-fw-semibold fs-5 mb-0">الإجابة</dt>
            <dd class="mb-0" style="white-space: pre-line;">{{ $faq->answer }}</dd>
        </dl>
    </div>
    <div class="nova-card-footer">
        <small class="nova-text-muted">
            أُنشئ في {{ $faq->created_at?->format('Y-m-d H:i') }}
            @if ($faq->updated_at)
                · آخر تعديل {{ $faq->updated_at->format('Y-m-d H:i') }}
            @endif
        </small>
    </div>
</div>
@endsection
