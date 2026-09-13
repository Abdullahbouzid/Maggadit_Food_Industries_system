@extends('dashboard.partials.layout')

@section('title', 'Contact Information')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-address-book" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            Contact Information
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">View organization contact details</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('dashboard') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="nova-row nova-gap-4">
    <div class="nova-col nova-col-12">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-address-book me-2"></i> تفاصيل التواصل</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-4">
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">الهاتف</label>
                            <p class="mb-0">{{ $organization?->phone ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">واتساب</label>
                            <p class="mb-0">{{ $organization?->whatsapp ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">البريد الإلكتروني</label>
                            <p class="mb-0">{{ $organization?->email ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">الموقع الإلكتروني</label>
                            <p class="mb-0">{{ $organization?->website ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-12">
                        <div class="nova-form-group">
                            <label class="nova-label">العنوان</label>
                            <p class="mb-0">{{ $organization?->address ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">خط العرض</label>
                            <p class="mb-0">{{ $organization?->latitude ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="nova-col nova-col-md-6">
                        <div class="nova-form-group">
                            <label class="nova-label">خط الطول</label>
                            <p class="mb-0">{{ $organization?->longitude ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customers --}}
    <div class="nova-col nova-col-12">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-users me-2"></i> عملاء المنظمة</h5>
            </div>
            <div class="nova-card-body">
                @if(isset($customers) && $customers->count() > 0)
                    <div class="nova-table-scroll">
                        <table class="nova-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>اسم المستخدم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الصورة الشخصية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                <tr>
                                    <td class="nova-fw-semibold">{{ $loop->iteration }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->username }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        @if($customer->avatar)
                                            <img src="{{ asset('uploads/customers/' . $customer->avatar) }}" alt="{{ $customer->name }}" class="nova-avatar nova-avatar-sm" style="width: 36px; height: 36px; object-fit: cover;">
                                        @else
                                            <div class="nova-stat-icon primary" style="width: 36px; height: 36px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="nova-empty">
                        <div class="nova-empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="nova-empty-title">لا توجد عملاء</h3>
                        <p class="nova-empty-text">لا يوجد عملاء مرتبطين بهذه المنظمة.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection