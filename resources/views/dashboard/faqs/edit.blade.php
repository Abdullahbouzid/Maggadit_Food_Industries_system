@extends('dashboard.partials.layout')

@section('title', 'تعديل سؤال شائع')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل سؤال شائع
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عدل بيانات السؤال والإجابة</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('faqs.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> العودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-body">
        <form action="{{ route('faqs.update', $faq->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="nova-form-group">
                <label class="nova-label" for="category">
                    <i class="fas fa-tag me-1"></i> الفئة (اختياري)
                </label>
                <input type="text" name="category" id="category" class="nova-input" value="{{ old('category', $faq->category) }}" placeholder="مثال: الحساب، الطلبات، التوصيل...">
                <div class="nova-form-help">اختر فئة لتصنيف السؤال.</div>
            </div>

            <div class="nova-form-group">
                <label class="nova-label" for="question">
                    <i class="fas fa-question-circle me-1"></i> السؤال <span class="nova-text-danger">*</span>
                </label>
                <input type="text" name="question" id="question" class="nova-input @error('question') nova-input-error @enderror" value="{{ old('question', $faq->question) }}" required>
                @error('question')
                    <div class="nova-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="nova-form-group">
                <label class="nova-label" for="answer">
                    <i class="fas fa-pen me-1"></i> الإجابة <span class="nova-text-danger">*</span>
                </label>
                <textarea name="answer" id="answer" rows="5" class="nova-textarea @error('answer') nova-input-error @enderror" required>{{ old('answer', $faq->answer) }}</textarea>
                @error('answer')
                    <div class="nova-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="nova-form-actions">
                <a href="{{ route('faqs.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-save"></i> تحديث
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
