@extends('dashboard.partials.layout')

@section('title', __('messages.payment_deadline_page.title'))

@section('content')

{{-- Hero Header --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-clock" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.payment_deadline_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.payment_deadline_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('payment-deadlines.create') }}" class="nova-btn nova-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.payment_deadline_page.create') }}
        </a>
    </div>
</div>

{{-- Info Box --}}
<div class="nova-alert nova-alert-info nova-mb-4">
    <div class="nova-alert-body">
        <i class="fas fa-info-circle nova-mr-2"></i>
        <div>
            <strong>{{ __('messages.payment_deadline_page.info_title') }}</strong>
            <p class="nova-mb-0">{{ __('messages.payment_deadline_page.info_body') }}</p>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('payment-deadlines.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 180px;">
                    <label class="nova-label">{{ __('messages.payment_deadline_page.filter_payment_type') }}</label>
                    <select name="payment_type" class="nova-select">
                        <option value="">{{ __('messages.payment_deadline_page.payment_type_all') }}</option>
                        <option value="cash" {{ (request('payment_type') ?? '') === 'cash' ? 'selected' : '' }}>
                            {{ __('messages.payment_deadline_page.payment_type_cash') }}
                        </option>
                        <option value="credit" {{ (request('payment_type') ?? '') === 'credit' ? 'selected' : '' }}>
                            {{ __('messages.payment_deadline_page.payment_type_credit') }}
                        </option>
                    </select>
                </div>
                <div class="nova-form-group" style="min-width: 200px;">
                    <label class="nova-label">{{ __('messages.payment_deadline_page.filter_applies_to') }}</label>
                    <select name="applies_to" class="nova-select">
                        <option value="">{{ __('messages.payment_deadline_page.payment_type_all') }}</option>
                        <option value="all" {{ (request('applies_to') ?? '') === 'all' ? 'selected' : '' }}>
                            {{ __('messages.payment_deadline_page.applies_to_all') }}
                        </option>
                        <option value="specific_customers" {{ (request('applies_to') ?? '') === 'specific_customers' ? 'selected' : '' }}>
                            {{ __('messages.payment_deadline_page.applies_to_specific') }}
                        </option>
                    </select>
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.payment_deadline_page.apply') }}
                    </button>
                    <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.payment_deadline_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Settings Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.payment_deadline_page.col_payment_type') }}</th>
                        <th>{{ __('messages.payment_deadline_page.col_hours_limit') }}</th>
                        <th>{{ __('messages.payment_deadline_page.col_applies_to') }}</th>
                        <th>{{ __('messages.payment_deadline_page.col_customers_count') }}</th>
                        <th>{{ __('messages.payment_deadline_page.col_status') }}</th>
                        <th>{{ __('messages.payment_deadline_page.col_updated_at') }}</th>
                        <th class="nova-text-end">{{ __('messages.payment_deadline_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $setting)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $setting->id }}</span></td>
                        <td>
                            <span class="nova-badge {{ $setting->payment_type === 'cash' ? 'nova-badge-success' : 'nova-badge-warning' }}">
                                {{ $setting->payment_type === 'cash' ? __('messages.payment_deadline_page.payment_type_cash') : __('messages.payment_deadline_page.payment_type_credit') }}
                            </span>
                        </td>
                        <td>{{ $setting->hours_limit ?? 0 }} {{ __('messages.payment_deadline_page.hours') }}</td>
                        <td>
                            {{ $setting->applies_to === 'all' 
                                ? __('messages.payment_deadline_page.applies_to_all') 
                                : __('messages.payment_deadline_page.applies_to_specific') }}
                        </td>
                        <td class="nova-text-end">
                            @if($setting->applies_to === 'specific_customers')
                                {{ $setting->customer_ids ? count($setting->customer_ids) : 0 }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="nova-badge {{ $setting->is_active ? 'nova-badge-success' : 'nova-badge-danger' }}">
                                {{ $setting->is_active ? __('messages.payment_deadline_page.status_active') : __('messages.payment_deadline_page.status_inactive') }}
                            </span>
                        </td>
                        <td>
                            {{ $setting->updated_at ? \Carbon\Carbon::parse($setting->updated_at)->format('Y-m-d H:i') : '-' }}
                            @if($setting->updated_by)
                                <br><small class="nova-text-muted">{{ $setting->updated_by->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td class="nova-text-end">
                            <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                <a href="{{ route('payment-deadlines.edit', $setting->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.payment_deadline_page.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('payment-deadlines.toggle', $setting->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.payment_deadline_page.toggle_active') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fas fa-{{ $setting->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('payment-deadlines.destroy', $setting->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="nova-icon-btn nova-btn-sm nova-btn-danger" title="{{ __('messages.payment_deadline_page.delete') }}" onclick="event.preventDefault(); confirmDelete(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-clock" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.payment_deadline_page.empty') }}</p>
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
                    عرض {{ $settings->firstItem() ?? 0 }} إلى {{ $settings->lastItem() ?? 0 }} من أصل {{ $settings->total() }} سجلاً
                </div>
                <div class="nova-pagination">
                    {{ $settings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection