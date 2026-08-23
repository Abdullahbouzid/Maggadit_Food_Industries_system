<?php

namespace App\Services\Warehouse;

use App\Models\BatchItemStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function create(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            $invoiceNumber = $data['invoice_number'] ?? $this->generateInvoiceNumber();

            $invoice = Invoice::create([
                'customer_id' => $data['customer_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => 0,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $unitPrice = $itemData['unit_price'] ?? $item->price;
                $quantity = $itemData['quantity'];

                $offer = Offer::where('item_id', $item->id)
                    ->active()
                    ->first();

                $originalUnitPrice = $unitPrice;
                $discountAmount = 0;

                if ($offer) {
                    $unitPrice = $offer->new_price;
                    $discountAmount = $originalUnitPrice - $unitPrice;
                }

                $subtotal = $quantity * $unitPrice;
                $total += $subtotal;

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $item->id,
                    'offer_id' => $offer?->id,
                    'warehouse_id' => $itemData['warehouse_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'original_unit_price' => $originalUnitPrice,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $subtotal,
                ]);
            }

            $invoice->update(['total_amount' => $total]);

            if ($data['status'] ?? null) {
                $invoice->update(['status' => $data['status']]);
            }

            return $invoice->fresh();
        });
    }

    public function update(Invoice $invoice, array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update([
                'customer_id' => $data['customer_id'] ?? $invoice->customer_id,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'status' => $data['status'] ?? $invoice->status,
                'notes' => $data['notes'] ?? $invoice->notes,
            ]);

            $existingItemIds = $invoice->items()->pluck('item_id')->toArray();
            $newItemIds = collect($items)->pluck('item_id')->toArray();

            $itemsToRemove = array_diff($existingItemIds, $newItemIds);
            InvoiceItem::where('invoice_id', $invoice->id)
                ->whereIn('item_id', $itemsToRemove)
                ->delete();

            $total = 0;

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $unitPrice = $itemData['unit_price'] ?? $item->price;
                $quantity = $itemData['quantity'];
                $subtotal = $quantity * $unitPrice;
                $total += $subtotal;

                InvoiceItem::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'item_id' => $item->id],
                    [
                        'warehouse_id' => $itemData['warehouse_id'] ?? null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]
                );
            }

            $invoice->update(['total_amount' => $total]);

            return $invoice->fresh(['items.item', 'items.warehouse']);
        });
    }

    public function cancel(Invoice $invoice): void
    {
        $invoice->update(['status' => 'cancelled']);
    }

    public function getItemWarehouses(int $itemId): array
    {
        $stocks = BatchItemStock::whereHas('batchItem', function ($q) use ($itemId) {
            $q->where('item_id', $itemId);
        })->with('warehouse')->get();

        $warehouses = [];
        foreach ($stocks as $stock) {
            if (! isset($warehouses[$stock->warehouse_id])) {
                $warehouses[$stock->warehouse_id] = [
                    'id' => $stock->warehouse_id,
                    'name' => $stock->warehouse->name ?? 'Unknown',
                    'name_en' => $stock->warehouse->name_en ?? 'Unknown',
                    'quantity' => 0,
                    'available_stock' => 0,
                ];
            }
            $warehouses[$stock->warehouse_id]['quantity'] += $stock->quantity;
            $warehouses[$stock->warehouse_id]['available_stock'] += $stock->quantity;
        }

        return array_values($warehouses);
    }

    public function getAvailableStockForItemInWarehouse(int $itemId, int $warehouseId): float
    {
        return (float) BatchItemStock::whereHas('batchItem', function ($q) use ($itemId) {
            $q->where('item_id', $itemId);
        })->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    public function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('Y').'-'.strtoupper(Str::random(6));
    }
}
