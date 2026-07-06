@extends('dashboard.partials.layout')

@section('title', 'Create Notification')

@section('content')
<div class="wrapper">
    <div class="main">
        <main class="content">
            <div class="container-fluid p-0">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-2">
                            <i data-feather="bell" class="align-middle me-2"></i>
                            Create Notification
                        </h1>
                        <p class="text-muted">Send a new notification to your customers</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <form action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4">
                                <!-- Subject -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Subject (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject_en" class="form-control @error('subject_en') is-invalid @enderror" value="{{ old('subject_en') }}" placeholder="Enter subject in English">
                                    @error('subject_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">العنوان (بالعربية) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject_ar" class="form-control @error('subject_ar') is-invalid @enderror" value="{{ old('subject_ar') }}" placeholder="أدخل العنوان بالعربية">
                                    @error('subject_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Body -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Body (English) <span class="text-danger">*</span></label>
                                    <textarea name="body_en" class="form-control @error('body_en') is-invalid @enderror" rows="4" placeholder="Enter body content in English">{{ old('body_en') }}</textarea>
                                    @error('body_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المحتوى (بالعربية) <span class="text-danger">*</span></label>
                                    <textarea name="body_ar" class="form-control @error('body_ar') is-invalid @enderror" rows="4" placeholder="أدخل المحتوى بالعربية">{{ old('body_ar') }}</textarea>
                                    @error('body_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Image -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Image <small class="text-muted">(optional)</small></label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    <small class="text-muted">Max 2MB (JPG, PNG, GIF, SVG)</small>
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Video -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Video <small class="text-muted">(optional)</small></label>
                                    <input type="file" name="video" class="form-control @error('video') is-invalid @enderror" accept="video/*">
                                    <small class="text-muted">Max 20MB (MP4, AVI, MOV)</small>
                                    @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Group -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Group <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="group" class="form-control @error('group') is-invalid @enderror" value="{{ old('group') }}" placeholder="e.g., VIP, New Users">
                                    @error('group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Channel -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Channel <span class="text-danger">*</span></label>
                                    <select name="channel" class="form-select @error('channel') is-invalid @enderror">
                                        <option value="all" {{ old('channel') == 'all' ? 'selected' : '' }}>All Channels</option>
                                        <option value="email" {{ old('channel') == 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="push" {{ old('channel') == 'push' ? 'selected' : '' }}>Push Notification</option>
                                        <option value="sms" {{ old('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                                    </select>
                                    @error('channel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Icon Type -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Icon Type <span class="text-danger">*</span></label>
                                    <select name="icon_type" class="form-select @error('icon_type') is-invalid @enderror">
                                        <option value="notification" {{ old('icon_type') == 'notification' ? 'selected' : '' }}>General Bell (إشعار عام)</option>
                                        <option value="offer" {{ old('icon_type') == 'offer' ? 'selected' : '' }}>Offer (عرض)</option>
                                        <option value="payment" {{ old('icon_type') == 'payment' ? 'selected' : '' }}>Payment (دفع)</option>
                                        <option value="receive" {{ old('icon_type') == 'receive' ? 'selected' : '' }}>Receive (استلام)</option>
                                        <option value="delivery" {{ old('icon_type') == 'delivery' ? 'selected' : '' }}>Delivery (توصيل)</option>
                                    </select>
                                    @error('icon_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Customers -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Customers <small class="text-muted">(optional)</small></label>
                                    <select name="customers[]" class="form-select @error('customers') is-invalid @enderror" multiple>
                                        <option value="all" {{ old('customers') && in_array('all', old('customers')) ? 'selected' : '' }}>All Customers (الكل)</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customers') && in_array($customer->id, old('customers')) ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    @error('customers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Scheduled At -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Schedule <small class="text-muted">(optional)</small></label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}">
                                    <small class="text-muted">Leave empty to send immediately</small>
                                    @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Send Now -->
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4 pt-2">
                                        <input type="hidden" name="is_sent" value="0">
                                        <input type="checkbox" name="is_sent" class="form-check-input" id="isSent" value="1" {{ old('is_sent') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="isSent">Send Now</label>
                                        <br>
                                        <small class="text-muted">Check to send immediately, or schedule for later</small>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i data-feather="send"></i> Send Notification
                                        </button>
                                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                            <i data-feather="x"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
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