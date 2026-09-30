<?php

/**
 * End-to-end test of the customer-app order path (مسار B):
 *   customer checkout -> order(new)
 *   -> admin review (pending_review)
 *   -> admin edit (admin_edited)
 *   -> admin sends quote (quotation_sent, reserves stock, notifies)
 *   -> customer approves (customer_approved -> invoice_generated -> pending_approval, invoice created)
 *   -> admin approves invoice (approved -> in_delivery)
 *
 * Runs inside a transaction that is always rolled back.
 */

use App\Enums\OrderStatus;
use App\Contracts\OrderLifecycleServiceInterface;
use App\Services\OrderStepPresenter;

$out = [];
function step(string $msg) { global $out; $out[] = $msg; }
function statusOf($order): string {
    $s = $order->fresh()->status;
    return $s instanceof OrderStatus ? $s->value : (string) $s;
}
function assertTrue(bool $cond, string $msg) {
    global $out;
    $out[] = ($cond ? '  PASS  ' : '  FAIL  ').$msg;
    if (! $cond) { $out[] = '!!!! FAILED: '.$msg; }
}

DB::beginTransaction();

try {
    $customer = App\Models\Customer::first();
    $item = App\Models\Item::whereHas('packs')->first() ?: App\Models\Item::first();
    $pack = $item?->packs()->first();

    assertTrue((bool) $customer, 'customer exists: #'.($customer?->id ?? 'none'));
    assertTrue((bool) $item, 'item exists: '.($item?->code ?? 'none'));

    $orderService = app(App\Services\OrderService::class);
    $lifecycle = app(OrderLifecycleServiceInterface::class);
    $presenter = app(OrderStepPresenter::class);

    // ---------- 1. Customer checkout ----------
    $order = $orderService->create(
        customer: $customer,
        items: [[
            'item_id' => $item->id,
            'item_pack_id' => $pack?->id,
            'quantity' => 2,
        ]],
        deliveryAddress: 'عنوان اختبار',
        deliveryPhone: '0500000000',
        user: null,
    );

    step('order created: '.$order->order_number);
    assertTrue(statusOf($order) === OrderStatus::New->value, 'order starts at "new" (got: '.statusOf($order).')');
    assertTrue($order->items->count() === 1, 'order has 1 line item');
    assertTrue((float) $order->total > 0, 'order total computed: '.$order->total);
    assertTrue($order->statusHistory()->count() === 1, 'initial status history row created');

    // ---------- 2. Admin review ----------
    $order = $lifecycle->transition($order, OrderStatus::PendingReview, notes: 'مراجعة من الإدارة');
    assertTrue(statusOf($order) === OrderStatus::PendingReview->value, 'new -> pending_review');

    // ---------- 3. Admin edit ----------
    $order = $lifecycle->transition($order, OrderStatus::AdminEdited, notes: 'تعديل الأسعار');
    assertTrue(statusOf($order) === OrderStatus::AdminEdited->value, 'pending_review -> admin_edited');

    // ---------- 4. Admin sends quote ----------
    $order = $lifecycle->transition($order, OrderStatus::QuotationSent, notes: 'إرسال عرض السعر');
    assertTrue(statusOf($order) === OrderStatus::QuotationSent->value, 'admin_edited -> quotation_sent');

    $order->refresh();
    $quotation = $order->quotation()->latest('version')->first();
    assertTrue($quotation !== null, 'quotation auto-created on quotation_sent');
    assertTrue(! empty($quotation?->quotation_number), 'quotation has a number: '.($quotation?->quotation_number ?? 'NONE'));
    assertTrue($quotation?->customer_response === 'pending', 'quotation awaits customer response');

    $notif = App\Models\Notification::where('customers', 'like', '%'.$customer->id.'%')
        ->where('group', 'quotation')->latest('id')->first();
    assertTrue($notif !== null, 'quotation notification created for customer');

    // ---------- 5. Steps presenter mid-flow ----------
    $steps = $presenter->steps($order->fresh());
    $current = array_values(array_filter($steps, fn ($s) => $s['state'] === 'current'));
    assertTrue(count($current) === 1, 'exactly one current step (got '.count($current).')');
    assertTrue($current[0]['key'] === 'quotation', 'current step is "quotation" (got '.($current[0]['key'] ?? '?').')');
    assertTrue($presenter->canCustomerRespond($order->fresh()), 'customer may respond while quotation_sent');
    assertTrue($presenter->progress($order->fresh()) === 46, 'progress is 46% (got '.$presenter->progress($order->fresh()).')');

    // ---------- 6. Customer responds via QuotationService ----------
    $quotationService = app(App\Services\QuotationService::class);
    $quotationService->recordCustomerResponse($quotation, 'approved', 'موافق على العرض');

    $order->refresh();
    $finalStatus = statusOf($order);
    step('after approval -> '.$finalStatus);

    assertTrue($finalStatus === OrderStatus::PendingApproval->value,
        'approval cascades customer_approved -> invoice_generated -> pending_approval (got '.$finalStatus.')');

    $invoice = App\Models\Invoice::where('order_id', $order->id)->first();
    assertTrue($invoice !== null, 'invoice auto-created on approval');
    assertTrue((bool) $invoice?->invoice_number, 'invoice has a number: '.($invoice?->invoice_number ?? 'NONE'));
    assertTrue($invoice?->status === 'pending_approval', 'invoice starts at pending_approval (got '.($invoice?->status ?? 'NONE').')');
    assertTrue($invoice?->items()->count() === 1, 'invoice has 1 item line');
    assertTrue((float) $invoice->total_amount > 0, 'invoice total: '.$invoice->total_amount);

    $historyStatuses = $order->statusHistory()->orderBy('id')->pluck('to_status')->all();
    step('status history: '.implode(' -> ', $historyStatuses));
    assertTrue(in_array('customer_approved', $historyStatuses, true), 'history contains customer_approved');
    assertTrue(in_array('invoice_generated', $historyStatuses, true), 'history contains invoice_generated');
    assertTrue(in_array('pending_approval', $historyStatuses, true), 'history contains pending_approval');

    // ---------- 7. Steps presenter after approval ----------
    $steps = $presenter->steps($order->fresh());
    $current = array_values(array_filter($steps, fn ($s) => $s['state'] === 'current'));
    assertTrue(count($current) === 1, 'one current step after approval');
    assertTrue($current[0]['key'] === 'invoice', 'current step is "invoice" (got '.($current[0]['key'] ?? '?').')');
    assertTrue(! $presenter->canCustomerRespond($order->fresh()), 'customer can no longer respond');

    $timeline = $presenter->timeline($order->fresh());
    assertTrue(count($timeline) >= 5, 'timeline has >=5 entries (got '.count($timeline).')');
    assertTrue(collect($timeline)->every(fn ($e) => ! empty($e['to'])), 'every timeline entry has a label');

    // ---------- final leg: run the lifecycle all the way to delivered ----------
    echo "\n--- final leg: pending_approval -> approved -> in_delivery -> delivered ---\n";

    $svc = app(App\Contracts\OrderLifecycleServiceInterface::class);
    $o = $order->fresh();

    $svc->transition($o, App\Enums\OrderStatus::Approved, notes: 'اعتماد الإدارة');
    assertTrue($o->fresh()->status === App\Enums\OrderStatus::Approved,
        'pending_approval -> approved (got '.$o->fresh()->status->value.')');

    $svc->transition($o->fresh(), App\Enums\OrderStatus::InDelivery, notes: 'خرجت للتوصيل');
    assertTrue($o->fresh()->status === App\Enums\OrderStatus::InDelivery,
        'approved -> in_delivery (got '.$o->fresh()->status->value.')');

    $svc->transition($o->fresh(), App\Enums\OrderStatus::Delivered, notes: 'سلمت للعميل');
    $o->refresh();
    assertTrue($o->status === App\Enums\OrderStatus::Delivered,
        'in_delivery -> delivered (got '.$o->status->value.')');

    // The invoice runs on its own lifecycle (InvoiceStatus has no "delivered"
    // state), so it must stay pending_approval until admin approves it
    // separately via InvoiceLifecycleService -- it must not be clobbered by
    // the order's delivery transitions.
    $inv = $o->invoices()->latest('id')->first();
    assertTrue($inv !== null, 'order still has its generated invoice');
    assertTrue($inv->status === App\Enums\InvoiceStatus::PendingApproval->value,
        'invoice keeps its own pending_approval status after delivery (got '.($inv?->status ?? 'none').')');

    $history = $o->statusHistory()->orderBy('id')->pluck('to_status')->all();
    assertTrue(count($history) === 10,
        'history records all 10 states (got '.count($history).': '.implode(' -> ', $history).')');

    $moves = App\Models\InventoryMovement::where('reference_type', App\Models\Order::class)
        ->where('reference_id', $o->id)->count();
    assertTrue($moves > 0, 'stock movements reference this order (got '.$moves.')');

    $presenter = app(App\Services\OrderStepPresenter::class);
    assertTrue($presenter->progress($o) === 100, 'progress reaches 100% (got '.$presenter->progress($o).'%)');
    assertTrue(! $presenter->canCustomerRespond($o), 'a delivered order is closed to the customer');

    echo implode("\n", $GLOBALS['out'])."\n";
} catch (\Throwable $e) {
    echo implode("\n", $GLOBALS['out'])."\n";
    echo '!!!! EXCEPTION: '.get_class($e).': '.$e->getMessage()."\n";
    echo '     at '.$e->getFile().':'.$e->getLine()."\n";
} finally {
    DB::rollBack();
    echo "\n(rolled back — no data was written)\n";
}
