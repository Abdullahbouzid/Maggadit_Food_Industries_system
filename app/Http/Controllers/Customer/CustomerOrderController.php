<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function checkout(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        $rawItems = $request->input('items', []);
        if (! is_array($rawItems) || empty($rawItems)) {
            return response()->json(['success' => false, 'message' => 'السلة فارغة أو البيانات غير صحيحة'], 422);
        }

        $items = [];
        $totalAmount = 0;

        foreach ($rawItems as $rawItem) {
            $itemId = $rawItem['id'] ?? $rawItem['item_id'] ?? null;
            $quantity = (float) ($rawItem['quantity'] ?? 1);
            $packId = $rawItem['pack_id'] ?? null;

            if ($itemId && $quantity > 0) {
                $item = Item::find($itemId);
                if (! $item) {
                    continue;
                }

                $priceRecord = $this->getItemPriceRecord($item, $customer, $packId);
                $price = $priceRecord['price'];

                $items[] = [
                    'id' => $itemId,
                    'pack_id' => $packId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'price_list_id' => $priceRecord['price_list_id'],
                    'price_history_id' => $priceRecord['price_history_id'],
                ];
                $totalAmount += ($price * $quantity);
            }
        }

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على منتجات صالحة في السلة'], 422);
        }

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'price_list_id' => $customer->price_list_id,
            'invoice_number' => 'INV-'.strtoupper(uniqid()),
            'invoice_date' => now(),
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'notes' => 'تم الطلب من التطبيق',
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_id' => $item['id'],
                'item_pack_id' => $item['pack_id'],
                'warehouse_id' => null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'price_list_id' => $item['price_list_id'],
                'price_history_id' => $item['price_history_id'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استلام طلبك بنجاح! جاري معالجته.',
            'invoice_id' => $invoice->id,
        ]);
    }

    public function downloadPdf($id, Request $request)
    {
        $invoice = Invoice::where('invoice_number', $id)
            ->orWhere('id', $id)
            ->with(['customer', 'priceList', 'items.item', 'items.itemPack.uom', 'items.priceList', 'items.priceHistory'])
            ->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'الفاتورة غير موجودة');
        }

        $lang = $request->query('lang', 'bilingual');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice', 'lang'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function orderHistory(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with(['items.item' => function ($q) {
                $q->select('id', 'name_ar', 'name_en');
            }, 'items.warehouse'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        if ($invoices->isEmpty()) {
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/orders', $token);
                if ($remoteData && isset($remoteData['orders'])) {
                    $orders = collect($remoteData['orders'])->map(function ($order) use ($customer) {
                        return [
                            'id' => $order['invoice_number'] ?? $order['id'] ?? '#',
                            'date' => $order['invoice_date'] ?? $order['date'] ?? now()->format('Y-m-d'),
                            'total' => (float) ($order['total_amount'] ?? $order['total'] ?? 0),
                            'status' => $order['status'] ?? 'pending',
                            'status_text' => $order['status'] == 'delivered' || $order['status'] == 'completed' ? 'مكتمل' :
                                            ($order['status'] == 'pending' ? 'قيد المعالجة' : 'ملغي'),
                            'items' => collect($order['items'] ?? [])->map(function ($item) {
                                return [
                                    'name' => $item['item_name_ar'] ?? $item['item_name_en'] ?? $item['name'] ?? 'منتج',
                                    'qty' => (float) ($item['quantity'] ?? $item['qty'] ?? 0),
                                    'price' => (float) ($item['unit_price'] ?? $item['price'] ?? 0),
                                    'warehouse' => $item['warehouse_name'] ?? $item['warehouse'] ?? 'غير محدد',
                                ];
                            })->toArray(),
                            'address' => $order['notes'] ?? 'العنوان المسجل (افتراضي)',
                            'phone' => $customer->username ?? 'غير متوفر',
                            'payment_method' => 'عند الاستلام',
                            'delivery_fee' => 15,
                            'coupon_discount' => 0,
                        ];
                    })->toArray();

                    $stats = [
                        'all' => count($orders),
                        'pending' => count(array_filter($orders, fn($o) => $o['status'] == 'pending')),
                        'delivered' => count(array_filter($orders, fn($o) => $o['status'] == 'delivered' || $o['status'] == 'completed')),
                    ];

                    return view('app_android.more.order_history', compact('orders', 'stats'));
                }
            }
        }

        $orders = $invoices->map(function ($invoice) use ($customer) {
            return [
                'id' => $invoice->invoice_number,
                'date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
                'total' => (float) $invoice->total_amount,
                'status' => $invoice->status,
                'status_text' => $invoice->status == 'delivered' || $invoice->status == 'completed' ? 'مكتمل' :
                                ($invoice->status == 'pending' ? 'قيد المعالجة' : 'ملغي'),
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'name' => $item->item->name_ar ?? $item->item->name_en ?? 'منتج',
                        'qty' => (float) $item->quantity,
                        'price' => (float) $item->unit_price,
                        'warehouse' => $item->warehouse ? $item->warehouse->name : 'غير محدد',
                    ];
                })->toArray(),
                'address' => 'العنوان المسجل (افتراضي)',
                'phone' => $customer->username ?? 'غير متوفر',
                'payment_method' => 'عند الاستلام',
                'delivery_fee' => 15,
                'coupon_discount' => 0,
            ];
        })->toArray();

        $stats = [
            'all' => $invoices->count(),
            'pending' => $invoices->where('status', 'pending')->count(),
            'delivered' => $invoices->whereIn('status', ['delivered', 'completed'])->count(),
        ];

        return view('app_android.more.order_history', compact('orders', 'stats'));
    }
}
