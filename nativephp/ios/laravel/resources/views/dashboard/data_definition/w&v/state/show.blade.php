@extends('dashboard.partials.layout')

@section('title', 'State Details')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            State: {{ $state->code }}
        </h2>
        <p class="nova-page-subtitle">View state classification details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('states.edit', $state) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> Back to List
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
        <h5 class="nova-fw-bold mb-0"><i class="fas fa-file-alt me-1"></i> State Details</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <tr>
                    <th style="width: 30%;">ID</th>
                    <td>{{ $state->id }}</td>
                </tr>
                <tr>
                    <th>الكود</th>
                    <td><span class="nova-badge secondary">{{ $state->code }}</span></td>
                </tr>
                <tr>
                    <th>الاسم (إنجليزي)</th>
                    <td>{{ $state->name_en }}</td>
                </tr>
                <tr>
                    <th>الاسم (عربي)</th>
                    <td>{{ $state->name_ar ?: '—' }}</td>
                </tr>
                <tr>
                    <th>البلد</th>
                    <td>{{ $state->country?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>الوصف</th>
                    <td>{{ $state->description ?: '—' }}</td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>
                        @if($state->is_active)
                            <span class="nova-badge success"><i class="fas fa-check me-1"></i> Active</span>
                        @else
                            <span class="nova-badge secondary"><i class="fas fa-x me-1"></i> Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ترتيب الترتيب</th>
                    <td>{{ $state->sort_order ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>
                        <i class="fas fa-calendar nova-text-muted me-1"></i>
                        {{ $state->created_at ? $state?->created_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td>
                        <i class="fas fa-history nova-text-muted me-1"></i>
                        {{ $state->updated_at ? $state?->updated_at?->format('F d, Y h:i A') ?? '—' : '—' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <a href="{{ route('states.index') }}" class="nova-btn nova-btn-outline">
                <i class="fas fa-list"></i> Back to List
            </a>
            <div class="nova-flex nova-gap-2">
                <a href="{{ route('states.edit', $state) }}" class="nova-btn nova-btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="nova-btn nova-btn-danger"
                        onclick="confirmDelete({{ $state->id }}, '{{ addslashes($state->name_en) }}')">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete state
                <strong id="deleteName" class="nova-text-danger"></strong>?<br>This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">Cancel</button>
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
        document.getElementById('deleteForm').action = '/states/' + id;
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
