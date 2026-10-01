@extends('dashboard.partials.layout')

@section('title', __('messages.wallet_page.title'))

@section('content')

{{-- Hero Header --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-wallet" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.wallet_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.wallet_page.subtitle') }}
        </p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card nova-kpi-primary">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.wallet_page.kpi_total_balance') }}</div>
                <div class="nova-kpi-value">{{ number_format($totals['total_balance'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card nova-kpi-accent">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.wallet_page.kpi_wallets_count') }}</div>
                <div class="nova-kpi-value">{{ $totals['wallets_count'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="nova-col nova-4">
        <div class="nova-card nova-kpi-card nova-kpi-success">
            <div class="nova-card-body">
                <div class="nova-kpi-label">{{ __('messages.wallet_page.kpi_total_deposited') }}</div>
                <div class="nova-kpi-value">{{ number_format($totals['total_deposited'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('wallets.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 250px;">
                    <label class="nova-label">{{ __('messages.wallet_page.search_placeholder') }}</label>
                    <input type="text" name="search" class="nova-input" value="{{ request('search') }}" placeholder="{{ __('messages.wallet_page.search_placeholder') }}">
                </div>
                <div class="nova-form-group" style="min-width: 200px;">
                    <label class="nova-label">{{ __('messages.wallet_page.filter_currency') }}</label>
                    <select name="currency_id" class="nova-select">
                        <option value="">{{ __('messages.wallet_page.all_currencies') }}</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ (request('currency_id') ?? '') === (string) $currency->id ? 'selected' : '' }}>
                                {{ $currency->symbol ?? $currency->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="min-width: 200px;">
                    <label class="nova-label">{{ __('messages.wallet_page.filter_status') }}</label>
                    <select name="status" class="nova-select">
                        <option value="">{{ __('messages.wallet_page.status_all') }}</option>
                        <option value="active" {{ (request('status') ?? '') === 'active' ? 'selected' : '' }}>
                            {{ __('messages.wallet_page.status_active') }}
                        </option>
                        <option value="inactive" {{ (request('status') ?? '') === 'inactive' ? 'selected' : '' }}>
                            {{ __('messages.wallet_page.status_inactive') }}
                        </option>
                    </select>
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.wallet_page.apply') }}
                    </button>
                    <a href="{{ route('wallets.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.wallet_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Wallets Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.wallet_page.col_customer') }}</th>
                        <th>{{ __('messages.wallet_page.col_currency') }}</th>
                        <th>{{ __('messages.wallet_page.col_balance') }}</th>
                        <th>{{ __('messages.wallet_page.col_total_deposited') }}</th>
                        <th>{{ __('messages.wallet_page.col_total_spent') }}</th>
                        <th>{{ __('messages.wallet_page.col_status') }}</th>
                        <th class="nova-text-end">{{ __('messages.wallet_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $wallet->id }}</span></td>
                        <td>
                            <div>
                                <div class="nova-fw-semibold">{{ $wallet->customer->name ?? '—' }}</div>
                                <small class="nova-text-muted">{{ $wallet->customer->phone ?? '—' }}</small>
                            </div>
                        </td>
                        <td>{{ $wallet->currency->symbol ?? $wallet->currency->code ?? '—' }}</td>
                        <td class="nova-text-end">{{ number_format($wallet->balance ?? 0, 2) }}</td>
                        <td class="nova-text-end">{{ number_format($wallet->total_deposited ?? 0, 2) }}</td>
                        <td class="nova-text-end">{{ number_format($wallet->total_spent ?? 0, 2) }}</td>
                        <td>
                            <span class="nova-badge {{ ($wallet->is_active ?? false) ? 'nova-badge-success' : 'nova-badge-danger' }}">
                                {{ ($wallet->is_active ?? false) ? __('messages.wallet_page.status_active') : __('messages.wallet_page.status_inactive') }}
                            </span>
                        </td>
                        <td class="nova-text-end">
                            <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                <a href="{{ route('wallets.show', $wallet->id) }}" class="nova-icon-btn nova-btn-sm" title="{{ __('messages.wallet_page.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="nova-icon-btn nova-btn-sm nova-btn-primary js-open-wallet-modal"
                                    data-modal="depositModal{{ $wallet->id }}"
                                    title="{{ __('messages.wallet_page.deposit') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-wallet" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.wallet_page.empty') }}</p>
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
                    {{ __('messages.wallet_page.showing_count', ['count' => $wallets->count()]) }}
                </div>
                <div class="nova-pagination">
                    @if(method_exists($wallets, 'links'))
                        {{ $wallets->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Deposit Modals --}}
@foreach($wallets as $wallet)
<div class="nova-modal-backdrop" id="depositModal{{ $wallet->id }}" tabindex="-1" role="dialog" aria-labelledby="depositModalLabel{{ $wallet->id }}" aria-hidden="true">
    <div class="nova-modal nova-modal-dialog-centered" role="document">
        <div class="nova-modal-content">
            <div class="nova-modal-header">
                <h5 class="nova-modal-title" id="depositModalLabel{{ $wallet->id }}">
                    {{ __('messages.wallet_page.deposit') }} - {{ $wallet->customer->name ?? '—' }}
                </h5>
                <button type="button" class="nova-modal-close js-close-wallet-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('wallets.deposit', $wallet->id) }}">
                @csrf
                <div class="nova-modal-body">
                    <div class="nova-row nova-gap-3">
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">{{ __('messages.wallet_page.deposit_amount') }}</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="nova-input" required>
                            </div>
                        </div>
                        <div class="nova-col">
                            <div class="nova-form-group">
                                <label class="nova-label">{{ __('messages.wallet_page.deposit_method') }}</label>
                                <select name="method" class="nova-select" required>
                                    <option value="cash">{{ __('messages.wallet_page.deposit_method_cash') }}</option>
                                    <option value="bank_transfer">{{ __('messages.wallet_page.deposit_method_bank_transfer') }}</option>
                                    <option value="cheque">{{ __('messages.wallet_page.deposit_method_cheque') }}</option>
                                    <option value="online">{{ __('messages.wallet_page.deposit_method_online') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.wallet_page.deposit_reference') }}</label>
                        <input type="text" name="reference_number" class="nova-input">
                    </div>
                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('messages.wallet_page.deposit_notes') }}</label>
                        <textarea name="notes" class="nova-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="nova-modal-footer">
                    <button type="button" class="nova-btn nova-btn-outline js-close-wallet-modal">{{ __('messages.wallet_page.cancel') }}</button>
                    <button type="submit" class="nova-btn nova-btn-primary">{{ __('messages.wallet_page.deposit_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
@push('scripts')
<script>
(function () {
    'use strict';

    // النمط Nova: نافذة الإيداع تُفتح بإضافة كلاس show، لأن المشروع لا يحمّل Bootstrap.
    document.addEventListener('click', function (event) {
        const opener = event.target.closest('.js-open-wallet-modal');
        if (opener) {
            const modal = document.getElementById(opener.dataset.modal);
            if (modal) {
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                const amount = modal.querySelector('input[name="amount"]');
                if (amount) { amount.focus(); }
            }
            return;
        }

        const closer = event.target.closest('.js-close-wallet-modal');
        if (closer) {
            const modal = closer.closest('.nova-modal-backdrop');
            if (modal) {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
            }
            return;
        }

        // النقر على الخلفية يغلق النافذة
        if (event.target.classList && event.target.classList.contains('nova-modal-backdrop')) {
            event.target.classList.remove('show');
            event.target.setAttribute('aria-hidden', 'true');
        }
    });

    // إغلاق بالمفتاح Escape
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.nova-modal-backdrop.show').forEach(function (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    });
})();
</script>
@endpush
