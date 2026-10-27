<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>فاتورة مبيعات - {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 0mm 0mm 14mm 0mm; size: a4 portrait; }

    body {
        font-family: 'DejaVu Sans', 'XBNazanin', Arial, sans-serif;
        color: #1c2b24;
        font-size: 10px;
        direction: rtl;
        margin: 0;
        padding: 0;
        line-height: 1.7;
    }

    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: middle; }
    .page { padding: 0 12mm; }

    /* ================= الترويسة ================= */
    .header { text-align: center; padding: 8mm 0 0; }
    .logo { width: 84px; height: 84px; }
    .brand-en {
        font-size: 13px; font-weight: bold; color: #1f4d3a;
        letter-spacing: 1.5px; margin-top: 2px;
    }
    .brand-ar { font-size: 9px; color: #6b7d73; margin-top: 1px; }

    .doc-title {
        text-align: center; font-size: 21px; font-weight: bold;
        color: #1f4d3a; margin: 10px 0 0;
    }
    .doc-rule {
        height: 2px; width: 110px; background-color: #c8963e;
        margin: 5px auto 16px;
    }

    /* ================= بيانات الفاتورة ================= */
    .meta { margin-bottom: 12px; }
    .meta td { padding: 4px 6px; font-size: 10px; line-height: 1.5; }
    .meta .lbl   { width: 19%; color: #1f4d3a; font-weight: bold; white-space: nowrap; text-align: right; }
    .meta .colon { width: 2%;  color: #9db3a6; text-align: center; }
    .meta .val   { width: 29%; color: #1c2b24; text-align: right; }

    /* ================= جدول الأصناف ================= */
    .items { margin-top: 6px; }
    .items thead { display: table-header-group; }
    .items th {
        background-color: #eef4f0;
        color: #1f4d3a;
        font-size: 9px;
        font-weight: bold;
        padding: 7px 4px;
        text-align: center;
        border-top: 1.5px solid #1f4d3a;
        border-bottom: 1.5px solid #1f4d3a;
        white-space: nowrap;
    }
    .items td {
        padding: 7px 4px;
        font-size: 9.5px;
        text-align: center;
        border-bottom: 1px solid #dde5e0;
    }
    .items tr { page-break-inside: avoid; }
    .items .name  { text-align: right; font-weight: bold; }
    .items .code  { direction: ltr; }
    .items .num   { font-weight: bold; color: #1f4d3a; }
    .items .sub   { font-size: 8px; color: #6b7d73; font-weight: normal; }
    .items .empty td { height: 20px; }

    /* ================= الإجماليات ================= */
    .totals-wrap { margin-top: 10px; page-break-inside: avoid; }
    .totals td { padding: 4px 6px; font-size: 10px; }
    .totals .lbl   { text-align: right; color: #1c2b24; }
    .totals .colon { width: 12px; text-align: center; color: #9db3a6; }
    .totals .val   { width: 92px; text-align: left; font-weight: bold; color: #1f4d3a; }
    .totals .grand td { border-top: 1.5px solid #c8963e; padding-top: 7px; }
    .totals .grand .lbl { font-weight: bold; color: #1f4d3a; }
    .totals .grand .val { font-size: 12px; }

    /* ================= الدفعات ================= */
    .payments { margin-top: 10px; page-break-inside: avoid; }
    .payments th {
        background-color: #eef4f0;
        color: #1f4d3a;
        font-size: 9px;
        font-weight: bold;
        padding: 6px 4px;
        text-align: center;
        border-top: 1.5px solid #1f4d3a;
        border-bottom: 1.5px solid #1f4d3a;
    }
    .payments td {
        padding: 5px 4px;
        font-size: 9px;
        text-align: center;
        border-bottom: 1px solid #dde5e0;
    }
    .payments tr { page-break-inside: avoid; }

    /* ================= الشروط ================= */
    .terms { margin-top: 24px; page-break-inside: avoid; }
    .terms-title {
        display: inline-block;
        font-size: 10.5px; font-weight: bold; color: #1f4d3a;
        padding-bottom: 3px; border-bottom: 2px solid #c8963e;
        margin-bottom: 6px;
    }
    .terms-text {
        font-size: 9.5px; color: #3b4d44; line-height: 1.9;
        text-align: right;
    }

    /* ================= التوقيع ================= */
    .sign { margin-top: 34px; page-break-inside: avoid; }
    .sign td { width: 50%; font-size: 10px; font-weight: bold; color: #1f4d3a; }
    .sign .left { text-align: left; padding-left: 14mm; }

    .end-band {
        margin-top: 20px; height: 8px;
        background-color: #1f4d3a; border-top: 3px solid #c8963e;
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

    $employee = $invoice->approvedBy;
    $employeeName = $employee?->name ?: '—';
    $employeeCode = $employee?->id ?? ($invoice->approved_by ?? '—');

    $priceListLabel = $invoice->priceList
        ? $invoice->priceList->name
        : ($customer && $customer->priceList ? $customer->priceList->name : 'قائمة الأسعار المعتمدة');

    $currencyName = $invoice->currency ? ($invoice->currency->name_ar ?: $invoice->currency->code) : 'ر.س / SAR';
    $currencyCode = $invoice->currency->code ?? 'SAR';
    $exchangeRate = $invoice->exchange_rate ? (float) $invoice->exchange_rate : 1.0;

    $statusRaw   = $invoice->status;
    $statusLabel = $statusRaw
        ? ucfirst(str_replace('_', ' ', (string) $statusRaw))
        : 'قيد المعالجة';

    $paymentType   = $invoice->payment_type ?? '—';
    $paymentMethod = $invoice->payment_method ?? '—';
    $paymentRef    = $invoice->payment_reference ?? '—';

    $totalAmount = (float) ($invoice->total_amount ?? 0);
    $paidAmount  = (float) ($invoice->paid_amount ?? 0);
    $dueAmount   = $totalAmount - $paidAmount;

    $approvedAt = $invoice->approved_at ? \Carbon\Carbon::parse($invoice->approved_at) : null;
    $paidAt     = $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at) : null;
    $dueDate    = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date) : null;
    $deadline   = $invoice->payment_deadline_at ? \Carbon\Carbon::parse($invoice->payment_deadline_at) : null;

    $orderNo     = $invoice->order?->order_number ?? '—';
    $quotationNo = $invoice->quotation?->quotation_number ?? '—';
    $branchName  = $invoice->order && $invoice->order->branch ? $invoice->order->branch->name : '—';

    $subtotalCalculated = 0;
    $minRows = 5;
@endphp

<div class="page">

    <!-- ================= الترويسة ================= -->
    <div class="header">
        <img src="{{ asset('icon.png') }}" alt="الشعار" class="logo">
        <div class="brand-en">MAGADIT FOOD INDUSTRIES</div>
        <div class="brand-ar">صناعات مجد للتغذية والمواد الغذائية</div>
    </div>

    <div class="doc-title">فاتورة مبيعات</div>
    <div class="doc-rule"></div>

    <!-- ================= بيانات الفاتورة ================= -->
    <table class="meta">
        <tr>
            <td class="lbl">تاريخ الفاتورة</td>
            <td class="colon">:</td>
            <td class="val">{{ $invoiceDate->format('H:i:s d.m.Y') }}</td>

            <td class="lbl">رقم الفاتورة</td>
            <td class="colon">:</td>
            <td class="val">{{ $invoiceNo }}</td>
        </tr>
        <tr>
            <td class="lbl">اسم العميل</td>
            <td class="colon">:</td>
            <td class="val">{{ $customerName }}</td>

            <td class="lbl">رمز العميل</td>
            <td class="colon">:</td>
            <td class="val">{{ $customer->id ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">نوع العميل</td>
            <td class="colon">:</td>
            <td class="val">{{ $customerType }}</td>

            <td class="lbl">رقم الهاتف</td>
            <td class="colon">:</td>
            <td class="val">{{ $phone }}</td>
        </tr>
        <tr>
            <td class="lbl">اسم الموظف</td>
            <td class="colon">:</td>
            <td class="val">{{ $employeeName }}</td>

            <td class="lbl">رمز الموظف</td>
            <td class="colon">:</td>
            <td class="val">{{ $employeeCode }}</td>
        </tr>
        <tr>
            <td class="lbl">حالة الفاتورة</td>
            <td class="colon">:</td>
            <td class="val">{{ $statusLabel }}</td>

            <td class="lbl">قائمة الأسعار</td>
            <td class="colon">:</td>
            <td class="val">{{ $priceListLabel }}</td>
        </tr>
        <tr>
            <td class="lbl">العملة</td>
            <td class="colon">:</td>
            <td class="val">{{ $currencyName }} ({{ $currencyCode }})</td>

            <td class="lbl">سعر الصرف</td>
            <td class="colon">:</td>
            <td class="val">{{ number_format($exchangeRate, 6, '.', '') }}</td>
        </tr>
        <tr>
            <td class="lbl">نوع الدفع</td>
            <td class="colon">:</td>
            <td class="val">{{ $paymentType }}</td>

            <td class="lbl">طريقة الدفع</td>
            <td class="colon">:</td>
            <td class="val">{{ $paymentMethod }}</td>
        </tr>
        <tr>
            <td class="lbl">رقم العملية</td>
            <td class="colon">:</td>
            <td class="val">{{ $paymentRef }}</td>

            <td class="lbl">تاريخ الاستحقاق</td>
            <td class="colon">:</td>
            <td class="val">{{ $dueDate ? $dueDate->format('d.m.Y') : '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">تاريخ الاعتماد</td>
            <td class="colon">:</td>
            <td class="val">{{ $approvedAt ? $approvedAt->format('d.m.Y H:i') : '—' }}</td>

            <td class="lbl">موعد السداد</td>
            <td class="colon">:</td>
            <td class="val">{{ $deadline ? $deadline->format('d.m.Y H:i') : '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">تاريخ السداد</td>
            <td class="colon">:</td>
            <td class="val">{{ $paidAt ? $paidAt->format('d.m.Y H:i') : '—' }}</td>

            <td class="lbl">رقم الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $orderNo }}</td>
        </tr>
        <tr>
            <td class="lbl">الفرع</td>
            <td class="colon">:</td>
            <td class="val">{{ $branchName }}</td>

            <td class="lbl">رقم عرض السعر</td>
            <td class="colon">:</td>
            <td class="val">{{ $quotationNo }}</td>

            <td class="lbl">عدد الأصناف</td>
            <td class="colon">:</td>
            <td class="val">{{ $invoice->items->count() }}</td>
        </tr>
        <tr>
            <td class="lbl">عنوان العميل</td>
            <td class="colon">:</td>
            <td class="val" colspan="5">{{ $address }}</td>
        </tr>
    </table>

    <!-- ================= جدول الأصناف ================= -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 13%;">رمز المادة</th>
                <th style="width: 20%;">الباركود</th>
                <th style="width: 22%; text-align: right;">المادة</th>
                <th style="width: 11%;">الوحدة</th>
                <th style="width: 7%;">الكمية</th>
                <th style="width: 8%;">السعر</th>
                <th style="width: 10%;">قائمة الأسعار</th>
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

                    $rowPriceList = $item->priceList?->name ?? '—';
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
                    <td>{{ (float) $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2, '.', '') }}</td>
                    <td>{{ $rowPriceList }}</td>
                    <td class="num">{{ number_format($lineTotal, 2, '.', '') }}</td>
                </tr>
            @endforeach

            @for($i = $invoice->items->count(); $i < $minRows; $i++)
                <tr class="empty">
                    <td>&nbsp;</td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- ================= الإجماليات ================= -->
    <table class="totals-wrap">
        <tr>
            <td style="width: 52%;"></td>
            <td style="width: 48%;">
                <table class="totals">
                    <tr>
                        <td class="lbl">صافي المجموع قبل الضريبة والخصم</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($subtotalCalculated, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">إجمالي الفاتورة</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($totalAmount, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">المبلغ المدفوع</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($paidAmount, 2, '.', '') }}</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl">المبلغ المتبقي</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($dueAmount, 2, '.', '') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ================= الدفعات ================= -->
    @if($invoice->payments && $invoice->payments->count())
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

    <!-- ================= الشروط والملاحظات ================= -->
    <div class="terms">
        <div class="terms-title">الشروط والملاحظات</div>
        <div class="terms-text">
            هذه فاتورة مبيعات صادرة إلكترونياً من نظام ماجاديت، وتكون صالحة عند ورودها إلى مخازن
            الشركة مع ختم المستلم.
        </div>
        <div class="terms-text" style="margin-top:6px;">
            تخضع جميع الأصناف لفحص الجودة قبل التسليم، ويجب تقديم أي ملاحظة تتعلق بالجودة خلال
            24 ساعة من تاريخ الاستلام.
        </div>
        @if($invoice->notes)
            <div class="terms-text" style="margin-top:6px;">
                <b style="color:#1f4d3a;">ملاحظات:</b> {{ $invoice->notes }}
            </div>
        @endif
    </div>

    <!-- ================= التوقيع ================= -->
    <table class="sign">
        <tr>
            <td>توقيع و ختم الموظف</td>
            <td class="left">توقيع و ختم العميل</td>
        </tr>
    </table>

</div>

<div class="end-band"></div>

@includeIf('pdf.partials.footer')

</body>
</html>
