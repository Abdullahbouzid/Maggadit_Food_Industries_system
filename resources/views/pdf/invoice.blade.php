<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة مبيعات - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'XBNazanin', 'Arial', sans-serif;
            color: #1f2937;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12px;
            direction: rtl;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }
        .company-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }
        .invoice-badge {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 8px 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            display: inline-block;
            text-align: center;
        }
        .meta-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            width: 100%;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .meta-label {
            color: #4b5563;
            font-weight: bold;
            font-size: 11px;
        }
        .meta-value {
            color: #111827;
            font-size: 12px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 10px;
            font-size: 11px;
            text-align: right;
            border: 1px solid #4f46e5;
        }
        .items-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .summary-box {
            width: 40%;
            float: left;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px;
            font-size: 12px;
        }
        .summary-total {
            border-top: 2px solid #4f46e5;
            font-weight: bold;
            font-size: 14px;
            color: #4f46e5;
        }
        .clear {
            clear: both;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge-list {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <h1 class="company-title">صناعات مجد للتغذية والمواد الغذائية</h1>
                <div class="company-subtitle">Maggadit Food Industries System</div>
                <div class="company-subtitle">المملكة العربية السعودية / Kingdom of Saudi Arabia</div>
            </td>
            <td style="width: 40%; text-align: left;">
                <div class="invoice-badge">
                    فاتورة مبيعات / INVOICE
                </div>
                <div style="margin-top: 5px; font-weight: bold; font-size: 13px; color: #374151;">
                    # {{ $invoice->invoice_number }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Customer and Invoice Meta Information -->
    <div class="meta-card">
        <table class="meta-table">
            <tr>
                <td style="width: 50%;">
                    <div class="meta-label">معلومات العميل / Customer Details:</div>
                    <div class="meta-value" style="font-size: 14px; font-weight: bold; color: #111827;">
                        {{ $invoice->customer ? ($invoice->customer->name ?: $invoice->customer->username) : 'عميل افتراضي' }}
                    </div>
                    @if($invoice->customer && $invoice->customer->phone)
                        <div class="meta-value">الهاتف / Phone: {{ $invoice->customer->phone }}</div>
                    @endif
                    @if($invoice->customer && $invoice->customer->address)
                        <div class="meta-value">العنوان / Address: {{ $invoice->customer->address }}</div>
                    @endif
                </td>
                <td style="width: 50%;">
                    <div class="meta-label">تاريخ الفاتورة / Invoice Date:</div>
                    <div class="meta-value">{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d') }}</div>
                    
                    <div class="meta-label" style="margin-top: 6px;">قائمة الأسعار المطبقة / Price List:</div>
                    <div class="meta-value">
                        <span class="badge-list">
                            {{ $invoice->priceList ? $invoice->priceList->name : ($invoice->customer && $invoice->customer->priceList ? $invoice->customer->priceList->name : 'قائمة الأسعار المعتمدة') }}
                        </span>
                    </div>

                    <div class="meta-label" style="margin-top: 6px;">حالة الفاتورة / Status:</div>
                    <div class="meta-value" style="font-weight: bold; color: #047857;">
                        {{ $invoice->status == 'delivered' || $invoice->status == 'completed' ? 'تم التوصيل / Delivered' : 'قيد المعالجة / Processing' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 45%;">المنتج والعبوة / Item & Packaging</th>
                <th style="width: 15%;" class="text-center">وحدة القياس / UOM</th>
                <th style="width: 10%;" class="text-center">الكمية / Qty</th>
                <th style="width: 12%;" class="text-left">السعر / Price</th>
                <th style="width: 13%;" class="text-left">المجموع / Total</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotalCalculated = 0; @endphp
            @foreach($invoice->items as $index => $item)
                @php
                    $lineTotal = $item->subtotal ?: ($item->unit_price * $item->quantity);
                    $subtotalCalculated += $lineTotal;
                    $itemName = $item->item ? ($item->item->name_ar ?: $item->item->name_en) : 'منتج محدد';
                    $packLabel = 'وحدة قياسية';
                    if ($item->itemPack) {
                        $uom = $item->itemPack->packCapacity?->uom ? ($item->itemPack->packCapacity->uom->name_ar ?? $item->itemPack->packCapacity->uom->name_en) : null;
                        $packLabel = $item->itemPack->packType?->name ?? ($uom ?: ('عبوة ' . (float)$item->itemPack->quantity_in_pack));
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong style="color: #111827;">{{ $itemName }}</strong>
                        @if($item->itemPack)
                            <div style="font-size: 10px; color: #4f46e5; margin-top: 2px;">
                                العبوة: {{ $packLabel }} (حجم {{ (float)$item->itemPack->quantity_in_pack }})
                            </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $packLabel }}</td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-left">{{ number_format($item->unit_price, 2) }} ر.س</td>
                    <td class="text-left" style="font-weight: bold;">{{ number_format($lineTotal, 2) }} ر.س</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Summary Box -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="meta-label">المجموع الفرعي / Subtotal:</td>
                <td class="text-left">{{ number_format($subtotalCalculated, 2) }} ر.س</td>
            </tr>
            <tr class="summary-total">
                <td>الإجمالي الكلي / Grand Total:</td>
                <td class="text-left">{{ number_format($invoice->total_amount, 2) }} ر.س</td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>

    <!-- Footer -->
    <div class="footer">
        تم إصدار هذه الفاتورة إلكترونياً من نظام شركات مجد للتغذية والمواد الغذائية - Maggadit Food Industries System<br>
        شكراً لتسوقكم معنا! / Thank you for your business!
    </div>

</body>
</html>
