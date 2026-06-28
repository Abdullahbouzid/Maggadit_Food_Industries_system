@extends('dashboard.partials.layout')

@section('title', 'نتائج الاستبيان')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">نتائج: {{ $survey->title }}</h1>
                <p class="text-muted small mb-0">
                    {{ $survey->responses()->count() }} رد  •  {{ $survey->questions->count() }} سؤال
                    <span class="badge {{ $survey->is_active ? 'bg-success' : 'bg-secondary' }} ms-2">
                        {{ $survey->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-outline-primary btn-sm">
                    <i data-feather="edit" style="width:14px"></i> تعديل
                </a>
                <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i data-feather="arrow-right" style="width:14px"></i> العودة
                </a>
            </div>
        </div>
    </div>

    {{-- إحصائيات عامة --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px;">
                <div class="h2 fw-bold text-primary mb-0">{{ $survey->responses()->count() }}</div>
                <small class="text-muted">إجمالي الردود</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px;">
                <div class="h2 fw-bold text-success mb-0">{{ $survey->questions->count() }}</div>
                <small class="text-muted">عدد الأسئلة</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px;">
                <div class="h2 fw-bold text-warning mb-0">{{ $survey->estimated_minutes }}</div>
                <small class="text-muted">دقيقة (تقديري)</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px;">
                <div class="h2 fw-bold text-info mb-0">
                    {{ $survey->created_at->format('d/m') }}
                </div>
                <small class="text-muted">تاريخ الإنشاء</small>
            </div>
        </div>
    </div>

    {{-- نتائج الأسئلة --}}
    <div class="row g-4">
        @foreach($stats as $i => $stat)
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="badge fw-bold px-3 py-2" 
                              style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:10px;font-size:0.9rem;">
                            {{ $i + 1 }}
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $stat['question'] }}</h6>
                            <small class="text-muted">
                                @switch($stat['type'])
                                    @case('choice') اختيار من متعدد @break
                                    @case('rating') تقييم بالنجوم @break
                                    @case('text') إجابة نصية @break
                                    @case('yesno') نعم / لا @break
                                    @case('multiselect') اختيار متعدد @break
                                @endswitch
                                • {{ $stat['total'] }} إجابة
                            </small>
                        </div>
                    </div>

                    @if(isset($stat['breakdown']) && count($stat['breakdown']) > 0)
                        @php $maxCount = max($stat['breakdown']); @endphp
                        @foreach($stat['breakdown'] as $option => $count)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold">{{ $option }}</span>
                                <span class="text-muted">{{ $count }} ({{ $stat['total'] > 0 ? round($count/$stat['total']*100) : 0 }}%)</span>
                            </div>
                            <div class="progress" style="height:8px;border-radius:10px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width:{{ $stat['total'] > 0 ? round($count/$stat['total']*100) : 0 }}%; background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:10px;"
                                     aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $stat['total'] }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @elseif(isset($stat['text_answers']))
                        @if(count($stat['text_answers']) > 0)
                            <div style="max-height:200px;overflow-y:auto;">
                                @foreach($stat['text_answers'] as $answer)
                                <div class="p-2 mb-1 rounded-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                    <small class="text-gray-700">{{ $answer }}</small>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small text-center">لا توجد إجابات نصية بعد.</p>
                        @endif
                    @else
                        <p class="text-muted small text-center py-3">لا توجد بيانات كافية.</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- قائمة الردود --}}
    @if($survey->responses->count() > 0)
    <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
        <div class="card-header border-0 py-3 px-4" style="background:#f8fafc;border-radius:16px 16px 0 0;">
            <h6 class="fw-bold mb-0">العملاء الذين أجابوا على الاستبيان</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f1f5f9;">
                        <tr>
                            <th class="px-4">#</th>
                            <th>اسم العميل</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ الإجابة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($survey->responses as $response)
                        <tr>
                            <td class="px-4">{{ $loop->iteration }}</td>
                            <td>{{ $response->customer->name ?? 'غير معروف' }}</td>
                            <td class="text-muted small">{{ $response->customer->email ?? '-' }}</td>
                            <td class="small text-muted">{{ $response->submitted_at?->format('Y-m-d H:i') ?? $response->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
