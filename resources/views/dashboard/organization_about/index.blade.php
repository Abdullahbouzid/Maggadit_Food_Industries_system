@extends('dashboard.partials.layout')

@section('title', 'Organization About')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-info-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Organization About
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage 'About Us' page content for organizations</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Entry
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif
@if(session('warning'))
    <div class="nova-alert warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('warning') }}
    </div>
@endif

@if(session('import_errors'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>أخطاء الاستيراد:</strong>
        <ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-info-circle"></i>
        </div>
        <p class="nova-stat-label">Total Entries</p>
        <p class="nova-stat-value">{{ $abouts->total() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-building"></i>
        </div>
        <p class="nova-stat-label">Active Organizations</p>
        <p class="nova-stat-value">{{ $abouts->where('is_active', true)->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-edit"></i>
        </div>
        <p class="nova-stat-label">With Title</p>
        <p class="nova-stat-value">{{ $abouts->whereNotNull('title')->count() }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon danger">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <p class="nova-stat-label">Inactive</p>
        <p class="nova-stat-value">{{ $abouts->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- About Table --}}
<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                About List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" class="nova-input" placeholder="Search about entries...">
        </div>
        <div class="nova-toolbar-actions">
            <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> Add Entry
            </a>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Organization</th>
                    <th>Title</th>
                    <th>Vision</th>
                    <th>Status</th>
                    <th class="nova-text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($abouts as $about)
                <tr class="{{ $about->is_active ? '' : 'nova-row-inactive' }}">
                    <td class="nova-fw-semibold">{{ $loop->iteration + ($abouts->currentPage() - 1) * $abouts->perPage() }}</td>
                    <td>
                        <div class="nova-flex nova-items-center">
                            <div class="nova-stat-icon primary nova-me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-building"></i>
                            </div>
                            <span>{{ $about->organization->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>{{ $about->title ?? '—' }}</td>
                    <td>{{ Str::limit($about->vision, 30) ?: '—' }}</td>
                    <td>
                        @if($about->is_active)
                            <span class="nova-badge success">Active</span>
                        @else
                            <span class="nova-badge secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('organization-about.show', $about) }}" class="nova-icon-btn nova-btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('organization-about.edit', $about) }}" class="nova-icon-btn nova-btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <x-table-actions
                                :view-url="null"
                                :edit-url="null"
                                :delete-url="route('organization-about.destroy', $about)"
                                :delete-name="$about->title ?? 'About Entry'"
                                delete-modal="deleteModal" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="nova-empty-title">No about entries found</h3>
                            <p class="nova-empty-text">Create your first about entry to get started.</p>
                            <a href="{{ route('organization-about.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create Entry
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
                Showing {{ $abouts->firstItem() ?? 0 }} to {{ $abouts->lastItem() ?? 0 }} of {{ $abouts->total() }} entries
            </div>
            <div class="nova-pagination">
                {{ $abouts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<x-delete-modal modal-id="deleteModal" />

<style>
.nova-row-inactive {
    background: rgba(148, 163, 184, 0.05);
}
.nova-me-2 {
    margin-right: 0.5rem;
}
</style>
