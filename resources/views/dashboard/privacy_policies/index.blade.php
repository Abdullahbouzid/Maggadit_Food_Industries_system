@extends('dashboard.partials.layout')

@section('title', '{{ __('messages.app.page__8') }}')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-shield-alt" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.app.page__17') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-3">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-shield-alt"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__6') }}</p>
        <p class="nova-stat-value">{{ $policies->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-file-text"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__7') }}</p>
        <p class="nova-stat-value">{{ $policies->where('is_active', true)->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__8') }}</p>
        <p class="nova-stat-value">{{ $policies->where('type', 'privacy_policy')->count() }}</p>
    </div>
</div>

{{-- Policies Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.app.page__2') }}
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search policies...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> {{ __('messages.app.page__17') }}
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__20') }}</th>
                    <th>{{ __('messages.app.page__21') }}</th>
                    <th>{{ __('messages.app.page__22') }}</th>
                    <th>{{ __('messages.app.page__23') }}</th>
                    <th style="text-align: right;">{{ __('messages.app.page__24') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $policy)
                <tr>
                    <td>{{ $policy->id }}</td>
                    <td>
                        <span class="nova-fw-bold">{{ $policy->{{ __('messages.app.page_') }} ?? $policy->type }}</span>
                    </td>
                    <td>
                        @if($policy->type == 'privacy_policy')
                            <span class="nova-badge secondary">{{ __('messages.app.page__11') }}</span>
                        @else
                            <span class="nova-badge accent">{{ __('messages.app.page__12') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($policy->is_active)
                            <span class="nova-badge success">{{ __('messages.app.page__13') }}</span>
                        @else
                            <span class="nova-badge secondary">{{ __('messages.app.page__14') }}</span>
                        @endif
                    </td>
                    <td>{{ $policy->created_at->format('Y-m-d') }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('privacy-policies.show', $policy) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('privacy-policies.edit', $policy) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                             <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="Delete" onclick="confirmDelete('{{ $policy->id }}', '{{ addslashes($policy->{{ __('messages.app.page_') }} ?? ($policy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة')) }}')">
                                 <i class="fas fa-trash-alt"></i>
                             </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">{{ __('messages.app.page__3') }}</h3>
                            <p class="nova-empty-text">{{ __('messages.app.page__9') }}</p>
                            <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.app.page__17') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-pagination">
                {{ $policies->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deletePolicyModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete policy <strong id="deletePolicyName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deletePolicyModal').classList.remove('show')">{{ __('messages.app.page__15') }}</button>
                <form id="deletePolicyForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__16') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deletePolicyName').innerText = name;
        document.getElementById('deletePolicyForm').action = '/privacy-policies/' + id;
        document.getElementById('deletePolicyModal').classList.add('show');
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
@endpush