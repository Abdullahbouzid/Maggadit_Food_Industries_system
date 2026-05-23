{{-- resources/views/admin/permissions/index.blade.php --}}
@extends('dashboard.partials.layout')

@section('title', 'Permissions Management')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12 col-lg-8">
            <h1 class="display-5 fw-bold mb-2">
                <i data-feather="key" class="align-middle me-2"></i>
                System Permissions
            </h1>
            <p class="text-muted">Manage all available permissions in the system</p>
        </div>
        <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
            @can('create_permissions')
            <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i data-feather="plus-circle"></i> New Permission
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0"><i data-feather="list"></i> Permissions List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">#</th><th>Permission Name</th><th>Guard</th><th>Assigned to Roles</th><th class="pe-4 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $perm)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $loop->iteration + ($permissions->currentPage()-1)*$permissions->perPage() }}</td>
                            <td><code>{{ $perm->name }}</code></td>
                            <td>{{ $perm->guard_name }}</td>
                            <td>
                                @php $rolesList = $perm->roles->pluck('name')->take(3)->join(', '); @endphp
                                {{ $rolesList ?: '—' }}
                                @if($perm->roles->count() > 3) ... @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group btn-group-sm">
                                    @can('edit_permissions')
                                    <a href="{{ route('permissions.edit', $perm) }}" class="btn btn-outline-warning"><i data-feather="edit"></i></a>
                                    @endcan
                                    @can('delete_permissions')
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('{{ $perm->id }}', '{{ addslashes($perm->name) }}')"><i data-feather="trash-2"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5">No permissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $permissions->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deletePermissionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body text-center p-4">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3"><i data-feather="alert-triangle" class="text-danger" style="width:40px"></i></div>
                <h4>Confirm Deletion</h4>
                <p>Delete permission <strong id="deletePermName"></strong>?<br>This might affect roles assigned to it.</p>
                <form id="deletePermForm" method="POST" action="">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.confirmDelete = (id, name) => {
        document.getElementById('deletePermName').innerText = name;
        document.getElementById('deletePermForm').action = `/admin/permissions/${id}`;
        new bootstrap.Modal(document.getElementById('deletePermissionModal')).show();
    };
    setTimeout(() => document.querySelectorAll('.alert').forEach(a=>a.classList.add('fade')), 3000);
</script>
@endpush