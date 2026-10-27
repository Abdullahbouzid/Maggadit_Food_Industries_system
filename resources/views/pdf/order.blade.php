<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>طلبية - {{ $order->order_number }}</title>
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

    /* ================= بيانات الطلبية ================= */
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
    $docNo = $order->order_number ?? '—';

    $orderDate = $order->created_at
        ? \Carbon\Carbon::parse($order->created_at)
        : now();

    $customer     = $order->customer;
    $customerName = $customer ? ($customer->name ?: $customer->username) : 'عميل نقدي';
    $customerType = $customer->type ?? 'Credit Customer';

    $phone    = $order->delivery_phone ?: ($customer->phone ?? '—');
    $address  = $order->delivery_address ?: ($customer->default_address ?? '—');

    $employee = $order->createdBy;
    $employeeName = $employee?->name ?: '—';
    $employeeCode = $employee?->id ?? ($order->created_by ?? '—');

    $warehouse = $order->warehouse;
    $warehouseName = $warehouse ? ($warehouse->name_ar ?: $warehouse->name_en) : 'المستودع الرئيسي';
    $warehouseCode = $warehouse?->code ?? ($order->warehouse_id ?? '—');

    $branch = $order->branch;
    $branchName = $branch ? ($branch->name ?? '—') : '—';

    $currencyName = $order->currency ? ($order->currency->name_ar ?: $order->currency->code) : 'ر.س / SAR';
    $currencyCode = $order->currency->code ?? 'SAR';
    $exchangeRate = $order->exchange_rate ? (float) $order->exchange_rate : 1.0;

    $statusRaw = $order->status;
    $statusLabel = $statusRaw instanceof \App\Enums\OrderStatus
        ? $statusRaw->label()
        : ucfirst(str_replace('_', ' ', (string) $statusRaw));

    $scheduledDelivery = $order->scheduled_delivery_date
        ? \Carbon\Carbon::parse($order->scheduled_delivery_date)
        : null;

    $confirmedAt = $order->confirmed_at ? \Carbon\Carbon::parse($order->confirmed_at) : null;
    $cancelledAt = $order->cancelled_at ? \Carbon\Carbon::parse($order->cancelled_at) : null;

    $latestQuote = $order->quotation->isEmpty() ? null : $order->quotation->first();

    $priceListLabel = $latestQuote && $latestQuote->priceList
        ? $latestQuote->priceList->name
        : ($customer && $customer->priceList ? $customer->priceList->name : 'قائمة الأسعار المعتمدة');

    $deliveryNotes = $order->delivery_notes ?? '—';

    $subtotal      = (float) ($order->subtotal ?? 0);
    $discountTotal = (float) ($order->discount_total ?? $order->discount_amount ?? 0);
    $taxTotal      = (float) ($order->tax_total ?? $order->tax_amount ?? 0);
    $shippingFee   = (float) ($order->shipping_fee ?? 0);
    $grandTotal    = (float) ($order->total ?? $order->total_amount ?? 0);

    $minRows = 5;
@endphp

