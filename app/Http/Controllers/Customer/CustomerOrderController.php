<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
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
            ->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        }

        return view('app_android.order_history', compact('orders'));
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
            ->with(['items.item', 'items.pack', 'delivery', 'invoices'])
            ->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        }

        return view('app_android.order_details', compact('order'));
    }
}