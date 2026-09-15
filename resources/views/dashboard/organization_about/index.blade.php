@extends('dashboard.partials.layout')

@section('title', '{{ __('messages.app.page__1') }}')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.app.page__16') }}
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
@if(session('warning'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('warning') }}
    </div>
@endif

@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>أخطاء الاستيراد:</strong>
        <ul style="margin-top: 0.5rem; padding-inline-start: 1.25rem;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-info-circle"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__6') }}</p>
        <p class="nova-stat-value">{{ $abouts->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-building"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__7') }}</p>
        <p class="nova-stat-value">{{ $abouts->where('is_active', true)->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-edit"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__8') }}</p>
        <p class="nova-stat-value">{{ $abouts->whereNotNull('{{ __('messages.app.page_') }}')->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.app.page__9') }}</p>
        <p class="nova-stat-value">{{ $abouts->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- About Table --}}
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
            <input type="search" class="nova-input" placeholder="Search about entries...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> {{ __('messages.app.page__17') }}
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__19') }}</th>
                    <th>{{ __('messages.app.page__20') }}</th>
                    <th>{{ __('messages.app.page__21') }}</th>
                    <th>{{ __('messages.app.page__22') }}</th>
                    <th class="nova-text-end">{{ __('messages.app.page__23') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($abouts as $about)
                <tr class="{{ $about->is_active ? '' : 'nova-row-inactive' }}">
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($abouts->currentPage() - 1) * $abouts->perPage() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary nova-me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-building"></i>
                            </div>
                            <span>{{ $about->organization->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>{{ $about->{{ __('messages.app.page_') }} ?? '—' }}</td>
                    <td>{{ Str::limit($about->vision, 30) ?: '—' }}</td>
                    <td>
                        @if($about->is_active)
                            <span class="nova-badge success">{{ __('messages.app.page__12') }}</span>
                        @else
                            <span class="nova-badge secondary">{{ __('messages.app.page__9') }}</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('organization-about.show', $about) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('organization-about.edit', $about) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="{{ __('messages.app.page__15') }}" onclick="confirm{{ __('messages.app.page__15') }}({{ $about->id }}, '{{ addslashes($about->{{ __('messages.app.page_') }} ?? 'About Entry') }}')">
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
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">{{ __('messages.app.page__3') }}</h3>
                            <p class="nova-empty-text">{{ __('messages.app.page__10') }}</p>
                            <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.app.page__18') }}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                Showing {{ $abouts->firstItem() ?? 0 }} to {{ $abouts->lastItem() ?? 0 }} of {{ $abouts->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $abouts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- {{ __('messages.app.page__15') }} Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete <strong id="deleteName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">{{ __('messages.app.page__14') }}</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__15') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirm{{ __('messages.app.page__15') }} = function(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteForm').action = '/organization-about/' + id;
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

<style>
.nova-row-inactive {
    background: rgba(148, 163, 184, 0.05);
}
.nova-me-2 {
    margin-inline-end: 0.5rem;
}
</style>
