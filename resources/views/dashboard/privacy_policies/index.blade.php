@extends('dashboard.partials.layout')

@section('title', 'سياسات الخصوصية')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="shield" class="align-middle me-2"></i>
                            سياسات الخصوصية والشروط
                        </h1>
                        <p class="text-muted">إدارة سياسات الخصوصية وشروط الخدمة التي تظهر للعملاء</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('privacy-policies.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة سياسة
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> القائمة</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>العنوان</th>
                                        <th>النوع</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($policies as $policy)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $policy->id }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $policy->title ?? $policy->type }}</span>
                                            </td>
                                            <td>
                                                @if($policy->type == 'privacy_policy')
                                                    <span class="badge bg-info">سياسة خصوصية</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">شروط خدمة</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($policy->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>{{ $policy->created_at->format('Y-m-d') }}</td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('privacy-policies.show', $policy) }}" class="btn btn-outline-info" title="عرض"><i data-feather="eye"></i></a>
                                                    <a href="{{ route('privacy-policies.edit', $policy) }}" class="btn btn-outline-warning" title="تعديل"><i data-feather="edit"></i></a>
                                                    <form action="{{ route('privacy-policies.destroy', $policy) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="shield" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد سياسات</h5>
                                                    <a href="{{ route('privacy-policies.create') }}" class="btn btn-primary">
                                                        <i data-feather="plus-circle"></i> إضافة سياسة
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>{{ $policies->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
