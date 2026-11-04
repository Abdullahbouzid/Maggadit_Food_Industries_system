<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>فاتورة مبيعات - {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 4mm 0mm 22mm 0mm; size: a4 portrait; }

    * { box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', 'XBNazanin', Arial, sans-serif;
        color: #2d2038;
        font-size: 10px;
        direction: rtl;
        unicode-bidi: embed;
        margin: 0;
        padding: 0;
        line-height: 1.65;
        orphans: 3;
        widows: 3;
    }

    table { border-collapse: collapse; width: 100%; }
    td, th {
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .page { padding: 0 12mm; }

    /* ================= شريط علوي ================= */
    .top-ornament {
        height: 8px;
        width: 100%;
        background: #6510b9;
        position: relative;
    }
    .top-ornament .accent {
        position: absolute;
        right: 0; top: 0; bottom: 0;
        width: 25%;
        background: #8b3fd1;
    }

    /* ================= الترويسة ================= */
    .header { padding: 8mm 0 4mm; }
    .header-table { table-layout: fixed; }
    .header-table td { vertical-align: middle; }
    .header-right  { width: 40%; text-align: right; padding-right: 2mm; }
    .header-center { width: 20%; text-align: center; }
    .header-left   { width: 40%; text-align: left;  padding-left: 2mm; }
    .logo {
        width: 68px;
        height: 68px;
        display: block;
        margin: 0 auto;
    }
    .brand-en {
        font-size: 12px;
        font-weight: bold;
        color: #6510b9;
        letter-spacing: 0.6px;
        direction: ltr;
        unicode-bidi: embed;
        display: block;
        margin-top: 4px;
        line-height: 1.4;
    }
    .brand-ar {
        font-size: 10px;
        color: #6b4a7d;
        direction: rtl;
        display: block;
        margin-top: 3px;
        line-height: 1.5;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 14px;
        font-size: 9px;
        font-weight: bold;
        text-align: center;
        max-width: 100%;
    }
    .status-badge.approved  { background: #e8f5ec; color: #1e7e34; border: 1px solid #b8ddc4; }
    .status-badge.cancelled { background: #fbeaea; color: #b02a2a; border: 1px solid #f0c4c4; }
    .status-badge.draft     { background: #f5f0fa; color: #6510b9; border: 1px solid #d9c5ee; }
    .status-badge.default   { background: #f3eafd; color: #6510b9; border: 1px solid #d9c5ee; }

    .doc-meta-mini {
        margin-top: 6px;
        font-size: 9px;
        color: #6b4a7d;
        text-align: left;
        direction: ltr;
    }
    .doc-meta-mini .code-box {
        color: #6510b9;
        font-weight: bold;
        direction: ltr;
        display: inline-block;
    }

    /* ================= عنوان المستند ================= */
    .title-frame {
        text-align: center;
        margin: 6px 0 16px;
        position: relative;
        direction: rtl;
    }
    .title-frame::before,
    .title-frame::after {
        content: '◆';
        color: #6510b9;
        font-size: 9px;
        position: absolute;
        top: 50%;
        margin-top: -8px;
    }
    .title-frame::before { right: 18%; }
    .title-frame::after  { left: 18%; }
    .title-line {
        display: inline-block;
        width: 15%;
        height: 1px;
        background: #d9c5ee;
        vertical-align: middle;
        margin: 0 10px;
    }
    .doc-title {
        display: inline-block;
        font-size: 22px;
        font-weight: bold;
        color: #6510b9;
        letter-spacing: 1px;
        vertical-align: middle;
        padding: 0 12px;
        direction: rtl;
    }

    /* ================= عناوين الأقسام ================= */
    .section-heading {
        margin: 16px 0 10px;
        table-layout: auto;
    }
    .section-heading td { vertical-align: middle; padding: 0; }
    .section-num {
        width: 26px;
        height: 26px;
        background: #6510b9;
        color: #ffffff;
        border-radius: 50%;
        text-align: center;
        font-size: 10px;
        font-weight: bold;
        line-height: 26px;
        display: inline-block;
        direction: ltr;
    }
    .section-title {
        font-size: 11px;
        font-weight: bold;
        color: #6510b9;
        padding-right: 8px;
        direction: rtl;
    }

    /* ================= صناديق البيانات ================= */
    .meta-box {
        background: #f9f5ff;
        border: 1px solid #e5d4f7;
        border-top: 3px solid #6510b9;
        border-radius: 0 0 4px 4px;
        padding: 10px 14px;
        height: 100%;
    }

    .meta-table { table-layout: fixed; }
    .meta-table td {
        padding: 5px 4px;
        font-size: 9.5px;
        line-height: 1.55;
        border-bottom: 1px dashed #e5d4f7;
        vertical-align: top;
    }
    .meta-table tr:last-child td { border-bottom: none; }
    .meta-table .lbl {
        color: #6510b9;
        font-weight: bold;
        width: 42%;
        text-align: right;
        direction: rtl;
    }
    .meta-table .val {
        color: #2d2038;
        width: 58%;
        text-align: right;
        direction: rtl;
    }
    .meta-table .val.ltr {
        direction: ltr;
        text-align: right;
    }

    /* ================= شريط التواريخ ================= */
    .timeline-card {
        background: #ffffff;
        border: 1px solid #e5d4f7;
        border-radius: 4px;
        margin-top: 10px;
    }
    .timeline-card table { table-layout: fixed; }
    .timeline-card td {
        width: 25%;
        text-align: center;
        padding: 10px 6px;
        border-left: 1px solid #e5d4f7;
        vertical-align: top;
    }
    .timeline-card td:last-child { border-left: none; }
    .timeline-icon {
        width: 22px;
        height: 22px;
        background: #f3eafd;
        color: #6510b9;
        border-radius: 50%;
        display: inline-block;
        line-height: 22px;
        font-size: 10px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 5px;
        direction: ltr;
    }
    .timeline-lbl {
        font-size: 8px;
        color: #6510b9;
        font-weight: bold;
        display: block;
        margin-bottom: 3px;
        direction: rtl;
        line-height: 1.5;
    }
    .timeline-val {
        font-size: 9.5px;
        color: #2d2038;
        font-weight: bold;
        display: block;
        direction: ltr;
        line-height: 1.4;
    }
    .timeline-val.empty { color: #b8a4c9; font-weight: normal; }

    /* ================= صندوق الملاحظات ================= */
    .notes-box {
        margin-top: 10px;
        background: #faf5ff;
        border: 1px solid #e5d4f7;
        border-right: 3px solid #6510b9;
        border-radius: 3px;
        padding: 8px 12px;
        font-size: 9.5px;
        color: #2d2038;
        line-height: 1.7;
        direction: rtl;
    }
    .notes-box .notes-lbl {
        color: #6510b9;
        font-weight: bold;
        margin-left: 4px;
    }

    /* ================= جدول الأصناف ================= */
    .items { margin-top: 4px; table-layout: fixed; }
    .items thead { display: table-header-group; }
    .items th {
        background: #6510b9;
        color: #ffffff;
        font-size: 9px;
        font-weight: bold;
        padding: 10px 5px;
        text-align: center;
        direction: rtl;
        line-height: 1.4;
    }
    .items th:first-child { border-top-right-radius: 5px; }
    .items th:last-child  { border-top-left-radius: 5px; }

    .items td {
        padding: 9px 5px;
        font-size: 9.5px;
        text-align: center;
        border-bottom: 1px solid #ede2f8;
        color: #2d2038;
        line-height: 1.5;
        direction: rtl;
    }
    .items tbody tr:nth-child(even):not(.empty) td { background: #faf5ff; }
    .items tbody tr:last-child td { border-bottom: none; }
    .items tr { page-break-inside: avoid; }

    .items .name {
        text-align: right;
        font-weight: bold;
        direction: rtl;
    }
    .items .code {
        direction: ltr;
        font-size: 9px;
        color: #4a3555;
        font-family: 'DejaVu Sans', Arial, sans-serif;
        text-align: center;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .items .num,
    .items .qty {
        direction: ltr;
        display: inline-block;
        text-align: center;
    }
    .items .num { font-weight: bold; color: #6510b9; }
    .items .sub {
        font-size: 8px;
        color: #6b4a7d;
        font-weight: normal;
        margin-top: 2px;
        direction: rtl;
        line-height: 1.4;
    }
    .items .disc {
        color: #6510b9;
        font-weight: bold;
        direction: ltr;
        display: inline-block;
    }
    .items .disc.zero {
        color: #c4b3d4;
        font-weight: normal;
    }
    .items .empty td { height: 24px; background: transparent !important; }

    /* ================= الملخص + التفقيق ================= */
    .summary-wrap { margin-top: 14px; page-break-inside: avoid; }
    .summary-wrap > tbody > tr > td { vertical-align: top; padding: 0; }

    .words-box {
        background: #f9f5ff;
        border: 1px solid #e5d4f7;
        border-right: 3px solid #6510b9;
        border-radius: 3px;
        padding: 12px 14px;
        height: 100%;
        direction: rtl;
    }
    .words-label {
        font-size: 8.5px;
        font-weight: bold;
        color: #6510b9;
        margin-bottom: 6px;
        direction: rtl;
        text-align: right;
    }
    .words-text {
        font-size: 11px;
        font-weight: bold;
        color: #2d2038;
        line-height: 1.75;
        direction: rtl;
        text-align: right;
    }
    .words-text em {
        font-style: normal;
        color: #6510b9;
        font-size: 10px;
    }

    /* بطاقة الإجماليات */
    .totals-card {
        background: #ffffff;
        border: 1px solid #e5d4f7;
        border-radius: 4px;
        overflow: hidden;
    }
    .totals-card table { margin: 0; table-layout: fixed; }
    .totals-card td {
        padding: 9px 12px;
        font-size: 10px;
    }
    .totals-card .lbl {
        text-align: right;
        color: #4a3555;
        direction: rtl;
        line-height: 1.5;
    }
    .totals-card .val {
        text-align: left;
        font-weight: bold;
        color: #6510b9;
        font-size: 11px;
        direction: ltr;
        line-height: 1.5;
        width: 42%;
    }
    .totals-card .currency {
        font-size: 9px;
        color: #6b4a7d;
        font-weight: normal;
    }
    .totals-card tr.row-light { background: #faf5ff; }

    .totals-card tr.grand { background: #6510b9; }
    .totals-card tr.grand td { padding: 12px 14px; color: #ffffff; }
    .totals-card tr.grand .lbl {
        color: #ffffff;
        font-weight: bold;
        font-size: 11px;
    }
    .totals-card tr.grand .val {
        font-size: 14px;
        color: #ffffff;
        font-weight: bold;
        direction: ltr;
        text-align: left;
    }
    .totals-card tr.grand .currency {
        color: #e5d4f7;
        font-size: 10px;
    }

    /* شريط الخصم */
    .discount-bar {
        height: 4px;
        background: #ede2f8;
        margin: 4px 12px 6px;
        position: relative;
    }
    .discount-bar-fill {
        position: absolute;
        right: 0; top: 0; bottom: 0;
        background: #8b3fd1;
    }

    /* ================= الدفعات ================= */
    .payments { margin-top: 14px; page-break-inside: avoid; }
    .payments th {
        background: #e8d9f9;
        color: #6510b9;
        font-size: 9px;
        font-weight: bold;
        padding: 7px 4px;
        text-align: center;
        border-top: 1.5px solid #6510b9;
        border-bottom: 1.5px solid #6510b9;
    }
    .payments td {
        padding: 6px 4px;
        font-size: 9px;
        text-align: center;
        border-bottom: 1px solid #e8d8f0;
    }
    .payments tbody tr:nth-child(even) td { background: #faf5ff; }
    .payments tr { page-break-inside: avoid; }

    /* ================= الشروط والأحكام ================= */
    .terms {
        margin-top: 18px;
        page-break-inside: avoid;
        background: #f9f5ff;
        border: 1px solid #e5d4f7;
        border-radius: 4px;
        padding: 12px 16px;
        direction: rtl;
        width: 100%;
    }
    .terms-title {
        font-size: 11px;
        font-weight: bold;
        color: #6510b9;
        padding-bottom: 6px;
        margin-bottom: 8px;
        border-bottom: 1px solid #e5d4f7;
        direction: rtl;
        text-align: right;
    }
    .terms-text {
        font-size: 9.5px;
        color: #2d2038;
        line-height: 1.85;
        text-align: right;
    }

    /* ================= التوقيعات + QR ================= */
    .closing-wrap {
        margin-top: 22px;
        page-break-inside: avoid;
        table-layout: fixed;
    }
    .closing-wrap > tbody > tr > td { vertical-align: top; padding: 0; }
    .qr-box {
        background: #faf5ff;
        border: 1px dashed #b8a4c9;
        border-radius: 4px;
        padding: 10px;
        text-align: center;
        height: 100%;
    }
    .qr-box-inner {
        width: 60px;
        height: 60px;
        margin: 0 auto 6px;
        background:
            repeating-linear-gradient(0deg, #6510b9 0px, #6510b9 3px, #ffffff 3px, #ffffff 6px),
            repeating-linear-gradient(90deg, #6510b9 0px, #6510b9 3px, #ffffff 3px, #ffffff 6px);
        background-size: 6px 6px;
        border: 1px solid #6510b9;
    }
    .qr-label {
        font-size: 8px;
        color: #6b4a7d;
        line-height: 1.4;
        direction: rtl;
        text-align: center;
    }
    .sign-table { table-layout: fixed; }
    .sign-table td { vertical-align: bottom; padding: 0; }
    .sign-cell {
        text-align: center;
        padding: 0 10mm;
        direction: rtl;
    }
    .sign-line {
        border-bottom: 2px dotted #6510b9;
        height: 1px;
        margin-bottom: 8px;
    }
    .sign-title {
        font-size: 10px;
        font-weight: bold;
        color: #6510b9;
        margin-bottom: 4px;
        line-height: 1.5;
        direction: rtl;
    }
    .sign-date {
        font-size: 8.5px;
        color: #8b7a9c;
        margin-top: 3px;
        line-height: 1.5;
        direction: rtl;
    }

    .receipt-declaration {
        margin-top: 14px;
        padding: 9px 14px;
        background: #f9f5ff;
        border: 1px dashed #b8a4c9;
        border-radius: 3px;
        font-size: 9px;
        color: #4a3555;
        line-height: 1.8;
        text-align: right;
        direction: rtl;
    }
    .receipt-declaration strong { color: #6510b9; }

    /* ================= شريط سفلي ================= */
    .bottom-ornament {
        margin-top: 18px;
        height: 6px;
        width: 100%;
        background: #6510b9;
        position: relative;
    }
    .bottom-ornament .accent {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 25%;
        background: #8b3fd1;
    }

    /* ================= الفوتر (يتكرر في كل صفحة) ================= */
    .pdf-footer {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: 18mm;
        background: #6510b9;
        border-top: 3px solid #8b3fd1;
        color: #ffffff;
        font-size: 8px;
        padding: 3mm 12mm 2mm;
        text-align: center;
        line-height: 1.5;
        direction: rtl;
    }
    .pdf-footer-company {
        font-weight: bold;
        font-size: 9px;
        color: #e5d4f7;
        margin-bottom: 3px;
        direction: rtl;
    }
    .pdf-footer-contact {
        direction: rtl;
        text-align: center;
    }
    .pdf-footer-contact span {
        display: inline-block;
        margin: 0 6px;
        color: #e5d4f7;
    }
    .pdf-footer-contact span + span::before {
        content: '•';
        color: #ffffff;
        margin-left: 6px;
    }
    .pdf-footer-license {
        font-size: 7px;
        color: #e5d4f7;
        margin-top: 3px;
        direction: rtl;
    }
</style>
</head>
<body>

@php
    /* ============ تجهيز البيانات ============ */
    $invoiceNo = $invoice->invoice_number ?? '—';

    $invoiceDate = $invoice->invoice_date
        ? \Carbon\Carbon::parse($invoice->invoice_date)
        : ($invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at) : now());

    $customer     = $invoice->customer;
    $customerName = $customer ? ($customer->name ?: $customer->username) : 'عميل نقدي';
    $customerType = $customer->type ?? 'Credit Customer';
    $phone        = $customer->phone ?? '—';
    $address      = $customer->default_address ?? '—';

    /* ============ معالجة الحالة بأمان ============ */
    $statusRaw = $invoice->status;

    $statusRawValue = $statusRaw instanceof \App\Enums\OrderStatus
        ? $statusRaw->value
        : (string) ($statusRaw ?? '');

    $statusLabel = $statusRaw instanceof \App\Enums\OrderStatus
        ? $statusRaw->label()
        : ($statusRawValue !== ''
            ? ucfirst(str_replace('_', ' ', $statusRawValue))
            : 'قيد المعالجة');

    $statusKey   = strtolower(str_replace([' ', '_'], '', $statusRawValue));
    $statusClass = 'default';

    if (
        str_contains($statusKey, 'approv')  ||
        str_contains($statusKey, 'complet') ||
        str_contains($statusKey, 'paid')    ||
        str_contains($statusKey, 'finaliz')
    ) {
        $statusClass = 'approved';
    } elseif (
        str_contains($statusKey, 'cancel') ||
        str_contains($statusKey, 'reject')
    ) {
        $statusClass = 'cancelled';
    } elseif (
        str_contains($statusKey, 'draft') ||
        str_contains($statusKey, 'pending')
    ) {
        $statusClass = 'draft';
    }

    $totalAmount = (float) ($invoice->total_amount ?? 0);
    $paidAmount  = (float) ($invoice->paid_amount ?? 0);
    $dueAmount   = $totalAmount - $paidAmount;

    $approvedAt = $invoice->approved_at ? \Carbon\Carbon::parse($invoice->approved_at) : null;
    $paidAt     = $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at) : null;
    $dueDate    = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date) : null;
    $deadline   = $invoice->payment_deadline_at ? \Carbon\Carbon::parse($invoice->payment_deadline_at) : null;

    $orderNo     = $invoice->order?->order_number ?? '—';
    $quotationNo = $invoice->quotation?->quotation_number ?? '—';

    $subtotalCalculated = 0;
    $minRows = 5;
@endphp

<div class="top-ornament"><div class="accent"></div></div>

<div class="page">

    <!-- ================= الترويسة ================= -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-right">
                    <span class="brand-en">MAGADIT FOOD INDUSTRIES</span>
                    <span class="brand-ar">صناعات مجد للتغذية والمواد الغذائية</span>
                </td>
                <td class="header-center">
                    <img src="file://{{ public_path('icon.png') }}" alt="الشعار" class="logo">
                </td>
                <td class="header-left">
                    <div class="status-badge {{ $statusClass }}">{{ $statusLabel }}</div>
                    <div class="doc-meta-mini">
                        <span class="code-box">{{ $invoiceNo }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ================= عنوان المستند ================= -->
    <div class="title-frame">
        <span class="title-line"></span>
        <span class="doc-title">فاتورة مبيعات</span>
        <span class="title-line"></span>
    </div>

    <!-- ================= 01 + 02 ================= -->
    <table class="meta-grid" style="table-layout: fixed;">
        <tr>
            <td style="width: 50%; padding-left: 3mm; vertical-align: top;">
                <table class="section-heading">
                    <tr>
                        <td style="width: 32px;"><span class="section-num">01</span></td>
                        <td><span class="section-title">بيانات العميل</span></td>
                    </tr>
                </table>
                <div class="meta-box">
                    <table class="meta-table">
                        <tr>
                            <td class="lbl">اسم العميل</td>
                            <td class="val">{{ $customerName }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">رمز العميل</td>
                            <td class="val ltr">{{ $customer->id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">نوع العميل</td>
                            <td class="val">{{ $customerType }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">رقم الهاتف</td>
                            <td class="val ltr">{{ $phone }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">العنوان</td>
                            <td class="val">{{ $address }}</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td style="width: 50%; padding-right: 3mm; vertical-align: top;">
                <table class="section-heading">
                    <tr>
                        <td style="width: 32px;"><span class="section-num">02</span></td>
                        <td><span class="section-title">بيانات الفاتورة</span></td>
                    </tr>
                </table>
                <div class="meta-box">
                    <table class="meta-table">
                        <tr>
                            <td class="lbl">رقم الفاتورة</td>
                            <td class="val ltr">{{ $invoiceNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">تاريخ الفاتورة</td>
                            <td class="val ltr">{{ $invoiceDate->format('H:i d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">رقم الطلبية</td>
                            <td class="val ltr">{{ $orderNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">رقم عرض السعر</td>
                            <td class="val ltr">{{ $quotationNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">نوع الدفع</td>
                            <td class="val">{{ $invoice->payment_type ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">طريقة الدفع</td>
                            <td class="val">{{ $invoice->payment_method ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">رقم العملية</td>
                            <td class="val ltr">{{ $invoice->payment_reference ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

        <!-- شريط التواريخ -->
        <tr>
            <td colspan="2">
                <div class="timeline-card">
                    <table>
                        <tr>
                            <td>
                                <div class="timeline-icon">⏱</div>
                                <span class="timeline-lbl">تاريخ الاستحقاق</span>
                                <span class="timeline-val {{ $dueDate ? '' : 'empty' }}">
                                    {{ $dueDate ? $dueDate->format('d.m.Y') : '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="timeline-icon">✓</div>
                                <span class="timeline-lbl">تاريخ الاعتماد</span>
                                <span class="timeline-val {{ $approvedAt ? '' : 'empty' }}">
                                    {{ $approvedAt ? $approvedAt->format('d.m.Y') : '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="timeline-icon">⏳</div>
                                <span class="timeline-lbl">موعد السداد</span>
                                <span class="timeline-val {{ $deadline ? '' : 'empty' }}">
                                    {{ $deadline ? $deadline->format('d.m.Y H:i') : '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="timeline-icon">💰</div>
                                <span class="timeline-lbl">تاريخ السداد</span>
                                <span class="timeline-val {{ $paidAt ? '' : 'empty' }}">
                                    {{ $paidAt ? $paidAt->format('d.m.Y H:i') : '—' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- ================= 03 الأصناف ================= -->
    <table class="section-heading" style="margin-top: 18px;">
        <tr>
            <td style="width: 32px;"><span class="section-num">03</span></td>
            <td><span class="section-title">الأصناف</span></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 13%;">رمز المادة</th>
                <th style="width: 20%;">الباركود</th>
                <th style="width: 22%; text-align: right;">المادة</th>
                <th style="width: 11%;">الوحدة</th>
                <th style="width: 7%;">الكمية</th>
                <th style="width: 8%;">السعر</th>
                <th style="width: 9%;">المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                @php
                    $lineTotal = $item->subtotal ?: ($item->unit_price * $item->quantity);
                    $subtotalCalculated += $lineTotal;

                    $product  = $item->item;
                    $itemName = $product
                        ? ($product->name_ar ?: $product->name_en)
                        : 'منتج';
                    $itemCode = $product ? ($product->code ?? '—') : '—';
                    $barcode  = ($product?->barcode) ?? ($item->itemPack?->barcode ?? '—');

                    $packLabel = 'وحدة';
                    if ($item->itemPack) {
                        $uom = $item->itemPack->packCapacity?->uom
                            ? ($item->itemPack->packCapacity->uom->name_ar ?? $item->itemPack->packCapacity->uom->name_en)
                            : null;
                        $packLabel = $item->itemPack->packType?->name
                            ?? ($uom ?: ('عبوة ' . (float) $item->itemPack->quantity_in_pack));
                    }
                @endphp
                <tr>
                    <td class="code">{{ $itemCode }}</td>
                    <td class="code">{{ $barcode }}</td>
                    <td class="name">
                        {{ $itemName }}
                        @if($item->itemPack && $item->itemPack->quantity_in_pack)
                            <div class="sub">حجم العبوة: {{ (float) $item->itemPack->quantity_in_pack }}</div>
                        @endif
                    </td>
                    <td>{{ $packLabel }}</td>
                    <td><span class="qty">{{ (float) $item->quantity }}</span></td>
                    <td><span class="num">{{ number_format($item->unit_price, 2, '.', '') }}</span></td>
                    <td><span class="num">{{ number_format($lineTotal, 2, '.', '') }}</span></td>
                </tr>
            @endforeach

            @for($i = $invoice->items->count(); $i < $minRows; $i++)
                <tr class="empty">
                    <td>&nbsp;</td><td></td><td></td><td></td>
                    <td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- ================= 04 الملخص المالي ================= -->
    <table class="section-heading" style="margin-top: 18px;">
        <tr>
            <td style="width: 32px;"><span class="section-num">04</span></td>
            <td><span class="section-title">الملخص المالي</span></td>
        </tr>
    </table>

    <table class="summary-wrap" style="table-layout: fixed;">
        <tr>
            <td style="width: 65%; vertical-align: top;">
                @if($invoice->notes || $invoice->delivery_notes)
                    <div class="notes-box">
                        <span class="notes-lbl">ملاحظات:</span>
                        {{ $invoice->notes ?: ($invoice->delivery_notes ?: '') }}
                    </div>
                @endif
            </td>

            <td style="width: 35%; vertical-align: top;">
                <div class="totals-card">
                    <table>
                        <tr class="row-light">
                            <td class="lbl">صافي المجموع</td>
                            <td class="val">
                                {{ number_format($subtotalCalculated, 2, '.', '') }}
                                <span class="currency">SAR</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="lbl">إجمالي الفاتورة</td>
                            <td class="val">
                                {{ number_format($totalAmount, 2, '.', '') }}
                                <span class="currency">SAR</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="lbl">المبلغ المدفوع</td>
                            <td class="val">
                                {{ number_format($paidAmount, 2, '.', '') }}
                                <span class="currency">SAR</span>
                            </td>
                        </tr>
                        <tr class="grand">
                            <td class="lbl">المبلغ المتبقي</td>
                            <td class="val">
                                {{ number_format($dueAmount, 2, '.', '') }}
                                <span class="currency">SAR</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- ================= الدفعات ================= -->
    @if($invoice->payments && $invoice->payments->count())
        <table class="section-heading" style="margin-top: 18px;">
            <tr>
                <td style="width: 32px;"><span class="section-num">05</span></td>
                <td><span class="section-title">سجل المدفوعات</span></td>
            </tr>
        </table>

        <table class="payments">
            <thead>
                <tr>
                    <th style="width: 22%;">رقم السند</th>
                    <th style="width: 20%;">التاريخ</th>
                    <th style="width: 20%;">الطريقة</th>
                    <th style="width: 20%;">المرجع</th>
                    <th style="width: 18%;">المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_number ?? ('#' . $payment->id) }}</td>
                        <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d.m.Y H:i') : '—' }}</td>
                        <td>{{ $payment->method ?? '—' }}</td>
                        <td class="code">{{ $payment->reference ?? '—' }}</td>
                        <td class="num">{{ number_format((float) $payment->amount, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ================= التوقيعات + QR ================= -->
    <table class="closing-wrap mt-5">
        <tr>
            <td style="width: 78%; padding-left: 4mm; vertical-align: top;">
                <table class="sign-table">
                    <tr>
                        <td class="sign-cell">
                            <div class="sign-line"></div>
                            <div class="sign-title">توقيع وختم الموظع المُسلِّم</div>
                            <div class="sign-date">الاسم: __________________</div>
                            <div class="sign-date">التاريخ: ____ / ____ / ________</div>
                        </td>
                        <td class="sign-cell">
                            <div class="sign-line"></div>
                            <div class="sign-title">توقيع وختم العميل / المُستلِم</div>
                            <div class="sign-date">الاسم: __________________</div>
                            <div class="sign-date">التاريخ: ____ / ____ / ________</div>
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 22%; vertical-align: top;">
                <div class="qr-box">
                    <div class="qr-box-inner"></div>
                    <div class="qr-label">
                        امسح الرمز<br>للتحقق من المستند
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="receipt-declaration">
        <strong>إقرار الاستلام:</strong>
        أقر أنا الموقع أدناه باستلام الأصناف المذكورة في هذه الفاتورة بحالة جيدة ومطابقة للمواصفات والكميات المتفق عليها، ولا يحق لي المطالبة بأي اعتراض بعد التوقيع.
    </div>

    <div class="bottom-ornament"><div class="accent"></div></div>

</div>

@includeIf('pdf.partials.footer')

</body>
</html>
