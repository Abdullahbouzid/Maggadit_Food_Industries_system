@extends('dashboard.partials.layout')
@section('title','Competitor Details')
@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i data-feather="info"></i> Competitor Details</h1>
            <p class="text-muted">Complete information for {{ $competitor->name }}</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('competitors.edit', $competitor) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
            <a href="{{ route('competitors.index') }}" class="btn btn-secondary"><i data-feather="arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white"><h5>Basic Information</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6"><strong>Name:</strong> {{ $competitor->name }}</div>
                <div class="col-md-6"><strong>Code:</strong> {{ $competitor->code }}</div>
                <div class="col-md-6"><strong>Organization:</strong> {{ $competitor->organization?->name ?? '—' }}</div>
                <div class="col-md-6"><strong>Competitor Type:</strong> {{ $competitor->competitor_type ?? '—' }}</div>
                <div class="col-md-6"><strong>GLN:</strong> {{ $competitor->gln ?? '—' }}</div>
                <div class="col-md-6"><strong>Contact Person:</strong> {{ $competitor->contact_person ?? '—' }}</div>
                <div class="col-md-6"><strong>Phone:</strong> {{ $competitor->phone ?? '—' }}</div>
                <div class="col-md-6"><strong>Email:</strong> {{ $competitor->email ?? '—' }}</div>
                <div class="col-12 mt-3"><strong>Address:</strong><br>{{ $competitor->address ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="card border-danger shadow-sm rounded-4">
        <div class="card-header bg-white text-danger"><h5>Danger Zone</h5></div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8"><p class="mb-0 text-muted">Permanently delete this competitor. This action cannot be undone.</p></div>
                <div class="col-md-4 text-md-end"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $competitor->id }}, '{{ addslashes($competitor->name) }}')"><i data-feather="trash-2"></i> Delete Competitor</button></div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal (same as index) -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger" style="width:40px"></i></div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p class="text-muted">Are you sure you want to delete <strong id="deleteCompetitorName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('competitors.destroy', $competitor) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteCompetitorName').innerText = name;
        new bootstrap.Modal(document.getElementById('globalDeleteModal')).show();
    }
</script>
@endsection