@extends('dashboard.partials.layout')

@section('title', 'عرض الرسالة')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="message-square" class="align-middle me-2"></i>
                            رسالة من {{ $contactMessage->name }}
                        </h1>
                        <p class="text-muted">تاريخ الإرسال: {{ $contactMessage->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('contact-messages.index') }}" class="btn btn-secondary">
                            <i data-feather="list"></i> القائمة
                        </a>
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

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                            <div class="card-header bg-primary text-white py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">تفاصيل الرسالة</h5>
                                    <span class="badge bg-{{ $contactMessage->status == 'new' ? 'warning text-dark' : ($contactMessage->status == 'read' ? 'info' : ($contactMessage->status == 'replied' ? 'success' : 'secondary')) }}">
                                        {{ $contactMessage->status == 'new' ? 'جديدة' : ($contactMessage->status == 'read' ? 'مقروءة' : ($contactMessage->status == 'replied' ? 'تم الرد' : 'مغلقة')) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-borderless mb-4">
                                    <tr>
                                        <th class="w-25">الاسم</th>
                                        <td>{{ $contactMessage->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>البريد الإلكتروني</th>
                                        <td><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td>
                                    </tr>
                                    @if($contactMessage->phone)
                                        <tr>
                                            <th>الهاتف</th>
                                            <td>{{ $contactMessage->phone }}</td>
                                        </tr>
                                    @endif
                                    @if($contactMessage->subject)
                                        <tr>
                                            <th>الموضوع</th>
                                            <td>{{ $contactMessage->subject }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>المنظمة</th>
                                        <td>{{ $contactMessage->organization?->name ?? 'عامة' }}</td>
                                    </tr>
                                </table>

                                <div class="bg-light p-4 rounded-3">
                                    <h6 class="fw-bold mb-2">الرسالة:</h6>
                                    <p class="white-space-pre-wrap mb-0">{{ $contactMessage->message }}</p>
                                </div>

                                @if($contactMessage->forwarded_to_email)
                                    <div class="alert alert-success mt-3">
                                        <i data-feather="check" class="align-middle me-1"></i>
                                        تم تحويل هذه الرسالة إلى البريد الإلكتروني في {{ $contactMessage->forwarded_at?->format('Y-m-d H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">تحديث الحالة</h6>
                                <form action="{{ route('contact-messages.status', $contactMessage) }}" method="POST" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        @foreach($statuses as $st)
                                            <option value="{{ $st }}" {{ $contactMessage->status == $st ? 'selected' : '' }}>
                                                {{ $st == 'new' ? 'جديدة' : ($st == 'read' ? 'مقروءة' : ($st == 'replied' ? 'تم الرد' : 'مغلقة')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>

                                <h6 class="fw-bold mt-4 mb-3">تحديث الأهمية</h6>
                                <form action="{{ route('contact-messages.importance', $contactMessage) }}" method="POST" class="d-inline">
                                    @csrf
                                    <select name="importance" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        @foreach($importances as $imp)
                                            <option value="{{ $imp }}" {{ $contactMessage->importance == $imp ? 'selected' : '' }}>
                                                {{ $imp == 'urgent' ? 'عاجلة' : ($imp == 'high' ? 'عالية' : ($imp == 'medium' ? 'متوسطة' : 'منخفضة')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap gap-2">
                                    <form action="{{ route('contact-messages.forward', $contactMessage) }}" method="POST" class="d-inline" onsubmit="return confirm('تحويل هذه الرسالة إلى البريد الإلكتروني؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-success" {{ $contactMessage->forwarded_to_email ? 'disabled' : '' }}>
                                            <i data-feather="send"></i> تحويل للبريد
                                        </button>
                                    </form>
                                    <form action="{{ route('contact-messages.close', $contactMessage) }}" method="POST" class="d-inline" onsubmit="return confirm('إغلاق هذه الرسالة؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" {{ $contactMessage->status == 'closed' ? 'disabled' : '' }}>
                                            <i data-feather="x-circle"></i> إغلاق
                                        </button>
                                    </form>
                                    <form action="{{ route('contact-messages.destroy', $contactMessage) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الرسالة نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i data-feather="trash-2"></i> حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
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
