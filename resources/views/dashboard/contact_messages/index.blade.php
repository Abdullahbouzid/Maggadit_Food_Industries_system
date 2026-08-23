@extends('dashboard.partials.layout')

@section('title', 'رسائل التواصل')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="message-square" class="align-middle me-2"></i>
                            رسائل التواصل
                        </h1>
                        <p class="text-muted">إدارة رسائل العملاء الواردة من نموذج "تواصل معنا"</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="fw-bold mb-0"><i data-feather="list"></i> القائمة</h5>
                        </div>
                        <form method="GET" class="row g-2">
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل الحالات</option>
                                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>جديدة</option>
                                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>مقروءة</option>
                                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>تم الرد</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="importance" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل الأهمية</option>
                                    <option value="low" {{ request('importance') == 'low' ? 'selected' : '' }}>منخفضة</option>
                                    <option value="medium" {{ request('importance') == 'medium' ? 'selected' : '' }}>متوسطة</option>
                                    <option value="high" {{ request('importance') == 'high' ? 'selected' : '' }}>عالية</option>
                                    <option value="urgent" {{ request('importance') == 'urgent' ? 'selected' : '' }}>عاجلة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>الأحدث أولاً</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم أولاً</option>
                                    <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>حسب الأهمية</option>
                                    <option value="unread" {{ request('sort') == 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary"><i data-feather="search"></i></button>
                                </div>
                            </div>
                            @if(request()->has('status') || request()->has('importance') || request()->has('search'))
                                <div class="col-12">
                                    <a href="{{ route('contact-messages.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i data-feather="x"></i> مسح الفلاتر
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>الاسم</th>
                                        <th>البريد</th>
                                        <th>الموضوع</th>
                                        <th>الحالة</th>
                                        <th>الأهمية</th>
                                        <th>تاريخ الإرسال</th>
                                        <th class="pe-4 text-end">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($messages as $msg)
                                        <tr class="{{ $msg->status === 'new' ? 'table-warning' : '' }}">
                                            <td class="ps-4 fw-semibold">{{ $msg->id }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $msg->name }}</span>
                                                @if($msg->phone)
                                                    <br><small class="text-muted">{{ $msg->phone }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $msg->email }}</td>
                                            <td>{{ $msg->subject ?? '—' }}</td>
                                            <td>
                                                @if($msg->status == 'new')
                                                    <span class="badge bg-warning text-dark">جديدة</span>
                                                @elseif($msg->status == 'read')
                                                    <span class="badge bg-info">مقروءة</span>
                                                @elseif($msg->status == 'replied')
                                                    <span class="badge bg-success">تم الرد</span>
                                                @else
                                                    <span class="badge bg-secondary">مغلقة</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($msg->importance == 'urgent')
                                                    <span class="badge bg-danger">عاجلة</span>
                                                @elseif($msg->importance == 'high')
                                                    <span class="badge bg-warning text-dark">عالية</span>
                                                @elseif($msg->importance == 'medium')
                                                    <span class="badge bg-info">متوسطة</span>
                                                @else
                                                    <span class="badge bg-secondary">منخفضة</span>
                                                @endif
                                            </td>
                                            <td>{{ $msg->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('contact-messages.show', $msg) }}" class="btn btn-outline-primary" title="عرض"><i data-feather="eye"></i></a>
                                                    <form action="{{ route('contact-messages.forward', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('تحويل هذه الرسالة إلى البريد الإلكتروني؟')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="تحويل للبريد" {{ $msg->forwarded_to_email ? 'disabled' : '' }}>
                                                            <i data-feather="send"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('contact-messages.destroy', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الرسالة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="حذف"><i data-feather="trash-2"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="py-4">
                                                    <i data-feather="inbox" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                    <h5 class="text-muted">لا توجد رسائل</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $messages->links('pagination::bootstrap-5') }}
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
