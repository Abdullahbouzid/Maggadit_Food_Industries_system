@extends('dashboard.partials.layout')

@section('title', '{{ __('messages.app.page__1') }}')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-question-circle" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('faqs.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.app.page__10') }}
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
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.app.page__1') }} List
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <form method="GET" action="{{ route('faqs.index') }}" class="nova-search-form">
                <input type="search" name="search" placeholder="Search {{ __('messages.app.page__1') }}..." value="{{ request('search') }}">
            </form>
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__13') }}</th>
                    <th>{{ __('messages.app.page__14') }}</th>
                    <th class="nova-text-end">{{ __('messages.app.page__15') }}</th>
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
                            <a href="{{ route('faqs.edit', $faq->id) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="Delete" onclick="confirmDelete('{{ $faq->id }}', '{{ addslashes($faq->question) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">No {{ __('messages.app.page__1') }} found</h3>
                            <p class="nova-empty-text">{{ __('messages.app.page__6') }}</p>
                            <a href="{{ route('faqs.create') }}" class="nova-btn nova-btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.app.page__12') }}
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

{{-- Delete Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteFaqModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete FAQ <strong id="deleteFaqName" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteFaqModal').classList.remove('show')">{{ __('messages.app.page__8') }}</button>
                <form id="deleteFaqForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__9') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteFaqName').innerText = name;
        document.getElementById('deleteFaqForm').action = '/faqs/' + id;
        document.getElementById('deleteFaqModal').classList.add('show');
    };
});
</script>
@endpush
