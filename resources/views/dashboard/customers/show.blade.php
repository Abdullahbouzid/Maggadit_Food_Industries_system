@extends('dashboard.partials.layout')

@section('title', 'تفاصيل العميل: ' . $customer->name)

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-user-tag" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            تفاصيل العميل
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">عرض كامل لمعلومات {{ $customer->name }}</p>
    </div>
    <div class="nova-actionbar-actions">
        <a href="{{ route('customers.edit', $customer) }}" class="nova-btn nova-btn-warning nova-btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('customers.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Row 1: معلومات أساسية + قائمة الأسعار --}}
<div class="nova-row cols-2 nova-mb-4">
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-info-circle" style="margin-inline-end: 0.5rem;"></i>
                ملومات العميل
            </h3>
        </div>
        <div class="nova-card-body">
            <div class="nova-table-scroll">
                <table class="nova-table">
                    <tr>
                        <th style="width: 30%;">الاسم</th>
                        <td class="nova-fw-semibold">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th>اسم المستخدم</th>
                        <td>{{ $customer->username }}</td>
                    </tr>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>
                            <i class="fas fa-envelope nova-text-muted me-1"></i>
                            <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>الهاتف</th>
                        <td>{{ $customer->phone ?? '<span class="nova-text-muted">—</span>' }}</td>
                    </tr>
                    <tr>
                        <th>العنوان</th>
                        <td>{{ $customer->default_address ?? '<span class="nova-text-muted">—</span>' }}</td>
                    </tr>
                    <tr>
                        <th>نوع العميل</th>
                        <td>
                            @if($customer->customer_type === 'credit')
                                <span class="nova-badge warning">آجل</span>
                            @else
                                <span class="nova-badge success">نقدي</span>
                            @endif
                        </td>
                    </tr>
                    @if($customer->customer_type === 'credit')
                        <tr>
                            <th>حد الائتمان</th>
                            <td>{{ number_format($customer->credit_limit ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>الرصيد الحالي</th>
                            <td>{{ number_format($customer->current_balance ?? 0, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Card: قائمة الأسعار --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-tag" style="margin-inline-end: 0.5rem;"></i>
                قائمة الأسعار المسندة
            </h3>
        </div>
        <div class="nova-card-body">
            @if($customer->priceList)
                <p><strong>القائمة الحالية:</strong> {{ $customer->priceList->name }}</p>
            @else
                <p class="nova-text-muted">لا توجد قائمة أسعار مسندة (سيُستخدم السعر الافتراضي)</p>
            @endif

            <form method="POST" action="{{ route('customers.price-list.update', $customer) }}">
                @csrf
                <div class="nova-form-group">
                    <label class="nova-label">تغيير قائمة الأسعار</label>
                    <select name="price_list_id" class="nova-select">
                        <option value="">— بدون (استخدام الافتراضي) —</option>
                        @foreach($allPriceLists as $pl)
                            <option value="{{ $pl->id }}" {{ $customer->price_list_id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }}{{ $pl->is_default ? ' (افتراضية)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="nova-btn nova-btn-primary">حفظ</button>
            </form>
        </div>
    </div>
</div>

{{-- Row 2: المحفظة --}}
<div class="nova-row cols-2 nova-mb-4">
    @forelse($customer->wallets ?? collect() as $wallet)
        <div class="nova-card">
            <div class="nova-card-header">
                <h3 class="nova-card-title">
                    <i class="fas fa-wallet" style="margin-inline-end: 0.5rem;"></i>
                    المحفظة ({{ $wallet->currency->symbol ?? $wallet->currency->code ?? '' }})
                </h3>
            </div>
            <div class="nova-card-body">
                <p><strong>الرصيد الحالي:</strong>
                    <span class="nova-text-success">{{ number_format($wallet->balance, 2) }}</span>
                </p>
                <p><strong>إجمالي الإيداعات:</strong> {{ number_format($wallet->total_deposited, 2) }}</p>
                <p><strong>إجمالي المصروف:</strong> {{ number_format($wallet->total_spent, 2) }}</p>

                <form method="POST" action="{{ route('wallets.deposit', $wallet) }}" class="nova-mt-2">
                    @csrf
                    @if ($errors->any())
                        <div class="nova-alert nova-alert-error nova-mb-3">
                            <i class="fas fa-circle-xmark"></i>
                            <ul class="nova-mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="nova-row cols-2">
                        <input type="number" step="0.01" min="0.01"
                               name="amount" class="nova-input"
                               placeholder="المبلغ" required>
                        <select name="method" class="nova-select" required>
                            <option value="cash">نقدي</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="cheque">شيك</option>
                            <option value="online">دفع إلكتروني</option>
                        </select>
                    </div>
                    <input type="text" name="reference_number"
                           class="nova-input nova-mt-2" placeholder="رقم المرجع (اختياري)">
                    <input type="text" name="notes"
                           class="nova-input nova-mt-2" placeholder="ملاحظات (اختياري)">
                    <button class="nova-btn nova-btn-success nova-mt-2">إيداع</button>
                </form>
            </div>
        </div>
    @empty
        <div class="nova-card">
            <div class="nova-card-body">
                @if ($errors->has('wallet'))
                    <div class="nova-alert nova-alert-error nova-mb-3">
                        <i class="fas fa-circle-xmark"></i> {{ $errors->first('wallet') }}
                    </div>
                @endif

                <p class="nova-text-muted">
                    <i class="fas fa-info-circle"></i>
                    لا توجد محفظة لهذا العميل.
                </p>

                <button type="button" class="nova-btn nova-btn-primary"
                        onclick="document.getElementById('createWalletModal').classList.add('show')">
                    <i class="fas fa-wallet" style="margin-inline-end: 0.5rem;"></i>
                    إنشاء محفظة
                </button>
            </div>
        </div>
    @endforelse
</div>

{{-- Row 3: سجل الحركات --}}
@if(($customer->wallets ?? collect())->isNotEmpty() && ($customer->wallets->first()->transactions ?? collect())->isNotEmpty())
<div class="nova-card nova-mt-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-history" style="margin-inline-end: 0.5rem;"></i>
            آخر حركات المحفظة
        </h3>
    </div>
    <div class="nova-card-body">
        <table class="nova-table nova-table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الرصيد بعد</th>
                    <th>طريقة الدفع</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->wallets->first()->transactions as $txn)
                <tr>
                    <td>{{ $txn->id }}</td>
                    <td>
                        @php
                            $isCredit = in_array($txn->type, ['deposit', 'refund']);
                        @endphp
                        <span class="nova-badge {{ $isCredit ? 'success' : 'warning' }}">
                            {{ $isCredit ? 'إيداع' : 'سحب' }}
                        </span>
                    </td>
                    <td>{{ number_format($txn->amount, 2) }}</td>
                    <td>{{ number_format($txn->balance_after, 2) }}</td>
                    <td>{{ $txn->method ?? '-' }}</td>
                    <td>{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Row 4: آخر الطلبات + الفواتير --}}
<div class="nova-row cols-2 nova-mt-4">
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-shopping-cart" style="margin-inline-end: 0.5rem;"></i>
                آخر الطلبات
            </h3>
        </div>
        <div class="nova-card-body">
            @forelse($customer->orders ?? collect() as $order)
                <div class="nova-flex nova-justify-between nova-py-2">
                    <a href="{{ route('orders.show', $order) }}">
                        {{ $order->order_number ?? '#' . $order->id }}
                    </a>
                    @php
                        $badgeClass = match($order->status) {
                            'delivered' => 'success',
                            'cancelled', 'expired', 'returned' => 'danger',
                            'pending_approval', 'pending_review', 'quotation_sent' => 'warning',
                            'approved', 'customer_approved' => 'success',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="nova-badge {{ $badgeClass }}">{{ $order->status }}</span>
                </div>
            @empty
                <p class="nova-text-muted">لا توجد طلبات</p>
            @endforelse
        </div>
    </div>

    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-file-invoice-dollar" style="margin-inline-end: 0.5rem;"></i>
                آخر الفواتيق
            </h3>
        </div>
        <div class="nova-card-body">
            @forelse($customer->invoices ?? collect() as $invoice)
                <div class="nova-flex nova-justify-between nova-py-2">
                    <a href="{{ route('invoices.show', $invoice) }}">
                        {{ $invoice->invoice_number ?? '#' . $invoice->id }}
                    </a>
                    <span class="nova-badge secondary">
                        {{ $invoice->status ?? '—' }}
                    </span>
                </div>
            @empty
                <p class="nova-text-muted">لا توجد فواتيق</p>
            @endforelse
        </div>
    </div>
</div>
{{-- ===== Modal: إنشاء محفظة للعميل (محفظة واحدة لكل عميل) ===== --}}
@if(($customer->wallets ?? collect())->isEmpty())
<div class="nova-modal-backdrop" id="createWalletModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-card" style="width: 100%; max-width: 460px;">
            <div class="nova-card-header">
                <h3 class="nova-card-title">
                    <i class="fas fa-wallet" style="margin-inline-end: 0.5rem;"></i>
                    إنشاء محفظة للعميل {{ $customer->name }}
                </h3>
            </div>
            <form method="POST" action="{{ route('wallets.store') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="nova-card-body">
                    @if ($errors->any())
                        <div class="nova-alert nova-alert-error nova-mb-3">
                            <i class="fas fa-circle-xmark"></i>
                            <ul class="nova-mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="nova-form-group nova-mb-3">
                        <label class="nova-label" for="walletCurrency">العملة</label>
                        <select name="currency_id" id="walletCurrency" class="nova-select" required>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    {{ (int) $currency->id === (int) $defaultCurrencyId ? 'selected' : '' }}>
                                    {{ $currency->code }}{{ $currency->symbol ? ' — ' . $currency->symbol : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="nova-form-group nova-mb-3">
                        <label class="nova-label" for="walletOpeningBalance">
                            الرصيد الافتتاحي <span class="nova-text-muted">(اختياري)</span>
                        </label>
                        <input type="number" name="opening_balance" id="walletOpeningBalance"
                               class="nova-input" step="0.01" min="0" value="0"
                               placeholder="0.00">
                        <small class="nova-text-muted">
                            إذا أدخلت مبلغاً فسيُسجَّل كإيداع في سجل حركات المحفظة.
                        </small>
                    </div>

                    <div class="nova-form-group nova-mb-0">
                        <label class="nova-label" for="walletNotes">
                            ملاحظات <span class="nova-text-muted">(اختياري)</span>
                        </label>
                        <input type="text" name="notes" id="walletNotes" class="nova-input"
                               maxlength="255" placeholder="سبب إنشاء المحفظة">
                    </div>
                </div>

                <div class="nova-card-header" style="border-top: 1px solid var(--nova-gray-200); border-bottom: none;">
                    <div class="nova-flex nova-justify-end nova-gap-2" style="width: 100%;">
                        <button type="button" class="nova-btn nova-btn-outline"
                                onclick="document.getElementById('createWalletModal').classList.remove('show')">
                            إلغاء
                        </button>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-check" style="margin-inline-end: 0.5rem;"></i>
                            إنشاء المحفظة
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // إعادة فتح النافذة تلقائياً عند وجود خطأ في التحقق
    @if ($errors->any())
        document.getElementById('createWalletModal')?.classList.add('show');
    @endif
});
</script>
@endpush
