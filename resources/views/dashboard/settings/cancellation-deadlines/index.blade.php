@extends('dashboard.partials.layout')

@section('title', __('messages.cancellation_deadline.title'))

@push('styles')
<style>
    /* ============ Layout Grid ============ */
    .nova-settings-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    @media (min-width: 992px) {
        .nova-settings-grid {
            grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
        }
    }
    .nova-settings-grid > * { min-width: 0; }

    /* ============ Row actions ============ */
    .nova-row-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .375rem;
    }
    .nova-row-actions form { margin: 0; display: inline-flex; }

    /* ============ Sticky form on desktop ============ */
    @media (min-width: 992px) {
        .nova-settings-form-card { position: sticky; top: 1rem; }
    }
</style>
@endpush

@section('content')

@php
    // حماية من اختلاف نوع $settings (Collection أو Paginator)
    $totalCount = $settings instanceof \Illuminate\Pagination\LengthAwarePaginator
        ? $settings->total()
        : $settings->count();
@endphp

{{-- ===== Action Bar ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-clock-rotate-left" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.cancellation_deadline.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.cancellation_deadline.subtitle') }}
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-credit-card"></i> {{ __('messages.payment_deadline_page.title') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-settings-grid">

    {{-- ============ LEFT: Add Rule Form ============ --}}
    <div class="nova-card nova-settings-form-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title mb-0">
                <i class="fas fa-plus-circle"></i>
                {{ __('messages.cancellation_deadline.add_rule') }}
            </h3>
        </div>
        <div class="nova-card-body">
            <form method="POST" action="{{ route('cancellation-deadlines.store') }}">
                @csrf

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-arrow-right-from-bracket me-1"></i>
                        {{ __('messages.cancellation_deadline.from_stage') }}
                    </label>
                    <select name="from_stage" class="nova-select" required>
                        <option value="">{{ __('messages.cancellation_deadline.select_stage') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ old('from_stage') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_stage')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i>
                        {{ __('messages.cancellation_deadline.to_stage') }}
                    </label>
                    <select name="to_stage" class="nova-select" required>
                        <option value="">{{ __('messages.cancellation_deadline.select_stage') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ old('to_stage') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_stage')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-hourglass-half me-1"></i>
                        {{ __('messages.cancellation_deadline.hours_limit') }}
                    </label>
                    <input type="number"
                           name="hours_limit"
                           min="0"
                           max="87600"
                           value="{{ old('hours_limit', 48) }}"
                           class="nova-input"
                           required>
                    <div class="nova-form-help">1 day = 24 hours</div>
                    @error('hours_limit')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-note-sticky me-1"></i>
                        {{ __('messages.cancellation_deadline.notes') }}
                    </label>
                    <input type="text"
                           name="notes"
                           value="{{ old('notes') }}"
                           class="nova-input"
                           placeholder="{{ __('messages.cancellation_deadline.notes_placeholder') }}">
                    @error('notes')
                        <div class="nova-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="nova-btn nova-btn-primary nova-w-100">
                    <i class="fas fa-plus"></i> {{ __('messages.cancellation_deadline.add_rule') }}
                </button>
            </form>
        </div>
    </div>

    {{-- ============ RIGHT: Rules Table ============ --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title mb-0">
                <i class="fas fa-list"></i>
                {{ __('messages.cancellation_deadline.title') }}
                <span class="nova-badge primary" style="margin-inline-start: .5rem;">
                    {{ $totalCount }}
                </span>
            </h3>
        </div>

        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.cancellation_deadline.col_from') }}</th>
                        <th>{{ __('messages.cancellation_deadline.col_to') }}</th>
                        <th>{{ __('messages.cancellation_deadline.col_limit') }}</th>
                        <th>{{ __('messages.cancellation_deadline.col_status') }}</th>
                        <th>{{ __('messages.cancellation_deadline.col_updated') }}</th>
                        <th style="text-align: end;">{{ __('messages.cancellation_deadline.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                    @php
                        // حساب عرض المدة بشكل صحيح (48 ساعة = 2 أيام)
                        $hours = (int) $setting->hours_limit;
                        if ($hours >= 24) {
                            $days = $hours / 24;
                            $durationLabel = ($days == (int) $days ? (int) $days : round($days, 1))
                                . ' ' . __('messages.cancellation_deadline.days');
                        } else {
                            $durationLabel = $hours . ' ' . __('messages.cancellation_deadline.hours_short');
                        }

                        $fromLabel = $setting->fromStatus()?->label() ?? '—';
                        $toLabel   = $setting->toStatus()?->label() ?? '—';
                    @endphp
                    <tr>
                        <td class="nova-fw-semibold">{{ $fromLabel }}</td>
                        <td>
                            <i class="fas fa-arrow-left nova-text-muted" style="margin-inline-end: .35rem; font-size: .75rem;"></i>
                            {{ $toLabel }}
                        </td>
                        <td>
                            <span class="nova-badge info">
                                <i class="fas fa-hourglass-half" style="margin-inline-end: .25rem;"></i>
                                {{ $durationLabel }}
                            </span>
                        </td>
                        <td>
                            @if ($setting->is_active)
                                <span class="nova-badge success">
                                    <i class="fas fa-circle-check" style="margin-inline-end: .25rem;"></i>
                                    {{ __('messages.payment_deadline_page.status_active') }}
                                </span>
                            @else
                                <span class="nova-badge danger">
                                    <i class="fas fa-circle-xmark" style="margin-inline-end: .25rem;"></i>
                                    {{ __('messages.payment_deadline_page.status_inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="nova-text-muted nova-fs-sm">
                            {{ $setting->updated_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td style="text-align: end;">
                            <div class="nova-row-actions">

                                {{-- Toggle --}}
                                <form method="POST"
                                      action="{{ route('cancellation-deadlines.toggle', $setting->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="nova-icon-btn nova-btn-sm {{ $setting->is_active ? 'nova-text-success' : 'nova-text-muted' }}"
                                            title="{{ __('messages.payment_deadline_page.toggle_active') }}">
                                        <i class="fas fa-toggle-{{ $setting->is_active ? 'on' : 'off' }}"></i>
                                    </button>
                                </form>

                                {{-- Delete (uses global modal) --}}
                                <button type="button"
                                        class="nova-icon-btn nova-btn-sm nova-text-danger"
                                        title="{{ __('messages.payment_deadline_page.delete') }}"
                                        onclick="confirmDelete(this)"
                                        data-url="{{ route('cancellation-deadlines.destroy', $setting->id) }}"
                                        data-name="{{ $fromLabel }} → {{ $toLabel }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="nova-empty">
                                <div class="nova-empty-icon">
                                    <i class="fas fa-clock-rotate-left"></i>
                                </div>
                                <h3 class="nova-empty-title">
                                    {{ __('messages.cancellation_deadline.empty_state') }}
                                </h3>
                                <p class="nova-empty-text">
                                    {{ __('messages.cancellation_deadline.subtitle') }}
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination — only if paginated --}}
        @if ($settings instanceof \Illuminate\Pagination\LengthAwarePaginator && $settings->hasPages())
        <div class="nova-card-footer">
            <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
                <div class="nova-text-muted nova-fs-sm">
                    Showing {{ $settings->firstItem() ?? 0 }} to {{ $settings->lastItem() ?? 0 }} of {{ $settings->total() }} rules
                </div>
                {{ $settings->appends(request()->query())->links() }}
            </div>
        </div>
        @endif

    </div>

</div>

{{-- ==================== Delete Confirmation Modal ==================== --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">{{ __('Confirm Deletion') }}</h4>
            <p class="nova-delete-modal-text">
                {{ __('messages.payment_deadline_page.warning_delete') }}
                <br>
                <strong data-delete-target class="nova-text-danger"></strong>
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('deleteModal').classList.remove('show')">
                    {{ __('Cancel') }}
                </button>
                <form id="deleteForm" method="POST" action="">
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
    // Delete confirmation
    window.confirmDelete = function(btn) {
        const name = btn.dataset.name || 'this rule';
        const url  = btn.dataset.url;

        document.querySelector('#deleteModal [data-delete-target]').innerText = name;
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteModal').classList.add('show');
    };

    // Close on backdrop click
    document.querySelectorAll('.nova-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) backdrop.classList.remove('show');
        });
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.nova-modal-backdrop.show').forEach(function(m) {
                m.classList.remove('show');
            });
        }
    });

    // Auto-dismiss alerts
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