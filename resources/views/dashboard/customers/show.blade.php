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

{{-- ============================================
     Quick Stats Cards
     ============================================ --}}
@php
    $walletFirst   = $customer->wallets->first() ?? null;
    $branchesCount = ($branches ?? collect())->count();
    $ordersCount   = ($customer->orders ?? collect())->count();
    $invoicesCount = ($customer->invoices ?? collect())->count();

    // بيانات الخريطة: الفروع التي تملك إحداثيات
    $branchesMapData = ($branches ?? collect())
        ->filter(fn($b) => $b->latitude && $b->longitude)
        ->map(fn($b) => [
            'id'        => $b->id,
            'name'      => $b->name,
            'phone'     => $b->phone,
            'address'   => $b->address,
            'lat'       => (float) $b->latitude,
            'lng'       => (float) $b->longitude,
            'is_active' => (bool) $b->is_active,
            'url'       => route('branches.show', $b->id),
            'edit_url'  => route('branches.edit', $b->id),
        ])
        ->values()
        ->toArray();
    $branchesWithCoordsCount = count($branchesMapData);
@endphp

<div class="customer-stats-grid">
    <div class="customer-stat-card">
        <div class="customer-stat-icon customer-stat-icon-primary">
            <i class="fas fa-store"></i>
        </div>
        <div class="customer-stat-body">
            <span class="customer-stat-label">عدد الفروع</span>
            <span class="customer-stat-value">{{ $branchesCount }}</span>
        </div>
    </div>

    <div class="customer-stat-card">
        <div class="customer-stat-icon customer-stat-icon-success">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="customer-stat-body">
            <span class="customer-stat-label">رصيد المحفظة</span>
            <span class="customer-stat-value">
                {{ $walletFirst ? number_format($walletFirst->balance, 2) : '—' }}
            </span>
        </div>
    </div>

    <div class="customer-stat-card">
        <div class="customer-stat-icon customer-stat-icon-warning">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="customer-stat-body">
            <span class="customer-stat-label">عدد الطلبات</span>
            <span class="customer-stat-value">{{ $ordersCount }}</span>
        </div>
    </div>

    <div class="customer-stat-card">
        <div class="customer-stat-icon customer-stat-icon-info">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="customer-stat-body">
            <span class="customer-stat-label">عدد الفواتير</span>
            <span class="customer-stat-value">{{ $invoicesCount }}</span>
        </div>
    </div>
</div>

{{-- ============================================
     Row 1: معلومات أساسية + قائمة الأسعار
     ============================================ --}}
