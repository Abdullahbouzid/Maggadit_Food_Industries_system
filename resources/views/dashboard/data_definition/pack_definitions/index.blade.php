@extends('dashboard.partials.layout')

@section('title', 'تعريفات الباكجات')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="package" class="align-middle me-2"></i>
                            تعريفات الباكجات (Pack Definitions)
                        </h1>
                        <p class="text-muted">إدارة وتعريف أنواع الباكجات القابلة لإعادة الاستخدام للمنتجات</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="{{ route('pack-definitions.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> إضافة تعريف باكج جديد
                        </a>
                        <a href="{{ route('pack-operations.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="settings"></i> إدارة أنواع الباكجات
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0">قائمة تعريفات الباكجات ({{ $total }})</h5>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('pack-definitions.index') }}" class="d-flex">
                                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="بحث بالاسم، الكود، الباركود..." value="{{ request('search') }}">
                                    <select name="pack_type_id" class="form-select form-select-sm me-2" style="width: auto;">
                                        <option value="">كل الأنواع</option>
                                        @foreach($packTypes as $type)
                                            <option value="{{ $type->id }}" {{ request('pack_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en ?? $type->name_ar }}</option>
                                        @endforeach
                                    </select>
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
                                        <th>الكود</th>
                                        <th>نوع الباكج</th>
                                        <th>السعة</th>
                                        <th>الكمية/عبوة</th>
                                        <th>الباركود</th>
                                        <th>الأبعاد (H×W×D)</th>
                                        <th>الوزن</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packDefinitions as $packDef)
                                        @php
                                            $packType = $packDef->packType;
                                            $packCapacity = $packDef->packCapacity;
                                            $packTypeName = $packType?->name_ar ?? $packType?->name_en ?? '-';
                                            $packCapacityValue = $packCapacity?->value ?? '-';
                                            $packCapacityUom = $packCapacity?->uom?->code ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $packDef->name_ar ?? $packDef->name_en ?? $packDef->name ?? 'بدون اسم' }}</strong>
                                                @if($packDef->description)
                                                    <br><small class="text-muted">{{ Str::limit($packDef->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td><code>{{ $packDef->code ?? '-' }}</code></td>
                                            <td><span class="badge bg-info">{{ $packTypeName }}</span></td>
                                            <td>{{ $packCapacityValue }} {{ $packCapacityUom }}</td>
                                            <td><span class="badge bg-primary">{{ $packDef->quantity_in_pack }}</span></td>
                                            <td><code>{{ $packDef->barcode ?? '-' }}</code></td>
                                            <td>
                                                @if($packDef->height || $packDef->width || $packDef->depth)
                                                    {{ $packDef->height }}×{{ $packDef->width }}×{{ $packDef->depth }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $packDef->weight ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('pack-definitions.edit', $packDef) }}" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <form action="{{ route('pack-definitions.destroy', $packDef) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i data-feather="inbox" class="mb-2" style="width: 40px;"></i>
                                                <p class="mb-0">لا توجد تعريفات باكجات. قم بإنشاء أول تعريف.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $packDefinitions->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
