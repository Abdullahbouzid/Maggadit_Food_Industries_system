<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
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
            $price = (float) ($rawItem['price'] ?? $rawItem['new_price'] ?? $rawItem['unit_price'] ?? 0);

            if ($itemId && $quantity > 0) {
                $exists = Item::where('id', $itemId)->exists();
                if (! $exists) {
                    $firstItem = Item::first();
                    $itemId = $firstItem ? $firstItem->id : null;
                }

                if ($itemId) {
                    $items[] = [
                        'id' => $itemId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                    $totalAmount += ($price * $quantity);
                }
            }
        }

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على أضناف صالحة في السلة'], 422);
        }

        $totalAmount += 15;

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
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
                'warehouse_id' => null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استلام طلبك بنجاح! جاري معالجته.',
            'invoice_id' => $invoice->id,
        ]);
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
            $invoices = Invoice::with(['items.item' => function ($q) {
                $q->select('id', 'name_ar', 'name_en');
            }, 'items.warehouse'])
            ->orderBy('invoice_date', 'desc')
            ->get();
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
