@extends('dashboard.partials.layout')

@section('title', 'Pack Types')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="layers" class="align-middle me-2"></i>
                            Pack Types
                        </h1>
                        <p class="text-muted">إدارة أنواع الباكجات (صندوق 3×3، قطعة، إلخ)</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="{{ route('pack-types.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة نوع باكج جديد
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0">قائمة أنواع الباكجات ({{ $total }})</h5>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('pack-types.index') }}" class="d-flex">
                                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="بحث بالاسم، الكود..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-sm btn-primary">بحث</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الاسم (إنجليزي)</th>
                                        <th>الكود</th>
                                        <th>عدد القطع</th>
                                        <th>الوصف</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packTypes as $packType)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $packType->name_ar ?? $packType->name ?? '-' }}</strong></td>
                                            <td>{{ $packType->name_en ?? '-' }}</td>
                                            <td><code>{{ $packType->code ?? '-' }}</code></td>
                                            <td>{{ $packType->pieces_per_pack ?? '-' }}</td>
                                            <td>{{ $packType->description ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('pack-types.edit', $packType) }}" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <form action="{{ route('pack-types.destroy', $packType) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i data-feather="inbox" class="mb-2" style="width: 40px;"></i>
                                                <p class="mb-0">لا توجد أنواع باكجات. قم بإنشاء أول نوع.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $packTypes->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
