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
        ?int $warehouseId = null,
        ?int $currencyId = null,
    ): Order {
        return $this->create(
            customer: $customer,
            items: $items,
            deliveryAddress: $deliveryAddress,
            deliveryPhone: $deliveryPhone,
            scheduledDeliveryDate: $scheduledDeliveryDate,
            user: $user,
            warehouseId: $warehouseId,
            currencyId: $currencyId,
        );
    }

    public function create(
        Customer $customer,
        array $items,
        ?string $deliveryAddress = null,
        ?string $deliveryPhone = null,
        ?Carbon $scheduledDeliveryDate = null,
        ?User $user = null,
        ?int $warehouseId = null,
        ?int $currencyId = null,
    ): Order {
        return DB::transaction(function () use (
            $customer, $items, $user, $deliveryAddress, $deliveryPhone, $scheduledDeliveryDate, $warehouseId, $currencyId
        ) {
            $inventoryService = app(InventoryServiceInterface::class);
            $pricingService = app(PricingServiceInterface::class);

            $warehouse = $warehouseId
                ? Warehouse::findOrFail($warehouseId)
                : Warehouse::where('organization_id', $customer->active_organization_id ?? 1)->firstOrFail();

            $resolvedCurrencyId = $currencyId ?? $this->resolveCurrencyId($customer);

            $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) (Order::count() + 1), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'currency_id' => $resolvedCurrencyId,
                'status' => OrderStatus::New->value,
                'price_list_snapshot_id' => $customer->price_list_id,
                'exchange_rate' => 1.0,
                'delivery_address' => $deliveryAddress,
                'delivery_phone' => $deliveryPhone,
                'scheduled_delivery_date' => $scheduledDeliveryDate,
                'created_by' => $user?->id,
                'notes' => null,
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => 0,
                'shipping_fee' => 0,
                'total_amount' => 0,
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
                    itemPackId: $itemData['item_pack_id'] ? (int) $itemData['item_pack_id'] : null,
                );

                if ($availability->available <= 0) {
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
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit_price' => $priceResult->unitPrice,
                    'discount_percent' => 0,
                    'discount_amount' => $priceResult->discount ?? 0,
                    'tax_percent' => $priceResult->taxPercent ?? 0,
                    'tax_amount' => $priceResult->taxAmount ?? 0,
                    'line_total' => ($priceResult->finalUnitPrice * ($itemData['quantity'] ?? 1)) + ($priceResult->taxAmount ?? 0),
                    'subtotal' => $priceResult->finalUnitPrice * ($itemData['quantity'] ?? 1),
                    'price_source' => $priceResult->source->value,
                    'price_list_item_id' => $priceResult->priceListItemId,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::New->value,
                'changed_by' => $user?->id,
                'notes' => 'Order created',
            ]);

            $this->recalculateTotals($order);

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
                itemPackId: $itemData['item_pack_id'] ? (int) $itemData['item_pack_id'] : null,
            );

            if ($availability->available <= 0) {
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
                'warehouse_id' => $order->warehouse_id,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $priceResult->unitPrice,
                'discount_percent' => 0,
                'discount_amount' => $priceResult->discount ?? 0,
                'tax_percent' => $priceResult->taxPercent ?? 0,
                'tax_amount' => $priceResult->taxAmount ?? 0,
                'line_total' => ($priceResult->finalUnitPrice * ($itemData['quantity'] ?? 1)) + ($priceResult->taxAmount ?? 0),
                'subtotal' => $priceResult->finalUnitPrice * ($itemData['quantity'] ?? 1),
                'price_source' => $priceResult->source->value,
                'price_list_item_id' => $priceResult->priceListItemId,
            ]);

            $this->recalculateTotals($order);

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
            'discount_amount' => $data['discount_amount'] ?? $orderItem->discount_amount,
        ]);

        $subtotal = ($orderItem->unit_price * $orderItem->quantity) - $orderItem->discount_amount;
        $orderItem->update(['subtotal' => $subtotal]);

        $this->recalculateTotals($order);

        return $orderItem->fresh();
    }

    private function recalculateTotals(Order $order): void
    {
        $items = $order->items;
        $subtotal = $items->sum('subtotal');
        $discountTotal = $items->sum('discount_amount');
        $taxTotal = $items->sum('tax_amount');
        $total = $subtotal - $discountTotal + $taxTotal;

        $order->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'total_amount' => $total,
        ]);
    }

    private function resolveCurrencyId(Customer $customer): int
    {
        return $customer->priceList?->currency_id ?? \App\Models\Currency::first()->id ?? 1;
    }

    /**
     * Create a new quotation from an existing order
     */
    public function createQuotationFromOrder(Order $order, Customer $customer): \App\Models\Quotation
    {
        return DB::transaction(function () use ($order, $customer) {
            $quotationNumber = 'QUO-' . now()->format('Ymd') . '-' . str_pad((string) (\App\Models\Quotation::count() + 1), 6, '0', STR_PAD_LEFT);

            $quotation = \App\Models\Quotation::create([
                'quotation_number' => $quotationNumber,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'created_by' => auth()->id(),
                'status' => 'draft',
                'valid_until' => now()->addDays(30),
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'total' => $order->total,
                'notes' => 'عرض سعر بناءً على طلبية رقم: ' . $order->order_number,
            ]);

            foreach ($order->items as $orderItem) {
                \App\Models\QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $orderItem->item_id,
                    'item_pack_id' => $orderItem->item_pack_id,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'discount_percent' => $orderItem->discount_percent ?? 0,
                    'discount_amount' => $orderItem->discount_amount ?? 0,
                    'tax_percent' => $orderItem->tax_percent ?? 0,
                    'tax_amount' => $orderItem->tax_amount ?? 0,
                    'line_total' => $orderItem->line_total,
                    'subtotal' => $orderItem->subtotal ?? ($orderItem->quantity * $orderItem->unit_price),
                ]);
            }

            return $quotation;
        });
    }
}