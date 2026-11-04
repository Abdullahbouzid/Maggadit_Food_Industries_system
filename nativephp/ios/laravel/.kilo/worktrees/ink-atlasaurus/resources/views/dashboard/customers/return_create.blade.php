@extends('dashboard.partials.layout')

@section('title', 'تسجيل استرجاع')

@section('content')

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-rotate-left" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تسجيل استرجاع — {{ $customer->name }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            الفاتورة {{ $invoice->invoice_number }} بتاريخ {{ $invoice->invoice_date?->format('Y-m-d') }}
        </p>
    </div>
    <div>
        <a href="{{ route('customers.show', $customer) }}" class="nova-btn">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>
</div>

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('customers.returns.store', [$customer, $invoice]) }}">
    @csrf

    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">بيانات الاسترجاع</h3>
        </div>
        <div class="nova-card-body">
            <div class="nova-row nova-gap-4">
                <div class="nova-col-4">
                    <div class="nova-form-group">
                        <label class="nova-label">نوع الاسترجاع</label>
                        <select name="type" class="nova-select" required>
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}">
                                    {{ $type->value === 'full' ? 'استرجاع كامل' : 'استرجاع جزئي' }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="nova-col-8">
                    <div class="nova-form-group">
                        <label class="nova-label">سبب الاسترجاع</label>
                        <input type="text" name="reason" class="nova-input" required
                               placeholder="مثال: تالف، منتهي الصلاحية، خطأ في التوصيل">
                        @error('reason')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">الكميات القابلة للاسترجاع</h3>
        </div>
        <div class="nova-card-body">
            <div class="table-responsive">
                <table class="nova-table">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>العبوة</th>
                            <th>المفوتر</th>
                            <th>مُرجَع سابقًا</th>
                            <th>القابل للاسترجاع</th>
                            <th>سعر الوحدة</th>
                            <th style="width: 180px;">كمية الاسترجاع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td>
                                    {{ $line['name'] }}
                                    <span class="nova-text-muted nova-fs-sm">({{ $line['code'] }})</span>
                                </td>
                                <td>{{ $line['pack'] }}</td>
                                <td>{{ rtrim(rtrim(number_format($line['quantity'], 2), '0'), '.') }}</td>
                                <td>{{ rtrim(rtrim(number_format($line['returned'], 2), '0'), '.') }}</td>
                                <td class="nova-fw-semibold">
                                    {{ rtrim(rtrim(number_format($line['returnable'], 2), '0'), '.') }}
                                </td>
                                <td>{{ number_format($line['unit_price'], 2) }}</td>
                                <td>
                                    <input type="number"
                                           name="quantities[{{ $line['id'] }}]"
                                           class="nova-input"
                                           step="0.001"
                                           min="0"
                                           max="{{ $line['returnable'] }}"
                                           value="0">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="nova-text-muted nova-fs-sm" style="margin-top: 0.75rem;">
                اترك الكمية <span dir="ltr">0</span> لكل سطر لا تريد استرجاعه. لا يمكن تجاوز الكمية القابلة للاسترجاع.
            </p>
        </div>
        <div class="nova-card-footer">
            <button type="submit" class="nova-btn nova-btn-primary">
                <i class="fas fa-check"></i> تسجيل الاسترجاع
            </button>
            <a href="{{ route('customers.show', $customer) }}" class="nova-btn">إلغاء</a>
        </div>
    </div>
</form>

@endsection