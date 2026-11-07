@props([
    'id'    => 'deleteModal',
    'title' => 'Confirm Deletion',
])

<div class="nova-modal-backdrop" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-delete-modal">
            <div class="nova-delete-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h4 class="nova-delete-modal-title">{{ $title }}</h4>
            <p class="nova-delete-modal-text">
                Are you sure you want to delete
                <strong data-delete-target class="nova-text-danger"></strong>?
                <br>This action cannot be undone.
            </p>
            <div class="nova-delete-modal-actions">
                <button type="button" class="nova-btn nova-btn-outline"
                        onclick="document.getElementById('{{ $id }}').classList.remove('show')">
                    Cancel
                </button>
                <form data-delete-form method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(btn) {
        const name    = btn.dataset.name || 'this item';
        const url     = btn.dataset.url;
        const modalId = btn.dataset.modal || 'deleteModal';

        const modal = document.getElementById(modalId);
        if (!modal) { console.error('Modal not found:', modalId); return; }

        modal.querySelector('[data-delete-target]').innerText = name;
        modal.querySelector('[data-delete-form]').action = url;
        modal.classList.add('show');
    };

    document.querySelectorAll('.nova-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) backdrop.classList.remove('show');
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.nova-modal-backdrop.show').forEach(function(m) {
                m.classList.remove('show');
            });
        }
    });
});
</script>
@endpush
@endonce