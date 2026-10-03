<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Order;
use App\Models\Quotation;
use App\Services\Customer\ImagePathService;
use App\Services\OrderService;
use App\Services\OrderStepPresenter;
use App\Services\PdfGenerator;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(
        ImagePathService $imagePathService,
        private OrderService $orderService,
        private OrderStepPresenter $stepPresenter,
        private QuotationService $quotationService,
    ) {
        $this->imagePathService = $imagePathService;
    }

    /**
     * Create an order from the customer cart.
     *
     * This starts the customer path (مسار B) of the order lifecycle:
     *   new → pending_review → admin_edited → quotation_sent → (customer decision)
     * The order becomes visible in the admin dashboard immediately.
     */
    public function checkout(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.pack_id' => 'nullable|integer',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_phone' => 'nullable|string|max:30',
            'scheduled_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $lines = [];
        $rejected = [];

        foreach ($validated['items'] as $rawItem) {
            $item = Item::find($rawItem['id']);

            if (! $item) {
                $rejected[] = ['id' => $rawItem['id'], 'reason' => 'المنتج غير موجود'];

                continue;
            }

            $packId = $rawItem['pack_id'] ?? null;
            if ($packId && ! \App\Models\ItemPack::where('id', $packId)->where('item_id', $item->id)->exists()) {
                $packId = null;
            }

            $lines[] = [
                'item_id' => $item->id,
                'item_pack_id' => $packId,
                'quantity' => (float) $rawItem['quantity'],
            ];
        }

        if (empty($lines)) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على منتجات صالحة في السلة',
                'rejected' => $rejected,
            ], 422);
        }

        try {
            $order = $this->orderService->create(
                customer: $customer,
                items: $lines,
                deliveryAddress: $validated['delivery_address'] ?? $customer->default_address,
                deliveryPhone: $validated['delivery_phone'] ?? $customer->phone,
                scheduledDeliveryDate: isset($validated['scheduled_delivery_date'])
                    ? \Carbon\Carbon::parse($validated['scheduled_delivery_date'])
                    : null,
                user: null,
                warehouseId: null,
                currencyId: $customer->priceList?->currency_id,
            );
        } catch (\Throwable $e) {
            Log::error('Customer checkout failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إنشاء الطلبية. '.($e->getMessage()),
            ], 422);
        }

        $order->load('statusHistory');

        return response()->json([
            'success' => true,
            'message' => 'تم استلام طلبك بنجاح وسيتم مراجعته.',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'redirect' => route('customer.order_details', $order->id),
            'steps' => $this->stepPresenter->steps($order),
        ]);
    }

    /**
     * Customer order history: orders + their invoices.
     */
    /**
     * True when the order is in "quotation_sent" AND a quotation is still
     * awaiting the customer's decision.
     *
     * The status alone is not enough: a quote may already carry a customer
     * response, in which case the buttons must not be shown again.
     */
    private function canRespondToQuotation(Order $order): bool
    {
        if (! $this->stepPresenter->canCustomerRespond($order)) {
            return false;
        }

        return $order->quotation()
            ->where('status', 'sent')
            ->where(function ($query) {
                $query->whereNull('customer_response')
                    ->orWhere('customer_response', 'pending');
            })
            ->exists();
    }

    public function orderHistory(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $orders = Order::where('customer_id', $customer->id)
            ->with([
                'items.item:id,name_ar,name_en',
                'quotation:id,order_id,quotation_number,status,customer_response,total',
                'invoices:id,order_id,invoice_number,status,total_amount',
            ])
            ->orderByDesc('id')
            ->get();

        // If local DB is empty, fetch from external API (for APK)
        if ($orders->isEmpty()) {
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/orders', $token);
                if ($remoteData && isset($remoteData['orders']) && is_array($remoteData['orders'])) {
                    $orders = collect($remoteData['orders'])->map(function ($inv) {
                        $quotation = isset($inv['quotation']) ? (object) $inv['quotation'] : null;
                        $invoice = isset($inv['invoice']) ? (object) $inv['invoice'] : null;
                        
                        return (object) [
                            'id' => $inv['id'] ?? $inv['invoice_id'] ?? null,
                            'order_number' => $inv['invoice_number'] ?? $inv['order_number'] ?? null,
                            'created_at' => $inv['date'] ?? $inv['invoice_date'] ?? null,
                            'total' => $inv['total_amount'] ?? 0,
                            'status' => $inv['status'] ?? 'pending',
                            'items' => isset($inv['items']) ? collect($inv['items'])->map(fn($i) => (object) [
                                'item' => (object) [
                                    'name_ar' => $i['name'] ?? null,
                                    'name_en' => $i['name_en'] ?? null,
                                ],
                                'quantity' => $i['quantity'] ?? 0,
                                'unit_price' => $i['unit_price'] ?? 0,
                            ]) : collect(),
                            'quotation' => $quotation,
                            'invoices' => $invoice ? collect([$invoice]) : collect(),
                        ];
                    });
                }
            }
        }

        $rows = $orders->map(function ($order) use ($customer) {
            $meta = $this->stepPresenter->currentStatusMeta($order);
            $quotation = $order->quotation ?? null;
            $invoice = $order->invoices->first() ?? null;

            return [
                'id' => $order->id,
                'number' => $order->order_number ?? $order->invoice_number ?? $order->id,
                'date' => $order->created_at?->format('Y-m-d') ?? $order->date ?? null,
                'total' => (float) ($order->total ?? $order->total_amount ?? 0),
                'status' => $this->stepPresenter->currentStatusValue($order),
                'status_text' => $meta['label'],
                'status_class' => $meta['class'],
                'progress' => $this->stepPresenter->progress($order),
                'items_count' => $order->items->count(),
                'has_quotation' => (bool) $quotation,
                'has_invoice' => (bool) $invoice,
                'invoice_number' => $invoice?->invoice_number,
                'quotation_number' => $quotation?->quotation_number,
                'can_respond' => $this->canRespondToQuotation($order),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج'),
                    'qty' => (float) $item->quantity,
                    'price' => (float) $item->unit_price,
                ])->all(),
            ];
        })->all();

        $stats = [
            'all' => count($rows),
            'pending' => count(array_filter($rows, fn ($r) => ! in_array($r['status'], [
                OrderStatus::Delivered->value,
                OrderStatus::Cancelled->value,
                OrderStatus::Rejected->value,
                OrderStatus::Returned->value,
            ], true))),
            'delivered' => count(array_filter($rows, fn ($r) => $r['status'] === OrderStatus::Delivered->value)),
        ];

        return view('app_android.more.order_history', compact('rows', 'stats'));
    }

    /**
     * Order details with the full step timeline and quotation panel.
     */
    public function orderDetails(Request $request, $id)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $order = Order::where('customer_id', $customer->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id);
                if (is_numeric($id)) {
                    $q->orWhere('order_number', $id);
                }
            })
            ->first();

        if (! $order) {
            return view('app_android.more.order_details', [
                'order' => null,
                'steps' => [],
                'timeline' => [],
                'orderItems' => [],
                'quotation' => null,
                'quotationItems' => [],
                'invoice' => null,
                'statusMeta' => ['label' => '', 'class' => 'text-gray-600 bg-gray-100'],
                'progress' => 0,
                'canRespond' => false,
            ]);
        }

        $order->load(['items.item', 'items.pack', 'items.warehouse', 'quotation.items', 'invoices']);

        $quotation = $order->quotation->sortByDesc('version')->first();
        $invoice = $order->invoices->first();

        $orderItems = $order->items->map(function ($item) {
            $packLabel = 'وحدة';
            if ($item->pack) {
                $uom = $item->pack->packCapacity?->uom;
                $packLabel = $item->pack->packType?->name
                    ?: ($uom?->name_ar ?: $uom?->name_en ?: ('عبوة '.(float) $item->pack->quantity_in_pack));
            }

            return [
                'name' => $item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج'),
                'code' => $item->item?->code ?: '—',
                'pack' => $packLabel,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) ($item->line_total ?: $item->subtotal ?: ((float) $item->unit_price * (float) $item->quantity)),
                'warehouse' => $item->warehouse?->name_ar ?: ($item->warehouse?->name_en ?: 'غير محدد'),
            ];
        })->all();

        $quotationItems = $quotation
            ? $quotation->items->map(function ($item) {
                return [
                    'name' => $item->item_name_snapshot ?: ($item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج')),
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount_percent,
                    'line_total' => (float) ($item->line_total ?: $item->subtotal ?: 0),
                ];
            })->all()
            : [];

        return view('app_android.more.order_details', [
            'order' => $order,
            'orderId' => $order->id,
            'steps' => $this->stepPresenter->steps($order),
            'timeline' => $this->stepPresenter->timeline($order),
            'orderItems' => $orderItems,
            'quotation' => $quotation,
            'quotationItems' => $quotationItems,
            'invoice' => $invoice,
            'statusMeta' => $this->stepPresenter->currentStatusMeta($order),
            'progress' => $this->stepPresenter->progress($order),
            'canRespond' => $this->canRespondToQuotation($order),
        ]);
    }

    /**
     * Customer responds to a quotation: approve / reject / request changes.
     *
     * This drives the order state machine through OrderLifecycleService.
     */
    public function respondToQuotation(Request $request, $id)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $validated = $request->validate([
            'response' => 'required|in:approved,rejected,changes_requested',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Target the quotation that is actually awaiting a decision. Selecting
        // the highest version regardless of its response blocks the customer
        // permanently once an older quote keeps a higher version.
        $quotation = Quotation::where('order_id', $id)
            ->where('customer_id', $customer->id)
            ->where('status', 'sent')
            ->where(function ($query) {
                $query->whereNull('customer_response')
                    ->orWhere('customer_response', 'pending');
            })
            ->latest('version')
            ->first();

        if (! $quotation) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد عرض سعر بانتظار ردك لهذه الطلبية.',
            ], 404);
        }

        if (! $this->stepPresenter->canCustomerRespond($quotation->order)) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن الرد على هذا العرض في الوقت الحالي.',
            ], 422);
        }

        // طلب التعديل يتطلب كتابة الملاحظات
        if ($validated['response'] === 'changes_requested' && blank($validated['notes'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى كتابة تفاصيل التعديل المطلوب.',
            ], 422);
        }

        try {
            $this->quotationService->recordCustomerResponse(
                $quotation,
                $validated['response'],
                $validated['notes'] ?? null,
            );
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Quotation response failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'تعذر تسجيل ردك. حاول مرة أخرى.'], 500);
        }

        $messages = [
            'approved' => 'تم اعتماد عرض السعر وسيتم إصدار الفاتورة.',
            'rejected' => 'تم رفض عرض السعر.',
            'changes_requested' => 'تم إرسال طلب التعديلات إلى الإدارة.',
        ];

        $order = $quotation->order->refresh();
        $meta = $this->stepPresenter->currentStatusMeta($order);

        return response()->json([
            'success' => true,
            'message' => $messages[$validated['response']],
            'status' => $meta['label'],
            'steps' => $this->stepPresenter->steps($order),
        ]);
    }

    /**
     * Download the invoice PDF for one of the customer's own orders.
     */
    public function downloadPdf($id, Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $invoice = $this->findCustomerInvoice($customer, $id);

        if (! $invoice) {
            return redirect()->back()->with('error', 'الفاتورة غير موجودة');
        }

        $invoice->load(['customer', 'priceList', 'currency', 'items.item', 'items.itemPack.packType', 'items.priceList']);

        $lang = $request->query('lang', 'bilingual');

        return PdfGenerator::download('pdf.invoice', compact('invoice', 'lang'), "invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download the order (طلبية) PDF for one of the customer's own orders.
     */
    public function downloadOrderPdf($id, Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $order = Order::where('customer_id', $customer->id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->with(['customer', 'currency', 'warehouse', 'createdBy', 'items.item', 'items.pack', 'items.warehouse', 'quotation'])
            ->first();

        if (! $order) {
            return redirect()->back()->with('error', 'الطلبية غير موجودة');
        }

        return PdfGenerator::download('pdf.order', compact('order'), "order-{$order->order_number}.pdf");
    }

    /**
     * Download the quotation PDF for one of the customer's own orders.
     */
    public function downloadQuotationPdf($id, Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $quotation = Quotation::where('order_id', $id)
            ->where('customer_id', $customer->id)
            ->with(['customer', 'createdBy', 'order.customer', 'order.warehouse', 'order.currency', 'items.item', 'items.pack', 'items.warehouse'])
            ->latest('version')
            ->first();

        if (! $quotation) {
            return redirect()->back()->with('error', 'لا توجد عروض أسعار لهذا الطلب');
        }

        return PdfGenerator::download('pdf.quotation', compact('quotation'), "quotation-{$quotation->quotation_number}.pdf");
    }

    /**
     * Resolve an invoice by invoice_number, id, or order id — always scoped to the customer.
     */
    private function findCustomerInvoice(\App\Models\Customer $customer, $id): ?Invoice
    {
        $query = Invoice::where('customer_id', $customer->id)
            ->where(function ($q) use ($id) {
                $q->where('invoice_number', $id);
                if (is_numeric($id)) {
                    $q->orWhere('id', $id)->orWhere('order_id', $id);
                }
            });

        return $query->first();
    }
}
