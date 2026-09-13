@extends('dashboard.partials.layout')

@section('title', 'Surveys Management')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-clipboard-list" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Surveys
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage customer app surveys</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('surveys.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Survey
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-row cols-2" style="grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));">
    @forelse($surveys as $survey)
    <div class="nova-card" style="height: 100%;">
        <div class="nova-card-header" style="background: {{ $survey->is_active ? 'linear-gradient(135deg, var(--nova-primary), var(--nova-secondary))' : 'var(--nova-surface-2)' }}; border-bottom: none; padding: 1rem 1.5rem;">
            <div>
                <h3 class="nova-card-title" style="margin: 0; color: {{ $survey->is_active ? 'white' : 'var(--nova-text)' }};">
                    {{ $survey->title }}
                </h3>
                <small style="color: {{ $survey->is_active ? 'rgba(255,255,255,0.75)' : 'var(--nova-text-3)' }};">
                    <i class="fas fa-question-circle"></i> {{ $survey->questions->count() }} Questions • <i class="fas fa-clock"></i> {{ $survey->estimated_minutes }} min
                </small>
            </div>
            <span class="nova-badge {{ $survey->is_active ? 'success' : 'secondary' }}">
                {{ $survey->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="nova-card-body">
            <p class="nova-text-muted nova-fs-sm" style="margin-bottom: 1rem;">{{ $survey->description ?? 'No description' }}</p>

            <div class="nova-row cols-3" style="margin-bottom: 1rem; gap: 0.5rem;">
                <div class="nova-stat" style="padding: 1rem; text-align: center; background: rgba(16, 185, 129, 0.08); border-radius: 10px;">
                    <div class="nova-stat-value" style="font-size: 1.5rem; color: var(--nova-primary); margin: 0;">
                        <i class="fas fa-comments"></i> {{ $survey->responses_count }}
                    </div>
                    <div class="nova-stat-label">Responses</div>
                </div>
                <div class="nova-stat" style="padding: 1rem; text-align: center; background: rgba(14, 165, 233, 0.08); border-radius: 10px;">
                    <div class="nova-stat-value" style="font-size: 1.5rem; color: var(--nova-secondary); margin: 0;">
                        <i class="fas fa-question-circle"></i> {{ $survey->questions->count() }}
                    </div>
                    <div class="nova-stat-label">Questions</div>
                </div>
                <div class="nova-stat" style="padding: 1rem; text-align: center; background: rgba(245, 158, 11, 0.08); border-radius: 10px;">
                    <div class="nova-stat-value" style="font-size: 1.5rem; color: var(--nova-accent); margin: 0;">
                        <i class="fas fa-clock"></i> {{ $survey->estimated_minutes }}
                    </div>
                    <div class="nova-stat-label">Minutes</div>
                </div>
            </div>

            @if($survey->starts_at || $survey->ends_at)
            <div class="nova-flex nova-gap-3 nova-fs-sm nova-text-muted" style="margin-bottom: 1rem; flex-wrap: wrap;">
                @if($survey->starts_at)
                <span><i class="fas fa-calendar"></i> Starts: {{ $survey->starts_at->format('Y-m-d') }}</span>
                @endif
                @if($survey->ends_at)
                <span><i class="fas fa-clock"></i> Ends: {{ $survey->ends_at->format('Y-m-d') }}</span>
                @endif
            </div>
            @endif

            <div class="nova-flex nova-gap-2 nova-flex-wrap">
                <a href="{{ route('surveys.show', $survey) }}" class="nova-btn nova-btn-outline nova-btn-sm">
                    <i class="fas fa-chart-bar"></i> Results
                </a>
                <a href="{{ route('surveys.edit', $survey) }}" class="nova-btn nova-btn-outline nova-btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('surveys.toggle', $survey) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="nova-btn nova-btn-outline nova-btn-sm {{ $survey->is_active ? 'nova-btn-warning' : 'nova-btn-primary' }}">
                        <i class="fas fa-{{ $survey->is_active ? 'pause' : 'play' }}"></i>
                        {{ $survey->is_active ? 'Pause' : 'Activate' }}
                    </button>
                </form>
                <button type="button" class="nova-btn nova-btn-outline nova-btn-sm nova-text-danger"
                        onclick="confirmDelete({{ $survey->id }}, '{{ addslashes($survey->title) }}')">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
        <div class="nova-card-footer" style="border-top: 1px solid var(--nova-border-light); background: var(--nova-surface-2);">
            <small class="nova-text-muted"><i class="fas fa-clock"></i> Created {{ $survey->created_at->format('Y/m/d H:i') }}</small>
        </div>
    </div>
    @empty
    <div class="nova-card" style="grid-column: 1 / -1;">
        <div class="nova-card-body">
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="nova-empty-title">No surveys found</h3>
                <p class="nova-empty-text">Create your first survey to start collecting customer feedback.</p>
                <a href="{{ route('surveys.create') }}" class="nova-btn nova-btn-primary">
                    <i class="fas fa-plus"></i> Create Survey
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteSurveyModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">Confirm Deletion</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete survey <strong id="deleteSurveyName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteSurveyModal').classList.remove('show')">Cancel</button>
                <form id="deleteSurveyForm" method="POST" action="" style="display: inline;">
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
        document.getElementById('deleteSurveyName').innerText = name;
        document.getElementById('deleteSurveyForm').action = '/surveys/' + id;
        document.getElementById('deleteSurveyModal').classList.add('show');
    };
});
</script>
@endpush
