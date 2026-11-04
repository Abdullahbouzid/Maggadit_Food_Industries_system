@extends('dashboard.partials.layout')

@section('title', __('messages.invoice_page.details'))

@push('styles')
<style>
    .nova-progress-bar {
        height: 0.5rem;
        border-radius: 9999px;
        overflow: hidden;
        background: var(--nova-surface-3);
    }
    .nova-progress-fill {
        height: 100%;
        background: var(--nova-success);
        transition: width 0.3s ease;
    }
    .nova-timeline-item {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .nova-timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: -0.5rem;
        width: 2px;
        background: var(--nova-border-color);
    }
    .nova-timeline-dot {
        position: absolute;
        left: 0.25rem;
        top: 0.125rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--nova-primary);
        border: 2px solid var(--nova-surface);
    }
</style>
@endpush

@php
$invoice = $invoice ?? null;
$items = $invoice->items ?? collect();
$order = $invoice->order ?? null;
$customer = $invoice->customer ?? null;
$delivery = null;
try {
    $delivery = $invoice->deliveries()->latest()->first();
} catch (\Throwable $e) {
    $delivery = null;
}
$payments = $invoice->payments ?? collect();
$returnRequests = $invoice->returnRequests ?? collect();
$invoiceStatus = \App\Enums\InvoiceStatus::tryFrom($invoice->status ?? '');
$currencySymbol = $invoice->currency->symbol ?? $invoice->currency->code ?? '';
$progress = $invoice->total_amount > 0 
    ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
$deadline = $invoice->payment_deadline_at;
$daysLeft = $deadline ? now()->diffInDays($deadline, false) : null;
$remaining = $invoice->total_amount - $invoice->paid_amount;

// محفظة العميل (لعرض الرصيد عند الدفع) + حدود السداد المسموح بها
$wallet = null;
$walletBalance = 0.0;
try {
    $wallet = $invoice->customer?->wallets()->first();
    $walletBalance = (float) ($wallet?->balance ?? 0);
} catch (\Throwable $e) {
    $wallet = null;
    $walletBalance = 0.0;
}
$isCreditCustomer = ($invoice->customer?->type ?? 'cash') === 'credit';
// العملاء الآجلون يمكنهم تجاوز رصيد المحفظة حتى حدّ الاعتماد (انظر WalletService::debit)
// عميل بلا محفظة أصلاً لا يُعرض له خيار الدفع بالمحفظة
$walletAvailable = $wallet
    ? ($isCreditCustomer
        ? $walletBalance + (float) ($invoice->customer?->credit_limit ?? 0)
        : $walletBalance)
    : null;
$canApprove = $invoiceStatus === \App\Enums\InvoiceStatus::PendingApproval;
$canReject = $invoiceStatus === \App\Enums\InvoiceStatus::PendingApproval;
$canCollect = in_array($invoiceStatus?->value, ['approved', 'partially_paid'], true);
@endphp

@section('content')

{{-- ===== Hero Header ===== --}}
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-file-invoice" style="color: var(--nova-primary); margin-inline-end: 0.5rem;"></i>
            {{ __('messages.invoice_page.details') }} #{{ $invoice->invoice_number }}
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            {{ $customer->name ?? '—' }} — {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('Y-m-d') : '—' }}
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <span class="nova-badge {{ $invoiceStatus?->badgeColor() ?? '' }}">
            {{ $invoiceStatus?->label() ?? '—' }}
        </span>
        @if($invoice->payment_type === 'cash')
            <span class="nova-badge success">{{ __('messages.invoice_page.payment_type_cash') }}</span>
        @elseif($invoice->payment_type === 'credit')
            <span class="nova-badge warning">{{ __('messages.invoice_page.payment_type_credit') }}</span>
        @endif
        <div style="display: flex; gap: 0.5rem; margin-left: 0.5rem;">
            <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-print"></i> {{ __('messages.invoice_page.print') }}
            </a>
            @if($canApprove)
                <button type="button" class="nova-btn nova-btn-primary nova-btn-sm" onclick="openInvoiceModal('approveModal')">
                    <i class="fas fa-check"></i> {{ __('messages.invoice_page.approve') }}
                </button>
                <button type="button" class="nova-btn nova-btn-danger nova-btn-sm" onclick="openInvoiceModal('rejectModal')">
                    <i class="fas fa-times"></i> {{ __('messages.invoice_page.reject') }}
                </button>
            @endif
            @if($canCollect)
                <button type="button" class="nova-btn nova-btn-primary nova-btn-sm" onclick="openInvoiceModal('paymentModal')">
                    <i class="fas fa-money-bill-wave"></i> {{ __('messages.invoice_page.record_payment') }}
                </button>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success nova-mb-3"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="nova-alert danger nova-mb-3">
        <ul style="margin: 0; padding-inline-start: 1.25rem;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== Row: Customer Info & Invoice Info ===== --}}
