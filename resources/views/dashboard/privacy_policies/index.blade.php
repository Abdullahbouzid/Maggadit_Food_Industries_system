@extends('dashboard.partials.layout')

@section('title', 'Privacy Policies')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-shield-alt" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Privacy & Terms
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage privacy policies and terms of service</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> Add Policy
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-3">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-shield-alt"></i>
        </div>
        <p class="nova-stat-label">Total Policies</p>
        <p class="nova-stat-value">{{ $policies->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-file-text"></i>
        </div>
        <p class="nova-stat-label">Active Policies</p>
        <p class="nova-stat-value">{{ $policies->where('is_active', true)->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <p class="nova-stat-label">Privacy Policies</p>
        <p class="nova-stat-value">{{ $policies->where('type', 'privacy_policy')->count() }}</p>
    </div>
</div>

{{-- Policies Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                Policies List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search policies...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Policy
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $policy)
                <tr>
                    <td>{{ $policy->id }}</td>
                    <td>
                        <span class="nova-fw-bold">{{ $policy->title ?? $policy->type }}</span>
                    </td>
                    <td>
                        @if($policy->type == 'privacy_policy')
                            <span class="nova-badge secondary">Privacy Policy</span>
                        @else
                            <span class="nova-badge accent">Terms of Service</span>
                        @endif
                    </td>
                    <td>
                        @if($policy->is_active)
                            <span class="nova-badge success">Active</span>
                        @else
                            <span class="nova-badge secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $policy?->created_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('privacy-policies.show', $policy) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('privacy-policies.edit', $policy) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                             <x-table-actions
                                :view-url="null"
                                :edit-url="null"
                                :delete-url="route('privacy-policies.destroy', $policy)"
                                :delete-name="$policy->title ?? ($policy->type == 'privacy_policy' ? 'سياسة الخصوصية' : 'شروط الخدمة')"
                                delete-modal="deletePolicyModal" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="nova-empty-title">No policies found</h3>
                            <p class="nova-empty-text">Create your first policy to get started.</p>
                            <a href="{{ route('privacy-policies.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Add Policy
                            </a>
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
                عرض {{ $policies->firstItem() ?? 0 }} إلى {{ $policies->lastItem() ?? 0 }} من أصل {{ $policies->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $policies->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

<x-delete-modal modal-id="deletePolicyModal" />