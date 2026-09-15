@extends('dashboard.partials.layout')

@section('title', 'تعديل لاtification')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تعديل لاtification
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Update notification details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> رجوع to لاtifications
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-bell"></i> لاtification التفاصيل
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('notifications.update', $notification) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="nova-row g-4">
                {{-- Subject EN --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> Subject (English) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="subject_en" class="nova-input" value="{{ old('subject_en', $notification->subject_en) }}" placeholder="Enter subject in English">
                        @error('subject_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Subject AR --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> العنوان (بالعربية) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="subject_ar" class="nova-input" value="{{ old('subject_ar', $notification->subject_ar) }}" placeholder="أدخل العنوان بالعربية">
                        @error('subject_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Body EN --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> Body (English) <span class="nova-text-danger">*</span>
                        </label>
                        <textarea name="body_en" class="nova-textarea" rows="4" placeholder="Enter body content in English">{{ old('body_en', $notification->body_en) }}</textarea>
                        @error('body_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Body AR --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> المحتوى (بالعربية) <span class="nova-text-danger">*</span>
                        </label>
                        <textarea name="body_ar" class="nova-textarea" rows="4" placeholder="أدخل المحتوى بالعربية">{{ old('body_ar', $notification->body_ar) }}</textarea>
                        @error('body_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Current الصورة --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> Current الصورة
                        </label>
                        <div>
                            @if($notification->image)
                                <img src="{{ asset('uploads/' . $notification->image) }}" alt="Current الصورة" width="100" class="nova-object-fit-cover" style="border-radius: 10px; box-shadow: var(--nova-shadow-sm);">
                            @else
                                <span class="nova-text-muted">لا image</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Change الصورة --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-upload me-1"></i> Change الصورة <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        <div class="nova-form-help">Max 2MB (JPG, PNG, GIF, SVG)</div>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Current Video --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-video me-1"></i> Current Video
                        </label>
                        <div>
                            @if($notification->video)
                                <video width="100" controls class="nova-object-fit-cover" style="border-radius: 10px;">
                                    <source src="{{ asset('uploads/' . $notification->video) }}" type="video/mp4">
                                </video>
                            @else
                                <span class="nova-text-muted">لا video</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Change Video --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-upload me-1"></i> Change Video <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="file" name="video" class="nova-input" accept="video/*">
                        <div class="nova-form-help">Max 20MB (MP4, AVI, MOV)</div>
                        @error('video') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Group --}}
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-users-cog me-1"></i> Group <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="text" name="group" class="nova-input" value="{{ old('group', $notification->group) }}" placeholder="e.g., VIP, جديد Users">
                        @error('group') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Channel --}}
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-bell me-1"></i> Channel <span class="nova-text-danger">*</span>
                        </label>
                        <select name="channel" class="nova-select">
                            <option value="all" {{ old('channel', $notification->channel) == 'all' ? 'selected' : '' }}>الكل Channels</option>
                            <option value="email" {{ old('channel', $notification->channel) == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="push" {{ old('channel', $notification->channel) == 'push' ? 'selected' : '' }}>Push لاtification</option>
                            <option value="sms" {{ old('channel', $notification->channel) == 'sms' ? 'selected' : '' }}>SMS</option>
                        </select>
                        @error('channel') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Icon Type --}}
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> Icon Type <span class="nova-text-danger">*</span>
                        </label>
                        <select name="icon_type" class="nova-select">
                            <option value="notification" {{ old('icon_type', $notification->icon_type) == 'notification' ? 'selected' : '' }}>General Bell (إشعار عام)</option>
                            <option value="منfer" {{ old('icon_type', $notification->icon_type) == 'منfer' ? 'selected' : '' }}>Offer (عرض)</option>
                            <option value="payment" {{ old('icon_type', $notification->icon_type) == 'payment' ? 'selected' : '' }}>Payment (دفع)</option>
                            <option value="receive" {{ old('icon_type', $notification->icon_type) == 'receive' ? 'selected' : '' }}>Receive (استلام)</option>
                            <option value="delivery" {{ old('icon_type', $notification->icon_type) == 'delivery' ? 'selected' : '' }}>Delivery (توصيل)</option>
                        </select>
                        @error('icon_type') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Customers --}}
                <div class="nova-col nova-col-md-4">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-users me-1"></i> Customers <small class="nova-text-muted">(optional)</small>
                        </label>
                        <select name="customers[]" class="nova-select" multiple>
                            @php
                                $selectedCustomers = old('customers', is_string($notification->customers) ? json_decode($notification->customers, true) : ($notification->customers ?? []));
                                if (!is_array($selectedCustomers)) $selectedCustomers = [];
                            @endphp
                            <option value="all" {{ in_array('all', $selectedCustomers) ? 'selected' : '' }}>الكل Customers (الكل)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ in_array($customer->id, $selectedCustomers) ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <div class="nova-form-help">Hold Ctrl/Cmd to select multiple</div>
                        @error('customers') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Scheduled At --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-calendar-alt me-1"></i> Schedule <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="datetime-local" name="scheduled_at" class="nova-input nova-input-sm" value="{{ old('scheduled_at', $notification->scheduled_at ? $notification->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                        <div class="nova-form-help">Leave empty to send immediately</div>
                        @error('scheduled_at') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Send لاw --}}
                <div class="nova-col nova-col-md-6 d-flex align-items-end">
                    <div class="nova-form-check">
                        <input type="hidden" name="is_sent" value="0">
                        <input type="checkbox" name="is_sent" class="nova-form-check-input" id="isSent" value="1" {{ old('is_sent', $notification->is_sent) ? 'checked' : '' }}>
                        <label class="nova-form-check-label" for="isSent">Send لاw</label>
                        <div class="nova-form-help">Check to send immediately</div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> Update لاtification
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
