@extends('dashboard.partials.layout')

@section('title', 'Customer Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="user" class="align-middle me-2"></i>
                            Customer Details
                        </h1>
                        <p class="text-muted">View complete information about <strong>{{ $customer->name }}</strong></p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-lg shadow-sm me-1">
                            <i data-feather="edit"></i> Edit
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm">
                            <i data-feather="arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <!-- Customer Profile Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Avatar Column -->
                            <div class="col-md-4 text-center">
                                @if($customer->avatar)
                                    <img src="{{ asset('uploads/customers/' . $customer->avatar) }}" alt="{{ $customer->name }}" class="img-fluid rounded-circle" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                @else
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px; font-size: 4rem; color: #6c757d; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Details Column -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h2 class="fw-bold">{{ $customer->name }}</h2>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">Username</label>
                                        <p class="fw-semibold">{{ $customer->username }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">Email</label>
                                        <p><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">Active Organization</label>
                                        <p>
                                            @if($customer->activeOrganization)
                                                <span class="badge bg-success bg-opacity-10 text-dark px-3 py-2">
                                                    {{ $customer->activeOrganization->name }}
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">None</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">All Organizations</label>
                                        <p>
                                            @if($customer->organizations->count())
                                                @foreach($customer->organizations as $org)
                                                    <span class="badge bg-info bg-opacity-10 text-dark me-1">{{ $org->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted fst-italic">No organizations assigned</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">Created At</label>
                                        <p>{{ $customer->created_at->format('M d, Y H:i:s') }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fw-semibold text-muted">Updated At</label>
                                        <p>{{ $customer->updated_at->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional: Additional actions -->
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning px-4">
                        <i data-feather="edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger px-4" onclick="confirmDelete('{{ $customer->id }}', '{{ addslashes($customer->name) }}')">
                        <i data-feather="trash-2"></i> Delete
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal (same as index) -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
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
                <p class="text-muted">Are you sure you want to delete customer <strong id="deleteCustomerName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteCustomerForm" method="POST" action="">
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
        // Define delete confirmation function (reused from index)
        window.confirmDelete = function(id, name) {
            document.getElementById('deleteCustomerName').innerText = name;
            document.getElementById('deleteCustomerForm').action = '/customers/' + id;
            var modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
            modal.show();
        };
        // Auto-dismiss alerts if any (though not shown in show, but for safety)
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush