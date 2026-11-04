@extends('dashboard.partials.layout')

@section('title', 'محفظة ' . ($wallet->customer->name ?? 'العميل'))

@section('content')

@php
    $currencyLabel = $wallet->currency->code ?? '';
    $symbol = $wallet->currency->symbol ?? '';
@endphp

<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-wallet" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            محفظة {{ $wallet->customer->name ?? '—' }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ $currencyLabel }}{{ $symbol ? ' (' . $symbol . ')' : '' }}
            @if ($wallet->is_active)
                <span class="nova-badge success">نشطة</span>
            @else
                <span class="nova-badge danger">موقوفة</span>
            @endif
        </p>
    </div>
    <div>
        <a href="{{ route('wallets.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> كل المحافظ
        </a>
        <a href="{{ route('customers.show', $wallet->customer_id) }}" class="nova-btn nova-btn-sm">
            <i class="fas fa-user"></i> صفحة العميل
        </a>
    </div>
</div>

@if (session('success'))
    <div class="nova-alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="nova-alert danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="nova-alert danger">
        <ul class="nova-mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== ملخص المحفظة ===== --}}
<div class="nova-row nova-gap-4 nova-mb-4">
    <div class="nova-col-3">
        <div class="nova-stat">
            <div class="nova-stat-value">{{ number_format((float) $wallet->balance, 2) }}</div>
            <p class="nova-stat-label">الرصيد الحالي</p>
        </div>
    </div>
    <div class="nova-col-3">
        <div class="nova-stat">
            <div class="nova-stat-value nova-text-success">{{ number_format((float) $stats['deposits'], 2) }}</div>
            <p class="nova-stat-label">إجمالي الإيداعات</p>
        </div>
    </div>
    <div class="nova-col-3">
        <div class="nova-stat">
            <div class="nova-stat-value nova-text-danger">{{ number_format($stats['withdrawals'] + $stats['purchases'], 2) }}</div>
            <p class="nova-stat-label">إجمالي المصروفات</p>
        </div>
    </div>
    <div class="nova-col-3">
        <div class="nova-stat">
            <div class="nova-stat-value">{{ $stats['count'] }}</div>
            <p class="nova-stat-label">عدد الحركات</p>
        </div>
    </div>
</div>

<div class="nova-row nova-gap-4">
    {{-- ===== نموذج الإيداع ===== --}}
    <div class="nova-col-4">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">
                    <i class="fas fa-plus-circle" style="margin-inline-end: 0.5rem;"></i> إيداع رصيد
                </h3>
            </div>
            <div class="nova-card-body">
                <form method="POST" action="{{ route('wallets.deposit', $wallet) }}">
                    @csrf
                    <input type="hidden" name="return_to" value="wallet">

                    <div class="nova-form-group">
                        <label class="nova-label">المبلغ</label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="nova-input" required>
                        @error('amount')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">طريقة الإيداع</label>
                        <select name="method" class="nova-select" required>
                            <option value="cash">نقدي</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="cheque">شيك</option>
                            <option value="online">أونلاين</option>
                        </select>
                        @error('method')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">رقم المرجع</label>
                        <input type="text" name="reference_number" class="nova-input" maxlength="120">
                        @error('reference_number')
                            <div class="nova-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">ملاحظات</label>
                        <input type="text" name="notes" class="nova-input">
                    </div>

                    <button type="submit" class="nova-btn nova-btn-primary nova-w-100">
                        <i class="fas fa-check"></i> تسجيل الإيداع
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== حركات المحفظة ===== --}}
    <div class="nova-col-8">
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">
                    <i class="fas fa-list" style="margin-inline-end: 0.5rem;"></i> حركات المحفظة
                </h3>
            </div>

            <div class="nova-card-body">
                <form method="GET" class="nova-row nova-gap-3 nova-mb-3">
                    <div class="nova-col-3">
                        <select name="type" class="nova-select">
                            <option value="">كل الأنواع</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nova-col-3">
                        <select name="method" class="nova-select">
                            <option value="">كل الطرق</option>
                            @foreach ($methods as $method)
                                <option value="{{ $method }}" @selected($filters['method'] === $method)>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nova-col-3">
                        <input type="date" name="from" class="nova-input" value="{{ $filters['from'] }}">
                    </div>
                    <div class="nova-col-3">
                        <input type="date" name="to" class="nova-input" value="{{ $filters['to'] }}">
                    </div>
                    <div class="nova-col-12">
                        <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm">
                            <i class="fas fa-filter"></i> تصفية
                        </button>
                        <a href="{{ route('wallets.show', $wallet) }}" class="nova-btn nova-btn-sm">
                            <i class="fas fa-rotate-left"></i> إعادة تعيين
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="nova-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>النوع</th>
                                <th>الطريقة</th>
                                <th>المرجع</th>
                                <th>المبلغ</th>
                                <th>الرصيد بعدها</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                @php
                                    $amount = (float) $transaction->amount;
                                    $isCredit = $amount >= 0;
                                    $typeValue = is_object($transaction->type)
                                        ? $transaction->type->value
                                        : (string) $transaction->type;
                                    $typeLabel = is_object($transaction->type)
                                        ? $transaction->type->label()
                                        : $typeValue;
                                    $methodMap = [
                                        'cash' => 'نقدي', 'bank_transfer' => 'تحويل بنكي',
                                        'cheque' => 'شيك', 'online' => 'أونلاين',
                                        'wallet' => 'محفظة',
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $transaction->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="nova-badge {{ $isCredit ? 'success' : 'danger' }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $methodMap[$transaction->method] ?? ($transaction->method ?: '—') }}</td>
                                    <td>
                                        @if ($transaction->reference_type === 'App\\Models\\Invoice' && $transaction->reference)
                                            <a href="{{ route('invoices.show', $transaction->reference_id) }}">
                                                {{ $transaction->reference->invoice_number ?? 'فاتورة' }}
                                            </a>
                                        @elseif ($transaction->reference_number)
                                            {{ $transaction->reference_number }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="nova-fw-semibold {{ $isCredit ? 'nova-text-success' : 'nova-text-danger' }}">
                                        {{ $isCredit ? '+' : '−' }}{{ number_format(abs($amount), 2) }}
                                    </td>
                                    <td>{{ number_format((float) $transaction->balance_after, 2) }}</td>
                                    <td>{{ $transaction->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="nova-text-center nova-py-4">
                                        <p class="nova-text-muted">لا توجد حركات مطابقة</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="nova-mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
