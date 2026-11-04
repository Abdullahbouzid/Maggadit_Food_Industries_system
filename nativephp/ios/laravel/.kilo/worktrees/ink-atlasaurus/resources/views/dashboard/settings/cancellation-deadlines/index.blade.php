@extends('dashboard.partials.layout')

@section('title', __('messages.cancellation_deadline.title'))

@section('content')

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
    <div>
        <a href="{{ route('payment-deadlines.index') }}" class="nova-btn">
            <i class="fas fa-credit-card"></i> {{ __('messages.payment_deadline_page.title') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

<div class="nova-row nova-gap-4">
    <div class="nova-col-4">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">{{ __('messages.cancellation_deadline.add_rule') }}</h3>
            </div>
            <div class="nova-card-body">
                <form method="POST" action="{{ route('cancellation-deadlines.store') }}">
                    @csrf

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.cancellation_deadline.from_stage') }}</label>
                        <select name="from_stage" class="nova-select" required>
                            <option value="">{{ __('messages.cancellation_deadline.select_stage') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('from_stage')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.cancellation_deadline.to_stage') }}</label>
                        <select name="to_stage" class="nova-select" required>
                            <option value="">{{ __('messages.cancellation_deadline.select_stage') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('to_stage')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.cancellation_deadline.hours_limit') }}</label>
                        <input type="number" name="hours_limit" min="0" max="87600" value="48" class="nova-input" required>
                        @error('hours_limit')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.cancellation_deadline.notes') }}</label>
                        <input type="text" name="notes" class="nova-input"
                            placeholder="{{ __('messages.cancellation_deadline.notes_placeholder') }}">
                    </div>

                    <button type="submit" class="nova-btn nova-btn-primary nova-w-100">
                        <i class="fas fa-plus"></i> {{ __('messages.cancellation_deadline.add_rule') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="nova-col-8">
        <div class="nova-card">
            <div class="nova-card-body">
                <div class="table-responsive">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.cancellation_deadline.col_from') }}</th>
                                <th>{{ __('messages.cancellation_deadline.col_to') }}</th>
                                <th>{{ __('messages.cancellation_deadline.col_limit') }}</th>
                                <th>{{ __('messages.cancellation_deadline.col_status') }}</th>
                                <th>{{ __('messages.cancellation_deadline.col_updated') }}</th>
                                <th class="nova-text-end">{{ __('messages.cancellation_deadline.col_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($settings as $setting)
                                <tr>
                                    <td class="nova-fw-semibold">{{ $setting->fromStatus()?->label() ?? '—' }}</td>
                                    <td>{{ $setting->toStatus()?->label() ?? '—' }}</td>
                                    <td>
                                        <span class="nova-badge info">
                                            {{ number_format($setting->hours_limit) }}
                                            {{ $setting->hours_limit >= 24
                                                ? __('messages.cancellation_deadline.days')
                                                : __('messages.cancellation_deadline.hours_short') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($setting->is_active)
                                            <span class="nova-badge success">{{ __('messages.payment_deadline_page.status_active') }}</span>
                                        @else
                                            <span class="nova-badge danger">{{ __('messages.payment_deadline_page.status_inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="nova-text-muted nova-fs-sm">
                                        {{ $setting->updated_at?->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="nova-text-end">
                                        <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                            <form method="POST"
                                                action="{{ route('cancellation-deadlines.toggle', $setting->id) }}">
                                                @csrf
                                                <button type="submit" class="nova-icon-btn nova-btn-sm"
                                                    title="{{ __('messages.payment_deadline_page.toggle_active') }}">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('cancellation-deadlines.destroy', $setting->id) }}"
                                                onsubmit="return confirm('{{ __('messages.payment_deadline_page.warning_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="nova-icon-btn nova-btn-sm"
                                                    title="{{ __('messages.payment_deadline_page.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="nova-text-center nova-py-4">
                                        <p class="nova-text-muted">
                                            {{ __('messages.cancellation_deadline.empty_state') }}
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
