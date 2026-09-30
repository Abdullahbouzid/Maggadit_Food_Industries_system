@extends('dashboard.partials.layout')

@section('title', 'عرض السياسة')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            {{ $privacyPolicy->title ?? ($privacyPolicy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة') }}
        </h2>
        <p class="nova-page-subtitle">
            @if($privacyPolicy->organization)
                المنظمة: {{ $privacyPolicy->organization->name }}
            @else
                محتوى عام
            @endif
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('privacy-policies.edit', $privacyPolicy) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('privacy-policies.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
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

<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <div class="nova-flex nova-justify-between nova-items-center">
            <h5 class="nova-fw-bold mb-0">
                @if($privacyPolicy->type == 'privacy_policy')
                    <i class="fas fa-shield-alt me-1"></i> سياسة الخصوصية
                @else
                    <i class="fas fa-file-text me-1"></i> شروط الخدمة
                @endif
            </h5>
            <span class="nova-badge {{ $privacyPolicy->is_active ? 'success' : 'secondary' }}">
                {{ $privacyPolicy->is_active ? 'نشط' : 'غير نشط' }}
            </span>
        </div>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tbody>
                    <tr>
                        <th style="width: 30%;">Title</th>
                        <td>{{ $privacyPolicy->title ?? ($privacyPolicy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة') }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>
                            @if($privacyPolicy->type == 'privacy_policy')
                                <span class="nova-badge secondary">Privacy Policy</span>
                            @else
                                <span class="nova-badge accent">Terms of Service</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Organization</th>
                        <td>
                            {{ $privacyPolicy->organization ? $privacyPolicy->organization->name : 'عام (لجميع المنظمات)' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($privacyPolicy->is_active)
                                <span class="nova-badge success"><i class="fas fa-check me-1"></i> Active</span>
                            @else
                                <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>
                            <i class="fas fa-calendar nova-text-muted me-1"></i>
                            {{ $privacyPolicy->created_at ? $privacyPolicy->created_at->format('F d, Y h:i A') : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>
                            <i class="fas fa-history nova-text-muted me-1"></i>
                            {{ $privacyPolicy->updated_at ? $privacyPolicy->updated_at->format('F d, Y h:i A') : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="nova-mt-4">
            <h6 class="nova-fw-bold nova-text-muted" style="margin-bottom: 0.5rem;">المحتوى</h6>
            <div class="nova-card nova-mb-0">
                <div class="nova-card-body">
                    <div class="prose max-w-none">
                        {!! nl2br(e($privacyPolicy->content)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-gap-2">
            <a href="{{ route('privacy-policies.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> Back to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('privacy-policies.edit', $privacyPolicy) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="nova-btn nova-btn-outline nova-text-danger"
                        onclick="confirmDelete({{ $privacyPolicy->id }}, '{{ addslashes($privacyPolicy->title ?? ($privacyPolicy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة')) }}')">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete
                <strong id="deleteName" class="nova-text-danger"></strong>?<br>
                This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteModal').classList.remove('show')">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete</button>
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
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/privacy-policies/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };

    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
</script>
@endpush
