@extends('dashboard.partials.layout')

@section('title', 'تعديل السياسة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit-2" class="me-2"></i>
                            تعديل السياسة
                        </h1>
                    </div>
                </div>

                <form action="{{ route('privacy-policies.update', $privacyPolicy) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المنظمة</label>
                                    <select name="organization_id" class="form-select">
                                        <option value="">عامة (لجميع المنظمات)</option>
                                        @foreach($organizations as $id => $name)
                                            <option value="{{ $id }}" {{ old('organization_id', $privacyPolicy->organization_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">اتركه فارغاً ليكون هذا المحتوى عاماً</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="privacy_policy" {{ old('type', $privacyPolicy->type) == 'privacy_policy' ? 'selected' : '' }}>سياسة خصوصية</option>
                                        <option value="terms_of_service" {{ old('type', $privacyPolicy->type) == 'terms_of_service' ? 'selected' : '' }}>شروط خدمة</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">العنوان</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $privacyPolicy->title) }}" placeholder="مثال: سياسة الخصوصية الرسمية">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">المحتوى <span class="text-danger">*</span></label>
                                    <textarea name="content" rows="12" class="form-control @error('content') is-invalid @enderror" placeholder="أدخل نص السياسة أو الشروط هنا...">{{ old('content', $privacyPolicy->content) }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ $privacyPolicy->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="isActive">نشط</label>
                                        <div class="form-text">سيظهر هذا المحتوى للعملاء في تطبيقهم</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i data-feather="save"></i> حفظ التغييرات
                                    </button>
                                    <a href="{{ route('privacy-policies.index') }}" class="btn btn-light btn-lg px-5 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
