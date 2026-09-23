@extends('dashboard.partials.layout')

@section('title', __('messages.return_page.title'))

@section('content')

{{-- Hero Header --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-undo" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.return_page.title') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ __('messages.return_page.subtitle') }}
        </p>
    </div>
    <div>
        <a href="javascript:void(0)" class="nova-btn nova-btn-primary" title="{{ __('messages.return_page.add_return') }}">
            <i class="fas fa-plus"></i> {{ __('messages.return_page.add_return') }}
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-body">
        <form method="GET" action="{{ route('returns.index') }}" class="nova-filter-bar">
            <div class="nova-row nova-gap-3" style="align-items: end;">
                <div class="nova-form-group" style="min-width: 180px;">
                    <label class="nova-label">{{ __('messages.return_page.filter_status') }}</label>
                    <select name="status" class="nova-select">
                        <option value="">{{ __('messages.return_page.status_all') }}</option>
                        @foreach(\App\Enums\ReturnStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ (request('status') ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group" style="min-width: 180px;">
                    <label class="nova-label">{{ __('messages.return_page.filter_type') }}</label>
                    <select name="type" class="nova-select">
                        <option value="">{{ __('messages.return_page.type_all') }}</option>
                        @foreach(\App\Enums\ReturnType::cases() as $type)
                            <option value="{{ $type->value }}" {{ (request('type') ?? '') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nova-form-group">
                    <input type="text" name="invoice" class="nova-input" value="{{ request('invoice') }}" placeholder="{{ __('messages.return_page.filter_invoice') }}">
                </div>
                <div class="nova-form-group">
                    <input type="text" name="customer" class="nova-input" value="{{ request('customer') }}" placeholder="{{ __('messages.return_page.filter_customer') }}">
                </div>
                <div class="nova-form-group">
                    <input type="date" name="date_from" class="nova-input" value="{{ request('date_from') }}">
                </div>
                <div class="nova-form-group">
                    <input type="date" name="date_to" class="nova-input" value="{{ request('date_to') }}">
                </div>
                <div class="nova-form-group nova-flex" style="gap: 0.5rem; margin: 0;">
                    <button type="submit" class="nova-btn nova-btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.return_page.apply') }}
                    </button>
                    <a href="{{ route('returns.index') }}" class="nova-btn nova-btn-outline">
                        <i class="fas fa-undo"></i> {{ __('messages.return_page.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Status Tabs --}}
<div class="nova-status-tabs">
    <a href="{{ route('returns.index', array_merge(request()->except(['status', 'page']), ['status' => null])) }}"
       class="nova-status-tab {{ empty(request('status')) ? 'active' : '' }}">
        {{ __('messages.return_page.status_all') }}
        <span class="nova-badge">{{ $statusCounts->total ?? $returns->count() }}</span>
    </a>
    @foreach(\App\Enums\ReturnStatus::cases() as $status)
        <a href="{{ route('returns.index', array_merge(request()->except(['status', 'page']), ['status' => $status->value])) }}"
           class="nova-status-tab {{ (request('status') ?? '') === $status->value ? 'active' : '' }}">
            {{ $status->label() }}
            <span class="nova-badge">{{ $statusCounts[$status->value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Returns Table --}}
<div class="nova-card">
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.return_page.col_return_number') }}</th>
                        <th>{{ __('messages.return_page.col_invoice') }}</th>
                        <th>{{ __('messages.return_page.col_customer') }}</th>
                        <th>{{ __('messages.return_page.col_type') }}</th>
                        <th>{{ __('messages.return_page.col_reason') }}</th>
                        <th>{{ __('messages.return_page.col_amount') }}</th>
                        <th>{{ __('messages.return_page.col_status') }}</th>
                        <th>{{ __('messages.return_page.col_date') }}</th>
                        <th class="nova-text-end">{{ __('messages.return_page.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td><span class="nova-fw-semibold">{{ $return->id }}</span></td>
                        <td>{{ $return->return_number ?? '—' }}</td>
                        <td>{{ $return->invoice?->invoice_number ?? '—' }}</td>
                        <td>{{ $return->invoice?->customer->name ?? '—' }}</td>
                        <td>
                            @php
                                $rtype = \App\Enums\ReturnType::tryFrom($return->return_type);
                            @endphp
                            @if($rtype)
                                <span class="nova-badge">{{ $rtype->label() }}</span>
                            @else
                                <span class="nova-text-muted">{{ $return->return_type ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $return->reason ?? '—' }}</td>
                        <td>{{ $return->amount ? number_format($return->amount, 2) : '—' }}</td>
                        <td>
                            @php
                                $rstatus = \App\Enums\ReturnStatus::tryFrom($return->status);
                                $badgeClass = $rstatus?->badgeColor() ?? 'nova-text-muted';
                            @endphp
                            @if($rstatus)
                                <span class="nova-badge {{ $badgeClass }}">{{ $rstatus->label() }}</span>
                            @else
                                <span class="nova-text-muted">{{ $return->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $return->created_at ? \Carbon\Carbon::parse($return->created_at)->format('Y-m-d') : '—' }}</td>
                        <td class="nova-text-end">
                            <div class="nova-action-btns" style="display: inline-flex; gap: 0.375rem;">
                                <a href="javascript:void(0)" class="nova-icon-btn nova-btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-undo" style="font-size: 3rem; color: var(--nova-text-3); margin-bottom: 1rem;"></i>
                                <p>{{ __('messages.return_page.empty') }}</p>
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
                    {{ __('messages.return_page.showing_count', ['count' => $returns->count()]) }}
                </div>
                <div class="nova-pagination">
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection