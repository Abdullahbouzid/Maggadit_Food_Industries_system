@extends('dashboard.partials.layout')

@section('title', 'إضافة سؤال شائع')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3">إضافة سؤال شائع جديد</h1>
            <a href="{{ route('faqs.index') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('faqs.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">الفئة (اختياري)</label>
                            <input type="text" name="category" id="category" class="form-control" value="{{ old('category') }}" placeholder="مثال: الحساب، الطلبات، التوصيل...">
                        </div>

                        <div class="mb-3">
                            <label for="question" class="form-label">السؤال <span class="text-danger">*</span></label>
                            <input type="text" name="question" id="question" class="form-control" value="{{ old('question') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="answer" class="form-label">الإجابة <span class="text-danger">*</span></label>
                            <textarea name="answer" id="answer" rows="5" class="form-control" required>{{ old('answer') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
