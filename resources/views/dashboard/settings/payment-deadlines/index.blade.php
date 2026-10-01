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

                                <button type="button"
                                        class="nova-btn nova-btn-sm nova-btn-danger"
                                        title="{{ trans('messages.payment_deadline_page.delete') }}"
                                        onclick="confirmDelete('{{ route('payment-deadlines.destroy', $setting) }}', '{{ $setting->payment_type }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
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

        <div class="nova-mt-3">
            {{ $settings->links() }}
        </div>
    </div>
</div>

{{-- ===== نافذة تأكيد الحذف ===== --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-card" style="width: 100%; max-width: 440px;">
            <div class="nova-card-body nova-text-center">
                <i class="fas fa-triangle-exclamation" style="font-size: 2rem; color: var(--nova-danger);"></i>
                <p style="margin-top: 0.75rem;">
                    {{ trans('messages.payment_deadline_page.warning_confirm') }}
                </p>
            </div>
            <div class="nova-card-footer" style="justify-content: center;">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="closeDeleteModal()">
                    {{ trans('messages.payment_deadline_page.warning_cancel') }}
                </button>
                <button type="button" class="nova-btn nova-btn-danger" id="confirmDeleteBtn">
                    {{ trans('messages.payment_deadline_page.warning_delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<form method="POST" id="deleteForm" style="display: none;">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
<script>
    let deleteUrl = null;

    function confirmDelete(url) {
        deleteUrl = url;
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('show');
        deleteUrl = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteUrl) {
            return;
        }

        const form = document.getElementById('deleteForm');
        form.action = deleteUrl;
        form.submit();
    });
</script>
@endpush
