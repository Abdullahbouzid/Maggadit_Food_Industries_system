@extends('dashboard.partials.layout')

@section('title', trans('messages.payment_deadline_page.title'))

@section('content')

@php
    $appliesToLabels = [
        'all' => trans('messages.payment_deadline_page.applies_to_all'),
        'specific' => trans('messages.payment_deadline_page.applies_to_specific'),
    ];
    $paymentTypeLabels = [
        'cash' => trans('messages.payment_deadline_page.payment_type_cash'),
        'credit' => trans('messages.payment_deadline_page.payment_type_credit'),
        'prepaid' => __('Prepaid'),
    ];
@endphp

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-clock" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ trans('messages.payment_deadline_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ trans('messages.payment_deadline_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="{{ route('payment-deadlines.create') }}" class="nova-btn nova-btn-primary">
            <i class="fas fa-plus"></i> {{ trans('messages.payment_deadline_page.create') }}
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

<div class="nova-alert info nova-mb-4">
    <i class="fas fa-circle-info"></i> {{ trans('messages.payment_deadline_page.info_body') }}
</div>

<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" class="nova-row nova-gap-3">
            <div class="nova-col-4">
                <input type="text"
                       name="search"
                       class="nova-input"
                       value="{{ request('search') }}"
                       placeholder="{{ trans('messages.payment_deadline_page.col_payment_type') }}">
            </div>
            <div class="nova-col-3">
                <select name="status" class="nova-select">
                    <option value="">{{ trans('messages.payment_deadline_page.col_status') }}</option>
                    <option value="active" @selected(request('status') === 'active')>
                        {{ trans('messages.payment_deadline_page.status_active') }}
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        {{ trans('messages.payment_deadline_page.status_inactive') }}
                    </option>
                </select>
            </div>
            <div class="nova-col-5">
                <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-filter"></i> {{ trans('messages.payment_deadline_page.apply') }}
                </button>
                <a href="{{ route('payment-deadlines.index') }}" class="nova-btn nova-btn-sm">
                    <i class="fas fa-rotate-left"></i> {{ trans('messages.payment_deadline_page.reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-body">
        <div class="table-responsive">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>{{ trans('messages.payment_deadline_page.col_payment_type') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_hours_limit') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_applies_to') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_customers_count') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_status') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_updated_at') }}</th>
                        <th>{{ trans('messages.payment_deadline_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                        <tr>
                            <td class="nova-fw-semibold">
                                {{ $paymentTypeLabels[$setting->payment_type] ?? $setting->payment_type }}
                            </td>
                            <td>
                                {{ $setting->hours_limit }}
                                {{ $setting->hours_limit == 1
                                    ? trans('messages.payment_deadline_page.hours')
                                    : trans('messages.payment_deadline_page.hours_plural') }}
                            </td>
                            <td>
                                {{ $appliesToLabels[$setting->applies_to] ?? $setting->applies_to }}
                            </td>
                            <td>{{ count($setting->customer_ids ?? []) ?: '—' }}</td>
                            <td>
                                @if ($setting->is_active)
                                    <span class="nova-badge success">
                                        {{ trans('messages.payment_deadline_page.status_active') }}
                                    </span>
                                @else
                                    <span class="nova-badge danger">
                                        {{ trans('messages.payment_deadline_page.status_inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $setting->updated_at?->format('Y-m-d') ?? '—' }}</td>
                            <td style="white-space: nowrap;">
                                <a href="{{ route('payment-deadlines.edit', $setting) }}"
                                   class="nova-btn nova-btn-sm"
                                   title="{{ trans('messages.payment_deadline_page.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>

                                        <form method="POST"
                                              action="{{ route('payment-deadlines.toggle', $setting) }}"
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" class="nova-btn nova-btn-sm"
                                                    title="{{ trans('messages.payment_deadline_page.toggle_active') }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>

                                        <x-table-actions
                                            :view-url="null"
                                            :edit-url="route('payment-deadlines.edit', $setting)"
                                            :delete-url="route('payment-deadlines.destroy', $setting)"
                                            :delete-name="$setting->payment_type"
                                            delete-modal="deleteModal" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="nova-text-center nova-py-4">
                                <p class="nova-text-muted">{{ trans('messages.payment_deadline_page.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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

<x-delete-modal modal-id="deleteModal" />

@endsection
