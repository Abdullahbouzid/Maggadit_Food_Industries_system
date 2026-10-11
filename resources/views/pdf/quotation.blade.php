<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>عرض سعر - {{ $quotation->quotation_number }}</title>
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
    $docNo = $quotation->quotation_number ?? '—';

    $issueDate = $quotation->created_at
        ? \Carbon\Carbon::parse($quotation->created_at)
        : now();

    $order         = $quotation->order;
    $orderNo       = $order?->order_number ?? '—';
    $orderDate     = $order?->created_at ? \Carbon\Carbon::parse($order->created_at) : null;

    $customer     = $quotation->customer;
    $customerName = $customer ? ($customer->name ?: $customer->username) : 'عميل نقدي';
    $customerType = $customer->type ?? 'Credit Customer';
    $phone        = $customer->phone ?? ($order->delivery_phone ?? '—');
    $address      = $customer->default_address ?? ($order->delivery_address ?? '—');

    $employee = $quotation->createdBy;
    $employeeName = $employee?->name ?: '—';
    $employeeCode = $employee?->id ?? ($quotation->created_by ?? '—');

    $warehouseName = $order?->warehouse
        ? ($order->warehouse->name_ar ?: $order->warehouse->name_en)
        : 'المستودع الرئيسي';
    $warehouseCode = $order?->warehouse?->code ?? ($order->warehouse_id ?? '—');

    $priceListLabel = $order && $order->customer && $order->customer->priceList
        ? $order->customer->priceList->name
        : 'قائمة الأسعار المعتمدة';

    $statusRaw = $quotation->status;
    $statusLabel = $statusRaw ? ucfirst(str_replace('_', ' ', (string) $statusRaw)) : '—';

    $validUntil = $quotation->valid_until
        ? \Carbon\Carbon::parse($quotation->valid_until)
        : null;
    $acceptedAt = $quotation->accepted_at ? \Carbon\Carbon::parse($quotation->accepted_at) : null;
    $rejectedAt = $quotation->rejected_at ? \Carbon\Carbon::parse($quotation->rejected_at) : null;

    $currencyName = $order?->currency ? ($order->currency->name_ar ?: $order->currency->code) : 'ر.س / SAR';
    $currencyCode = $order?->currency->code ?? 'SAR';
    $exchangeRate = $order?->exchange_rate ? (float) $order->exchange_rate : 1.0;

    $subtotal   = (float) ($quotation->subtotal ?? 0);
    $discount   = (float) ($quotation->discount_total ?? 0);
    $taxAmount  = (float) ($quotation->tax_total ?? 0);
    $grandTotal = (float) ($quotation->total ?? 0);

    $minRows = 5;
@endphp

<div class="page">

    <!-- ================= الترويسة ================= -->
    <div class="header">
        <img src="{{ asset('icon.png') }}" alt="الشعار" class="logo">
        <div class="brand-en">MAGADIT FOOD INDUSTRIES</div>
        <div class="brand-ar">صناعات مجد للتغذية والمواد الغذائية</div>
    </div>

    <div class="doc-title">عرض سعر</div>
    <div class="doc-rule"></div>

    <!-- ================= بيانات عرض السعر ================= -->
    <table class="meta">
        <tr>
            <td class="lbl">تاريخ العرض</td>
            <td class="colon">:</td>
            <td class="val">{{ $issueDate->format('H:i:s d.m.Y') }}</td>

            <td class="lbl">رقم العرض</td>
            <td class="colon">:</td>
            <td class="val">{{ $docNo }}</td>
        </tr>
        <tr>
            <td class="lbl">رقم الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $orderNo }}</td>

            <td class="lbl">تاريخ الطلبية</td>
            <td class="colon">:</td>
            <td class="val">{{ $orderDate ? $orderDate->format('d.m.Y') : '—' }}</td>
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
            <td class="lbl">حالة العرض</td>
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
            <td class="lbl">ساري حتى</td>
            <td class="colon">:</td>
            <td class="val">{{ $validUntil ? $validUntil->format('d.m.Y') : '—' }}</td>

            <td class="lbl">تاريخ القبول</td>
            <td class="colon">:</td>
            <td class="val">{{ $acceptedAt ? $acceptedAt->format('d.m.Y H:i') : '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">تاريخ الرفض</td>
            <td class="colon">:</td>
            <td class="val">{{ $rejectedAt ? $rejectedAt->format('d.m.Y H:i') : '—' }}</td>

            <td class="lbl">عدد الأصناف</td>
            <td class="colon">:</td>
            <td class="val">{{ $quotation->items->count() }}</td>
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
                <th style="width: 20%; text-align: right;">المادة</th>
                <th style="width: 12%;">العبوة</th>
                <th style="width: 6%;">الكمية</th>
                <th style="width: 8%;">السعر</th>
                <th style="width: 7%;">الخصم</th>
                <th style="width: 7%;">الضريبة</th>
                <th style="width: 9%;">المجموع</th>
                <th style="width: 18%;">المستودع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
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
                    <td class="name">{{ $itemName }}</td>
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

            @for($i = $quotation->items->count(); $i < $minRows; $i++)
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
                        <td class="lbl">صافي المجموع قبل الضريبة والخصم</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($subtotal, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">مجموع الضريبة</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($taxAmount, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">مجموع الخصم</td>
                        <td class="colon">:</td>
                        <td class="val">{{ number_format($discount, 2, '.', '') }}</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl">صافي المجموع بعد الضريبة والخصم</td>
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
            الأسعار الواردة في هذا العرض سارية حتى {{ $validUntil ? $validUntil->format('d.m.Y') : 'تاريخ الإلغاء' }}
            وتكون خاضعة لإعادة التأكيد عند تغيير قائمة الأسعار.
        </div>
        <div class="terms-text" style="margin-top:6px;">
            يخضع تنفيذ هذا العرض لتوفر الأصناف الكمية في المخزون وقت تأكيد الطلب.
        </div>
        @if($quotation->notes)
            <div class="terms-text" style="margin-top:6px;">
                <b style="color:#1f4d3a;">ملاحظات:</b> {{ $quotation->notes }}
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