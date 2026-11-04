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
            $quotedResult = $pricingService->bulkQuote(
                $order->customer,
                $order->items->map(fn ($item) => [
                    'item_id' => $item->item_id,
                    'item_pack_id' => $item->item_pack_id,
                    'quantity' => $item->quantity,
                    'currency_id' => $order->currency_id,
                ])->toArray(),
            );

            $quotedLines = $quotedResult->lines;

            $quotation = Quotation::create([
                'quotation_number' => $this->generateQuotationNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'version' => $version,
                'currency_id' => $quotedResult->currencyId,
                'exchange_rate' => $order->exchange_rate,
                'status' => 'sent',
                'sent_at' => now(),
                'subtotal' => $quotedLines->sum(fn (QuotedLine $line) => $line->subtotal),
                'discount_total' => $quotedLines->sum(fn (QuotedLine $line) => $line->subtotal * $line->discount / 100),
                'tax_total' => $quotedLines->sum(fn (QuotedLine $line) => $line->taxAmount),
                'total' => $quotedResult->totalAmount,
                'valid_until' => now()->addDays(7),
                'customer_response' => 'pending',
                'created_by' => $user?->id,
            ]);

            foreach ($quotedLines as $line) {
                $itemNameSnapshot = \App\Models\Item::find($line->itemId)?->name_ar;
                $packNameSnapshot = $line->itemPackId
                    ? \App\Models\ItemPack::find($line->itemPackId)?->getDisplayNameAttribute()
                    : null;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $line->itemId,
                    'item_pack_id' => $line->itemPackId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'discount_percent' => $line->discount,
                    'discount_amount' => $line->subtotal * $line->discount / 100,
                    'tax_percent' => $line->taxPercent,
                    'tax_amount' => $line->taxAmount,
                    'line_total' => $line->total,
                    'subtotal' => $line->subtotal,
                    'item_name_snapshot' => $itemNameSnapshot,
                    'pack_name_snapshot' => $packNameSnapshot,
                ]);
            }

            return $quotation;
        });
    }

    /**
     * Generate a unique sequential quotation number: QUO-YYYYMMDD-NNNNNN
     */
    private function generateQuotationNumber(): string
    {
        $prefix = 'QUO-'.now()->format('Ymd').'-';
        $last = Quotation::where('quotation_number', 'like', $prefix.'%')
            ->orderByDesc('quotation_number')
            ->value('quotation_number');

        $sequence = $last ? ((int) substr($last, -6)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
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

    /**
     * Record the customer's response and drive the order state machine.
     *
     * approved            -> order: customer_approved  (auto: invoice_generated -> pending_approval)
     * rejected            -> order: customer_rejected  (auto: cancelled)
     * changes_requested   -> order: customer_requested_changes (auto: admin_edited)
     */
    public function recordCustomerResponse(
        Quotation $quotation,
        string $response,
        ?string $notes = null,
    ): Quotation {
        if (! in_array($response, ['approved', 'rejected', 'changes_requested'], true)) {
            throw new \InvalidArgumentException("Invalid customer response: {$response}");
        }

        if ($quotation->hasCustomerResponded()) {
            throw new \LogicException('تم تسجيل رد العميل على هذا العرض مسبقاً.');
        }

        $quotation = DB::transaction(function () use ($quotation, $response, $notes) {
            $quotation->update([
                'customer_response' => $response,
                'customer_response_at' => now(),
                'customer_notes' => $notes,
            ]);

            if ($response === 'approved') {
                $quotation->update(['status' => 'approved']);
                event(new QuotationApproved($quotation));
            } elseif ($response === 'rejected') {
                $quotation->update(['status' => 'rejected']);
            }

            return $quotation;
        });

        $this->syncOrderWithCustomerResponse($quotation, $response, $notes);

        return $quotation;
    }

    /**
     * Push the customer decision into the order lifecycle.
     */
    private function syncOrderWithCustomerResponse(Quotation $quotation, string $response, ?string $notes): void
    {
        $order = $quotation->order;
        if (! $order) {
            return;
        }

        $target = match ($response) {
            'approved' => OrderStatus::CustomerApproved,
            'rejected' => OrderStatus::CustomerRejected,
            'changes_requested' => OrderStatus::CustomerRequestedChanges,
        };

        $lifecycle = app(\App\Contracts\OrderLifecycleServiceInterface::class);

        $order->refresh();
        if (! $lifecycle->canTransitionTo($order, $target)) {
            return;
        }

        $lifecycle->transition(
            $order,
            $target,
            null,
            $notes ?: 'رد العميل على عرض السعر '.$quotation->quotation_number.': '.$response,
            true,
        );
    }
}
