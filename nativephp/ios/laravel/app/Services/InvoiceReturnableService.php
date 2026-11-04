<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

/**
 * Works out how much of each invoice line can still be returned, so both the
 * customer app and the admin customer page agree on the same returnable
 * quantity.
 */
class InvoiceReturnableService
{
    /**
     * @return \Illuminate\Support\Collection<int, array>
     */
    public function lines(Invoice $invoice)
    {
        $alreadyReturned = DB::table('return_items')
            ->join('return_requests', 'return_requests.id', '=', 'return_items.return_request_id')
            ->whereIn('return_requests.status', ['pending', 'approved', 'processed'])
            ->whereNull('return_requests.deleted_at')
            ->where('return_requests.invoice_id', $invoice->id)
            ->groupBy('return_items.invoice_item_id')
            ->selectRaw('return_items.invoice_item_id, SUM(return_items.quantity_returned) as returned')
            ->pluck('returned', 'invoice_item_id');

        return InvoiceItem::where('invoice_id', $invoice->id)
            ->with(['item', 'itemPack.packType', 'itemPack.packCapacity.uom'])
            ->get()
            ->map(function (InvoiceItem $line) use ($alreadyReturned) {
                $quantity = (float) $line->quantity;
                $returned = (float) ($alreadyReturned[$line->id] ?? 0);

                return [
                    'id' => $line->id,
                    'name' => $line->item?->name_ar ?: ($line->item?->name_en ?: 'منتج'),
                    'code' => $line->item?->code ?: '—',
                    'pack' => $line->itemPack?->packType?->name ?: 'وحدة',
                    'quantity' => $quantity,
                    'returned' => $returned,
                    'returnable' => max(0.0, $quantity - $returned),
                    'unit_price' => (float) $line->unit_price,
                    'batch_item_id' => $line->batch_item_id,
                ];
            })
            ->filter(fn (array $line) => $line['returnable'] > 0)
            ->values();
    }
}