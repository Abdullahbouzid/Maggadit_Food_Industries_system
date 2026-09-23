<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\QuotationServiceInterface;
use App\Contracts\PricingServiceInterface;
use App\DTOs\QuotedLine;
use App\Enums\OrderStatus;
use App\Enums\PriceSource;
use App\Events\QuotationApproved;
use App\Events\QuotationSent;
use App\Exceptions\ItemNotFoundException;
use App\Helpers\UnitOfWork;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PriceListVersionItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuotationService implements QuotationServiceInterface
{
    public function createFromOrder(Order $order, ?User $user = null): Quotation
    {
        return DB::transaction(function () use ($order, $user) {
            $latestVersion = Quotation::where('order_id', $order->id)->max('version');
            $version = ($latestVersion ?? 0) + 1;

            $pricingService = app(PricingServiceInterface::class);
            $quotedLines = $pricingService->bulkQuote(
                $order->customer,
                $order->items->map(fn ($item) => [
                    'item_id' => $item->item_id,
                    'item_pack_id' => $item->item_pack_id,
                    'quantity' => $item->quantity,
                    'currency_id' => $order->currency_id,
                ])->toArray(),
            );

            $quotation = Quotation::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'version' => $version,
                'currency_id' => $order->currency_id,
                'exchange_rate' => $order->exchange_rate,
                'subtotal' => $quotedLines->sum(fn (QuotedLine $line) => $line->lineTotal),
                'discount_total' => $quotedLines->sum(fn (QuotedLine $line) => $line->lineTotal * $line->discount / 100),
                'tax_total' => 0,
                'total' => $quotedLines->sum(fn (QuotedLine $line) => $line->finalUnitPrice * $line->quantity),
                'valid_until' => now()->addDays(7),
                'customer_response' => 'pending',
                'created_by' => $user?->id,
            ]);

            foreach ($quotedLines as $line) {
                $itemNameSnapshot = \App\Models\Item::find($line->itemId)->name_ar ?? null;
                $packNameSnapshot = \App\Models\ItemPack::find($line->itemPackId)->getDisplayNameAttribute() ?? null;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $line->itemId,
                    'item_pack_id' => $line->itemPackId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'discount_percent' => $line->discount,
                    'line_total' => $line->lineTotal,
                    'item_name_snapshot' => $itemNameSnapshot,
                    'pack_name_snapshot' => $packNameSnapshot,
                ]);
            }

            return $quotation;
        });
    }

    public function send(Quotation $quotation, ?User $user = null): void
    {
        DB::transaction(function () use ($quotation, $user) {
            $quotation->update(['sent_at' => now()]);

            OrderStatusHistory::create([
                'order_id' => $quotation->order_id,
                'from_status' => $quotation->order->status ?? null,
                'to_status' => OrderStatus::QuotationSent->value,
                'changed_by' => $user?->id,
                'notes' => 'Quotation sent',
            ]);

            event(new QuotationSent($quotation, $user));
        });
    }

    public function recordCustomerResponse(
        Quotation $quotation,
        string $response,
        ?string $notes = null,
    ): Quotation {
        if (!in_array($response, ['approved', 'rejected', 'changes_requested'], true)) {
            throw new \InvalidArgumentException("Invalid customer response: {$response}");
        }

        return DB::transaction(function () use ($quotation, $response, $notes) {
            $quotation->update([
                'customer_response' => $response,
                'customer_response_at' => now(),
                'customer_notes' => $notes,
            ]);

            if ($response === 'approved') {
                event(new QuotationApproved($quotation));
            }

            return $quotation;
        });
    }
}
