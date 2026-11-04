@extends('dashboard.partials.layout')

@section('title', 'نتائج الاستبيان')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-chart-bar" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            نتائج: {{ $survey->title }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            <i class="fas fa-comments"></i> {{ $survey->responses()->count() }} رد • <i class="fas fa-question-circle"></i> {{ $survey->questions->count() }} سؤال
            <span class="nova-badge {{ $survey->is_active ? 'success' : 'secondary' }} ms-2">
                {{ $survey->is_active ? 'نشط' : 'غير نشط' }}
            </span>
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('surveys.edit', $survey) }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('surveys.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> العودة
        </a>
    </div>
</div>

{{-- إحصائيات عامة --}}
<div class="nova-row cols-2 cols-lg-4 g-3 mb-4">
    <div class="nova-stat">
        <div class="nova-stat-value">
            <i class="fas fa-comments"></i> {{ $survey->responses()->count() }}
        </div>
        <div class="nova-stat-label">إجمالي الردود</div>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-value">
            <i class="fas fa-question-circle"></i> {{ $survey->questions->count() }}
        </div>
        <div class="nova-stat-label">عدد الأسئلة</div>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-value">
            <i class="fas fa-clock"></i> {{ $survey->estimated_minutes }}
        </div>
        <div class="nova-stat-label">دقيقة (تقديري)</div>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-value">
            <i class="fas fa-calendar"></i> {{ $survey?->created_at?->format('d/m') ?? '—' }}
        </div>
        <div class="nova-stat-label">تاريخ الإنشاء</div>
    </div>
</div>

{{-- نتائج الأسئلة --}}
<div class="nova-row g-4">
    @foreach($stats as $i => $stat)
    <div class="nova-col nova-col-lg-6">
        <div class="nova-form-card h-100">
            <div class="nova-form-card-header">
                <div class="d-flex align-items-start gap-3">
                    <span class="nova-badge primary">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $stat['question'] }}</h6>
                        <small class="nova-text-muted">
                            @switch($stat['type'])
                                @case('choice') <i class="fas fa-dot-circle"></i> اختيار من متعدد @break
                                @case('rating') <i class="fas fa-star"></i> تقييم بالنجوم @break
                                @case('text') <i class="fas fa-font"></i> إجابة نصية @break
                                @case('yesno') <i class="fas fa-check-circle"></i> نعم / لا @break
                                @case('multiselect') <i class="fas fa-check-square"></i> اختيار متعدد @break
                            @endswitch
                            • <i class="fas fa-poll-h"></i> {{ $stat['total'] }} إجابة
                        </small>
                    </div>
                </div>
            </div>
            <div class="nova-form-card-body">
                @if(isset($stat['breakdown']) && count($stat['breakdown']) > 0)
                    @php($maxCount = max($stat['breakdown']))
                    @foreach($stat['breakdown'] as $option => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold">{{ $option }}</span>
                            <span class="nova-text-muted">
                                <i class="fas fa-bar-chart"></i> {{ $count }} ({{ $stat['total'] > 0 ? round($count/$stat['total']*100) : 0 }}%)
                            </span>
                        </div>
                        <div class="nova-progress">
                            <div class="nova-progress-bar"
                                 style="width: {{ $stat['total'] > 0 ? round($count/$stat['total']*100) : 0 }}%"
                                 aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $stat['total'] }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                @elseif(isset($stat['text_answers']))
                    @if(count($stat['text_answers']) > 0)
                    <div style="max-height:200px;overflow-y:auto;">
                        @foreach($stat['text_answers'] as $answer)
                        <div class="nova-form-group" style="margin-bottom: 0.5rem;">
                            <small class="text-gray-700">{{ $answer }}</small>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="nova-text-muted small text-center">لا توجد إجابات نصية بعد.</p>
                    @endif
                @else
                <p class="nova-text-muted small text-center py-3">لا توجد بيانات كافية.</p>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- قائمة الردود --}}
@if($survey->responses->count() > 0)
<div class="nova-form-card mt-4">
    <div class="nova-form-card-header">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-users"></i> العملاء الذين أجابوا على الاستبيان
        </h6>
    </div>
    <div class="nova-form-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-user"></i> اسم العميل</th>
                        <th><i class="fas fa-envelope"></i> البريد الإلكتروني</th>
                        <th><i class="fas fa-clock"></i> تاريخ الإجابة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($survey->responses as $response)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $response->customer->name ?? 'غير معروف' }}</td>
                        <td class="nova-text-muted small">{{ $response->customer->email ?? '-' }}</td>
                        <td class="small nova-text-muted">
                            {{ $response->submitted_at?->format('Y-m-d H:i') ?? $response?->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

<style>
.nova-progress {
    height: 8px;
    border-radius: 10px;
    background: var(--nova-surface-2);
    overflow: hidden;
}
.nova-progress-bar {
    height: 100%;
    background: linear-gradient(135deg, var(--nova-primary), var(--nova-secondary));
    border-radius: 10px;
    transition: width 0.3s ease;
}
</style>
