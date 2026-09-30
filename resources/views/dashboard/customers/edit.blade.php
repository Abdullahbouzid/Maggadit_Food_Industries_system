@extends('dashboard.partials.layout')

@section('title', 'تعديل العميل')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-edit" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            تعديل العميل: {{ $customer->name }}
        </h2>
        <p class="nova-page-subtitle">تحرير بيانات العميل</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if($errors->any())
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <h6 class="nova-fw-bold" style="margin: 0 0 0.25rem;">يرجى تصحيح الأخطاء التالية:</h6>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-alt"></i> بيانات العميل
        </h5>
    </div>
    <div class="nova-card-body">
        <form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="nova-row g-4">
                {{-- Name --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-user me-1"></i> الاسم <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               class="nova-input @error('name') nova-invalid @enderror"
                               name="name"
                               value="{{ old('name', $customer->name) }}"
                               placeholder="أدخل الاسم الكامل..."
                               required>
                    </div>
                    @error('name') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Username --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-user-tag me-1"></i> اسم المستخدم <span class="nova-text-danger">*</span>
                        </label>
                        <input type="text"
                               class="nova-input @error('username') nova-invalid @enderror"
                               name="username"
                               value="{{ old('username', $customer->username) }}"
                               placeholder="أدخل اسم المستخدم..."
                               required>
                    </div>
                    @error('username') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-envelope me-1"></i> البريد الإلكتروني <span class="nova-text-danger">*</span>
                        </label>
                        <input type="email"
                               class="nova-input @error('email') nova-invalid @enderror"
                               name="email"
                               value="{{ old('email', $customer->email) }}"
                               placeholder="example@email.com"
                               required>
                    </div>
                    @error('email') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Phone --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-phone me-1"></i> رقم الهاتف
                        </label>
                        <input type="tel"
                               class="nova-input @error('phone') nova-invalid @enderror"
                               name="phone"
                               value="{{ old('phone', $customer->phone) }}"
                               placeholder="+218 9X XXX XXXX"
                               dir="ltr">
                    </div>
                    @error('phone') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Address --}}
                <div class="nova-col nova-col-md-12">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-map-marker-alt me-1"></i> العنوان
                        </label>
                        <textarea class="nova-input @error('default_address') nova-invalid @enderror"
                                  name="default_address"
                                  rows="3"
                                  placeholder="أدخل العنوان الكامل...">{{ old('default_address', $customer->default_address) }}</textarea>
                    </div>
                    @error('default_address') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Password --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-lock me-1"></i> كلمة المرور الجديدة
                        </label>
                        <input type="password"
                               class="nova-input @error('password') nova-invalid @enderror"
                               name="password"
                               minlength="8"
                               placeholder="أدخل كلمة المرور الجديدة (حد أدنى 8 أحرف)...">
                        <div class="nova-help-text">اتركه فارغاً للحفاظ على كلمة المرور الحالية.</div>
                    </div>
                    @error('password') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-lock me-1"></i> تأكيد كلمة المرور الجديدة
                        </label>
                        <input type="password"
                               class="nova-input @error('password_confirmation') nova-invalid @enderror"
                               name="password_confirmation"
                               minlength="8"
                               placeholder="أعد كتابة كلمة المرور الجديدة...">
                    </div>
                    @error('password_confirmation') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Avatar --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-image me-1"></i> الصورة الشخصية
                        </label>
                        @if($customer->avatar)
                            <div class="nova-mb-2">
                                <img src="{{ $customer->avatar_url }}" alt="الصورة الحالية" class="nova-avatar nova-avatar-md" style="width: 80px; height: 80px; object-fit: cover;">
                                <span class="nova-text-muted ms-2">الصورة الحالية</span>
                            </div>
                        @endif
                        <input type="file"
                               class="nova-input @error('avatar') nova-invalid @enderror"
                               name="avatar"
                               accept="image/*">
                        <div class="nova-help-text">حمّل صورة جديدة لاستبدال الحالية.</div>
                    </div>
                    @error('avatar') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Active Organization --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-building me-1"></i> المنظمة النشطة
                        </label>
                        <select class="nova-select @error('active_organization_id') nova-invalid @enderror"
                                name="active_organization_id">
                            <option value="">— بدون منظمة —</option>
                            @foreach($organizations as $id => $name)
                                <option value="{{ $id }}" {{ old('active_organization_id', $customer->active_organization_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('active_organization_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Price List --}}
                <div class="nova-col nova-col-md-6">
                    <div class="nova-form-group">
                        <label class="nova-label">
                            <i class="fas fa-tags me-1"></i> قائمة الأسعار
                        </label>
                        <select class="nova-select @error('price_list_id') nova-invalid @enderror"
                                name="price_list_id">
                            <option value="">— بدون قائمة —</option>
                            @foreach($priceLists as $list)
                                <option value="{{ $list->id }}" {{ old('price_list_id', $customer->price_list_id) == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('price_list_id') <div class="nova-form-error">{{ $message }}</div> @enderror
                </div>

                {{-- Form Actions --}}
                <div class="nova-col nova-col-12">
                    <div class="nova-form-actions">
                        <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline">
                            <i class="fas fa-x me-1"></i> إلغاء
                        </a>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save me-1"></i> تحديث
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
