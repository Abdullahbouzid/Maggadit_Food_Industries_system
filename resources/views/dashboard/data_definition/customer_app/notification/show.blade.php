@extends('dashboard.partials.layout')

@section('title', 'View Notification')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="eye" class="align-middle me-2"></i>
                            Notification Details
                        </h1>
                        <p class="text-muted">View notification details and content</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 p-lg-5">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Subject (EN)</label>
                                        <p class="fs-5">{{ $notification->subject_en }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">العنوان (AR)</label>
                                        <p class="fs-5">{{ $notification->subject_ar }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Body (EN)</label>
                                        <p class="fs-6">{{ $notification->body_en }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">المحتوى (AR)</label>
                                        <p class="fs-6">{{ $notification->body_ar }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted small text-uppercase">Channel</label>
                                        <p><span class="badge bg-info">{{ strtoupper($notification->channel) }}</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted small text-uppercase">Group</label>
                                        <p>{{ $notification->group ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted small text-uppercase">Status</label>
                                        <p>
                                            @if($notification->is_sent)
                                                <span class="badge bg-success">✅ Sent</span>
                                            @else
                                                <span class="badge bg-warning">⏳ Pending</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Customers</label>
                                        <p>
                                            @php
                                                $customers = json_decode($notification->customers ?? '[]', true);
                                            @endphp
                                            @if($customers)
                                                {{ implode(', ', $customers) }}
                                            @else
                                                <span class="text-muted">— All Customers —</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Scheduled At</label>
                                        <p>{{ $notification->scheduled_at ? $notification->scheduled_at->format('F d, Y \a\t h:i A') : '—' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Created At</label>
                                        <p>{{ $notification->created_at->format('F d, Y \a\t h:i A') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted small text-uppercase">Last Updated</label>
                                        <p>{{ $notification->updated_at->format('F d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex gap-3">
                                <a href="{{ route('notifications.edit', $notification) }}" class="btn btn-warning btn-lg px-4">
                                    <i data-feather="edit"></i> Edit
                                </a>
                                <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                    <i data-feather="arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 text-center">
                                <label class="fw-semibold text-muted small text-uppercase d-block mb-3">Media</label>
                                @if($notification->image)
                                    <img src="{{ asset('uploads/' . $notification->image) }}" alt="Image" class="img-fluid rounded border shadow-sm mb-3" style="max-height: 200px; object-fit: cover;">
                                @endif
                                @if($notification->video)
                                    <video controls class="w-100 rounded border shadow-sm">
                                        <source src="{{ asset('uploads/' . $notification->video) }}" type="video/mp4">
                                    </video>
                                @endif
                                @if(!$notification->image && !$notification->video)
                                    <div class="bg-light rounded p-5">
                                        <i data-feather="image" style="width: 60px; height: 60px;" class="text-muted"></i>
                                        <p class="text-muted mt-2">No media attached</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endpush