<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Traces, per item and pack, exactly which production batches a customer drew
 * stock from, how much was invoiced, how much came back, and whether that batch
 * has since expired. This is what makes a return traceable to a batch.
 */
class CustomerBatchTraceService
{
    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function forCustomer(Customer $customer)
    {
        $rows = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy(
                'invoice_items.item_id',
                'invoice_items.item_pack_id',
                'items.name_ar',
                'items.name_en',
                'items.code',
                'pack_types.name'
            )
            ->selectRaw(
                'invoice_items.item_id, invoice_items.item_pack_id,
                 COALESCE(items.name_ar, items.name_en) as item_name,
                 items.code,
                 COALESCE(pack_types.name, "—") as pack_name,
                 SUM(invoice_items.quantity) as invoiced_quantity,
                 SUM(invoice_items.line_total) as invoiced_value,
                 COUNT(DISTINCT invoices.id) as invoice_count'
            )
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->leftJoin('item_packs', 'item_packs.id', '=', 'invoice_items.item_pack_id')
            ->leftJoin('pack_types', 'pack_types.id', '=', 'item_packs.pack_type_id')
            ->orderBy('items.name_ar')
            ->get();

        $batchRows = $this->batchesForCustomer($customer);
        $returned = $this->returnedPerBatch($customer);

        return $rows->map(function ($row) use ($batchRows, $returned) {
            $batches = $batchRows->get((int) $row->item_id, collect())
                ->where('item_pack_id', (int) $row->item_pack_id)
                ->values();

            return (object) [
                'item_id' => (int) $row->item_id,
                'item_pack_id' => (int) $row->item_pack_id,
                'item_name' => $row->item_name ?: 'منتج',
                'code' => $row->code ?: '—',
                'pack_name' => $row->pack_name,
                'invoiced_quantity' => (float) $row->invoiced_quantity,
                'invoiced_value' => (float) $row->invoiced_value,
                'invoice_count' => (int) $row->invoice_count,
                'returned_quantity' => round($returned
                    ->where('item_pack_id', (int) $row->item_pack_id)
                    ->sum('quantity'), 2),
                'batches' => $batches->map(function ($batch) use ($returned) {
                    $back = (float) $returned
                        ->where('batch_item_id', $batch->batch_item_id)
                        ->sum('quantity');

                    $expiry = $batch->expiry_date
                        ? Carbon::parse($batch->expiry_date)
                        : null;

                    return (object) [
                        'batch_item_id' => (int) $batch->batch_item_id,
                        'batch_number' => $batch->batch_number ?: '—',
                        'expiry_date' => $batch->expiry_date,
                        'dispatched' => (float) $batch->quantity,
                        'returned' => $back,
                        'net' => round((float) $batch->quantity - $back, 3),
                        'is_expired' => $expiry !== null && $expiry->isPast(),
                        'days_to_expiry' => $expiry !== null
                            ? (int) Carbon::now()->startOfDay()->diffInDays($expiry, false)
                            : null,
                    ];
                })->values(),
            ];
        });
    }

    /**
     * Quantity of each batch that was invoiced to this customer.
     */
    private function batchesForCustomer(Customer $customer)
    {
        return DB::table('invoice_item_batches')
            ->join('invoice_items', 'invoice_items.id', '=', 'invoice_item_batches.invoice_item_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('batch_items', 'batch_items.id', '=', 'invoice_item_batches.batch_item_id')
            ->leftJoin('production_batches', 'production_batches.id', '=', 'batch_items.batch_id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy(
                'invoice_items.item_id',
                'invoice_items.item_pack_id',
                'invoice_item_batches.batch_item_id',
                'batch_items.id',
                'production_batches.batch_number',
                'batch_items.expiry_date'
            )
            ->selectRaw(
                'invoice_items.item_id,
                 invoice_items.item_pack_id,
                 batch_items.id as batch_item_id,
                 COALESCE(production_batches.batch_number, "—") as batch_number,
                 batch_items.expiry_date,
                 SUM(invoice_item_batches.quantity) as quantity'
            )
            ->orderBy('batch_items.expiry_date')
            ->get()
            ->groupBy('item_id');
    }

    /**
     * Quantity returned per batch, so the net per batch stays accurate.
     */
    private function returnedPerBatch(Customer $customer)
    {
        return DB::table('return_items')
            ->join('return_requests', 'return_requests.id', '=', 'return_items.return_request_id')
            ->where('return_requests.customer_id', $customer->id)
            ->whereNull('return_requests.deleted_at')
            ->whereIn('return_requests.status', ['pending', 'approved', 'processed'])
            ->groupBy('return_items.item_pack_id', 'return_items.batch_item_id')
            ->selectRaw(
                'return_items.item_pack_id, return_items.batch_item_id,
                 SUM(return_items.quantity_returned) as quantity'
            )
            ->get();
    }

    /**
     * Every return raised by this customer, newest first, with its invoice and
     * the lines behind it.
     */
    public function returnsFor(Customer $customer)
    {
        return \App\Models\ReturnRequest::with([
            'invoice',
            'items.item',
            'items.pack',
        ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(50)
            ->get();
    }
}
