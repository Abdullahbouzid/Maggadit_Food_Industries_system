@extends('dashboard.partials.layout')

@section('title', 'عرض الرسالة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-message-square" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            رسالة من {{ $contactMessage->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            <i class="fas fa-clock"></i> تاريخ الإرسال: {{ $contactMessage?->created_at?->format('Y-m-d H:i') ?? '—' }}
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('contact-messages.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-list"></i> القائمة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-info-circle"></i> تفاصيل الرسالة
            </h5>
            @php
                $statusClass = $contactMessage->status == 'new' ? 'warning' : ($contactMessage->status == 'read' ? 'info' : ($contactMessage->status == 'replied' ? 'success' : 'secondary'));
                $statusText = $contactMessage->status == 'new' ? 'جديدة' : ($contactMessage->status == 'read' ? 'مقروءة' : ($contactMessage->status == 'replied' ? 'تم الرد' : 'مغلقة'));
            @endphp
            <span class="nova-badge {{ $statusClass }}">{{ $statusText }}</span>
        </div>
    </div>
    <div class="nova-card-body">
        <table class="nova-table nova-table-borderless">
            <tr>
                <th class="nova-col nova-col-md-4">الاسم</th>
                <td>{{ $contactMessage->name }}</td>
            </tr>
            <tr>
                <th>البريد الإلكتروني</th>
                <td><a href="mailto:{{ $contactMessage->email }}" class="nova-text-primary">{{ $contactMessage->email }}</a></td>
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

        <div class="nova-form-card nova-mt-4">
            <div class="nova-form-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-file-alt"></i> الرسالة
                </h6>
            </div>
            <div class="nova-form-card-body">
                <p class="mb-0" style="white-space: pre-wrap;">{{ $contactMessage->message }}</p>
            </div>
        </div>

        @if($contactMessage->forwarded_to_email)
            <div class="nova-alert success nova-mt-4">
                <i class="fas fa-check-circle"></i>
                تم تحويل هذه الرسالة إلى البريد الإلكتروني في {{ $contactMessage->forwarded_at?->format('Y-m-d H:i') }}
            </div>
        @endif
    </div>
</div>

{{-- Update Actions --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-cog"></i> إدارة الحالة
        </h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-form-group">
            <label class="nova-label">
                <i class="fas fa-flag me-1"></i> تحديث الحالة
            </label>
            <form action="{{ route('contact-messages.status', $contactMessage) }}" method="POST">
                @csrf
                <select name="status" class="nova-select" onchange="this.form.submit()">
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ $contactMessage->status == $st ? 'selected' : '' }}>
                            {{ $st == 'new' ? 'جديدة' : ($st == 'read' ? 'مقروءة' : ($st == 'replied' ? 'تم الرد' : 'مغلقة')) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="nova-form-group nova-mt-3">
            <label class="nova-label">
                <i class="fas fa-exclamation-circle me-1"></i> تحديث الأهمية
            </label>
            <form action="{{ route('contact-messages.importance', $contactMessage) }}" method="POST">
                @csrf
                <select name="importance" class="nova-select" onchange="this.form.submit()">
                    @foreach($importances as $imp)
                        <option value="{{ $imp }}" {{ $contactMessage->importance == $imp ? 'selected' : '' }}>
                            {{ $imp == 'urgent' ? 'عاجلة' : ($imp == 'high' ? 'عالية' : ($imp == 'medium' ? 'متوسطة' : 'منخفضة')) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="nova-card nova-mt-4">
    <div class="nova-card-body">
        <div class="nova-flex nova-gap-2">
            <a href="{{ route('contact-messages.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
            <form action="{{ route('contact-messages.forward', $contactMessage) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="nova-btn nova-btn-success" {{ $contactMessage->forwarded_to_email ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i> تحويل للبريد
                </button>
            </form>
            <form action="{{ route('contact-messages.close', $contactMessage) }}" method="POST" class="d-inline" onsubmit="return confirm('إغلاق هذه الرسالة؟')">
                @csrf
                <button type="submit" class="nova-btn nova-btn-secondary" {{ $contactMessage->status == 'closed' ? 'disabled' : '' }}>
                    <i class="fas fa-times-circle"></i> إغلاق
                </button>
            </form>
            <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                    onclick="confirmDelete({{ $contactMessage->id }}, '{{ addslashes($contactMessage->name ?? 'Message') }}')">
                <i class="fas fa-trash-alt"></i> حذف
            </button>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteMessageModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete the message from <strong id="deleteMessageName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteMessageModal').classList.remove('show')">Cancel</button>
                <form id="deleteMessageForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteMessageName').innerText = name;
        document.getElementById('deleteMessageForm').action = '/contact-messages/' + id;
        document.getElementById('deleteMessageModal').classList.add('show');
    };
});
</script>
@endpush
