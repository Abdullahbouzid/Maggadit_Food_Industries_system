@extends('dashboard.partials.layout')

@section('title', 'Roles Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="shield" class="align-middle me-2"></i>
                            Roles & Permissions
                        </h1>
                        <p class="text-muted">Manage user roles and assign permissions</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        @can('create_roles')
                        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> New Role
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Roles</h6>
                                        <h2 class="mb-0">{{ $roles->total() }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="users" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Permissions</h6>
                                        <h2 class="mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="key" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Admins</h6>
                                        <h2 class="mb-0">
                                            @php
                                                try {
                                                    echo \App\Models\User::role('Admin')->count();
                                                } catch (\Exception $e) {
                                                    echo 0;
                                                }
                                            @endphp
                                        </h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="star" class="text-warning"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Roles List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Guard</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i data-feather="lock" style="width: 16px;"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $role->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($role->permissions->count() > 0)
                                                <span class="badge bg-info bg-opacity-10 text-dark px-3 py-2">
                                                    {{ $role->permissions->pluck('name')->take(3)->join(', ') }}
                                                    @if($role->permissions->count() > 3) ... @endif
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">No permissions</span>
                                            @endif
                                         </td>
                                        <td><code>web</code></td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('edit_roles')
                                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete_roles')
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete('{{ $role->id }}', '{{ addslashes($role->name) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="shield-off" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No roles found</h5>
                                                <p class="text-muted">Click "New Role" to create your first role.</p>
                                                @can('create_roles')
                                                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                                                    <i data-feather="plus-circle"></i> Create Role
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="small text-muted">
                                Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
                            </div>
                            <div>{{ $roles->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
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
                <p class="text-muted">Are you sure you want to delete role <strong id="deleteRoleName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteRoleForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteRoleName').innerText = name;
            document.getElementById('deleteRoleForm').action = '/roles/' + id;
            var modal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
            modal.show();
        };
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush