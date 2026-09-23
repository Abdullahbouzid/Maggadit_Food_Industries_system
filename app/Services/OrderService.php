<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Contracts\InventoryServiceInterface;
use App\Contracts\PricingServiceInterface;
use App\DTOs\AvailabilityResult;
use App\DTOs\PriceResult;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\NoPriceAvailableException;
use App\Models\Customer;
use App\Models\CustomerPriceList;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Warehouse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    public function createFromCustomer(
        Customer $customer,
        array $items,
        ?User $user = null,
        ?string $deliveryAddress = null,
        ?string $deliveryPhone = null,
        ?Carbon $scheduledDeliveryDate = null,
    ): Order {
        return $this->create(
            customer: $customer,
            items: $items,
            deliveryAddress: $deliveryAddress,
            deliveryPhone: $deliveryPhone,
            scheduledDeliveryDate: $scheduledDeliveryDate,
            user: $user,
        );
    }

    public function create(
        Customer $customer,
        array $items,
        ?string $deliveryAddress = null,
        ?string $deliveryPhone = null,
        ?Carbon $scheduledDeliveryDate = null,
        ?User $user = null,
    ): Order {
        return DB::transaction(function () use (
            $customer, $items, $user, $deliveryAddress, $deliveryPhone, $scheduledDeliveryDate
        ) {
            $inventoryService = app(InventoryServiceInterface::class);
            $pricingService = app(PricingServiceInterface::class);

            $warehouse = Warehouse::where('organization_id', $customer->active_organization_id ?? 1)
                ->firstOrFail();

            $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) (Order::count() + 1), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'status' => OrderStatus::New->value,
                'price_list_snapshot_id' => $customer->price_list_id,
                'delivery_address' => $deliveryAddress,
                'delivery_phone' => $deliveryPhone,
                'scheduled_delivery_date' => $scheduledDeliveryDate,
                'created_by' => $user?->id,
                'notes' => null,
            ]);

            foreach ($items as $itemData) {
                $item = Item::find($itemData['item_id'])
                    ?? throw new ItemNotFoundException(itemId: $itemData['item_id']);

                $itemPack = null;
                if (isset($itemData['item_pack_id'])) {
                    $itemPack = ItemPack::find($itemData['item_pack_id']);
                    if (!$itemPack) {
                        throw new ItemNotFoundException(itemId: $itemData['item_id'], itemPackId: $itemData['item_pack_id']);
                    }
                }

                $availability = $inventoryService->checkAvailability(
                    itemId: $item->id,
                    warehouseId: $warehouse->id,
                    quantity: (float) ($itemData['quantity'] ?? 1),
                    itemPackId: $itemData['item_pack_id'] ?? null,
                );

                if (!$availability->isAvailable()) {
                    throw new InsufficientStockException(
                        requested: (float) ($itemData['quantity'] ?? 1),
                        available: $availability->available,
                        itemId: $item->id,
                        warehouseId: $warehouse->id,
                    );
                }

                $priceResult = $pricingService->calculatePrice(
                    item: $item,
                    customer: $customer,
                    quantity: (float) ($itemData['quantity'] ?? 1),
                    itemPack: $itemPack,
                );

                if (!$priceResult->unitPrice) {
                    throw new NoPriceAvailableException(itemId: $item->id);
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_pack_id' => $itemPack?->id,
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit_price' => $priceResult->unitPrice,
                    'unit_cost' => $priceResult->unitCost ?? 0,
                    'discount_value' => $priceResult->discountAmount ?? 0,
                    'tax_percent' => $priceResult->taxPercent ?? 0,
                    'subtotal' => $priceResult->subtotal,
                    'total' => $priceResult->total,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::New->value,
                'changed_by' => $user?->id,
                'notes' => 'Order created',
            ]);

            return $order;
        });
    }

    public function addItem(Order $order, array $itemData, ?User $user = null): OrderItem
    {
        return DB::transaction(function () use ($order, $itemData, $user) {
            $inventoryService = app(InventoryServiceInterface::class);
            $pricingService = app(PricingServiceInterface::class);

            $item = Item::find($itemData['item_id'])
                ?? throw new ItemNotFoundException(itemId: $itemData['item_id']);

            $itemPack = null;
            if (isset($itemData['item_pack_id'])) {
                $itemPack = ItemPack::find($itemData['item_pack_id']);
                if (!$itemPack) {
                    throw new ItemNotFoundException(itemId: $itemData['item_id'], itemPackId: $itemData['item_pack_id']);
                }
            }

            $availability = $inventoryService->checkAvailability(
                itemId: $item->id,
                warehouseId: $order->warehouse_id,
                quantity: (float) ($itemData['quantity'] ?? 1),
                itemPackId: $itemData['item_pack_id'] ?? null,
            );

            if (!$availability->isAvailable()) {
                throw new InsufficientStockException(
                    requested: (float) ($itemData['quantity'] ?? 1),
                    available: $availability->available,
                    itemId: $item->id,
                    warehouseId: $order->warehouse_id,
                );
            }

            $priceResult = $pricingService->calculatePrice(
                item: $item,
                customer: $order->customer,
                quantity: (float) ($itemData['quantity'] ?? 1),
                itemPack: $itemPack,
            );

            if (!$priceResult->unitPrice) {
                throw new NoPriceAvailableException($item->name);
            }

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item->id,
                'item_pack_id' => $itemPack?->id,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $priceResult->unitPrice,
                'unit_cost' => $priceResult->unitCost ?? 0,
                'discount_value' => $priceResult->discountAmount ?? 0,
                'tax_percent' => $priceResult->taxPercent ?? 0,
                'subtotal' => $priceResult->subtotal,
                'total' => $priceResult->total,
            ]);

            return $orderItem;
        });
    }

    public function cancel(Order $order, ?string $reason = null, ?User $user = null): Order
    {
        $order->update([
            'status' => OrderStatus::Cancelled->value,
            'notes' => $reason ?? $order->notes,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => OrderStatus::from($order->getOriginal('status'))->value ?? null,
            'to_status' => OrderStatus::Cancelled->value,
            'changed_by' => $user?->id,
            'notes' => $reason,
        ]);

        return $order->fresh();
    }

    public function updateItem(Order $order, OrderItem $orderItem, array $data): OrderItem
    {
        $orderItem->update([
            'quantity' => $data['quantity'] ?? $orderItem->quantity,
            'unit_price' => $data['unit_price'] ?? $orderItem->unit_price,
            'discount_value' => $data['discount_value'] ?? $orderItem->discount_value,
        ]);

        $subtotal = ($orderItem->unit_price * $orderItem->quantity) - $orderItem->discount_value;
        $orderItem->update(['subtotal' => $subtotal]);

        return $orderItem->fresh();
    }
}
