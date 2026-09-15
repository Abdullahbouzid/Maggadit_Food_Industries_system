@extends('dashboard.partials.layout')

@section('title', '{{ __('messages.app.page__1') }}')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-{{ __('messages.app.page_') }}">
            <i class="fas fa-dollar-sign" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.app.page__1') }}
        </h2>
        <p class="nova-page-sub{{ __('messages.app.page_') }}" style="margin: 0.25rem 0 0;">{{ __('messages.app.page__5') }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('price-lists.create') }}" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> {{ __('messages.app.page__12') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-table-wrapper">
    <div class="nova-toolbar">
        <div class="nova-toolbar-filters">
            <h3 class="nova-card-{{ __('messages.app.page_') }}" style="margin: 0;">
                <i class="fas fa-list"></i>
                {{ __('messages.app.page__1') }}
            </h3>
        </div>
        <div class="nova-toolbar-search">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search price lists...">
        </div>
    </div>
    <div class="nova-table-scroll">
        <table class="nova-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.app.page__14') }}</th>
                    <th>{{ __('messages.app.page__15') }}</th>
                    <th>{{ __('messages.app.page__16') }}</th>
                    <th>{{ __('messages.app.page__17') }}</th>
                    <th>{{ __('messages.app.page__18') }}</th>
                    <th class="nova-text-end">{{ __('messages.app.page__19') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($priceLists as $priceList)
                <tr>
                    <td>{{ $priceList->id }}</td>
                    <td><span class="nova-fw-bold">{{ $priceList->name }}</span></td>
                    <td><small class="nova-text-muted">{{ Str::limit($priceList->description, 50) }}</small></td>
                    <td>
                        @if($priceList->status === 'active')
                            <span class="nova-badge success">{{ __('messages.app.page__8') }}</span>
                        @else
                            <span class="nova-badge secondary">{{ __('messages.app.page__9') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="nova-badge secondary">{{ $priceList->items_count ?? 0 }}</span>
                    </td>
                    <td><small class="nova-text-muted">{{ $priceList->creator?->name ?? '—' }}</small></td>
                    <td class="nova-text-end">
                        <div class="nova-table-actions">
                            <a href="{{ route('price-lists.show', $priceList) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('price-lists.edit', $priceList) }}" class="nova-icon-btn nova-btn-sm" {{ __('messages.app.page_') }}="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('price-lists.toggle-status', $priceList) }}" method="POST" class="nova-delete-form">
                                @csrf
                                <button type="submit" class="nova-icon-btn nova-btn-sm {{ $priceList->status === 'active' ? 'nova-text-warning' : 'nova-text-primary' }}" {{ __('messages.app.page_') }}="{{ $priceList->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $priceList->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="nova-icon-btn nova-btn-sm nova-text-danger" {{ __('messages.app.page_') }}="{{ __('messages.app.page__11') }}" onclick="confirm{{ __('messages.app.page__11') }}('{{ $priceList->id }}', '{{ addslashes($priceList->name) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="nova-empty">
                            <div class="nova-empty-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="nova-empty-{{ __('messages.app.page_') }}">{{ __('messages.app.page__3') }}</h3>
                            <p class="nova-empty-text">{{ __('messages.app.page__6') }}</p>
                            <a href="{{ route('price-lists.create') }}" class="nova-btn nova-btn-primary">
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
        <div class="nova-pagination">
            {{ $priceLists->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

{{-- {{ __('messages.app.page__11') }} Confirmation Modal (Nova) --}}
<div class="nova-modal-backdrop" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-{{ __('messages.app.page_') }}">{{ __('messages.app.page__4') }}</h4>
            <p class="nova-delete-modal-text">Are you sure you want to delete price list <strong id="delete{{ __('messages.app.page__14') }}" class="nova-text-danger"></strong>?<br>This action cannot be undone.</p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline" onclick="document.getElementById('deleteModal').classList.remove('show')">{{ __('messages.app.page__10') }}</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">{{ __('messages.app.page__11') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirm{{ __('messages.app.page__11') }} = function(id, name) {
        document.getElementById('delete{{ __('messages.app.page__14') }}').innerText = name;
        document.getElementById('deleteForm').action = '/price-lists/' + id;
        document.getElementById('deleteModal').classList.add('show');
    };
});
</script>
@endpush