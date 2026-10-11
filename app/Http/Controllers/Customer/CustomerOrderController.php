<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Models\Quotation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\Customer\ImagePathService;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    /**
     * Process customer checkout
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
            'survey_rating' => 'nullable|integer|min:1|max:5',
            'survey_feedback' => 'nullable|string|max:2000',
        ]);

        $lines = [];
        $rejected = [];
        $dbError = false;
        $allItemsMissingLocally = true; // Track if all items were missing from local DB

        foreach ($validated['items'] as $rawItem) {
            $item = null;

            // Try to find item in local DB first
            try {
                $item = Item::find($rawItem['id']);
            } catch (\Throwable $e) {
                $dbError = true;
                Log::error('Checkout DB query failed for item', [
                    'item_id' => $rawItem['id'],
                    'error' => $e->getMessage(),
                ]);
            }

            // If not found locally, skip remote validation - the external API will validate items itself
            // This prevents double-validation failures in APK/remote-only environments
            if (! $item) {
                // Don't call getRemoteItem() - let the external API validate items directly
                // Only mark as rejected if we're NOT in APK mode (some items found locally)
                if ($allItemsMissingLocally) {
                    // In APK mode, continue without validating - external API will handle validation
                    continue;
                } else {
                    // Not in APK mode - item must exist somewhere
                    $item = $this->getRemoteItem($rawItem['id'], $request);
                    if (! $item) {
                        $rejected[] = ['id' => $rawItem['id'], 'reason' => 'المنتج غير موجود'];
                        Log::debug('Checkout item not found (local and remote)', [
                            'raw_item' => $rawItem,
                            'expected_id' => $rawItem['id'],
                            'db_connection' => config('database.default'),
                        ]);

                        continue;
                    }
                }
            } else {
                // Found at least one item locally, so we're not in pure remote mode
                $allItemsMissingLocally = false;
            }

            $packId = $rawItem['pack_id'] ?? null;
            if ($packId) {
                // For local items, validate pack exists in DB
                // For remote items, validate pack exists in item's packs array
                $packValid = false;
                
                if ($item instanceof Item) {
                    // Local item - validate in DB
                    try {
                        $packValid = ItemPack::where('id', $packId)->where('item_id', $item->id)->exists();
                    } catch (\Throwable $e) {
                        $dbError = true;
                        Log::error('Checkout DB query failed for pack', [
                            'pack_id' => $packId,
                            'item_id' => $item->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    // Remote item - validate in item's packs array
                    $packs = $item->packs ?? [];
                    if (!is_array($packs)) {
                        $packs = [$packs];
                    }
                    foreach ($packs as $pack) {
                        if (is_array($pack)) {
                            $packObj = (object) $pack;
                        } else {
                            $packObj = $pack;
                        }
                        if ($packObj->id === $packId) {
                            $packValid = true;
                            break;
                        }
                    }
                }
                
                if (! $packValid) {
                    $packId = null;
                    Log::debug('Checkout pack not valid for item', [
                        'pack_id' => $rawItem['pack_id'],
                        'item_id' => $rawItem['id'],
                    ]);
                }
            }

            $lines[] = [
                'item_id' => $item->id,
                'item_pack_id' => $packId,
                'quantity' => (float) $rawItem['quantity'],
            ];
        }

        // If all items were missing from local DB, we're likely in APK/remote-only mode
        // In this case, bypass local order service and call external API directly
        // The external API will validate the items itself, so we don't need to re-validate via getRemoteItem
        if ($allItemsMissingLocally) {
            // Build the items array in the same format as JavaScript sends it
            $apiItems = [];
            
            foreach ($validated['items'] as $rawItem) {
                $apiItems[] = [
                    'id' => $rawItem['id'],
                    'quantity' => (float) $rawItem['quantity'],
                    'pack_id' => $rawItem['pack_id'] ?? null,
                ];
            }
            
            // Prepare checkout request for external API - same format as JavaScript sends
            $externalCheckoutData = [
                'items' => $apiItems,
                'delivery_address' => $validated['delivery_address'] ?? '',
                'delivery_phone' => $validated['delivery_phone'] ?? '',
                'scheduled_delivery_date' => $validated['scheduled_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? '',
                'survey_rating' => $validated['survey_rating'] ?? null,
                'survey_feedback' => $validated['survey_feedback'] ?? '',
            ];

            // Call external API checkout endpoint directly
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                try {
                    $response = Http::withToken($token)
                        ->withoutVerifying()
                        ->timeout(10)
                        ->post("{$this->baseUrl}/customer/checkout", $externalCheckoutData);

                    if ($response->successful()) {
                        $responseData = $response->json();

                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => true,
                                'message' => $responseData['message'] ?? 'تم إنشاء طلبيتك بنجاح!',
                                'order_id' => $responseData['order']['id'] ?? null,
                                'order_number' => $responseData['order']['order_number'] ?? null,
                                'total' => (float) ($responseData['order']['total_amount'] ?? 0),
                                'redirect' => route('customer.order_history', [], false),
                                'steps' => [], // External API doesn't provide steps in same format
                            ]);
                        }

                        return redirect()
                            ->route('customer.order_history')
                            ->with('success', $responseData['message'] ?? 'تم إنشاء طلبيتك بنجاح!')
                            ->with('order_number', $responseData['order']['order_number'] ?? '');
                    } else {
                        // External API returned error
                        $errorData = $response->json() ?? [];
                        $message = $errorData['message'] ?? 'فشل في إنشاء الطلب';

                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => $message,
                            ], 422);
                        }

                        return back()->withInput()->with(['error' => $message]);
                    }
                } catch (\Exception $e) {
                    Log::error('Checkout external API failed: ' . $e->getMessage());
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'فشل الاتصال بالخادم الخارجي',
                        ], 503);
                    }

                    return back()->withInput()->with([
                        'error' => 'فشل الاتصال بالخادم الخارجي',
                    ]);
                }
            } else {
                // No token available for external API call
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'يجب تسجيل الدخول أولاً',
                    ], 401);
                }

                return back()->withInput()->with([
                    'error' => 'يجب تسجيل الدخول أولاً',
                ]);
            }
        }

        if (empty($lines)) {
            $message = $dbError
                ? 'خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة مرة أخرى.'
                : 'لم يتم العثور على منتجات صالحة في السلة';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'rejected' => $rejected,
                ], 422);
            }

            return back()->withInput()->with([
                'error' => $message,
                'rejected' => $rejected,
            ]);
        }

        try {
            $order = $this->orderService->create(
                customer: $customer,
                items: $lines,
                deliveryAddress: $validated['delivery_address'] ?? $customer->default_address,
                deliveryPhone: $validated['delivery_phone'] ?? $customer->phone,
                scheduledDeliveryDate: isset($validated['scheduled_delivery_date'])
                    ? Carbon::parse($validated['scheduled_delivery_date'])
                    : null,
                user: null,
                warehouseId: null,
                currencyId: $customer->priceList?->currency_id,
                skipStockCheck: true,
            );

            if (! empty($validated['survey_rating'])) {
                $order->update([
                    'notes' => trim(($order->notes ?? '') . "\n\nتقييم العميل: " . $validated['survey_rating'] . "/5" . ($validated['survey_feedback'] ? "\nملاحظات: " . $validated['survey_feedback'] : '')),
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء طلبيتك بنجاح!',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total_amount,
                    'redirect' => route('customer.order_history', [], false),
                ]);
            }

            return redirect()
                ->route('customer.order_history')
                ->with('success', 'تم إنشاء طلبيتك بنجاح!')
                ->with('order_number', $order->order_number);
        } catch (\Throwable $e) {
            Log::error('Customer checkout failed: '.$e->getMessage());

            // Check if this is a database error (e.g., foreign key violation in APK mode)
            $isDatabaseError = false;
            if (str_contains($e->getMessage(), '23000') || str_contains($e->getMessage(), 'foreign key') || str_contains($e->getMessage(), 'SQLSTATE')) {
                $isDatabaseError = true;
            }

            // If database error and we have valid lines, try external API fallback
            if ($isDatabaseError && !empty($lines) && empty($rejected)) {
                // Build items for external API (same format as JavaScript sends)
                $apiItems = [];
                foreach ($validated['items'] as $rawItem) {
                    $remoteItem = $this->getRemoteItem($rawItem['id'], $request);
                    if (! $remoteItem) {
                        continue;
                    }
                    // We only need id, quantity, and pack_id for the external API
                    $apiItems[] = [
                        'id' => $rawItem['id'],
                        'quantity' => (float) $rawItem['quantity'],
                        'pack_id' => $rawItem['pack_id'] ?? null,
                    ];
                }

                if (!empty($apiItems)) {
                    $externalCheckoutData = [
                        'items' => $apiItems,
                        'delivery_address' => $validated['delivery_address'] ?? '',
                        'delivery_phone' => $validated['delivery_phone'] ?? '',
                        'scheduled_delivery_date' => $validated['scheduled_delivery_date'] ?? null,
                        'notes' => $validated['notes'] ?? '',
                        'survey_rating' => $validated['survey_rating'] ?? null,
                        'survey_feedback' => $validated['survey_feedback'] ?? '',
                    ];

                    $token = session('api_token') ?: $request->bearerToken();
                    if ($token) {
                        try {
                            $response = Http::withToken($token)
                                ->withoutVerifying()
                                ->timeout(10)
                                ->post("{$this->baseUrl}/customer/checkout", $externalCheckoutData);

                            if ($response->successful()) {
                                $responseData = $response->json();

                                if ($request->expectsJson()) {
                                    return response()->json([
                                        'success' => true,
                                        'message' => $responseData['message'] ?? 'تم إنشاء طلبيتك بنجاح!',
                                        'order_id' => $responseData['order']['id'] ?? null,
                                        'order_number' => $responseData['order']['order_number'] ?? null,
                                        'total' => (float) ($responseData['order']['total_amount'] ?? 0),
                                        'redirect' => route('customer.order_history', [], false),
                                        'steps' => [],
                                    ]);
                                }

                                return redirect()
                                    ->route('customer.order_history')
                                    ->with('success', $responseData['message'] ?? 'تم إنشاء طلبيتك بنجاح!')
                                    ->with('order_number', $responseData['order']['order_number'] ?? '');
                            } else {
                                $errorData = $response->json() ?? [];
                                $message = $errorData['message'] ?? 'فشل في إنشاء الطلب';

                                if ($request->expectsJson()) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => $message,
                                    ], 422);
                                }

                                return back()->withInput()->with(['error' => $message]);
                            }
                        } catch (\Exception $ex) {
                            Log::error('Checkout external API fallback failed: ' . $ex->getMessage());
                        }
                    }
                }
            }

            // If we reach here, fallback didn't work or wasn't applicable
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذر إنشاء الطلوية. '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with(['error' => 'تعذر إنشاء الطلوية. '.$e->getMessage()]);
        }
    }
    public function orderHistory(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
            }
            return redirect()->route('customer.login');
        }

        $orders = $customer->orders()
            ->with(['items.item', 'delivery', 'invoices'])
            ->latest()
            ->get();

        // Compute stats
        $stats = [
            'all' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'delivered' => $orders->where('status', 'delivered')->count(),
        ];

        $rows = [];
        foreach ($orders as $order) {
            $statusEnum = OrderStatus::tryFrom($order->status);
            $progress = 0;
            if ($statusEnum) {
                $progress = $statusEnum->value === 'delivered' ? 100 : ($statusEnum->value === 'pending' ? 30 : 0);
            }

            $orderItems = $order->items->count();
            $total = $order->total_amount ?? 0;
            $invoice = $order->invoices->first();
            $quotation = $order->quotation->first();

            // Determine if can respond (has pending quotation with customer-requested-changes status)
            $canRespond = !empty($quotation) && in_array($quotation->status ?? '', [
                'customer_requested_changes', 'pending_review', 'pending_approval'
            ]);

            $rows[] = [
                'id' => $order->id,
                'number' => $order->order_number ?? $order->id,
                'date' => $order->created_at?->format('Y/m/d') ?? '',
                'items_count' => $orderItems,
                'status_text' => $statusEnum?->label() ?? $order->status ?? 'مجهول',
                'status_class' => $statusEnum?->value ?? '',
                'total' => number_format($total, 2),
                'can_respond' => $canRespond,
                'has_invoice' => !empty($invoice),
                'quotation_number' => $quotation?->quotation_number ?? '',
                'progress' => $progress,
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => ['stats' => $stats, 'rows' => $rows],
            ]);
        }

        return view('app_android.more.order_history', compact('orders', 'stats', 'rows'));
    }

    public function orderDetails(Request $request, $id)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
            }
            return redirect()->route('customer.login');
        }

        $order = $customer->orders()
            ->with(['items.item', 'items.pack', 'delivery', 'invoices', 'quotation'])
            ->findOrFail($id);

        $steps = $this->buildOrderSteps($order);
        $timeline = $this->buildOrderTimeline($order);

        $statusEnum = OrderStatus::tryFrom($order->status);
        $progress = 0;
        if ($statusEnum) {
            $progress = $statusEnum->value === 'delivered' ? 100 : ($statusEnum->value === 'pending' ? 30 : 0);
        }

        $orderItems = $order->items->map(function ($item) {
            return [
                'name' => $item->item->name ?? $item->item->name_en ?? 'منتج',
                'code' => $item->item->code ?? '',
                'pack' => $item->pack?->name ?? '',
                'quantity' => $item->quantity,
                'unit_price' => $item->price ?? 0,
                'line_total' => number_format($item->quantity * ($item->price ?? 0), 2),
            ];
        })->toArray();

        $invoice = $order->invoices->first();
        $quotation = $order->quotation->first();

        $statusMeta = [];
        if ($statusEnum) {
            $statusMeta = [
                'label' => $statusEnum->label(),
                'class' => match ($statusEnum->value) {
                    'delivered', 'approved' => 'success',
                    'cancelled', 'rejected' => 'danger',
                    'pending', 'pending_review' => 'warning',
                    'admin_edited' => 'accent',
                    default => 'info',
                },
            ];
        }

        $canRespond = !empty($quotation) && in_array($quotation->status ?? '', [
            'customer_requested_changes', 'pending_review', 'pending_approval'
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'orderItems' => $orderItems,
                    'invoice' => $invoice,
                    'quotation' => $quotation,
                    'statusMeta' => $statusMeta,
                    'canRespond' => $canRespond,
                    'progress' => $progress,
                    'steps' => $steps,
                    'timeline' => $timeline,
                ],
            ]);
        }

        return view('app_android.more.order_details', compact(
            'order', 'orderItems', 'invoice', 'quotation',
            'statusMeta', 'canRespond', 'progress', 'steps', 'timeline'
        ));
    }
    
    public function downloadOrderPdf(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }
        
        $order = $customer->orders()
            ->with(['items.item', 'items.pack', 'delivery', 'invoices'])
            ->findOrFail($id);
        
        return response()->streamDownload(function() use ($order) {
            echo view('pdf.order', compact('order'))->render();
        }, "order_{$order->order_number}.pdf");
    }
    
    public function downloadPdf(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }
        
        $order = $customer->orders()
            ->with(['items.item', 'items.pack', 'delivery', 'invoices'])
            ->findOrFail($id);
        
        $invoice = $order->invoices->first();
        
        if (! $invoice) {
            return back()->with('error', 'لا توجد فاتورة لهذا الطلب');
        }
        
        return response()->streamDownload(function() use ($invoice) {
            echo view('pdf.invoice', compact('invoice'))->render();
        }, "invoice_{$invoice->invoice_number}.pdf");
    }
    
    public function downloadQuotationPdf(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }
        
        $order = $customer->orders()
            ->with(['items.item', 'items.pack', 'delivery', 'invoices'])
            ->findOrFail($id);
        
        $quotation = $order->quotation->first();
        
        if (! $quotation) {
            return back()->with('error', 'لا يوجد عرض أسعار لهذا الطلب');
        }
        
        return response()->streamDownload(function() use ($quotation) {
            echo view('pdf.quotation', compact('quotation'))->render();
        }, "quotation_{$quotation->quotation_number}.pdf");
    }

    private function getOrderStatusValue($order): string
    {
        $status = $order->status;
        if ($status instanceof \App\Enums\OrderStatus) {
            return $status->value;
        }
        return (string) $status;
    }

    private function buildOrderSteps($order): array
    {
        $status = $this->getOrderStatusValue($order);
        
        // Determine current step index
        $stepStatuses = [
            'new' => 0,
            'pending_review' => 1,
            'admin_edited' => 2,
            'quotation_sent' => 2,
            'customer_approved' => 3,
            'approved' => 3,
            'delivered' => 4,
            'cancelled' => 5,
            'rejected' => 5,
            'returned' => 5,
            'expired' => 5,
            'customer_rejected' => 5,
        ];
        
        $currentStepIndex = $stepStatuses[$status] ?? 0;
        $isTerminal = in_array($status, ['cancelled', 'rejected', 'returned', 'expired', 'customer_rejected']);
        
        $steps = [
            ['key' => 'submitted', 'label' => 'تم إرسال الطلب', 'icon' => 'fa-send', 'color' => 'purple'],
            ['key' => 'review', 'label' => 'قيد المراجعة', 'icon' => 'fa-magnifying-glass', 'color' => 'blue'],
            ['key' => 'quotation', 'label' => 'عرض السعر', 'icon' => 'fa-file-invoice-dollar', 'color' => 'indigo'],
            ['key' => 'approval', 'label' => 'موافقة العميل', 'icon' => 'fa-user-check', 'color' => 'emerald'],
            ['key' => 'delivery', 'label' => 'التسليم', 'icon' => 'fa-truck', 'color' => 'green'],
        ];
        
        if ($isTerminal) {
            $steps[] = ['key' => 'cancelled', 'label' => 'ملغي/مرفوض', 'icon' => 'fa-ban', 'color' => 'red'];
        }
        
        // Add state to each step
        foreach ($steps as $index => &$step) {
            if ($index < $currentStepIndex) {
                $step['state'] = 'done';
            } elseif ($index === $currentStepIndex) {
                $step['state'] = $isTerminal ? 'rejected' : 'current';
            } else {
                $step['state'] = 'pending';
            }
        }
        
        return $steps;
    }

    private function buildOrderTimeline($order): array
    {
        $timeline = [];
        $status = $this->getOrderStatusValue($order);
        $createdAt = $order->created_at?->format('Y-m-d');
        
        $timeline[] = ['to' => 'طلب جديد', 'by' => 'النظام', 'date' => $createdAt];
        
        if (in_array($status, ['admin_edited', 'quotation_sent', 'customer_approved', 'approved', 'delivered', 'cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'قيد المراجعة', 'by' => 'الإدارة', 'date' => $createdAt];
        }
        if (in_array($status, ['quotation_sent', 'customer_approved', 'approved', 'delivered', 'cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'عرض السعر', 'by' => 'الإدارة', 'date' => $createdAt];
        }
        if (in_array($status, ['customer_approved', 'approved', 'delivered'])) {
            $timeline[] = ['to' => 'موافقة العميل', 'by' => $order->customer?->name ?? 'العميل', 'date' => $createdAt];
        }
        if ($status === 'delivered') {
            $timeline[] = ['to' => 'تم التسليم', 'by' => 'التوصيل', 'date' => $createdAt];
        }
        if (in_array($status, ['cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'ملغي/مرفوض', 'by' => 'النظام', 'date' => $createdAt];
        }
        
        return $timeline;
    }
}