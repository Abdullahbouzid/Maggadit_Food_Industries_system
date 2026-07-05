@extends('dashboard.partials.layout')

@section('title', 'Notifications Management')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">

                {{-- Header --}}
                <div class="row mb-4">
                    <div class="col-12 col-lg-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="bell" class="align-middle me-2"></i>
                            Send Notification
                        </h1>
                        <p class="text-muted">Manage and send notifications to customers</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('notifications.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i data-feather="plus-circle"></i> Create New
                        </a>
                        <a href="{{ route('notifications.export', request()->query()) }}" class="btn btn-secondary btn-lg shadow-sm ms-2">
                            <i data-feather="download"></i> Export
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

                {{-- Statistics Cards --}}
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Total Notifications</h6>
                                        <h2 class="mb-0">{{ $total }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3"><i data-feather="bell" class="text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Sent</h6>
                                        <h2 class="mb-0">{{ $sent }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3"><i data-feather="check-circle" class="text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Pending</h6>
                                        <h2 class="mb-0">{{ $pending }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3"><i data-feather="clock" class="text-warning"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">Channels</h6>
                                        <h2 class="mb-0">{{ $channelsCount }}</h2>
                                    </div>
                                    <div class="col-auto">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3"><i data-feather="layers" class="text-info"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search Filter --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="{{ route('notifications.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i data-feather="search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by subject or body..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Channel</label>
                                <select name="channel" class="form-select">
                                    <option value="">All Channels</option>
                                    <option value="all" {{ request('channel') == 'all' ? 'selected' : '' }}>All</option>
                                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="push" {{ request('channel') == 'push' ? 'selected' : '' }}>Push</option>
                                    <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Per Page</label>
                                <select name="per_page" class="form-select">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    @if(request('search') || request('channel') || request('status') || request('per_page'))
                                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Data Table --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i data-feather="list"></i> Notifications List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Subject (EN)</th>
                                        <th>Subject (AR)</th>
                                        <th>Channel</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notificationsPaginated as $notification)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $loop->iteration + ($notificationsPaginated->currentPage() - 1) * $notificationsPaginated->perPage() }}</td>
                                        <td>{{ $notification->subject_en }}</td>
                                        <td>{{ $notification->subject_ar }}</td>
                                        <td><span class="badge bg-info">{{ strtoupper($notification->channel) }}</span></td>
                                        <td>{{ $notification->group ?? '—' }}</td>
                                        <td>
                                            @if($notification->is_sent)
                                                <span class="badge bg-success">✅ Sent</span>
                                            @else
                                                <span class="badge bg-warning">⏳ Pending</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('notifications.show', $notification) }}" class="btn btn-outline-info" title="View"><i data-feather="eye"></i></a>
                                                <a href="{{ route('notifications.edit', $notification) }}" class="btn btn-outline-warning" title="Edit"><i data-feather="edit"></i></a>
                                                @if(in_array($notification->channel, ['all', 'push']))
                                                <button type="button" class="btn btn-outline-success" title="Resend" onclick="confirmResend({{ $notification->id }}, '{{ addslashes($notification->subject_en) }}')">
                                                    <i data-feather="send"></i>
                                                </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $notification->id }}, '{{ addslashes($notification->subject_en) }}')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-4">
                                                <i data-feather="bell" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                                <h5 class="text-muted">No notifications found</h5>
                                                <p class="text-muted">Create your first notification now.</p>
                                                <a href="{{ route('notifications.create') }}" class="btn btn-primary"><i data-feather="plus-circle"></i> Create Notification</a>
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
                                Showing {{ $notificationsPaginated->firstItem() ?? 0 }} to {{ $notificationsPaginated->lastItem() ?? 0 }} of {{ $notificationsPaginated->total() }} entries
                            </div>
                            <div>{{ $notificationsPaginated->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Global Delete Modal --}}
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
                <p class="text-muted">Are you sure you want to delete the notification <strong id="deleteNotificationName" class="text-danger"></strong>?<br>This action cannot be undone.</p>
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

{{-- Global Resend Modal --}}
<div class="modal fade" id="globalResendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                    <i data-feather="send" class="text-success" style="width: 40px;"></i>
                </div>
                <h4 class="fw-bold">Confirm Resend</h4>
                <p class="text-muted">Are you sure you want to resend the notification <strong id="resendNotificationName" class="text-success"></strong>?<br>This will send a new push notification to the targeted devices.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="resendForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-success px-4">Resend Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
    .table thead th { border-bottom-width: 1px; font-weight: 600; }
    .btn-group .btn { transition: all 0.2s ease; }
    .btn-group .btn:hover { transform: translateY(-2px); }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') feather.replace();

        const perPageSelect = document.querySelector('select[name="per_page"]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }

        window.confirmDelete = function(id, name) {
            document.getElementById('deleteNotificationName').innerText = name;
            document.getElementById('deleteForm').action = '/notifications/' + id;
            const modal = new bootstrap.Modal(document.getElementById('globalDeleteModal'));
            modal.show();
        };

        window.confirmResend = function(id, name) {
            document.getElementById('resendNotificationName').innerText = name;
            document.getElementById('resendForm').action = '/notifications/' + id + '/resend';
            const modal = new bootstrap.Modal(document.getElementById('globalResendModal'));
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