@extends('dashboard.partials.layout')

@section('title', __('messages.dashboard.page_title'))

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-chart-line" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.dashboard.dashboard_overview') }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">{{ __('messages.dashboard.welcome_back') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <button class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> {{ __('messages.dashboard.export') }}
        </button>
        <button class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-filter"></i> {{ __('messages.dashboard.filter') }}
        </button>
        <button class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.dashboard.new_item') }}
        </button>
    </div>
</div>

<div class="nova-row cols-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.dashboard.total_sales') }}</p>
        <p class="nova-stat-value">$24,580</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 12.5% {{ __('messages.dashboard.this_month') }}
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.dashboard.customers_label') }}</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 8.2% {{ __('messages.dashboard.this_month') }}
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-box"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.dashboard.products_label') }}</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 5 {{ __('messages.dashboard.this_week') }}
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-file-invoice"></i>
        </div>
        <p class="nova-stat-label">{{ __('messages.dashboard.invoices_label') }}</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 18.3% {{ __('messages.dashboard.this_month') }}
        </p>
    </div>
</div>

<div class="nova-row cols-2">
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title">
                <i class="fas fa-chart-line"></i>
                {{ __('messages.dashboard.recent_activity') }}
            </h2>
            <button class="nova-btn nova-btn-ghost nova-btn-sm">{{ __('messages.dashboard.view_all') }}</button>
        </div>
        <div class="nova-card-body">
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="nova-empty-title">{{ __('messages.dashboard.analytics_coming_soon') }}</h3>
                <p class="nova-empty-text">{{ __('messages.dashboard.charts_will_appear_here') }}</p>
            </div>
        </div>
    </div>

    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title">
                <i class="fas fa-bell"></i>
                {{ __('messages.dashboard.recent_notifications') }}
            </h2>
            <button class="nova-btn nova-btn-ghost nova-btn-sm">{{ __('messages.dashboard.view_all') }}</button>
        </div>
        <div class="nova-card-body">
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="nova-empty-title">{{ __('messages.dashboard.no_notifications') }}</h3>
                <p class="nova-empty-text">{{ __('messages.dashboard.all_caught_up') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-bolt"></i>
            {{ __('messages.dashboard.quick_actions') }}
        </h2>
    </div>
    <div class="nova-card-body">
        <div class="nova-row cols-4">
            <a href="{{ route('items.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon primary">
                    <i class="fas fa-box"></i>
                </div>
                <p class="nova-stat-label">{{ __('messages.navbar.new_product') }}</p>
            </a>
            <a href="{{ route('customers.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon secondary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <p class="nova-stat-label">{{ __('messages.navbar.new_customer') }}</p>
            </a>
            <a href="{{ route('offers.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon accent">
                    <i class="fas fa-tag"></i>
                </div>
                <p class="nova-stat-label">{{ __('messages.navbar.new_offer') }}</p>
            </a>
            <a href="{{ route('invoices.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon danger">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <p class="nova-stat-label">{{ __('messages.navbar.new_invoice') }}</p>
            </a>
        </div>
    </div>
</div>
@endsection
