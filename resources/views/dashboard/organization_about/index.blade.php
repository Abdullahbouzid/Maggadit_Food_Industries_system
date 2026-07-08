@extends('dashboard.partials.layout')

@section('title', 'إدارة نبذة المنظمات')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="info" class="align-middle me-2"></i>
                            نبذة المنظمات
                        </h1>
                        <p class="text-muted">إدارة محتوى صفحة "نبذة عنا" لكل منظمة</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('organization-about.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة نبذة جديدة
                        </a>
                    </div>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Table --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> قائمة النبذات</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>المنظمة</th>
                                        <th>العنوان</th>
                                        <th>الرؤية</th>
                                        <th>الحالة</th>
                                        <th class="pe-4 text-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($abouts as $about)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($abouts->currentPage() - 1) * $abouts->perPage() }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $about->organization->name ?? '—' }}</span>
                                        </td>
                                        <td>{{ $about->title ?? '—' }}</td>
                                        <td>{{ Str::limit($about->vision, 30) ?: '—' }}</td>
                                        <td>
                                            @if($about->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('organization-about.show', $about) }}" class="btn btn-outline-info" title="عرض"><i data-feather="eye"></i></a>
                                                <a href="{{ route('organization-about.edit', $about) }}" class="btn btn-outline-warning" title="تعديل"><i data-feather="edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger" title="حذف" onclick="confirmDelete('{{ $about->id }}', '{{ addslashes($about->title ?? 'نبذة') }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="info" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">لا توجد نبذات</h5>
                                                <a href="{{ route('organization-about.create') }}" class="btn btn-primary">
                                                    <i data-feather="plus-circle"></i> إضافة أول نبذة
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
                            <div class="small text-muted">
                                عرض {{ $abouts->firstItem() ?? 0 }} إلى {{ $abouts->lastItem() ?? 0 }} من {{ $abouts->total() }} إدخال
                            </div>
                            <div>{{ $abouts->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">تأكيد الحذف</h4>
                <p class="text-muted">هل أنت متأكد من حذف <strong id="deleteName" class="text-danger"></strong>؟<br>هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">حذف نهائي</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/organization-about/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush