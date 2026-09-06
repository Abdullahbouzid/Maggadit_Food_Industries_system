@extends('dashboard.partials.layout')

@section('title', 'تعديل سعة الباكج')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="edit"></i> تعديل سعة الباكج
                        </h1>
                        <p class="text-muted">تعديل بيانات سعة الباكج</p>
                    </div>
                </div>

                <form action="{{ route('pack-capacities.update', $packCapacity) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i data-feather="info"></i> معلومات السعة</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">القيمة <span class="text-danger">*</span></label>
                                            <input type="number" step="any" name="value" class="form-control" value="{{ old('value', $packCapacity->value) }}" placeholder="500" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">وحدة القياس (UOM) <span class="text-danger">*</span></label>
                                            <select name="uom_id" class="form-select" required>
                                                <option value="">-- اختر --</option>
                                                @foreach($uoms as $uom)
                                                    <option value="{{ $uom->id }}" {{ old('uom_id', $packCapacity->uom_id) == $uom->id ? 'selected' : '' }}>
                                                        {{ $uom->name_en ?? $uom->name_ar }} ({{ $uom->uom_symbol }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (إنجليزي)</label>
                                            <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $packCapacity->name_en) }}" placeholder="500g">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الاسم (عربي)</label>
                                            <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $packCapacity->name_ar) }}" placeholder="500 جرام">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">الوصف</label>
                                            <textarea name="description" class="form-control" rows="3">{{ old('description', $packCapacity->description) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0"><i data-feather="shuffle"></i> وحدات القياس (UOMs)</h6>
                                    <a href="{{ route('uoms.create') }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i data-feather="plus-circle"></i> إضافة
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>الكود</th>
                                                    <th>الاسم</th>
                                                    <th>الرمز</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($uoms as $uom)
                                                    <tr>
                                                        <td><code>{{ $uom->code }}</code></td>
                                                        <td>{{ $uom->name_en ?? $uom->name_ar ?? '-' }}</td>
                                                        <td><span class="badge bg-secondary">{{ $uom->uom_symbol }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($uoms->count() == 0)
                                        <div class="text-muted text-center py-3">
                                            <p class="mb-0">لا توجد وحدات قياس. قم بإنشاء وحدة قياس أولاً.</p>
                                            <a href="{{ route('uoms.create') }}" class="btn btn-sm btn-primary mt-2" target="_blank">إضافة وحدة قياس</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i data-feather="save"></i> تحديث سعة الباكج
                            </button>
                            <a href="{{ route('pack-capacities.index') }}" class="btn btn-secondary btn-lg shadow-sm ms-2">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
