@extends('dashboard.partials.layout')

@section('title', 'تعديل نوع الباكج')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit"></i> تعديل نوع الباكج
                        </h1>
                        <p class="text-muted">تعديل بيانات نوع الباكج</p>
                    </div>
                </div>

                <form action="{{ route('pack-types.update', $packType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="info"></i> المعلومات الأساسية</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $packType->name) }}" placeholder="مثال: صندوق 3×3" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (إنجليزي)</label>
                                            <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $packType->name_en) }}" placeholder="Box 3x3">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (عربي)</label>
                                            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $packType->name_ar) }}" placeholder="صندوق 3×3">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الكود</label>
                                            <input type="text" name="code" class="form-control" value="{{ old('code', $packType->code) }}" placeholder="BOX_3X3">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">عدد القطع في العبوة</label>
                                            <input type="number" name="pieces_per_pack" class="form-control" value="{{ old('pieces_per_pack', $packType->pieces_per_pack) }}" min="1" placeholder="9">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">الوصف</label>
                                            <textarea name="description" class="form-control" rows="3">{{ old('description', $packType->description) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="save"></i> تحديث نوع الباكج
                            </button>
                            <a href="{{ route('pack-types.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
