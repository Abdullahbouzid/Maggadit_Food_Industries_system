<?php

namespace Database\Seeders;

use App\Enums\DeliveryStage;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The full sales chain: orders -> quotations -> invoices -> payments ->
 * deliveries -> returns/credit notes.
 *
 * Rows are written directly with the models' knowledge of the status
 * vocabulary, because running the real lifecycle services here would also
 * move stock, write reservations and fire notifications.
 */
class SalesLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = DB::table('customers')->orderBy('id')->get();
        $items = DB::table('items')->orderBy('id')->get();
        $packs = DB::table('item_packs')->orderBy('id')->get();
        $warehouses = DB::table('warehouses')->orderBy('id')->get();
        $currency = DB::table('currencies')->where('code', 'LYD')->first();
        $adminId = DB::table('users')->orderBy('id')->value('id');
        $driverId = DB::table('users')->orderBy('id')->value('id');
        $vehicleId = DB::table('vehicles')->orderBy('id')->value('id');

        if ($customers->isEmpty() || $items->isEmpty() || ! $currency) {
            $this->command?->warn('Missing customers/items/currency; skipping sales seeding.');

            return;
        }

        $warehouseId = $warehouses->first()->id;

        // Each entry drives one order, so the counts stay consistent and we
        // can exercise a different point of the lifecycle for every one.
        $flows = [
            OrderStatus::Delivered->value,
            OrderStatus::Delivered->value,
            OrderStatus::InDelivery->value,
            OrderStatus::Approved->value,
            OrderStatus::Approved->value,
            OrderStatus::InvoiceGenerated->value,
            OrderStatus::CustomerApproved->value,
            OrderStatus::QuotationSent->value,
            OrderStatus::PendingReview->value,
            OrderStatus::New->value,
            OrderStatus::Returned->value,
            OrderStatus::Cancelled->value,
        ];

        $created = [];

        foreach ($flows as $index => $finalStatus) {
            $customer = $customers->get($index % $customers->count());

            $order = $this->createOrder(
                $customer,
                $items,
                $packs,
                $warehouseId,
                $currency->id,
                $finalStatus,
                $index,
                $adminId,
            );

            if (! $order) {
                continue;
            }

            $created[] = [
                'order' => $order,
                'customer' => $customer,
                'status' => $finalStatus,
                'admin_id' => $adminId,
                'driver_id' => $driverId,
                'vehicle_id' => $vehicleId,
                'currency_id' => $currency->id,
                'warehouse_id' => $warehouseId,
            ];
        }

        $this->quotations($created);
        $this->invoices($created, $packs, $warehouseId, $currency->id);
        $this->deliveries($created);
        $this->returns($created, $packs, $warehouseId, $currency->id, $adminId);
        $this->creditNotes($currency->id);
    }

    private function createOrder($customer, $items, $packs, $warehouseId, $currencyId, $status, $index, $adminId): ?object
    {
        $number = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

        $existing = DB::table('orders')->where('order_number', $number)->first();
        if ($existing) {
            return $existing;
        }

        $subtotal = 0.0;
        $lines = [];

        for ($n = 0; $n < 3; $n++) {
            $item = $items->get(($index + $n) % $items->count());
            $itemPacks = $packs->where('item_id', $item->id)->values();
            $packId = $itemPacks->isNotEmpty() ? $itemPacks->first()->id : null;
            $quantity = 2 + $n;
            $unitPrice = 5 + (($index + $n) * 1.5);
            $lineTotal = $quantity * $unitPrice;
            $subtotal += $lineTotal;

            $lines[] = compact('item', 'packId', 'quantity', 'unitPrice', 'lineTotal');
        }

        $isCancelled = $status === OrderStatus::Cancelled->value;
        $total = round($subtotal, 2);

        $orderId = DB::table('orders')->insertGetId([
            'order_number' => $number,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouseId,
            'status' => $status,
            'currency_id' => $currencyId,
            'exchange_rate' => 1,
            'delivery_address' => 'شارع الجمهورية، طرابلس',
            'delivery_phone' => $customer->phone,
            'scheduled_delivery_date' => now()->addDays(2)->toDateString(),
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => $total,
            'shipping_fee' => 0,
            'total_amount' => $total,
            'notes' => 'طلب تجريبي مُولَّد',
            'created_by' => $adminId,
            'confirmed_at' => $isCancelled ? null : now()->subDays(3),
            'cancelled_at' => $isCancelled ? now()->subDay() : null,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        foreach ($lines as $line) {
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'item_id' => $line['item']->id,
                'item_pack_id' => $line['packId'],
                'warehouse_id' => $warehouseId,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unitPrice'],
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'line_total' => $line['lineTotal'],
                'subtotal' => $line['lineTotal'],
                'price_source' => 'price_list',
                'notes' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
        }

        // Status history: new -> ... -> final status.
        $path = $this->statusPath($status);
        $from = null;
        foreach ($path as $step) {
            DB::table('order_status_histories')->insert([
                'order_id' => $orderId,
                'from_status' => $from,
                'to_status' => $step,
                'changed_by' => $adminId,
                'notes' => 'تلقائي',
                'created_at' => now()->subDays(4),
            ]);
            $from = $step;
        }

        return DB::table('orders')->where('id', $orderId)->first();
    }

    private function statusPath(string $final): array
    {
        $paths = [
            OrderStatus::New->value => [OrderStatus::New->value],
            OrderStatus::PendingReview->value => [OrderStatus::New->value, OrderStatus::PendingReview->value],
            OrderStatus::QuotationSent->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value],
            OrderStatus::CustomerApproved->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value],
            OrderStatus::InvoiceGenerated->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value, OrderStatus::InvoiceGenerated->value],
            OrderStatus::Approved->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value, OrderStatus::InvoiceGenerated->value, OrderStatus::PendingApproval->value, OrderStatus::Approved->value],
            OrderStatus::InDelivery->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value, OrderStatus::InvoiceGenerated->value, OrderStatus::PendingApproval->value, OrderStatus::Approved->value, OrderStatus::InDelivery->value],
            OrderStatus::Delivered->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value, OrderStatus::InvoiceGenerated->value, OrderStatus::PendingApproval->value, OrderStatus::Approved->value, OrderStatus::InDelivery->value, OrderStatus::Delivered->value],
            OrderStatus::Returned->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value, OrderStatus::CustomerApproved->value, OrderStatus::InvoiceGenerated->value, OrderStatus::PendingApproval->value, OrderStatus::Approved->value, OrderStatus::Delivered->value, OrderStatus::Returned->value],
            OrderStatus::Cancelled->value => [OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::Cancelled->value],
        ];

        return $paths[$final] ?? [OrderStatus::New->value];
    }

    private function quotations(array $created): void
    {
        $statuses = ['approved', 'approved', 'approved', 'sent', 'sent', 'draft', 'rejected', 'expired', 'approved', 'sent'];

        foreach ($created as $index => $row) {
            $order = $row['order'];
            $number = 'QUO-'.now()->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            if (DB::table('quotations')->where('quotation_number', $number)->exists()) {
                continue;
            }

            $quotationId = DB::table('quotations')->insertGetId([
                'quotation_number' => $number,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'version' => 1,
                'currency_id' => $row['currency_id'],
                'exchange_rate' => 1,
                'status' => $statuses[$index % count($statuses)],
                'subtotal' => $order->subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => $order->total_amount,
                'valid_until' => now()->addDays(14)->toDateString(),
                'sent_at' => now()->subDays(2),
                'customer_response' => $statuses[$index % count($statuses)] === 'approved' ? 'approved' : 'pending',
                'customer_response_at' => $index % 2 === 0 ? now()->subDay() : null,
                'created_by' => $row['admin_id'],
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]);

            $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();
            foreach ($orderItems as $line) {
                $item = DB::table('items')->where('id', $line->item_id)->first();

                DB::table('quotation_items')->insert([
                    'quotation_id' => $quotationId,
                    'item_id' => $line->item_id,
                    'item_pack_id' => $line->item_pack_id,
                    'warehouse_id' => $line->warehouse_id,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => $line->line_total,
                    'subtotal' => $line->line_total,
                    'item_name_snapshot' => $item->name_ar ?? $item->name_en ?? 'منتج',
                    'price_list_id' => null,
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ]);
            }
        }
    }

    private function invoices(array $created, $packs, $warehouseId, $currencyId): void
    {
        // Only orders that reached the invoicing stage get an invoice.
        $invoiceable = [
            OrderStatus::Delivered->value,
            OrderStatus::Delivered->value,
            OrderStatus::InDelivery->value,
            OrderStatus::Approved->value,
            OrderStatus::Approved->value,
            OrderStatus::InvoiceGenerated->value,
            OrderStatus::CustomerApproved->value,
            OrderStatus::Returned->value,
        ];

        $statuses = [
            InvoiceStatus::Paid->value,
            InvoiceStatus::Paid->value,
            InvoiceStatus::PartiallyPaid->value,
            InvoiceStatus::Approved->value,
            InvoiceStatus::PendingApproval->value,
            InvoiceStatus::PendingApproval->value,
            InvoiceStatus::Approved->value,
            InvoiceStatus::Rejected->value,
            InvoiceStatus::Cancelled->value,
            InvoiceStatus::Expired->value,
        ];

        $counter = 0;

        foreach ($created as $row) {
            if (! in_array($row['status'], $invoiceable, true)) {
                continue;
            }

            $order = $row['order'];
            $number = 'INV-'.now()->format('Ymd').'-'.str_pad((string) ($counter + 1), 5, '0', STR_PAD_LEFT);
            $counter++;

            if (DB::table('invoices')->where('invoice_number', $number)->exists()) {
                continue;
            }

            $status = $statuses[($counter - 1) % count($statuses)];
            $isCancelled = $status === InvoiceStatus::Cancelled->value;
            $total = (float) $order->total_amount;

            $paid = 0.0;
            if ($status === InvoiceStatus::Paid->value) {
                $paid = $total;
            } elseif ($status === InvoiceStatus::PartiallyPaid->value) {
                $paid = round($total / 2, 2);
            }

            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_number' => $number,
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'invoice_date' => now()->subDays(2)->toDateString(),
                'payment_type' => $isCancelled ? PaymentType::Cash->value : PaymentType::Credit->value,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'subtotal' => $order->subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => $total,
                'total_amount' => $total,
                'status' => $status,
                'approved_by' => in_array($status, [
                    InvoiceStatus::Approved->value,
                    InvoiceStatus::PartiallyPaid->value,
                    InvoiceStatus::Paid->value,
                ], true) ? $row['admin_id'] : null,
                'approved_at' => in_array($status, [
                    InvoiceStatus::Approved->value,
                    InvoiceStatus::PartiallyPaid->value,
                    InvoiceStatus::Paid->value,
                ], true) ? now()->subDay() : null,
                'payment_deadline_at' => now()->addDays(3),
                'paid_amount' => $paid,
                'paid_at' => $paid > 0 ? now()->subHours(6) : null,
                'due_date' => now()->addDays(30)->toDateString(),
                'notes' => 'فاتورة تجريبية',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);

            $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();

            foreach ($orderItems as $line) {
                $invoiceItemId = DB::table('invoice_items')->insertGetId([
                    'invoice_id' => $invoiceId,
                    'item_id' => $line->item_id,
                    'item_pack_id' => $line->item_pack_id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'original_unit_price' => $line->unit_price,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => $line->line_total,
                    'subtotal' => $line->line_total,
                    'price_source' => 'price_list',
                    'currency_id' => $currencyId,
                    'exchange_rate' => 1,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);

                $batchItem = DB::table('batch_items')
                    ->where('item_id', $line->item_id)
                    ->first();

                if ($batchItem) {
                    DB::table('invoice_item_batches')->insert([
                        'invoice_item_id' => $invoiceItemId,
                        'batch_item_id' => $batchItem->id,
                        'quantity' => $line->quantity,
                        'unit_cost' => 0,
                    ]);
                }
            }

            $this->payments($invoiceId, $order->customer_id, $paid, $status, $row, $currencyId);
        }
    }

    private function payments(int $invoiceId, int $customerId, float $paid, string $status, array $row, int $currencyId): void
    {
        if ($paid <= 0) {
            return;
        }

        $method = $row['customer']->type === 'credit' ? 'wallet' : 'cash';
        $number = 'PAY-'.now()->format('Ymd').'-'.str_pad((string) $invoiceId, 4, '0', STR_PAD_LEFT);

        $paymentId = DB::table('payments')->insertGetId([
            'payment_number' => $number,
            'invoice_id' => $invoiceId,
            'customer_id' => $customerId,
            'amount' => $paid,
            'currency_id' => $currencyId,
            'exchange_rate' => 1,
            'method' => $method,
            'reference' => 'REF-'.str_pad((string) $invoiceId, 5, '0', STR_PAD_LEFT),
            'received_by' => $row['admin_id'],
            'paid_at' => now()->subHours(6),
            'notes' => 'دفعة تجريبية',
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(6),
        ]);

        // Reflect the payment in the wallet when that was the method used.
        if ($method !== 'wallet') {
            return;
        }

        $walletId = DB::table('customer_wallets')->where('customer_id', $customerId)->value('id');
        if (! $walletId) {
            return;
        }

        $balance = (float) DB::table('customer_wallets')->where('id', $walletId)->value('balance');
        $newBalance = $balance - $paid;

        DB::table('wallet_transactions')->insert([
            'wallet_id' => $walletId,
            'type' => WalletTransactionType::Purchase->value,
            'amount' => -$paid,
            'balance_after' => $newBalance,
            'currency_id' => $currencyId,
            'exchange_rate' => 1,
            'reference_type' => 'App\\Models\\Invoice',
            'reference_id' => $invoiceId,
            'method' => 'wallet',
            'created_by' => $row['admin_id'],
            'notes' => 'Payment via wallet',
            'created_at' => now()->subHours(6),
        ]);

        DB::table('customer_wallets')->where('id', $walletId)->update([
            'balance' => $newBalance,
            'total_spent' => DB::raw('total_spent + '.(float) $paid),
        ]);
    }

    private function deliveries(array $created): void
    {
        $deliverable = [
            OrderStatus::Delivered->value,
            OrderStatus::Delivered->value,
            OrderStatus::InDelivery->value,
            OrderStatus::Returned->value,
        ];

        foreach ($created as $row) {
            if (! in_array($row['status'], $deliverable, true)) {
                continue;
            }

            $invoice = DB::table('invoices')->where('order_id', $row['order']->id)->first();
            if (! $invoice) {
                continue;
            }

            $finalStage = $row['status'] === OrderStatus::InDelivery->value
                ? DeliveryStage::OutForDelivery->value
                : DeliveryStage::Received->value;

            $deliveryId = DB::table('deliveries')->insertGetId([
                'invoice_id' => $invoice->id,
                'order_id' => $row['order']->id,
                'customer_id' => $row['order']->customer_id,
                'vehicle_id' => $row['vehicle_id'],
                'driver_id' => $row['driver_id'],
                'current_stage' => $finalStage,
                'started_at' => now()->subDays(1),
                'completed_at' => $finalStage === DeliveryStage::Received->value ? now()->subHours(10) : null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(10),
            ]);

            $path = $finalStage === DeliveryStage::Received->value
                ? [
                    DeliveryStage::Shipping->value,
                    DeliveryStage::OutForDelivery->value,
                    DeliveryStage::Unloading->value,
                    DeliveryStage::Received->value,
                ]
                : [DeliveryStage::Shipping->value, DeliveryStage::OutForDelivery->value];

            foreach ($path as $step => $stage) {
                DB::table('delivery_stage_logs')->insert([
                    'delivery_id' => $deliveryId,
                    'stage' => $stage,
                    'updated_by' => $row['driver_id'],
                    'latitude' => 32.8872 + ($step * 0.001),
                    'longitude' => 13.1913 + ($step * 0.001),
                    'notes' => 'تحديث مرحلة التوصيل',
                    'created_at' => now()->subDays(1)->addHours($step * 3),
                ]);
            }
        }
    }

    private function returns(array $created, $packs, $warehouseId, int $currencyId, ?int $adminId): void
    {
        $returned = array_values(array_filter(
            $created,
            fn ($row) => $row['status'] === OrderStatus::Returned->value
        ));

        // Ensure at least 5 return rows regardless of how many orders were
        // returned, by cycling through the delivered invoices.
        $invoices = DB::table('invoices')->whereNotIn('status', [
            InvoiceStatus::Cancelled->value,
            InvoiceStatus::Rejected->value,
        ])->orderBy('id')->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $statuses = [
            ReturnStatus::Processed->value,
            ReturnStatus::Approved->value,
            ReturnStatus::Pending->value,
            ReturnStatus::Rejected->value,
            ReturnStatus::Processed->value,
        ];

        foreach ($invoices as $index => $invoice) {
            $number = 'RET-'.now()->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            if (DB::table('return_requests')->where('return_number', $number)->exists()) {
                continue;
            }

            $status = $statuses[$index % count($statuses)];
            $refund = round((float) $invoice->total_amount * 0.25, 2);

            $requestId = DB::table('return_requests')->insertGetId([
                'return_number' => $number,
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'customer_id' => $invoice->customer_id,
                'type' => $index % 2 === 0 ? ReturnType::Partial->value : ReturnType::Full->value,
                'reason' => $index % 2 === 0 ? 'تلف في العبوة' : 'خطأ في الكمية',
                'status' => $status,
                'requested_by' => $adminId,
                'reviewed_by' => $status === ReturnStatus::Pending->value ? null : $adminId,
                'reviewed_at' => $status === ReturnStatus::Pending->value ? null : now()->subDay(),
                'total_refund' => $status === ReturnStatus::Processed->value ? $refund : 0,
                'notes' => 'طلب إرجاع تجريبي',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                continue;
            }

            $line = $invoiceItems->first();
            $quantity = min(2, (int) $line->quantity);
            $refundAmount = round((float) $line->unit_price * $quantity, 2);

            DB::table('return_items')->insert([
                'return_request_id' => $requestId,
                'invoice_item_id' => $line->id,
                'item_id' => $line->item_id,
                'item_pack_id' => $line->item_pack_id,
                'quantity_returned' => $quantity,
                'unit_price' => $line->unit_price,
                'refund_amount' => $refundAmount,
                'condition' => $index % 2 === 0 ? 'damaged' : 'good',
                'notes' => 'تم فحص الصنف',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

        }

    }

    /**
     * Back-fill credit notes for any approved/processed return that does not
     * have one yet, so the table is populated even on a re-run.
     */
    private function creditNotes(int $currencyId): void
    {
        $returns = DB::table('return_requests')
            ->whereIn('status', [ReturnStatus::Processed->value, ReturnStatus::Approved->value])
            ->orderBy('id')
            ->get();

        foreach ($returns as $index => $return) {
            if (DB::table('credit_notes')->where('return_request_id', $return->id)->exists()) {
                continue;
            }

            $item = DB::table('return_items')
                ->where('return_request_id', $return->id)
                ->first();

            $refund = (float) ($item->refund_amount ?? 0);
            $isProcessed = $return->status === ReturnStatus::Processed->value;

            DB::table('credit_notes')->insert([
                // Derive the number from the return id so re-runs cannot collide.
                'credit_note_number' => 'CN-'.now()->format('Ymd').'-'.str_pad((string) $return->id, 4, '0', STR_PAD_LEFT),
                'return_request_id' => $return->id,
                'customer_id' => $return->customer_id,
                'currency_id' => $currencyId,
                'total' => $refund,
                'issued_at' => now()->subDay(),
                'applied_to_invoice_id' => $isProcessed ? $return->invoice_id : null,
                'notes' => 'إشعار دائن',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }
    }
}
