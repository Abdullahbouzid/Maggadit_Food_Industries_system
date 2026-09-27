<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Order;
use App\Models\PriceList;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::query()->with(['customer', 'currency', 'warehouse', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $statusCounts = [];
        foreach (OrderStatus::cases() as $status) {
            $statusCounts[$status->value] = Order::where('status', $status->value)->count();
        }

        $filters = [
            'status' => $request->status,
            'customer_id' => $request->customer_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'search' => $request->search,
        ];

        return view('dashboard.orders.index', compact('orders', 'filters', 'statusCounts'));
    }

    public function create()
    {
        $customers = Customer::with(['priceList', 'wallets' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name_en')->get();
        $items = Item::with(['packs', 'packs.packType', 'packs.packCapacity'])
            ->where('is_inactive', false)
            ->orderBy('name_ar')
            ->get();
        $currencies = Currency::orderBy('code')->get();
        $priceLists = PriceList::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.orders.create', compact('customers', 'warehouses', 'items', 'currencies', 'priceLists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_pack_id' => 'nullable|exists:item_packs,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'scheduled_delivery_date' => 'nullable|date',
        ]);

        try {
            $customer = Customer::findOrFail($validated['customer_id']);

            $order = $this->orderService->createFromCustomer(
                $customer,
                $validated['items'],
                auth()->user(),
                $request->input('delivery_address'),
                $request->input('delivery_phone'),
                isset($validated['scheduled_delivery_date'])
                    ? \Carbon\Carbon::parse($validated['scheduled_delivery_date'])
                    : null,
                (int) $validated['warehouse_id'],
                (int) $validated['currency_id'],
            );

            return redirect()->route('orders.show', $order)
                ->with('success', 'تم إنشاء الطلب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'currency', 'items', 'quotation', 'delivery', 'statusHistory']);

        return view('dashboard.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['customer', 'currency', 'items.item', 'items.pack', 'items.pack.packType', 'items.pack.packCapacity', 'warehouse']);
        $customers = Customer::with(['priceList', 'wallets' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name_en')->get();
        $items = Item::with(['packs', 'packs.packType', 'packs.packCapacity'])
            ->where('is_inactive', false)
            ->orderBy('name_ar')
            ->get();
        $currencies = Currency::orderBy('code')->get();
        $priceLists = PriceList::where('status', 'active')->orderBy('name')->get();

        $itemsData = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name_en' => $item->name_en,
                'name_ar' => $item->name_ar,
                'code' => $item->code,
                'packs' => $item->packs->map(function ($pack) {
                    return [
                        'id' => $pack->id,
                        'name' => $pack->name ?? $pack->packDefinition->name ?? '',
                        'pack_type' => $pack->packType->name_ar ?? '',
                        'capacity' => $pack->packCapacity->formatted_value ?? '',
                        'price' => $pack->price ?? 0,
                        'quantity_in_pack' => $pack->quantity_in_pack ?? 1,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return view('dashboard.orders.edit', compact('order', 'customers', 'warehouses', 'items', 'currencies', 'priceLists', 'itemsData'));
    }

    public function update(Request $request, Order $order)
    {
        $status = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);
        if ($status && in_array($status->value, ['delivered', 'cancelled', 'returned', 'expired'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'لا يمكن تعديل الطلب في هذه الحالة');
        }

        // TODO: Replace with OrderService when available
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_pack_id' => 'nullable|exists:item_packs,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'scheduled_delivery_date' => 'nullable|date',
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                $order->update([
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'currency_id' => $validated['currency_id'],
                    'notes' => $validated['notes'] ?? null,
                    'scheduled_delivery_date' => $validated['scheduled_delivery_date'] ?? null,
                ]);

                $order->items()->delete();

                foreach ($validated['items'] as $itemData) {
                    $order->items()->create([
                        'item_id' => $itemData['item_id'],
                        'item_pack_id' => $itemData['item_pack_id'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_percent' => $itemData['discount_percent'] ?? 0,
                        'line_total' => ($itemData['quantity'] * $itemData['unit_price']) * (1 - ($itemData['discount_percent'] ?? 0) / 100),
                    ]);
                }
            });

            return redirect()->route('orders.show', $order)->with('success', 'تم تحديث الطلب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Order $order)
    {
        if ($order->invoice()->exists()) {
            return redirect()->route('orders.index')->with('error', 'لا يمكن حذف الطلب لوجود فاتورة مرتبطة به');
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', 'تم حذف الطلب بنجاح');
    }

    /**
     * Get products available in a warehouse
     */
    public function warehouseProducts($warehouseId)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Get products from BatchItemStock (production batches) - same as warehouse show page
        $stocks = \App\Models\BatchItemStock::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with(['batchItem.item', 'batchItem.pack', 'batchItem.pack.packType', 'batchItem.pack.packCapacity', 'batchItem.batch'])
            ->get()
            ->filter(function ($stock) {
                return $stock->batchItem !== null
                    && $stock->batchItem->item !== null
                    && $stock->batchItem->batch !== null;
            });

        // Also get inventory records (for non-batch products)
        $inventories = Inventory::with(['item', 'pack', 'pack.packType', 'pack.packCapacity'])
            ->where('warehouse_id', $warehouseId)
            ->where('quantity_on_hand', '>', 0)
            ->get();

        // Group batch stocks by item_pack
        $groupedBatches = $stocks->groupBy(function ($stock) {
            return $stock->batchItem->item_pack_id ?? 0;
        });

        $batchProducts = $groupedBatches->map(function ($stockGroup) {
            $first = $stockGroup->first();
            $batchItem = $first->batchItem;
            $item = $batchItem->item;
            $pack = $batchItem->pack;

            // Skip if pack is null
            if (!$pack) {
                return null;
            }

            $quantityInPack = $pack->quantity_in_pack ?? 1;

            $totalPacks = $stockGroup->sum('quantity');
            $totalUnits = $totalPacks * $quantityInPack;

            $batches = $stockGroup->map(function ($stock) use ($quantityInPack) {
                $batch = $stock->batchItem->batch;
                $packQty = $stock->batchItem->pack?->quantity_in_pack ?? 1;
                return [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number ?? '',
                    'pack_count' => $stock->quantity,
                    'expiry_date' => $batch->expiry_date ?? null,
                ];
            })->values();

            $imageUrl = $item->images->first()?->image_url ?? null;

            return [
                'item_id' => $item->id,
                'item_name' => $item->name_ar ?? $item->name_en ?? '',
                'item_name_ar' => $item->name_ar ?? '',
                'item_code' => $item->code ?? '',
                'pack_id' => $pack->id,
                'pack_name' => $pack->getDisplayNameAttribute() ?? '',
                'pack_type' => $pack->packType->name_ar ?? '',
                'pack_capacity' => $pack->packCapacity->formatted_value ?? '',
                'quantity_in_pack' => $quantityInPack,
                'price' => $pack->price ?? 0,
                'available_qty' => $totalPacks,
                'image_url' => $imageUrl ? asset('storage/' . $imageUrl) : null,
                'barcode' => $pack->barcode ?? '',
                'batches' => $batches,
                'batches_count' => $batches->count(),
                'source' => 'batch',
            ];
        })->filter(fn ($p) => $p !== null && $p['available_qty'] > 0)->values();

        // Add inventory products (excluding those already from batches)
        $batchItemPackIds = $batchProducts->pluck('pack_id')->toArray();

        $inventoryProducts = $inventories->map(function ($inv) {
            $available = max(0, ($inv->quantity_on_hand ?? 0) - ($inv->reserved_quantity ?? 0));
            $imageUrl = $inv->item->images->first()?->image_url ?? null;

            return [
                'item_id' => $inv->item_id,
                'item_name' => $inv->item->name_ar ?? $inv->item->name_en ?? '',
                'item_name_ar' => $inv->item->name_ar ?? '',
                'item_code' => $inv->item->code ?? '',
                'pack_id' => $inv->item_pack_id,
                'pack_name' => $inv->pack?->getDisplayNameAttribute() ?? '',
                'pack_type' => $inv->pack?->packType->name_ar ?? '',
                'pack_capacity' => $inv->pack?->packCapacity->formatted_value ?? '',
                'quantity_in_pack' => $inv->pack?->quantity_in_pack ?? 1,
                'price' => $inv->pack?->price ?? 0,
                'available_qty' => $available,
                'image_url' => $imageUrl ? asset('storage/' . $imageUrl) : null,
                'barcode' => $inv->pack?->barcode ?? '',
                'batches' => [],
                'batches_count' => 0,
                'source' => 'inventory',
            ];
        })->filter(function ($p) use ($batchItemPackIds) {
            return $p['available_qty'] > 0 && !in_array($p['pack_id'], $batchItemPackIds);
        })->values();

        return response()->json($batchProducts->merge($inventoryProducts)->values());
    }

    /**
     * Get price from price list for specific item and pack
     */
    public function priceListPrice(Request $request, $priceListId)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'pack_id' => 'required|exists:item_packs,id',
        ]);

        $priceList = PriceList::findOrFail($priceListId);

        $activeVersion = $priceList->versions()
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', now())
            ->latest('effective_date')
            ->first();

        if (!$activeVersion) {
            return response()->json(['price' => 0]);
        }

        $priceItem = PriceListVersionItem::where('price_list_version_id', $activeVersion->id)
            ->where('item_id', $request->item_id)
            ->where('item_pack_id', $request->pack_id)
            ->first();

        return response()->json([
            'price' => $priceItem?->price ?? 0,
        ]);
    }

    /**
     * Send new price quote to customer
     */
    public function sendNewPriceQuote(Request $request, Order $order)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_pack_id' => 'nullable|exists:item_packs,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);

            // Create a new quotation based on the order
            $quotation = $this->orderService->createQuotationFromOrder($order, $customer);

            return redirect()->route('orders.show', $order)
                ->with('success', 'تم إرسال عرض السعر الجديد بنجاح. رقم العرض: ' . $quotation->quotation_number);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