<div class="page">

    <!-- ================= الترويسة ================= -->
    <div class="header">
        <img src="{{ asset('icon.png') }}" alt="الشعار" class="logo">
        <div class="brand-en">MAGADIT FOOD INDUSTRIES</div>
        <div class="brand-ar">صناعات مجد للتغذية والمواد الغذائية</div>
    </div>

    <div class="doc-title">طلبية</div>
    <div class="doc-rule"></div>

    <!-- ================= بيانات الطلبية ================= -->
    <table class="meta">
        <tr>
            <td class="lbl">تاريخ الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $orderDate->format('H:i:s d.m.Y') }}</td>

            <td class="lbl">رقم الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $docNo }}</td>
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
            <td class="lbl">اسم المستودع</td>
            <td class="colon">:</td>
            <td class="val">{{ $warehouseName }}</td>

            <td class="lbl">رمز المستودع</td>
            <td class="colon">:</td>
            <td class="val">{{ $warehouseCode }}</td>
        </tr>
        <tr>
            <td class="lbl">الفرع</td>
            <td class="colon">:</td>
            <td class="val">{{ $branchName }}</td>

            <td class="lbl">حالة الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $statusLabel }}</td>
        </tr>

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
            <td class="lbl">التسليم المجدول</td>
            <td class="colon">:</td>
            <td class="val">{{ $scheduledDelivery ? $scheduledDelivery->format('d.m.Y') : '—' }}</td>

            <td class="lbl">تاريخ التأكيد</td>
            <td class="colon">:</td>
            <td class="val">{{ $confirmedAt ? $confirmedAt->format('d.m.Y H:i') : '—' }}</td>
        </tr>
        @if($cancelledAt)
        <tr>
            <td class="lbl">تاريخ الإلغاء</td>
            <td class="colon">:</td>
            <td class="val">{{ $cancelledAt->format('d.m.Y H:i') }}</td>

            <td class="lbl">عرض السعر</td>
            <td class="colon">:</td>
            <td class="val">{{ $latestQuote ? $latestQuote->quotation_number : '—' }}</td>
        </tr>
        @else
        <tr>
            <td class="lbl">تاريخ الإلغاء</td>
            <td class="colon">:</td>
            <td class="val">—</td>

            <td class="lbl">عرض السعر</td>
            <td class="colon">:</td>
            <td class="val">{{ $latestQuote ? $latestQuote->quotation_number : '—' }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">عنوان التسليم</td>
            <td class="colon">:</td>
            <td class="val" colspan="5">{{ $address }}</td>
        </tr>
        <tr>
            <td class="lbl">ملاحظات التسليم</td>
            <td class="colon">:</td>
            <td class="val" colspan="5">{{ $deliveryNotes }}</td>
        </tr>
    </table>

    <!-- ================= جدول الأصناف ================= -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 12%;">رمز المادة</th>
                <th style="width: 20%; text-align: right;">المادة</th>
                <th style="width: 12%;">العبوة</th>
                <th style="width: 6%;">الكمية</th>
                <th style="width: 8%;">السعر</th>
                <th style="width: 7%;">الخصم</th>
                <th style="width: 7%;">الضريبة</th>
                <th style="width: 9%;">المجموع</th>
                <th style="width: 19%;">المستودع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php
                    $lineTotal = $item->line_total ?: ($item->subtotal ?: ($item->unit_price * $item->quantity));

                    $product  = $item->item;
                    $itemName = $product
                        ? ($product->name_ar ?: $product->name_en)
                        : 'منتج';
                    $itemCode = $product ? ($product->code ?? '—') : '—';

                    $packLabel = 'وحدة';
                    if ($item->pack) {
                        $uom = $item->pack->packCapacity?->uom
                            ? ($item->pack->packCapacity->uom->name_ar ?? $item->pack->packCapacity->uom->name_en)
                            : null;
                        $packLabel = $item->pack->packType?->name
                            ?? ($uom ?: ('عبوة ' . (float) $item->pack->quantity_in_pack));
                    }

                    $itemWarehouse = $item->warehouse
                        ? ($item->warehouse->name_ar ?: $item->warehouse->name_en)
                        : $warehouseName;
                @endphp
                <tr>
                    <td class="code">{{ $itemCode }}</td>
                    <td class="name">
                        {{ $itemName }}
                        @if($item->notes)
                            <div class="sub">{{ $item->notes }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $packLabel }}
                        @if($item->pack && $item->pack->quantity_in_pack)
                            <div class="sub">{{ (float) $item->pack->quantity_in_pack }}</div>
                        @endif
                    </td>
                    <td>{{ (float) $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2, '.', '') }}</td>
                    <td>{{ number_format((float) $item->discount_amount, 2, '.', '') }}</td>
                    <td>{{ number_format((float) $item->tax_amount, 2, '.', '') }}</td>
                    <td class="num">{{ number_format($lineTotal, 2, '.', '') }}</td>
                    <td>{{ $itemWarehouse }}</td>
                </tr>
            @endforeach

            @for($i = $order->items->count(); $i < $minRows; $i++)
                <tr class="empty">
                    <td>&nbsp;</td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td>
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
                        <td class="lbl">صافي المجموع (قبل الضريبة والخصم)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($subtotal, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">مجموع الخصم</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($discountTotal, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">مجموع الضريبة</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($taxTotal, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">رسوم الشحن</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($shippingFee, 2, '.', '') }}</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl">صافي المجموع (بعد الضريبة والخصم)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($grandTotal, 2, '.', '') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ================= الشروط والملاحظات ================= -->
    <div class="terms">
        <div class="terms-title">الشروط والملاحظات</div>
        <div class="terms-text">
            يتم تنفيذ هذه الطلية وفقاً لقائمة الأسعار المعتمدة، وتخضع جميع الأصناف لفحص الجودة قبل التسليم.
        </div>
        <div class="terms-text" style="margin-top:6px;">
            يجب تقديم أي ملاحظة تتعلق بجودة الأصناف خلال 24 ساعة من تاريخ الاستلام.
        </div>
        @if($order->notes)
            <div class="terms-text" style="margin-top:6px;">
                <b style="color:#1f4d3a;">ملاحظات:</b> {{ $order->notes }}
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
