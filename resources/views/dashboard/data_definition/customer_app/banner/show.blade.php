@extends('dashboard.partials.layout')

@section('title', 'Banner Details')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="image" class="align-middle me-2"></i>
                            Banner Details
                        </h1>
                        <p class="text-muted">View full information about this banner</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('banners.edit', $banner) }}" class="btn btn-warning btn-lg shadow-sm">
                            <i data-feather="edit"></i> Edit
                        </a>
                        <a href="{{ route('banners.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Details Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        <div class="row g-4">

                            {{-- Image --}}
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    @if($banner->image)
                                        <img src="{{ asset('uploads/' . $banner->image) }}" alt="{{ $banner->title }}" class="img-fluid rounded border shadow-sm" style="max-height: 250px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded p-5">
                                            <i data-feather="image" style="width: 64px; height: 64px;" class="text-muted"></i>
                                            <p class="text-muted mt-2">No image uploaded</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="col-md-8">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Title</label>
                                            <p class="fw-semibold fs-5 mb-0">{{ $banner->title ?? '—' }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Type</label>
                                            <p class="fw-semibold fs-5 mb-0"><span class="badge bg-info">{{ ucfirst($banner->type) }}</span></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">URL</label>
                                            <p class="fw-semibold fs-5 mb-0">
                                                @if($banner->url)
                                                    <a href="{{ $banner->url }}" target="_blank" class="text-primary">{{ $banner->url }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Order</label>
                                            <p class="fw-semibold fs-5 mb-0">{{ $banner->order }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Show in Customer App</label>
                                            <p class="fw-semibold fs-5 mb-0">
                                                @if($banner->show_in_customer_app)
                                                    <span class="badge bg-success">Visible</span>
                                                @else
                                                    <span class="badge bg-secondary">Hidden</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Created At</label>
                                            <p class="fw-semibold fs-5 mb-0">{{ $banner->created_at->format('Y-m-d h:i A') }}</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border-bottom pb-2">
                                            <label class="text-muted text-uppercase small fw-semibold">Last Updated</label>
                                            <p class="fw-semibold fs-5 mb-0">{{ $banner->updated_at->diffForHumans() }}</p>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- Optional: Additional Actions --}}
                <div class="mt-4">
                    <div class="d-flex gap-2">
                        <a href="{{ route('banners.index') }}" class="btn btn-outline-secondary"><i data-feather="arrow-left"></i> Back to Banners</a>
                        <a href="{{ route('banners.edit', $banner) }}" class="btn btn-warning"><i data-feather="edit"></i> Edit</a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ $banner->id }}', '{{ addslashes($banner->title ?? 'Banner') }}')">
                            <i data-feather="trash-2"></i> Delete
                        </button>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="globalDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="alert-triangle" class="text-danger" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Deletion</h4>
                <p class="text-muted">Are you sure you want to delete the banner <strong id="deleteBannerName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        window.confirmDelete = function(id, name) {
            document.getElementById('deleteBannerName').innerText = name;
            document.getElementById('deleteForm').action = '/banners/' + id;
            const modal = new bootstrap.Modal(document.getElementById('globalDeleteModal'));
            modal.show();
        };

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (typeof bootstrap !== 'undefined') {
                    let bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
    });
</script>
@endpush