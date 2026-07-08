@extends('dashboard.partials.layout')

@section('title', 'تعديل نبذة منظمة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit" class="align-middle me-2"></i>
                            تعديل نبذة منظمة
                        </h1>
                        <p class="text-muted">قم بتحديث بيانات نبذة المنظمة</p>
                    </div>
                </div>

                {{-- Form --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form action="{{ route('organization-about.update', $organizationAbout) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                {{-- Organization --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المنظمة <span class="text-danger">*</span></label>
                                    <select name="organization_id" class="form-select @error('organization_id') is-invalid @enderror" required>
                                        <option value="">اختر المنظمة</option>
                                        @foreach($organizations as $id => $name)
                                            <option value="{{ $id }}" {{ old('organization_id', $organizationAbout->organization_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('organization_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Title --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">العنوان الرئيسي</label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $organizationAbout->title) }}" placeholder="مثل: ماجاديت للصناعات الغذائية">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">الوصف / القصة</label>
                                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="أدخل وصف المنظمة...">{{ old('description', $organizationAbout->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Vision --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الرؤية</label>
                                    <input type="text" name="vision" class="form-control @error('vision') is-invalid @enderror" value="{{ old('vision', $organizationAbout->vision) }}" placeholder="الرؤية المستقبلية">
                                    @error('vision')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Mission --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الرسالة</label>
                                    <input type="text" name="mission" class="form-control @error('mission') is-invalid @enderror" value="{{ old('mission', $organizationAbout->mission) }}" placeholder="رسالة المنظمة">
                                    @error('mission')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Values (JSON array) --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">القيم الأساسية</label>
                                    <div id="values-container">
                                        @php $values = old('values', json_decode($organizationAbout->values ?? '[]', true)); @endphp
                                        @if($values && count($values) > 0)
                                            @foreach($values as $value)
                                                <div class="input-group mb-2">
                                                    <input type="text" name="values[]" class="form-control" value="{{ $value }}" placeholder="أدخل قيمة">
                                                    <button type="button" class="btn btn-danger remove-value"><i data-feather="x"></i></button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="values[]" class="form-control" placeholder="مثال: الجودة أولاً">
                                                <button type="button" class="btn btn-danger remove-value"><i data-feather="x"></i></button>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" id="add-value" class="btn btn-sm btn-outline-primary mt-1">
                                        <i data-feather="plus"></i> أضف قيمة
                                    </button>
                                    @error('values')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Statistics (JSON) --}}
                                <div class="mb-3">
                                    <label class="form-label">الإحصائيات (مفتاح: قيمة)</label>
                                    <div id="statistics-fields">
                                        <div class="row g-2 mb-2 statistic-item">
                                            <div class="col-5">
                                                <input type="text" name="statistics[0][key]" class="form-control" placeholder="المفتاح (مثلاً: سنوات)">
                                            </div>
                                            <div class="col-5">
                                                <input type="text" name="statistics[0][value]" class="form-control" placeholder="القيمة (مثلاً: 15)">
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-danger remove-statistic">حذف</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="add-statistic" class="btn btn-sm btn-secondary">+ إضافة إحصائية</button>
                                </div>
                                {{-- Image --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">صورة الشعار</label>
                                    @if($organizationAbout->image_url)
                                        <div class="mb-2">
                                            <img src="{{ $organizationAbout->image_url }}" alt="الشعار" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">اتركه فارغاً إذا لم ترغب في تغيير الصورة</div>
                                </div>

                                {{-- Cover Image --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">صورة الغلاف</label>
                                    @if($organizationAbout->cover_image_url)
                                        <div class="mb-2">
                                            <img src="{{ $organizationAbout->cover_image_url }}" alt="الغلاف" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                                    @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">اتركه فارغاً إذا لم ترغب في تغيير الصورة</div>
                                </div>

                                {{-- Is Active --}}
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $organizationAbout->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="isActive">نشط</label>
                                        <div class="form-text">عند التفعيل، ستظهر هذه النبذة للعملاء في تطبيقهم</div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i data-feather="save"></i> تحديث
                                    </button>
                                    <a href="{{ route('organization-about.index') }}" class="btn btn-light btn-lg px-5 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add value input
    document.getElementById('add-value').addEventListener('click', function() {
        const container = document.getElementById('values-container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" name="values[]" class="form-control" placeholder="أدخل قيمة">
            <button type="button" class="btn btn-danger remove-value"><i data-feather="x"></i></button>
        `;
        container.appendChild(div);
        feather.replace();
        div.querySelector('.remove-value').addEventListener('click', function() {
            div.remove();
        });
    });

    // Remove value (existing)
    document.querySelectorAll('.remove-value').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.input-group').remove();
        });
    });

    // Add statistic
    document.getElementById('add-statistic').addEventListener('click', function() {
        const container = document.getElementById('statistics-fields');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 statistic-item';
        div.innerHTML = `
            <div class="col-5">
                <input type="text" name="statistics[][key]" class="form-control" placeholder="المفتاح (مثلاً: سنوات)">
            </div>
            <div class="col-5">
                <input type="text" name="statistics[][value]" class="form-control" placeholder="القيمة (مثلاً: 15)">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-danger remove-statistic"><i data-feather="x"></i></button>
            </div>
        `;
        container.appendChild(div);
        feather.replace();
        div.querySelector('.remove-statistic').addEventListener('click', function() {
            div.remove();
        });
    });

    // Remove statistic (existing)
    document.querySelectorAll('.remove-statistic').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.row').remove();
        });
    });
</script>
@endpush