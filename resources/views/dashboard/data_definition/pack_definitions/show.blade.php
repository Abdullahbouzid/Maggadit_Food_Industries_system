@extends('dashboard.partials.layout')

@section('title', 'تفاصيل تعريف الباكج')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <a href="{{ route('pack-definitions.index') }}" class="btn btn-secondary">
                            <i data-feather="arrow-left" class="align-middle me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-0 pt-4">
                                <h5 class="fw-bold mb-0">معلومات التعريف</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">الاسم</label>
                                        <p class="fw-semibold">{{ $packDefinition->name_ar ?? $packDefinition->name_en ?? $packDefinition->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">الكود</label>
                                        <p><code>{{ $packDefinition->code ?? '-' }}</code></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-0 pt-4">
                                <h5 class="fw-bold mb-0">مواصفات العبوة</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">نوع الباكج</label>
                                        <p><span class="badge bg-info">{{ $packDefinition->packType?->name_ar ?? $packDefinition->packType?->name_en ?? '-' }}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted">سعة الباكج</label>
                                        <p>{{ $packDefinition->packCapacity?->value ?? '-' }} {{ $packDefinition->packCapacity?->uom?->code ?? '' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">الكمية في العبوة</label>
                                        <p class="fw-bold">{{ $packDefinition->quantity_in_pack }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">الباركود</label>
                                        <p><code>{{ $packDefinition->barcode ?? '-' }}</code></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">فاصل التسلسل</label>
                                        <p>{{ $packDefinition->serial_separator ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">معامل المعادلة</label>
                                        <p>{{ $packDefinition->equivalency_factor ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-0 pt-4">
                                <h5 class="fw-bold mb-0">الأبعاد والوزن</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">الارتفاع</label>
                                        <p>{{ $packDefinition->height ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">العرض</label>
                                        <p>{{ $packDefinition->width ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">العمق</label>
                                        <p>{{ $packDefinition->depth ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">الوزن</label>
                                        <p>{{ $packDefinition->weight ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="settings"></i> الإعدادات</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" {{ $packDefinition->is_weight_priced ? 'checked' : '' }} disabled>
                                    <label class="form-check-label">تسعير بالوزن</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" {{ $packDefinition->allow_fraction_in_qty ? 'checked' : '' }} disabled>
                                    <label class="form-check-label">السماح بكسور في الكمية</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" {{ $packDefinition->is_default_pack ? 'checked' : '' }} disabled>
                                    <label class="form-check-label">باكج افتراضي</label>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="tag"></i> عمليات الباكج</h6>
                            </div>
                            <div class="card-body">
                                @forelse($packDefinition->packOperations as $operation)
                                    <span class="badge bg-primary me-1 mb-1">{{ $operation->name_en ?? $operation->name_ar }}</span>
                                @empty
                                    <p class="text-muted small">لا توجد عمليات</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0"><i data-feather="layers"></i> مجموعات الباكج</h6>
                            </div>
                            <div class="card-body">
                                @forelse($packDefinition->packGroups as $group)
                                    <span class="badge bg-success me-1 mb-1">{{ $group->name_en ?? $group->name_ar }}</span>
                                @empty
                                    <p class="text-muted small">لا توجد مجموعات</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('pack-definitions.edit', $packDefinition) }}" class="btn btn-primary">
                                <i data-feather="edit"></i> تعديل
                            </a>
                            <form action="{{ route('pack-definitions.destroy', $packDefinition) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i data-feather="trash-2"></i> حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
