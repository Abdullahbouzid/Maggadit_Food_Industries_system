<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
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
        $query = Order::query()->with(['customer', 'currency']);

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
        $customers = Customer::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name_en')->get();
        $items = Item::with(['packs', 'packs.packType', 'packs.packCapacity'])
            ->where('is_inactive', false)
            ->orderBy('name_ar')
            ->get();
        $currencies = Currency::orderBy('code')->get();

        return view('dashboard.orders.create', compact('customers', 'warehouses', 'items', 'currencies'));
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
        $customers = Customer::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name_en')->get();
        $items = Item::with(['packs', 'packs.packType', 'packs.packCapacity'])
            ->where('is_inactive', false)
            ->orderBy('name_ar')
            ->get();
        $currencies = Currency::orderBy('code')->get();

        return view('dashboard.orders.edit', compact('order', 'customers', 'warehouses', 'items', 'currencies'));
    }

    public function update(Request $request, Order $order)
    {
        $status = OrderStatus::tryFrom($order->status);
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
}
