@extends('dashboard.partials.layout')

@section('title', 'Create Notification')

@push('styles')
<style>
    /* ============ Form Sections ============ */
    .nova-form-section {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px dashed #e5e7eb;
    }
    .nova-form-section:last-of-type {
        border-bottom: none;
        margin-bottom: .5rem;
    }
    .nova-form-section-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--nova-primary);
        margin-bottom: .75rem;
    }
    .nova-form-section-title i { font-size: .9rem; }

    /* ============ Custom Grid (bulletproof) ============ */
    .nova-form-grid {
        display: grid;
        grid-template-columns: 1fr;   /* موبايل: عمود واحد */
        gap: 1rem;
        width: 100%;
    }
    .nova-form-grid > * { min-width: 0; } /* يمنع تجاوز الحقول */

    /* عمودان من التابلت (≥576px) فما فوق */
    @media (min-width: 576px) {
        .nova-form-grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
@endpush

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-bell" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Create Notification
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">Send a new notification to your customers</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Notifications
        </a>
    </div>
</div>

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
            <i class="fas fa-bell"></i> Notification Details
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ==================== 1) Content ==================== --}}
            <div class="nova-form-section">
                <h6 class="nova-form-section-title">
                    <i class="fas fa-edit"></i> Content / المحتوى
                </h6>
                <div class="nova-form-grid cols-2">

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> Subject (English) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="subject_en" class="nova-input" value="{{ old('subject_en') }}" placeholder="Enter subject in English">
                        @error('subject_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-heading me-1"></i> العنوان (بالعربية) <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text" name="subject_ar" class="nova-input" value="{{ old('subject_ar') }}" placeholder="أدخل العنوان بالعربية">
                        @error('subject_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> Body (English) <span class="nova-text-danger">*</span>
                        </label>
                        <textarea name="body_en" class="nova-textarea" rows="4" placeholder="Enter body content in English">{{ old('body_en') }}</textarea>
                        @error('body_en') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-align-left me-1"></i> المحتوى (بالعربية) <span class="nova-text-danger">*</span>
                        </label>
                        <textarea name="body_ar" class="nova-textarea" rows="4" placeholder="أدخل المحتوى بالعربية">{{ old('body_ar') }}</textarea>
                        @error('body_ar') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>

            {{-- ==================== 2) Attachments ==================== --}}
            <div class="nova-form-section">
                <h6 class="nova-form-section-title">
                    <i class="fas fa-paperclip"></i> Attachments / المرفقات
                </h6>
                <div class="nova-form-grid cols-2">

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> Image <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="file" name="image" class="nova-input" accept="image/*">
                        <div class="nova-form-help">Max 2MB (JPG, PNG, GIF, SVG)</div>
                        @error('image') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-video me-1"></i> Video <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="file" name="video" class="nova-input" accept="video/*">
                        <div class="nova-form-help">Max 20MB (MP4, AVI, MOV)</div>
                        @error('video') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>

            {{-- ==================== 3) Delivery & Targeting ==================== --}}
            <div class="nova-form-section">
                <h6 class="nova-form-section-title">
                    <i class="fas fa-bullseye"></i> Delivery &amp; Targeting / التوصيل والاستهداف
                </h6>
                <div class="nova-form-grid cols-2">

                    {{-- Channel --}}
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-bell me-1"></i> Channel <span class="nova-text-danger">*</span>
                        </label>
                        <select name="channel" class="nova-select">
                            <option value="all"   {{ old('channel') == 'all'   ? 'selected' : '' }}>All Channels</option>
                            <option value="email" {{ old('channel') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="push"  {{ old('channel') == 'push'  ? 'selected' : '' }}>Push Notification</option>
                            <option value="sms"   {{ old('channel') == 'sms'   ? 'selected' : '' }}>SMS</option>
                        </select>
                        @error('channel') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Icon Type --}}
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tag me-1"></i> Icon Type <span class="nova-text-danger">*</span>
                        </label>
                        <select name="icon_type" class="nova-select">
                            <option value="notification" {{ old('icon_type') == 'notification' ? 'selected' : '' }}>General Bell (إشعار عام)</option>
                            <option value="offer"        {{ old('icon_type') == 'offer'        ? 'selected' : '' }}>Offer (عرض)</option>
                            <option value="payment"      {{ old('icon_type') == 'payment'      ? 'selected' : '' }}>Payment (دفع)</option>
                            <option value="receive"      {{ old('icon_type') == 'receive'      ? 'selected' : '' }}>Receive (استلام)</option>
                            <option value="delivery"     {{ old('icon_type') == 'delivery'     ? 'selected' : '' }}>Delivery (توصيل)</option>
                        </select>
                        @error('icon_type') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Group --}}
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-users-cog me-1"></i> Group <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="text" name="group" class="nova-input" value="{{ old('group') }}" placeholder="e.g., VIP, New Users">
                        @error('group') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Customers --}}
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-users me-1"></i> Customers <small class="nova-text-muted">(optional)</small>
                        </label>
                        <select name="customers[]" class="nova-select" multiple size="4">
                            <option value="all" {{ old('customers') && in_array('all', old('customers')) ? 'selected' : '' }}>All Customers (الكل)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customers') && in_array($customer->id, old('customers')) ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <div class="nova-form-help">Hold Ctrl/Cmd to select multiple</div>
                        @error('customers') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>

            {{-- ==================== 4) Scheduling ==================== --}}
            <div class="nova-form-section">
                <h6 class="nova-form-section-title">
                    <i class="fas fa-clock"></i> Scheduling / الجدولة
                </h6>
                <div class="nova-form-grid cols-2">

                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-calendar-alt me-1"></i> Schedule <small class="nova-text-muted">(optional)</small>
                        </label>
                        <input type="datetime-local" name="scheduled_at" class="nova-input" value="{{ old('scheduled_at') }}">
                        <div class="nova-form-help">Leave empty to send immediately</div>
                        @error('scheduled_at') <div class="nova-form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="nova-form-group" style="display:flex; align-items:flex-end;">
                        <div class="nova-form-check" style="padding: .65rem 1rem; background:#f9fafb; border-radius:.5rem; width:100%;">
                            <input type="hidden" name="is_sent" value="0">
                            <input type="checkbox" name="is_sent" class="nova-form-check-input" id="isSent" value="1" {{ old('is_sent') ? 'checked' : '' }}>
                            <label class="nova-form-check-label fw-bold" for="isSent">
                                <i class="fas fa-paper-plane me-1"></i> Send Now
                            </label>
                            <div class="nova-form-help mb-0">Check to send immediately, or schedule for later</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ==================== Actions ==================== --}}
            <div class="nova-form-actions" style="border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                <a href="{{ route('notifications.index') }}" class="nova-btn nova-btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="nova-btn nova-btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
            </div>

        </form>
    </div>
</div>
@endsection