<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col" style="flex: 1;">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-user" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.customer_info') }}</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.customer_info') }}</p>
                        <p class="mb-0"><strong>{{ $customer->name ?? '—' }}</strong></p>
                        <small class="nova-text-muted">{{ $customer->email ?? '' }}</small>
                    </div>
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.print') }}</p>
                        <p class="mb-0"><strong>{{ $customer->phone ?? '—' }}</strong></p>
                        <small class="nova-text-muted">{{ $customer->address ?? '—' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="nova-col" style="flex: 1;">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-file-invoice" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.invoice_info') }}</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.details') }}</p>
                        <p class="mb-0"><strong>#{{ $invoice->invoice_number }}</strong></p>
                        <small class="nova-text-muted">{{ $order ? route('orders.show', $order) : '#' }} {{ $order->order_number ?? $invoice->id }}</small>
                    </div>
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.payment_method') }}</p>
                        <p class="mb-0"><strong>{{ $invoice->payment_type ?? '—' }}</strong></p>
                        <small class="nova-text-muted">{{ __('messages.invoice_page.currency') }}: {{ $currencySymbol }}</small>
                    </div>
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.exchange_rate') ?? 'Exchange Rate' }}</p>
                        <p class="mb-0"><strong>{{ $invoice->exchange_rate ?? '1.000000' }}</strong></p>
                        <small class="nova-text-muted">{{ __('messages.invoice_page.paid_at') ?? 'Paid At' }}: {{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('Y-m-d') : '—' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Card: Items ===== --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-box" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.items') }}</h5>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-wrapper">
            <table class="nova-table nova-table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.invoice_page.col_item') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_pack') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_quantity') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_unit_price') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_discount') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_tax') }}</th>
                        <th style="text-align: center;">{{ __('messages.invoice_page.col_line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    <tr>
                        <td class="nova-fw-semibold">{{ $index + 1 }}</td>
                        <td>
                            <div class="nova-flex nova-items-center">
                                <div>
                                    <span class="nova-fw-bold">{{ $item->item->name_ar ?? $item->item->name_en ?? '—' }}</span>
                                    <small class="nova-text-muted d-block">{{ $item->item->category->name_ar ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="nova-text-center">{{ $item->itemPack?->display_name ?? '—' }}</td>
                        <td class="nova-text-center">{{ $item->quantity }}</td>
                        <td class="nova-text-center">{{ $currencySymbol }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="nova-text-center">{{ $item->discount_amount ? number_format($item->discount_amount, 2) . '%' : '0' }}</td>
                        <td class="nova-text-center">{{ $item->tax_amount ?? 0 }}</td>
                        <td class="nova-text-center">{{ $currencySymbol }} {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="nova-text-center" style="padding: 2rem;">
                            <span class="nova-text-muted">{{ __('messages.invoice_page.no_payments') }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="nova-card-footer">
            <div class="nova-flex nova-justify-end">
                <div class="nova-text-end">
                    <table class="nova-table nova-table-borderless" style="max-width: 300px;">
                        <tbody>
                            <tr>
                                <td class="nova-text-muted">{{ __('messages.invoice_page.subtotal') }}:</td>
                                <td class="nova-fw-bold">{{ $currencySymbol }} {{ number_format($items->sum('subtotal'), 2) }}</td>
                            </tr>
                            @if($items->sum('discount_amount') > 0)
                            <tr>
                                <td class="nova-text-success">{{ __('messages.invoice_page.discount_total') }}:</td>
                                <td class="nova-fw-bold nova-text-success">- {{ $currencySymbol }} {{ number_format($items->sum('discount_amount'), 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="nova-text-muted">{{ __('messages.invoice_page.tax_total') }}:</td>
                                <td class="nova-fw-bold">{{ $currencySymbol }} {{ number_format($items->sum('tax_amount'), 2) }}</td>
                            </tr>
                            <tr style="border-top: 1px solid var(--nova-border);">
                                <td class="nova-fw-bold">{{ __('messages.invoice_page.total') }}:</td>
                                <td class="nova-fw-bold" style="font-size: 1.25rem; color: var(--nova-primary);">{{ $currencySymbol }} {{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Row: Payment Summary & Payment Deadline ===== --}}
<div class="nova-row nova-gap-3 nova-mb-4">
    <div class="nova-col" style="flex: 1;">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-coins" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.payment_summary') }}</h5>
            </div>
            <div class="nova-card-body">
                <div class="nova-row nova-gap-3 nova-align-items-center">
                    <div class="nova-col" style="flex: 2;">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.total') }}</p>
                        <p class="mb-0"><strong>{{ $currencySymbol }} {{ number_format($invoice->total_amount, 2) }}</strong></p>
                    </div>
                    <div class="nova-col" style="flex: 2;">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.paid_at') }}</p>
                        <p class="mb-0"><strong>{{ $currencySymbol }} {{ number_format($invoice->paid_amount, 2) }}</strong></p>
                    </div>
                    <div class="nova-col" style="flex: 2;">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.remaining') }}</p>
                        <p class="mb-0"><strong class="{{ $remaining <= 0 ? 'nova-text-success' : 'nova-text-danger' }}">{{ $currencySymbol }} {{ number_format($remaining, 2) }}</strong></p>
                    </div>
                </div>
                <div class="nova-mt-3">
                    <div class="nova-text-muted nova-fs-sm" style="margin-bottom: 0.25rem;">{{ __('messages.invoice_page.payment_method') ?? 'Payment Progress' }}</div>
                    <div class="nova-progress-bar">
                        <div class="nova-progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                @if($invoice->payment_method)
                    <div class="nova-mt-2">
                        <small class="nova-text-muted">{{ __('messages.invoice_page.payment_method') }}: {{ $invoice->payment_method }}</small>
                    </div>
                @endif
                @if($invoice->paid_at)
                    <div class="nova-mt-1">
                        <small class="nova-text-muted">{{ __('messages.invoice_page.paid_at') }}: {{ \Carbon\Carbon::parse($invoice->paid_at)->format('Y-m-d H:i') }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if($invoice->payment_type === 'cash')
    <div class="nova-col" style="flex: 1;">
        <div class="nova-card">
            <div class="nova-card-header">
                <h5 class="nova-card-title"><i class="fas fa-clock" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.details') }}</h5>
            </div>
            <div class="nova-card-body">
                @php
                $daysLeftFormatted = null;
                $deadlineClass = 'success';
                if ($daysLeft === null) {
                    $daysLeftFormatted = __('messages.invoice_page.no_deadline');
                } elseif ($daysLeft < 0) {
                    $daysLeftFormatted = __('messages.invoice_page.overdue');
                    $deadlineClass = 'danger';
                } elseif ($daysLeft <= 1) {
                    $daysLeftFormatted = '< 1 ' . __('messages.invoice_page.days_left');
                    $deadlineClass = 'danger';
                } elseif ($daysLeft <= 3) {
                    $daysLeftFormatted = $daysLeft . ' ' . __('messages.invoice_page.days_left');
                    $deadlineClass = 'warning';
                } else {
                    $daysLeftFormatted = $daysLeft . ' ' . __('messages.invoice_page.days_left');
                    $deadlineClass = 'info';
                }
                @endphp
                <div class="nova-row nova-gap-3">
                    <div class="nova-col">
                        <p class="nova-text-muted" style="margin: 0;">{{ __('messages.invoice_page.extend_deadline') }}</p>
                        <p class="mb-0"><span class="nova-badge {{ $deadlineClass }}">{{ !is_string($daysLeftFormatted) ? $daysLeftFormatted : $daysLeftFormatted }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ===== Card: Payments History ===== --}}
@if($payments->isNotEmpty())
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-history" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.payments_history') }}</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.invoice_page.total') }}</th>
                        <th>{{ __('messages.invoice_page.payment_method') }}</th>
                        <th>{{ __('messages.invoice_page.paid_at') }}</th>
                        <th>{{ __('messages.invoice_page.customer_info') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="nova-fw-semibold">{{ $payment->payment_number }}</td>
                        <td>{{ $currencySymbol }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->method ?? '—' }}</td>
                        <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d H:i') : '—' }}</td>
                        <td>{{ $payment->receiver?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-invoice" style="font-size: 2rem; color: var(--nova-text-3);"></i>
                                <p class="nova-text-muted nova-mt-2">{{ __('messages.invoice_page.no_payments') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ===== Card: Returns ===== --}}
@if($returnRequests->isNotEmpty())
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-undo" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.returns') }}</h5>
    </div>
    <div class="nova-card-body">
        <div class="nova-table-wrapper">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.invoice_page.type') }}</th>
                        <th>{{ __('messages.invoice_page.remaining') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnRequests as $return)
                    @php
                        $rStatus = $return->status instanceof \App\Enums\ReturnStatus
                            ? $return->status
                            : \App\Enums\ReturnStatus::tryFrom((string) ($return->status->value ?? $return->status));
                        $rType = $return->type instanceof \App\Enums\ReturnType
                            ? $return->type
                            : \App\Enums\ReturnType::tryFrom((string) ($return->type->value ?? $return->type));
                    @endphp
                    <tr>
                        <td class="nova-fw-semibold">{{ $return->return_number ?? $return->id }}</td>
                        <td>{{ $rType?->label() ?? '—' }}</td>
                        <td>
                            <span class="nova-badge {{ $rStatus?->badgeColor() ?? 'nova-text-muted' }}">
                                {{ $rStatus?->label() ?? '—' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="nova-text-center nova-py-4">
                            <div class="nova-empty">
                                <i class="fas fa-undo" style="font-size: 2rem; color: var(--nova-text-3);"></i>
                                <p class="nova-text-muted nova-mt-2">{{ __('messages.invoice_page.no_returns') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ===== Card: Timeline ===== --}}
<div class="nova-card">
    <div class="nova-card-header">
        <h5 class="nova-card-title"><i class="fas fa-clock" style="margin-inline-end: 0.5rem;"></i> {{ __('messages.invoice_page.timeline') }}</h5>
    </div>
    <div class="nova-card-body">
        @if($invoice->created_at || $invoice->approved_at || $invoice->paid_at || $invoice->due_date)
        <div class="nova-timeline">
            @if($invoice->created_at)
            <div class="nova-timeline-item">
                <div class="nova-timeline-dot"></div>
                <div>
                    <span class="nova-fw-bold">{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d H:i') }}</span>
                    <p class="nova-text-muted mb-0">{{ __('messages.invoice_page.created_at') ?? 'Created Invoice' }}</p>
                </div>
            </div>
            @endif
            @if($invoice->approved_at)
            <div class="nova-timeline-item">
                <div class="nova-timeline-dot"></div>
                <div>
                    <span class="nova-fw-bold">{{ \Carbon\Carbon::parse($invoice->approved_at)->format('Y-m-d H:i') }}</span>
                    <p class="nova-text-muted mb-0">{{ __('messages.invoice_page.approved_at') ?? 'Approved' }}</p>
                </div>
            </div>
            @endif
            @if($invoice->paid_at)
            <div class="nova-timeline-item">
                <div class="nova-timeline-dot"></div>
                <div>
                    <span class="nova-fw-bold">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('Y-m-d H:i') }}</span>
                    <p class="nova-text-muted mb-0">{{ __('messages.invoice_page.paid_at') }}</p>
                </div>
            </div>
            @endif
            @if($invoice->due_date)
            <div class="nova-timeline-item">
                <div class="nova-timeline-dot"></div>
                <div>
                    <span class="nova-fw-bold">{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d H:i') }}</span>
                    <p class="nova-text-muted mb-0">{{ __('messages.invoice_page.due_date') ?? 'Due Date' }}</p>
                </div>
            </div>
            @endif
        </div>
        @else
        <p class="nova-text-muted">{{ __('messages.invoice_page.timeline') ?? 'No events recorded' }}</p>
        @endif
    </div>
</div>

@endsection

{{-- ===== Modals: الموافقة / الرفض / تسجيل الدفعة ===== --}}
@if($canApprove)
<div class="nova-modal-backdrop" id="approveModal" onclick="closeInvoiceModal(event,'approveModal')">
    <div class="nova-modal" onclick="event.stopPropagation()">
        <div class="nova-modal-header">
            <h5 class="nova-modal-title"><i class="fas fa-check"></i> {{ __('messages.invoice_page.approve') }}</h5>
            <button type="button" class="nova-modal-close" onclick="closeInvoiceModal(null,'approveModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('invoices.approve', $invoice->id) }}">
            @csrf
            <div class="nova-modal-body">
                <p class="nova-text-muted mb-0">
                    سيتم اعتماد الفاتورة <strong>#{{ $invoice->invoice_number }}</strong>
                    باسم {{ $customer->name ?? '—' }} بمبلغ {{ number_format((float) $invoice->total_amount, 2) }} {{ $currencySymbol }}.
                </p>
                <div class="nova-form-group nova-mt-3">
                    <label class="nova-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="nova-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="nova-modal-footer">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeInvoiceModal(null,'approveModal')">إلغاء</button>
                <button type="submit" class="nova-btn nova-btn-primary"><i class="fas fa-check"></i> تأكيد الموافقة</button>
            </div>
        </form>
    </div>
</div>

<div class="nova-modal-backdrop" id="rejectModal" onclick="closeInvoiceModal(event,'rejectModal')">
    <div class="nova-modal" onclick="event.stopPropagation()">
        <div class="nova-modal-header">
            <h5 class="nova-modal-title"><i class="fas fa-times"></i> {{ __('messages.invoice_page.reject') }}</h5>
            <button type="button" class="nova-modal-close" onclick="closeInvoiceModal(null,'rejectModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('invoices.reject', $invoice->id) }}">
            @csrf
            <div class="nova-modal-body">
                <p class="nova-text-muted">سيتم رفض الفاتورة وإلغاؤها.</p>
                <div class="nova-form-group nova-mt-2">
                    <label class="nova-label">سبب الرفض <span class="text-danger">*</span></label>
                    <textarea name="reason" class="nova-textarea" rows="2" required></textarea>
                </div>
            </div>
            <div class="nova-modal-footer">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeInvoiceModal(null,'rejectModal')">إلغاء</button>
                <button type="submit" class="nova-btn nova-btn-danger"><i class="fas fa-times"></i> تأكيد الرفض</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($canCollect)
<div class="nova-modal-backdrop" id="paymentModal" onclick="closeInvoiceModal(event,'paymentModal')">
    <div class="nova-modal" onclick="event.stopPropagation()">
        <div class="nova-modal-header">
            <h5 class="nova-modal-title"><i class="fas fa-money-bill-wave"></i> {{ __('messages.invoice_page.record_payment') }}</h5>
            <button type="button" class="nova-modal-close" onclick="closeInvoiceModal(null,'paymentModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('invoices.payments', $invoice->id) }}">
            @csrf
            <div class="nova-modal-body">
                <div class="nova-alert info nova-mb-3">
                    <div>الإجمالي: <strong>{{ number_format((float) $invoice->total_amount, 2) }} {{ $currencySymbol }}</strong></div>
                    <div>المسدّد: <strong>{{ number_format((float) $invoice->paid_amount, 2) }} {{ $currencySymbol }}</strong></div>
                    <div>المتبقي: <strong>{{ number_format((float) $remaining, 2) }} {{ $currencySymbol }}</strong></div>
                    @if($walletAvailable !== null)
                        <div>
                            رصيد المحفظة: <strong>{{ number_format($walletBalance, 2) }} {{ $currencySymbol }}</strong>
                            @if($isCreditCustomer)
                                (حد الاعتماد {{ number_format((float) ($invoice->customer?->credit_limit ?? 0), 2) }})
                            @endif
                        </div>
                    @endif
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">المبلغ <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="nova-input" step="0.01" min="0.01"
                           max="{{ (float) $remaining }}" value="{{ (float) $remaining }}" required>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">طريقة الدفع <span class="text-danger">*</span></label>
                    <select name="method" class="nova-select" required>
                        @if($walletAvailable !== null)
                            <option value="wallet">محفظة العميل</option>
                        @endif
                        <option value="cash" @if($walletAvailable === null) selected @endif>نقدًا</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="cheque">شيك</option>
                        <option value="online">دفع إلكتروني</option>
                    </select>
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">رقم المرجع</label>
                    <input type="text" name="reference" class="nova-input">
                </div>

                <div class="nova-form-group">
                    <label class="nova-label">ملاحظات</label>
                    <textarea name="notes" class="nova-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="nova-modal-footer">
                <button type="button" class="nova-btn nova-btn-outline" onclick="closeInvoiceModal(null,'paymentModal')">إلغاء</button>
                <button type="submit" class="nova-btn nova-btn-primary"><i class="fas fa-check"></i> تسجيل الدفعة</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function openInvoiceModal(id) {
    var el = document.getElementById(id);
    if (el) { el.classList.add('show'); }
}

function closeInvoiceModal(e, id) {
    if (e && e.target !== e.currentTarget) { return; }
    var el = document.getElementById(id);
    if (el) { el.classList.remove('show'); }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['approveModal', 'rejectModal', 'paymentModal'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) { el.classList.remove('show'); }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash alerts (but never the informational panel inside the payment modal)
    setTimeout(function() {
        document.querySelectorAll('.nova-alert').forEach(function(alert) {
            if (alert.closest('.nova-modal')) { return; }
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 8000);
});
</script>
@endpush