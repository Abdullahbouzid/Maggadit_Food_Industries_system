<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ReturnServiceInterface;
use App\DTOs\PriceResult;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\WalletTransactionType;
use App\Events\ReturnProcessed;
use App\Events\ReturnRequested;
use App\Models\AuditLog;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceItemBatch;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnService implements ReturnServiceInterface
{
    /**
     * Create a return request from an invoice.
     */
    public function createRequest(
        Invoice $invoice,
        Customer $customer,
        string $type,
        string $reason,
        array $items,
        ?User $user = null,
    ): ReturnRequest {
        return DB::transaction(function () use ($invoice, $customer, $type, $reason, $items, $user) {
            $returnRequest = ReturnRequest::create([
                'return_number' => $this->generateReturnNumber(),
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'type' => $type,
                'reason' => $reason,
                'status' => 'pending',
                'requested_by' => $user?->id,
                'notes' => null,
            ]);

            foreach ($items as $item) {
                $invoiceItem = InvoiceItem::findOrFail($item['invoice_item_id']);
                $batchItemId = $item['batch_item_id'] ?? $invoiceItem->batch_item_id;

                ReturnItem::create([
                    'return_request_id' => $returnRequest->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'item_id' => $invoiceItem->item_id,
                    'item_pack_id' => $invoiceItem->item_pack_id,
                    'batch_item_id' => $batchItemId,
                    'quantity_returned' => $item['quantity'],
                    'unit_price' => (float) $invoiceItem->unit_price,
                    'refund_amount' => (float) ($invoiceItem->unit_price * $item['quantity']),
                    'condition' => $item['condition'] ?? 'good',
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            event(new ReturnRequested($returnRequest, $user));

            $this->recordAudit($returnRequest, 'created', null, $returnRequest);

            return $returnRequest;
        });
    }

    /**
     * Approve a return request.
     */
    public function approve(ReturnRequest $request, ?User $reviewer = null, ?string $notes = null): ReturnRequest
    {
        $this->ensureStatus($request, ['pending']);

        $request->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
        ]);

        $this->recordAudit($request, 'approved', ['status' => 'pending'], $request);

        return $request;
    }

    /**
     * Reject a return request.
     */
    public function reject(ReturnRequest $request, ?User $reviewer = null, ?string $reason = null): ReturnRequest
    {
        $this->ensureStatus($request, ['pending']);

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
            'notes' => $reason,
        ]);

        $this->recordAudit($request, 'rejected', null, $request);

        return $request;
    }

    /**
     * Process an approved return request.
     * Creates a CreditNote, returns inventory, refunds wallet if applicable.
     */
    public function process(ReturnRequest $request): CreditNote
    {
        $this->ensureStatus($request, ['approved']);

        return DB::transaction(function () use ($request) {
            $invoice = $request->invoice;
            $customer = $request->customer;
            $currencyId = $invoice->currency_id ?? $customer->price_list_id;
            $currency = \App\Models\Currency::find($currencyId);

            $totalRefund = 0.0;

            foreach ($request->items as $returnItem) {
                app(InventoryService::class)->receiveReturn($returnItem);
                $totalRefund += (float) $returnItem->refund_amount;
            }

            $creditNote = CreditNote::create([
                'credit_note_number' => $this->generateCreditNoteNumber(),
                'return_request_id' => $request->id,
                'customer_id' => $customer->id,
                'currency_id' => $currency->id ?? 1,
                'total' => $totalRefund,
                'issued_at' => now(),
                'applied_to_invoice_id' => $invoice->id,
                'notes' => null,
            ]);

            $request->update(['status' => 'processed']);

            if ($customer->wallet_enabled) {
                app(WalletService::class)->refund(
                    $customer,
                    $totalRefund,
                    $creditNote,
                    null,
                    'Refund from return #'.$request->return_number,
                );
            }

            event(new ReturnProcessed($request, $creditNote));

            $this->recordAudit($request, 'processed', null, $request);
            $this->recordAudit($creditNote, 'created', null, $creditNote);

            return $creditNote;
        });
    }

    /**
     * Apply a credit note to an invoice.
     */
    public function applyToInvoice(CreditNote $creditNote, ?Invoice $invoice = null): Invoice
    {
        return DB::transaction(function () use ($creditNote, $invoice) {
            $invoice = $invoice ?? $creditNote->appliedToInvoice ?? $creditNote->returnRequest->invoice;

            $remainingPayment = (float) $invoice->total_amount - (float) $invoice->paid_amount;

            $appliedAmount = min((float) $creditNote->total, $remainingPayment);

            if ($invoice instanceof Invoice && $invoice->payment_type !== PaymentType::Cash->value) {
                throw new \Exception('Credit notes can only be applied to cash invoices.');
            }

            $invoice->update([
                'paid_amount' => $invoice->paid_amount + $appliedAmount,
                'status' => $invoice->paid_amount + $appliedAmount >= $invoice->total_amount
                    ? InvoiceStatus::Paid->value
                    : InvoiceStatus::PartiallyPaid->value,
            ]);

            $this->recordAudit($invoice, 'credit_note_applied', null, $invoice);

            return $invoice;
        });
    }

    /**
     * Generate a unique return number.
     */
    private function generateReturnNumber(): string
    {
        return 'RET-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique credit note number.
     */
    private function generateCreditNoteNumber(): string
    {
        return 'CN-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ensure the return request is in an allowed status.
     */
    private function ensureStatus(ReturnRequest $request, array $allowed): void
    {
        if (!in_array($request->status, $allowed, true)) {
            throw new \Exception(sprintf(
                "Return request #{$request->id} is in status '%s'. Allowed: %s",
                $request->status,
                implode(', ', $allowed),
            ));
        }
    }

    /**
     * Record audit log entry.
     */
    private function recordAudit($model, string $event, ?array $oldValues, $newModel): void
    {
        AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newModel instanceof \Illuminate\Database\Eloquent\Model ? $newModel->toArray() : ['value' => $newModel],
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
