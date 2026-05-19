@extends('dashboard.partials.layout')

@section('title', 'Cities List')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col">
            <h1><i data-feather="map"></i> All Cities</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('cities.create') }}" class="btn btn-primary">
                <i data-feather="plus-circle"></i> Add City
            </a>
            <a href="{{ route('geographical.index') }}" class="btn btn-secondary ms-2">
                <i data-feather="arrow-left"></i> Back to Map
            </a>
        </div>
    </div>

    <div class="card shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>City Name</th>
                        <th>Code</th>
                        <th>Country</th>
                        <th>Coordinates</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cities as $city)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $city->name }}</td>
                        <td>{{ $city->code ?? '—' }}</td>
                        <td>{{ $city->country->name ?? '—' }}</td>
                        <td>
                            @if($city->latitude && $city->longitude)
                                {{ $city->latitude }}, {{ $city->longitude }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cities.show', $city) }}" class="btn btn-outline-info" title="View">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('cities.edit', $city) }}" class="btn btn-outline-warning" title="Edit">
                                    <i data-feather="edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDeleteCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i data-feather="map" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                            <h5 class="text-muted">No cities found</h5>
                            <a href="{{ route('cities.create') }}" class="btn btn-primary mt-2">
                                <i data-feather="plus-circle"></i> Add your first city
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p>Delete city <strong id="deleteCityName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteCityForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: translateY(-2px); }
</style>
@endpush

@push('scripts')
<script>
    window.confirmDeleteCity = function(id, name) {
        document.getElementById('deleteCityName').innerText = name;
        document.getElementById('deleteCityForm').action = '/cities/' + id;
        new bootstrap.Modal(document.getElementById('deleteCityModal')).show();
    };
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush