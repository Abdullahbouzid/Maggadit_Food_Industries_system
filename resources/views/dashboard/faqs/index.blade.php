@extends('dashboard.partials.layout')

@section('title', 'FAQs')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-question-circle" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            FAQs
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Manage frequently asked questions</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('faqs.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> New Question
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
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('error') }}
    </div>
@endif

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-title" style="margin: 0;">
                <i class="fas fa-list"></i>
                FAQs List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <form method="GET" action="{{ route('faqs.index') }}" class="nova-search-form">
                <input type="search" name="search" placeholder="Search FAQs..." value="{{ request('search') }}">
            </form>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Question</th>
                    <th class="nova-text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $faq->category ?? 'بدون فئة' }}</td>
                    <td>{{ $faq->question }}</td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <x-table-actions
                                :view-url="null"
                                :edit-url="route('faqs.edit', $faq->id)"
                                :delete-url="route('faqs.destroy', $faq->id)"
                                :delete-name="$faq->question"
                                delete-modal="deleteFaqModal" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h3 class="nova-empty-title">No FAQs found</h3>
                            <p class="nova-empty-text">Click "New Question" to create your first FAQ.</p>
                            <a href="{{ route('faqs.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> Create FAQ
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
                @if($faqs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    Showing {{ $faqs->firstItem() }} to {{ $faqs->lastItem() }} of {{ $faqs->total() }} entries
                    <div class="nova-pagination">
                        {{ $faqs->links() }}
                    </div>
                @else
                    Showing {{ $faqs->count() }} entries
                @endif
            </div>
        </div>
    </div>
</div>

<x-delete-modal modal-id="deleteFaqModal" />
