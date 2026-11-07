@props([
    'viewUrl'     => null,
    'editUrl'     => null,
    'deleteUrl'   => null,
    'deleteName'  => '',
    'deleteModal' => 'deleteModal',
    'extra'       => null,
])

<div class="nova-table-actions">
    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="nova-icon-btn nova-btn-sm" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if($editUrl)
        <a href="{{ $editUrl }}" class="nova-icon-btn nova-btn-sm" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if($extra)
        {{ $extra }}
    @endif

    @if($deleteUrl)
        <button type="button"
                class="nova-icon-btn nova-btn-sm nova-text-danger"
                title="Delete"
                onclick="confirmDelete(this)"
                data-url="{{ $deleteUrl }}"
                data-name="{{ $deleteName }}"
                data-modal="{{ $deleteModal }}">
            <i class="fas fa-trash-alt"></i>
        </button>
    @endif
</div>