<div class="customer-detail-grid">
    {{-- Info Card --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-info-circle me-1"></i>
                معلومات العميل
            </h3>
        </div>
        <div class="nova-card-body" style="padding: 0;">
            <table class="branch-info-table">
                <tbody>
                    <tr>
                        <th><i class="fas fa-user"></i> الاسم</th>
                        <td class="branch-info-strong">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-at"></i> اسم المستخدم</th>
                        <td>{{ $customer->username ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-envelope"></i> البريد الإلكتروني</th>
                        <td>
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}" class="branch-info-link">
                                    {{ $customer->email }}
                                </a>
                            @else
                                <span class="branch-info-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone"></i> الهاتف</th>
                        <td dir="ltr" style="text-align: start;">
                            {{ $customer->phone ?: '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-map-marker-alt"></i> العنوان</th>
                        <td>{{ $customer->default_address ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user-tag"></i> نوع العميل</th>
                        <td>
                            @if($customer->customer_type === 'credit')
                                <span class="customer-badge customer-badge-warning">
                                    <i class="fas fa-clock"></i> آجل
                                </span>
                            @else
                                <span class="customer-badge customer-badge-success">
                                    <i class="fas fa-money-bill-wave"></i> نقدي
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($customer->customer_type === 'credit')
                        <tr>
                            <th><i class="fas fa-gauge-high"></i> حد الائتمان</th>
                            <td class="branch-info-strong">
                                {{ number_format($customer->credit_limit ?? 0, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-scale-balanced"></i> الرصيد الحالي</th>
                            <td class="branch-info-strong customer-balance">
                                {{ number_format($customer->current_balance ?? 0, 2) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Price List Card --}}
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-tag me-1"></i>
                قائمة الأسعار المسندة
            </h3>
        </div>
        <div class="nova-card-body">
            <div class="customer-price-banner {{ $customer->priceList ? 'customer-price-banner-active' : 'customer-price-banner-muted' }}">
                <div class="customer-price-icon">
                    <i class="fas {{ $customer->priceList ? 'fa-check-circle' : 'fa-circle-info' }}"></i>
                </div>
                <div>
                    <div class="customer-price-label">القائمة الحالية</div>
                    <div class="customer-price-value">
                        {{ $customer->priceList?->name ?? 'لا توجد (سيعتمد السعر الافتراضي)' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('customers.price-list.update', $customer) }}" class="nova-mt-3">
                @csrf
                <div class="nova-form-group">
                    <label class="nova-label">
                        <i class="fas fa-exchange-alt me-1"></i>
                        تغيير قائمة الأسعار
                    </label>
                    <select name="price_list_id" class="nova-select">
                        <option value="">— بدون (استخدام الافتراضي) —</option>
                        @foreach($allPriceLists as $pl)
                            <option value="{{ $pl->id }}" {{ $customer->price_list_id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }}{{ $pl->is_default ? ' (افتراضية)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="nova-btn nova-btn-primary nova-mt-2">
                    <i class="fas fa-save me-1"></i> حفظ التغييرات
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ============================================
     NEW: خريطة الفروع الكبيرة
     ============================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header customer-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-map-location-dot me-1"></i>
            خريطة مواقع الفروع
            @if($branchesWithCoordsCount > 0)
                <span class="customer-count-badge">{{ $branchesWithCoordsCount }}</span>
            @endif
        </h3>
        <div class="nova-card-actions">
            @if($branchesWithCoordsCount > 0)
                <button type="button" id="mapResetViewBtn" class="nova-btn nova-btn-outline nova-btn-sm">
                    <i class="fas fa-expand"></i> إظهار جميع الفروع
                </button>
            @endif
            <a href="{{ route('branches.index') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                <i class="fas fa-list"></i> كل الفروع
            </a>
        </div>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        @if($branchesWithCoordsCount > 0)
            <div class="customer-map-legend">
                <span class="customer-map-legend-item">
                    <span class="customer-map-legend-dot" style="background: #16a34a;"></span>
                    نشط
                </span>
                <span class="customer-map-legend-item">
                    <span class="customer-map-legend-dot" style="background: #94a3b8;"></span>
                    غير نشط
                </span>
                <span class="customer-map-legend-hint">
                    <i class="fas fa-hand-pointer"></i> اضغط على أي علامة لعرض التفاصيل
                </span>
            </div>
            <div id="customerBranchesMap" class="customer-map-large"></div>
        @else
            <div class="branches-empty" style="padding: 3rem 1.5rem;">
                <div class="branches-empty-icon" style="width: 72px; height: 72px; font-size: 1.75rem;">
                    <i class="fas fa-map-location-dot"></i>
                </div>
                <h4 class="branches-empty-title">لا توجد مواقع جغرافية للفروع</h4>
                <p class="branches-empty-text">
                    أضف إحداثيات GPS لفروع هذا العميل لتظهر على الخريطة
                </p>
                <a href="{{ route('branches.index') }}" class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-edit"></i> إدارة الفروع
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ============================================
     Branches Section
     ============================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header customer-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-store me-1"></i>
            الفروع
            <span class="customer-count-badge">{{ $branchesCount }}</span>
        </h3>
        <div class="nova-card-actions">
            <a href="{{ route('branches.create', ['customer_id' => $customer->id]) }}"
               class="nova-btn nova-btn-primary nova-btn-sm">
                <i class="fas fa-plus"></i> إضافة فرع
            </a>
        </div>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        @if($branchesCount > 0)
            <div class="branches-table-wrap">
                <table class="branches-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>اسم الفرع</th>
                            <th>الهاتف</th>
                            <th>العنوان</th>
                            <th>توقيت العمل</th>
                            <th>الحالة</th>
                            <th style="width: 130px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $index => $branch)
                            <tr>
                                <td class="branches-row-num">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('branches.show', $branch->id) }}" class="branches-name-link">
                                        <i class="fas fa-store"></i>
                                        {{ $branch->name }}
                                    </a>
                                </td>
                                <td class="branches-cell-muted" dir="ltr">{{ $branch->phone ?: '—' }}</td>
                                <td class="branches-cell-muted">{{ $branch->address ?: '—' }}</td>
                                <td class="branches-cell-muted">
                                    @if($branch->working_hours_from && $branch->working_hours_to)
                                        <i class="fas fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($branch->working_hours_from)->format('H:i') }}
                                        —
                                        {{ \Carbon\Carbon::parse($branch->working_hours_to)->format('H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="branches-status {{ $branch->is_active ? 'branches-status-active' : 'branches-status-inactive' }}">
                                        <span class="branches-status-dot"></span>
                                        {{ $branch->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="branches-actions">
                                        <a href="{{ route('branches.show', $branch->id) }}"
                                           class="nova-btn nova-btn-sm nova-btn-outline"
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('branches.edit', $branch->id) }}"
                                           class="nova-btn nova-btn-sm nova-btn-warning"
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('branches.destroy', $branch->id) }}"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الفرع؟');"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="nova-btn nova-btn-sm nova-btn-danger"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="branches-empty" style="padding: 2.5rem 1.5rem;">
                <div class="branches-empty-icon" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    <i class="fas fa-store-slash"></i>
                </div>
                <h4 class="branches-empty-title">لا توجد فروع لهذا العميل</h4>
                <p class="branches-empty-text">ابدأ بإضافة أول فرع</p>
                <a href="{{ route('branches.create', ['customer_id' => $customer->id]) }}"
                   class="nova-btn nova-btn-primary nova-btn-sm">
                    <i class="fas fa-plus"></i> إضافة فرع
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ============================================
     Wallet Section
     ============================================ --}}
<div class="nova-mb-4">
    @forelse($customer->wallets ?? collect() as $wallet)
        <div class="customer-wallet-card">
            <div class="customer-wallet-header">
                <div class="customer-wallet-title-wrap">
                    <div class="customer-wallet-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <h3 class="customer-wallet-title">
                            المحفظة
                            <span class="customer-wallet-currency">
                                {{ $wallet->currency->symbol ?? $wallet->currency->code ?? '' }}
                            </span>
                        </h3>
                        <p class="customer-wallet-sub">إدارة رصيد المحفظة والإيداعات</p>
                    </div>
                </div>
            </div>

            <div class="customer-wallet-stats">
                <div class="customer-wallet-stat">
                    <span class="customer-wallet-stat-label">الرصيد الحالي</span>
                    <span class="customer-wallet-stat-value customer-wallet-balance">
                        {{ number_format($wallet->balance, 2) }}
                    </span>
                </div>
                <div class="customer-wallet-stat">
                    <span class="customer-wallet-stat-label">إجمالي الإيداعات</span>
                    <span class="customer-wallet-stat-value customer-wallet-deposit">
                        {{ number_format($wallet->total_deposited, 2) }}
                    </span>
                </div>
                <div class="customer-wallet-stat">
                    <span class="customer-wallet-stat-label">إجمالي المصروف</span>
                    <span class="customer-wallet-stat-value customer-wallet-spent">
                        {{ number_format($wallet->total_spent, 2) }}
                    </span>
                </div>
            </div>

            <div class="customer-wallet-form-wrap">
                <h4 class="customer-wallet-form-title">
                    <i class="fas fa-plus-circle me-1"></i> إيداع جديد
                </h4>

                <form method="POST" action="{{ route('wallets.deposit', $wallet) }}">
                    @csrf
                    @if ($errors->any())
                        <div class="nova-alert danger nova-mb-3">
                            <i class="fas fa-circle-xmark"></i>
                            <ul class="nova-mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="customer-wallet-form-grid">
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-coins me-1"></i> المبلغ
                            </label>
                            <input type="number" step="0.01" min="0.01"
                                   name="amount" class="nova-input"
                                   placeholder="0.00" required>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-credit-card me-1"></i> طريقة الدفع
                            </label>
                            <select name="method" class="nova-select" required>
                                <option value="cash">نقدي</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="cheque">شيك</option>
                                <option value="online">دفع إلكتروني</option>
                            </select>
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-hashtag me-1"></i> رقم المرجع
                            </label>
                            <input type="text" name="reference_number"
                                   class="nova-input" placeholder="اختياري">
                        </div>
                        <div class="nova-form-group">
                            <label class="nova-label">
                                <i class="fas fa-comment me-1"></i> ملاحظات
                            </label>
                            <input type="text" name="notes"
                                   class="nova-input" placeholder="اختياري">
                        </div>
                    </div>

                    <button class="nova-btn nova-btn-success nova-mt-3">
                        <i class="fas fa-plus-circle me-1"></i> تنفيذ الإيداع
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="nova-card">
            <div class="nova-card-body">
                @if ($errors->has('wallet'))
                    <div class="nova-alert danger nova-mb-3">
                        <i class="fas fa-circle-xmark"></i> {{ $errors->first('wallet') }}
                    </div>
                @endif

                <div class="branches-empty" style="padding: 2.5rem 1.5rem;">
                    <div class="branches-empty-icon" style="width: 64px; height: 64px; font-size: 1.5rem;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h4 class="branches-empty-title">لا توجد محفظة لهذا العميل</h4>
                    <p class="branches-empty-text">أنشئ محفظة لبدء تسجيل الإيداعات والمصاريف</p>
                    <button type="button" class="nova-btn nova-btn-primary"
                            onclick="document.getElementById('createWalletModal').classList.add('show')">
                        <i class="fas fa-wallet"></i> إنشاء محفظة
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- ============================================
     Wallet Transactions
     ============================================ --}}
@if(($customer->wallets ?? collect())->isNotEmpty() && ($customer->wallets->first()->transactions ?? collect())->isNotEmpty())
    <div class="nova-card nova-mb-4">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-history me-1"></i>
                آخر حركات المحفظة
            </h3>
        </div>
        <div class="nova-card-body" style="padding: 0;">
            <div class="branches-table-wrap">
                <table class="branches-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th>الرصيد بعد</th>
                            <th>طريقة الدفع</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->wallets->first()->transactions as $txn)
                            @php
                                $isCredit = in_array($txn->type, ['deposit', 'refund']);
                            @endphp
                            <tr>
                                <td class="branches-row-num">{{ $txn->id }}</td>
                                <td>
                                    <span class="branches-status {{ $isCredit ? 'branches-status-active' : 'customer-status-warning' }}">
                                        <span class="branches-status-dot"></span>
                                        {{ $isCredit ? 'إيداع' : 'سحب' }}
                                    </span>
                                </td>
                                <td class="branch-info-strong">{{ number_format($txn->amount, 2) }}</td>
                                <td class="branches-cell-muted">{{ number_format($txn->balance_after, 2) }}</td>
                                <td class="branches-cell-muted">{{ $txn->method ?? '—' }}</td>
                                <td class="branches-cell-muted">
                                    {{ $txn?->created_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- ============================================
     Orders + Invoices (two columns)
     ============================================ --}}
<div class="customer-two-col">
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-shopping-cart me-1"></i>
                آخر الطلبات
            </h3>
        </div>
        <div class="nova-card-body">
            @forelse($customer->orders ?? collect() as $order)
                <div class="customer-list-row">
                    <a href="{{ route('orders.show', $order) }}" class="customer-list-link">
                        <i class="fas fa-receipt"></i>
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
                    <span class="customer-badge customer-badge-{{ $badgeClass }}">
                        {{ $order->status }}
                    </span>
                </div>
            @empty
                <div class="customer-list-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>لا توجد طلبات</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">
                <i class="fas fa-file-invoice-dollar me-1"></i>
                آخر الفواتير
            </h3>
        </div>
        <div class="nova-card-body">
            @forelse($customer->invoices ?? collect() as $invoice)
                <div class="customer-list-row">
                    <a href="{{ route('invoices.show', $invoice) }}" class="customer-list-link">
                        <i class="fas fa-file-invoice"></i>
                        {{ $invoice->invoice_number ?? '#' . $invoice->id }}
                    </a>
                    <span class="customer-badge customer-badge-secondary">
                        {{ $invoice->status ?? '—' }}
                    </span>
                </div>
            @empty
                <div class="customer-list-empty">
                    <i class="fas fa-file-invoice"></i>
                    <p>لا توجد فواتير</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ============================================
     Returns Log
     ============================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-rotate-left me-1"></i>
            سجل الاسترجاعات
        </h3>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div class="branches-table-wrap">
            <table class="branches-table">
                <thead>
                    <tr>
                        <th>رقم الاسترجاع</th>
                        <th>الفاتورة</th>
                        <th>النوع</th>
                        <th>الحالة</th>
                        <th>عدد الأصناف</th>
                        <th>قيمة الاسترداد</th>
                        <th>التاريخ</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td class="branch-info-strong">{{ $return->return_number }}</td>
                            <td>
                                <a href="{{ route('invoices.show', $return->invoice_id) }}" class="branch-info-link">
                                    {{ $return->invoice?->invoice_number ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span class="customer-badge customer-badge-info">
                                    {{ $return->type?->value === 'full' ? 'كامل' : 'جزئي' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusValue = is_object($return->status) ? $return->status->value : (string) $return->status;
                                    $statusMap = [
                                        'pending'   => ['قيد المراجعة', 'warning'],
                                        'approved'  => ['معتمد', 'success'],
                                        'rejected'  => ['مرفوض', 'danger'],
                                        'processed' => ['منفذ', 'success'],
                                    ];
                                    [$statusLabel, $statusColor] = $statusMap[$statusValue] ?? [$statusValue, 'info'];
                                @endphp
                                <span class="customer-badge customer-badge-{{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="branches-cell-muted">{{ $return->items?->count() ?? 0 }}</td>
                            <td class="branch-info-strong">{{ number_format((float) $return->total_refund, 2) }}</td>
                            <td class="branches-cell-muted">{{ $return->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('returns.index') }}" class="nova-btn nova-btn-sm nova-btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="customer-table-empty">
                                    <i class="fas fa-rotate-left"></i>
                                    <p>لا توجد طلبات استرجاع لهذا العميل</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================
     Payments Log
     ============================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-money-bill-transfer me-1"></i>
            سجل الدفعات
        </h3>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div class="branches-table-wrap">
            <table class="branches-table">
                <thead>
                    <tr>
                        <th>رقم الإيصال</th>
                        <th>الفاتورة</th>
                        <th>المبلغ</th>
                        <th>الطريقة</th>
                        <th>تاريخ الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="branch-info-strong">{{ $payment->receipt_number ?? '—' }}</td>
                            <td>
                                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="branch-info-link">
                                    {{ $payment->invoice_number ?? '—' }}
                                </a>
                            </td>
                            <td class="branch-info-strong">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>
                                @php
                                    $methodMap = [
                                        'cash' => 'نقدي', 'bank_transfer' => 'تحويل بنكي',
                                        'card' => 'بطاقة', 'online' => 'أونلاين',
                                        'wallet' => 'محفظة', 'cheque' => 'شيك',
                                    ];
                                @endphp
                                <span class="customer-badge customer-badge-info">
                                    {{ $methodMap[$payment->payment_method] ?? $payment->payment_method }}
                                </span>
                            </td>
                            <td class="branches-cell-muted">
                                {{ $payment->paid_at ? \Illuminate\Support\Carbon::parse($payment->paid_at)->format('Y-m-d') : '—' }}
                            </td>
                            <td class="branches-cell-muted">{{ $payment->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="customer-table-empty">
                                    <i class="fas fa-money-bill-transfer"></i>
                                    <p>لا توجد دفعات مسجلة لهذا العميل</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================
     Batch Trace
     ============================================ --}}
<div class="nova-card nova-mb-4">
    <div class="nova-card-header">
        <h3 class="nova-card-title">
            <i class="fas fa-boxes-stacked me-1"></i>
            المنتجات والكميات حسب الباتش
        </h3>
    </div>
    <div class="nova-card-body" style="padding: 0;">
        <div class="branches-table-wrap">
            <table class="branches-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>العبوة</th>
                        <th>إجمالي المفوتر</th>
                        <th>إجمالي المُرجَع</th>
                        <th>الصافي</th>
                        <th style="min-width: 320px;">تفصيل الباتشات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batchTrace as $row)
                        <tr>
                            <td>
                                <span class="branch-info-strong">{{ $row->item_name }}</span>
                                <br>
                                <span class="branches-cell-muted" style="font-size: 0.8rem;">
                                    {{ $row->code }}
                                </span>
                            </td>
                            <td>
                                <span class="customer-badge customer-badge-info">{{ $row->pack_name }}</span>
                            </td>
                            <td class="branch-info-strong">
                                {{ rtrim(rtrim(number_format($row->invoiced_quantity, 2), '0'), '.') }}
                            </td>
                            <td class="{{ $row->returned_quantity > 0 ? 'customer-text-danger' : 'branches-cell-muted' }}">
                                {{ rtrim(rtrim(number_format($row->returned_quantity, 2), '0'), '.') }}
                            </td>
                            <td class="branch-info-strong">
                                {{ rtrim(rtrim(number_format($row->invoiced_quantity - $row->returned_quantity, 2), '0'), '.') }}
                            </td>
                            <td>
                                @forelse ($row->batches as $batch)
                                    <div class="nova-batch-line">
                                        <span class="customer-badge customer-badge-info">
                                            {{ $batch->batch_number }}
                                        </span>
                                        <span class="branches-cell-muted" style="font-size: 0.8rem;">
                                            {{ $batch->expiry_date ? 'ينتهي ' . $batch->expiry_date : 'بدون صلاحية' }}
                                            @if ($batch->is_expired)
                                                <span class="customer-badge customer-badge-danger">منتهي</span>
                                            @elseif ($batch->days_to_expiry !== null && $batch->days_to_expiry <= 30)
                                                <span class="customer-badge customer-badge-warning">يتبقى {{ $batch->days_to_expiry }} يوم</span>
                                            @endif
                                        </span>
                                        <span style="font-size: 0.85rem;">
                                            {{ rtrim(rtrim(number_format($batch->net, 2), '0'), '.') }}
                                            @if ($batch->returned > 0)
                                                <span class="branches-cell-muted">
                                                    (رجع {{ rtrim(rtrim(number_format($batch->returned, 2), '0'), '.') }})
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                @empty
                                    <span class="branches-cell-muted" style="font-size: 0.85rem;">
                                        لا يوجد تتبع باتش
                                    </span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="customer-table-empty">
                                    <i class="fas fa-boxes-stacked"></i>
                                    <p>لا توجد منتجات مفوترة لهذا العميل</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================
     Modal: إنشاء محفظة
     ============================================ --}}
@if(($customer->wallets ?? collect())->isEmpty())
<div class="nova-modal-backdrop" id="createWalletModal" tabindex="-1" aria-hidden="true">
    <div class="nova-modal">
        <div class="nova-card" style="width: 100%; max-width: 460px;">
            <div class="nova-card-header">
                <h3 class="nova-card-title">
                    <i class="fas fa-wallet me-1"></i>
                    إنشاء محفظة للعميل {{ $customer->name }}
                </h3>
            </div>
            <form method="POST" action="{{ route('wallets.store') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="nova-card-body">
                    @if ($errors->any())
                        <div class="nova-alert danger nova-mb-3">
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

                <div class="nova-card-header" style="border-top: 1px solid var(--nova-border, #e2e8f0); border-bottom: none;">
                    <div class="nova-flex nova-justify-end nova-gap-2" style="width: 100%;">
                        <button type="button" class="nova-btn nova-btn-outline"
                                onclick="document.getElementById('createWalletModal').classList.remove('show')">
                            إلغاء
                        </button>
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-check me-1"></i>
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ============================================
       Quick Stats Cards
       ============================================ */
    .customer-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .customer-stat-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid var(--nova-border, #e2e8f0);
        border-radius: var(--nova-radius-md, 0.75rem);
        transition: box-shadow 0.2s;
    }

    .customer-stat-card:hover { box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06); }

    .customer-stat-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .customer-stat-icon-primary { background: #dbeafe; color: #1d4ed8; }
    .customer-stat-icon-success { background: #dcfce7; color: #15803d; }
    .customer-stat-icon-warning { background: #fef3c7; color: #b45309; }
    .customer-stat-icon-info    { background: #e0f2fe; color: #0369a1; }

    .customer-stat-body { display: flex; flex-direction: column; gap: 0.2rem; min-width: 0; }

    .customer-stat-label {
        font-size: 0.75rem;
        color: var(--nova-text-muted, #64748b);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .customer-stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--nova-text, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ============================================
       Layout Grids
       ============================================ */
    .customer-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .customer-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* ============================================
       Info Table
       ============================================ */
    .branch-info-table { width: 100%; border-collapse: collapse; }
    .branch-info-table tbody tr { border-bottom: 1px solid var(--nova-border, #e2e8f0); }
    .branch-info-table tbody tr:last-child { border-bottom: none; }

    .branch-info-table th,
    .branch-info-table td {
        padding: 0.9rem 1.25rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .branch-info-table th {
        text-align: start;
        width: 35%;
        background: var(--nova-bg-soft, #f8fafc);
        color: var(--nova-text-muted, #475569);
        font-weight: 600;
        white-space: nowrap;
        border-inline-end: 1px solid var(--nova-border, #e2e8f0);
    }

    .branch-info-table th i {
        width: 18px;
        margin-inline-end: 0.5rem;
        color: var(--nova-primary, #2563eb);
    }

    .branch-info-table td { color: var(--nova-text, #1e293b); }

    .branch-info-strong { font-weight: 700; color: var(--nova-text, #0f172a) !important; }
    .branch-info-muted { color: var(--nova-text-muted, #64748b) !important; font-size: 0.85rem !important; }

    .branch-info-link {
        color: var(--nova-primary, #2563eb);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s;
    }

    .branch-info-link:hover {
        color: var(--nova-primary-dark, #1d4ed8);
        text-decoration: underline;
    }

    .customer-balance { color: #b45309 !important; }

    /* ============================================
       Badges
       ============================================ */
    .customer-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .customer-badge-success   { background: #dcfce7; color: #166534; }
    .customer-badge-warning   { background: #fef3c7; color: #b45309; }
    .customer-badge-danger    { background: #fee2e2; color: #b91c1c; }
    .customer-badge-info      { background: #e0f2fe; color: #0369a1; }
    .customer-badge-secondary { background: #f1f5f9; color: #64748b; }

    .customer-text-danger { color: #b91c1c; font-weight: 600; }

    /* ============================================
       Price List Banner
       ============================================ */
    .customer-price-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 0.65rem;
        border: 1px solid transparent;
    }

    .customer-price-banner-active { background: #f0fdf4; border-color: #bbf7d0; }
    .customer-price-banner-muted  { background: #f8fafc; border-color: #e2e8f0; }

    .customer-price-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        font-size: 1.15rem;
    }

    .customer-price-banner-active .customer-price-icon { color: #15803d; }
    .customer-price-banner-muted .customer-price-icon  { color: #64748b; }

    .customer-price-label { font-size: 0.75rem; color: var(--nova-text-muted, #64748b); font-weight: 500; }
    .customer-price-value { font-size: 0.95rem; font-weight: 700; color: var(--nova-text, #0f172a); }

    /* ============================================
       Card Header with count badge
       ============================================ */
    .customer-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .customer-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 22px;
        padding: 0 0.5rem;
        margin-inline-start: 0.5rem;
        border-radius: 999px;
        background: var(--nova-primary, #2563eb);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        vertical-align: middle;
    }

    /* ============================================
       NEW: Big Map
       ============================================ */
    .customer-map-large {
        width: 100%;
        height: 520px;
        background: #f1f5f9;
        border-radius: 0 0 var(--nova-radius-md, 0.75rem) var(--nova-radius-md, 0.75rem);
        position: relative;
        z-index: 1;
    }

    .customer-map-legend {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 0.7rem 1.25rem;
        background: var(--nova-bg-soft, #f8fafc);
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
        flex-wrap: wrap;
        font-size: 0.8rem;
        color: var(--nova-text-muted, #64748b);
    }

    .customer-map-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 500;
    }

    .customer-map-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .customer-map-legend-hint {
        margin-inline-start: auto;
        font-style: italic;
        font-size: 0.78rem;
    }

    /* Custom branch marker pin */
    .branch-map-pin {
        position: relative;
        width: 34px;
        height: 42px;
    }

    .branch-map-pin-head {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        transition: transform 0.15s;
    }

    .branch-map-pin:hover .branch-map-pin-head {
        transform: translateX(-50%) scale(1.15);
    }

    .branch-map-pin-tail {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
    }

    /* Leaflet popup custom */
    .branch-popup-content {
        direction: rtl;
        text-align: start;
        min-width: 230px;
        font-family: inherit;
    }

    .branch-popup-title {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .branch-popup-line {
        font-size: 0.8rem;
        color: #475569;
        margin-bottom: 0.3rem;
        display: flex;
        align-items: flex-start;
        gap: 0.4rem;
    }

    .branch-popup-line i {
        color: #64748b;
        margin-top: 0.15rem;
        flex-shrink: 0;
    }

    .branch-popup-status {
        display: inline-block;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .branch-popup-actions {
        display: flex;
        gap: 0.4rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e8f0;
    }

    .branch-popup-btn {
        flex: 1;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        transition: background 0.15s;
    }

    .branch-popup-btn-primary { background: #2563eb; color: #fff; }
    .branch-popup-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .branch-popup-btn-outline { background: #f1f5f9; color: #334155; }
    .branch-popup-btn-outline:hover { background: #e2e8f0; color: #0f172a; }

    /* ============================================
       Wallet Card
       ============================================ */
    .customer-wallet-card {
        background: #fff;
        border: 1px solid var(--nova-border, #e2e8f0);
        border-radius: var(--nova-radius-md, 0.75rem);
        overflow: hidden;
    }

    .customer-wallet-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
    }

    .customer-wallet-title-wrap { display: flex; align-items: center; gap: 1rem; }

    .customer-wallet-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 0.75rem;
        background: var(--nova-primary, #2563eb);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .customer-wallet-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--nova-text, #0f172a);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .customer-wallet-currency {
        display: inline-block;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        background: #fff;
        color: var(--nova-primary, #2563eb);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .customer-wallet-sub { margin: 0.15rem 0 0; font-size: 0.8rem; color: var(--nova-text-muted, #64748b); }

    .customer-wallet-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
    }

    .customer-wallet-stat {
        padding: 1.15rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        border-inline-end: 1px solid var(--nova-border, #e2e8f0);
    }

    .customer-wallet-stat:last-child { border-inline-end: none; }

    .customer-wallet-stat-label {
        font-size: 0.75rem;
        color: var(--nova-text-muted, #64748b);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .customer-wallet-stat-value { font-size: 1.25rem; font-weight: 700; }

    .customer-wallet-balance { color: #15803d; }
    .customer-wallet-deposit { color: #0369a1; }
    .customer-wallet-spent   { color: #b45309; }

    .customer-wallet-form-wrap { padding: 1.25rem 1.5rem; }

    .customer-wallet-form-title {
        margin: 0 0 1rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--nova-text, #0f172a);
    }

    .customer-wallet-form-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    /* ============================================
       List Rows
       ============================================ */
    .customer-list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--nova-border, #f1f5f9);
    }

    .customer-list-row:last-child { border-bottom: none; }

    .customer-list-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--nova-primary, #2563eb);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: color 0.15s;
    }

    .customer-list-link:hover { color: var(--nova-primary-dark, #1d4ed8); text-decoration: underline; }

    .customer-list-empty { text-align: center; padding: 1.5rem 1rem; color: var(--nova-text-muted, #94a3b8); }
    .customer-list-empty i { font-size: 1.75rem; margin-bottom: 0.5rem; display: block; }
    .customer-list-empty p { margin: 0; font-size: 0.875rem; }

    /* ============================================
       Table Empty State
       ============================================ */
    .customer-table-empty { text-align: center; padding: 2rem 1rem; color: var(--nova-text-muted, #94a3b8); }
    .customer-table-empty i { font-size: 1.75rem; margin-bottom: 0.5rem; display: block; }
    .customer-table-empty p { margin: 0; font-size: 0.875rem; }

    /* ============================================
       Batch Line
       ============================================ */
    .nova-batch-line {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding: 0.15rem 0;
    }

    /* ============================================
       Status Badges (shared)
       ============================================ */
    .branches-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .branches-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .branches-status-active   { background: #dcfce7; color: #166534; }
    .branches-status-inactive { background: #f1f5f9; color: #64748b; }
    .customer-status-warning  { background: #fef3c7; color: #b45309; }

    /* ============================================
       Table
       ============================================ */
    .branches-table-wrap { overflow-x: auto; }

    .branches-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }

    .branches-table thead th {
        text-align: start;
        padding: 0.85rem 1rem;
        background: var(--nova-bg-soft, #f8fafc);
        color: var(--nova-text-muted, #64748b);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
        white-space: nowrap;
    }

    .branches-table tbody td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--nova-border, #e2e8f0);
        color: var(--nova-text, #1e293b);
        vertical-align: middle;
    }

    .branches-table tbody tr:hover { background: var(--nova-bg-soft, #f8fafc); }
    .branches-table tbody tr:last-child td { border-bottom: none; }

    .branches-row-num { color: var(--nova-text-muted, #94a3b8); font-weight: 600; font-size: 0.85rem; }

    .branches-name-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--nova-primary, #2563eb);
        text-decoration: none;
    }

    .branches-name-link:hover { text-decoration: underline; }

    .branches-cell-muted { color: var(--nova-text-muted, #64748b); font-size: 0.875rem; }

    .branches-actions { display: inline-flex; gap: 0.35rem; align-items: center; }
    .branches-actions .nova-btn { padding: 0.4rem 0.6rem; }

    /* ============================================
       Empty State
       ============================================ */
    .branches-empty { text-align: center; padding: 3rem 1.5rem; }

    .branches-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--nova-bg-soft, #f1f5f9);
        color: var(--nova-text-muted, #94a3b8);
        font-size: 2rem;
    }

    .branches-empty-title {
        margin: 0 0 0.35rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--nova-text, #1e293b);
    }

    .branches-empty-text {
        margin: 0 0 1.25rem;
        color: var(--nova-text-muted, #64748b);
        font-size: 0.875rem;
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 1024px) {
        .customer-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .customer-detail-grid { grid-template-columns: 1fr; }
        .customer-two-col { grid-template-columns: 1fr; }
        .customer-wallet-form-grid { grid-template-columns: repeat(2, 1fr); }
        .customer-map-large { height: 420px; }
    }

    @media (max-width: 768px) {
        .customer-stats-grid { grid-template-columns: 1fr; }
        .customer-wallet-stats { grid-template-columns: 1fr; }
        .customer-wallet-stat { border-inline-end: none; border-bottom: 1px solid var(--nova-border, #e2e8f0); }
        .customer-wallet-stat:last-child { border-bottom: none; }
        .customer-wallet-form-grid { grid-template-columns: 1fr; }
        .branch-info-table th { width: 45%; }

        .customer-map-large { height: 360px; }
        .customer-map-legend-hint { display: none; }

        .branches-table thead th:nth-child(3),
        .branches-table thead th:nth-child(4),
        .branches-table thead th:nth-child(5),
        .branches-table tbody td:nth-child(3),
        .branches-table tbody td:nth-child(4),
        .branches-table tbody td:nth-child(5) {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
@if($branchesWithCoordsCount > 0)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchesData = @json($branchesMapData);
    const mapEl = document.getElementById('customerBranchesMap');
    if (!mapEl || !branchesData.length) return;

    // Center fallback
    const map = L.map(mapEl, {
        zoomControl: true,
        scrollWheelZoom: true,
    }).setView([24.7136, 46.6753], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = [];
    const markerById = {};

    branchesData.forEach(branch => {
        const color = branch.is_active ? '#16a34a' : '#94a3b8';

        const icon = L.divIcon({
            className: 'branch-map-pin-icon',
            html: `
                <div class="branch-map-pin">
                    <div class="branch-map-pin-head" style="background:${color};">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="branch-map-pin-tail" style="border-top:10px solid ${color};"></div>
                </div>
            `,
            iconSize: [34, 42],
            iconAnchor: [17, 42],
            popupAnchor: [0, -42]
        });

        const statusBg = branch.is_active ? '#dcfce7' : '#f1f5f9';
        const statusColor = branch.is_active ? '#166534' : '#64748b';
        const statusText = branch.is_active ? 'نشط' : 'غير نشط';

        const popupHtml = `
            <div class="branch-popup-content">
                <div class="branch-popup-title">
                    <i class="fas fa-store" style="color:${color};"></i>
                    ${escapeHtml(branch.name)}
                </div>
                ${branch.phone ? `
                    <div class="branch-popup-line">
                        <i class="fas fa-phone"></i>
                        <span dir="ltr">${escapeHtml(branch.phone)}</span>
                    </div>
                ` : ''}
                ${branch.address ? `
                    <div class="branch-popup-line">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${escapeHtml(branch.address)}</span>
                    </div>
                ` : ''}
                <div class="branch-popup-line">
                    <i class="fas fa-crosshairs"></i>
                    <span dir="ltr">${branch.lat.toFixed(6)}, ${branch.lng.toFixed(6)}</span>
                </div>
                <span class="branch-popup-status" style="background:${statusBg}; color:${statusColor};">
                    ${statusText}
                </span>
                <div class="branch-popup-actions">
                    <a href="${branch.url}" class="branch-popup-btn branch-popup-btn-primary">
                        <i class="fas fa-eye"></i> التفاصيل
                    </a>
                    <a href="${branch.edit_url}" class="branch-popup-btn branch-popup-btn-outline">
                        <i class="fas fa-edit"></i> تعديل
                    </a>
                </div>
            </div>
        `;

        const marker = L.marker([branch.lat, branch.lng], { icon })
            .addTo(map)
            .bindPopup(popupHtml);

        markers.push(marker);
        markerById[branch.id] = marker;
    });

    // Fit bounds to show all markers
    function fitAll() {
        if (markers.length === 1) {
            map.setView([branchesData[0].lat, branchesData[0].lng], 15);
        } else {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }
    }
    fitAll();

    // Reset view button
    document.getElementById('mapResetViewBtn')?.addEventListener('click', fitAll);

    // Fix size
    setTimeout(() => map.invalidateSize(), 250);

    // Simple HTML escape
    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});

// Wallet modal re-open on validation errors
document.addEventListener('DOMContentLoaded', function () {
    @if ($errors->any())
        document.getElementById('createWalletModal')?.classList.add('show');
    @endif
});
</script>
@endif
@endpush