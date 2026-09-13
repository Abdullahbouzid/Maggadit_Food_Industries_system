@extends('dashboard.partials.layout')

@section('title', 'Dashboard')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-chart-line" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Dashboard Overview
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Welcome back! Here's what's happening today.</p>
    </div>
    <div class="nova-actionbar-actions">
        <button class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-download"></i> Export
        </button>
        <button class="nova-btn nova-btn-secondary nova-btn-sm">
            <i class="fas fa-filter"></i> Filter
        </button>
        <button class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Item
        </button>
    </div>
</div>

<div class="nova-row cols-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <p class="nova-stat-label">Total Sales</p>
        <p class="nova-stat-value">$24,580</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 12.5% this month
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">Customers</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 8.2% this month
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-box"></i>
        </div>
        <p class="nova-stat-label">Products</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 5 new this week
        </p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-file-invoice"></i>
        </div>
        <p class="nova-stat-label">Invoices</p>
        <p class="nova-stat-value">0</p>
        <p class="nova-stat-change up">
            <i class="fas fa-arrow-up"></i> 18.3% this month
        </p>
    </div>
</div>

<div class="nova-row cols-2">
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title">
                <i class="fas fa-chart-line"></i>
                Recent Activity
            </h2>
            <button class="nova-btn nova-btn-ghost nova-btn-sm">View All</button>
        </div>
        <div class="nova-card-body">
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="nova-empty-title">Analytics Coming Soon</h3>
                <p class="nova-empty-text">Charts and graphs will be displayed here</p>
            </div>
        </div>
    </div>
    
    <div class="nova-card">
        <div class="nova-card-header">
            <h2 class="nova-card-title">
                <i class="fas fa-bell"></i>
                Recent Notifications
            </h2>
            <button class="nova-btn nova-btn-ghost nova-btn-sm">View All</button>
        </div>
        <div class="nova-card-body">
            <div class="nova-empty">
                <div class="nova-empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="nova-empty-title">No notifications</h3>
                <p class="nova-empty-text">You're all caught up!</p>
            </div>
        </div>
    </div>
</div>

<div class="nova-card">
    <div class="nova-card-header">
        <h2 class="nova-card-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h2>
    </div>
    <div class="nova-card-body">
        <div class="nova-row cols-4">
            <a href="{{ route('items.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon primary">
                    <i class="fas fa-box"></i>
                </div>
                <p class="nova-stat-label">New Product</p>
            </a>
            <a href="{{ route('customers.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon secondary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <p class="nova-stat-label">New Customer</p>
            </a>
            <a href="{{ route('offers.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon accent">
                    <i class="fas fa-tag"></i>
                </div>
                <p class="nova-stat-label">New Offer</p>
            </a>
            <a href="{{ route('invoices.create') }}" class="nova-stat" style="text-decoration: none; cursor: pointer;">
                <div class="nova-stat-icon danger">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <p class="nova-stat-label">New Invoice</p>
            </a>
        </div>
    </div>
</div>