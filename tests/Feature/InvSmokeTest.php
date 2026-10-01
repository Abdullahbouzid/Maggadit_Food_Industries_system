<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class InvSmokeTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void { parent::setUp(); if (!env('INVOICE_FLOW_TESTS')) { $this->markTestSkipped('opt-in'); } DB::beginTransaction(); }
    protected function tearDown(): void { if (DB::transactionLevel() > 0) { DB::rollBack(); } parent::tearDown(); }
    public function test_inventory_index_shows_warehouses_and_batches(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $this->get(route('inventory.index'))->assertOk();
    }
    public function test_inventory_alerts_page_loads(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $this->get(route('inventory.alerts'))->assertOk();
    }
    public function test_inventory_movements_page_loads(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $this->get(route('inventory.movements'))->assertOk();
    }
    public function test_raising_alerts_creates_notifications(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $before = DB::table('notifications')->count();
        $this->post(route('inventory.alerts.notify'))->assertRedirect();
        $this->assertGreaterThanOrEqual($before, DB::table('notifications')->count());
    }

    public function test_transfers_page_loads(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $this->get(route('inventory.transfers'))->assertOk();
    }

    public function test_transfer_between_warehouses_moves_stock(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $batchItem = \App\Models\BatchItem::with(['stock'])->get()
            ->first(fn ($b) => $b->stock->isNotEmpty());
        $from = $batchItem->stock->first();
        $to = \App\Models\Warehouse::where('id', '!=', $from->warehouse_id)->firstOrFail();

        $before = (float) \App\Models\BatchItemStock::where('batch_item_id', $batchItem->id)
            ->where('warehouse_id', $to->id)->value('quantity');

        $this->post(route('inventory.transfers.store'), [
            'movement_type' => 'transfer',
            'batch_item_id' => $batchItem->id,
            'from_warehouse_id' => $from->warehouse_id,
            'to_warehouse_id' => $to->id,
            'quantity' => 2,
        ])->assertSessionHas('success');

        $after = (float) \App\Models\BatchItemStock::where('batch_item_id', $batchItem->id)
            ->where('warehouse_id', $to->id)->value('quantity');

        $this->assertGreaterThan($before, $after);
    }

    public function test_fifo_dispense_takes_the_oldest_batch_first(): void
    {
        $service = app(\App\Services\WarehouseTransferService::class);

        $pack = \App\Models\ItemPack::query()
            ->whereIn('id', \Illuminate\Support\Facades\DB::table('batch_items')
                ->distinct()->pluck('item_pack_id'))
            ->firstOrFail();
        $batches = \App\Models\BatchItem::where('item_pack_id', $pack->id)
            ->orderBy('id')->get();
        $warehouse = \App\Models\Warehouse::find($batches->first()->warehouse_id);

        $allocations = $service->dispenseFifo($pack, $warehouse, 1);

        $this->assertNotEmpty($allocations);
        $this->assertSame(
            $batches->first()->id,
            $allocations[0]['batch_item_id'],
            'FIFO must take the oldest batch first.'
        );
    }

    public function test_transfer_rejects_more_than_available(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $batchItem = \App\Models\BatchItem::with(['stock'])->get()
            ->first(fn ($b) => $b->stock->isNotEmpty());
        $from = $batchItem->stock->first();
        $to = \App\Models\Warehouse::where('id', '!=', $from->warehouse_id)->firstOrFail();

        $this->post(route('inventory.transfers.store'), [
            'movement_type' => 'transfer',
            'batch_item_id' => $batchItem->id,
            'from_warehouse_id' => $from->warehouse_id,
            'to_warehouse_id' => $to->id,
            'quantity' => 999999,
        ])->assertSessionHas('error');
    }

    public function test_customer_detail_shows_payments_returns_and_batch_trace(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $customer = \App\Models\Customer::whereIn('id', function ($q) {
            $q->select('customer_id')->from('invoices');
        })->firstOrFail();

        $this->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('سجل الدفعات')
            ->assertSee('سجل الاسترجاعات')
            ->assertSee('المنتجات والكميات حسب الباتش');
    }

    public function test_batch_trace_caps_returned_quantity_per_line(): void
    {
        $service = app(\App\Services\InvoiceReturnableService::class);

        $invoice = \App\Models\Invoice::whereHas('items')->firstOrFail();
        $lines = $service->lines($invoice);

        $this->assertGreaterThan(0, $lines->count());

        foreach ($lines as $line) {
            $this->assertGreaterThan(0, $line['returnable']);
            $this->assertLessThanOrEqual(
                $line['quantity'],
                $line['returnable'] + $line['returned'],
                'Returnable plus returned may never exceed the invoiced quantity.'
            );
        }
    }

    public function test_admin_can_record_a_partial_return(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $customer = \App\Models\Customer::orderBy('id')->firstOrFail();
        $invoice = \App\Models\Invoice::where('customer_id', $customer->id)
            ->whereHas('items')->firstOrFail();

        $line = app(\App\Services\InvoiceReturnableService::class)
            ->lines($invoice)->first();

        $this->assertNotNull($line);

        $this->get(route('customers.returns.create', [$customer, $invoice]))->assertOk();

        $this->post(route('customers.returns.store', [$customer, $invoice]), [
            'type' => 'partial',
            'reason' => 'تالف أثناء النقل',
            'quantities' => [$line['id'] => 1],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('return_requests', [
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'reason' => 'تالف أثناء النقل',
        ]);
    }

    public function test_admin_return_rejects_more_than_returnable(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $customer = \App\Models\Customer::orderBy('id')->firstOrFail();
        $invoice = \App\Models\Invoice::where('customer_id', $customer->id)
            ->whereHas('items')->firstOrFail();

        $line = app(\App\Services\InvoiceReturnableService::class)
            ->lines($invoice)->first();

        $before = \App\Models\ReturnRequest::where('invoice_id', $invoice->id)->count();

        // Asking for far more than the line holds must be capped, not accepted whole.
        $this->post(route('customers.returns.store', [$customer, $invoice]), [
            'type' => 'partial',
            'reason' => 'اختبار تجاوز الكمية',
            'quantities' => [$line['id'] => 999999],
        ])->assertSessionHas('success');

        $after = \App\Models\ReturnRequest::where('invoice_id', $invoice->id)->count();

        $this->assertSame($before + 1, $after);

        $latest = \App\Models\ReturnRequest::where('invoice_id', $invoice->id)
            ->latest('id')->first();

        $this->assertSame(
            $line['returnable'],
            (float) $latest->items()->first()->quantity_returned,
            'A return must be capped at the returnable quantity.'
        );
    }

    public function test_wallet_page_shows_customer_and_transactions(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $wallet = \App\Models\CustomerWallet::whereHas('transactions')->firstOrFail();

        $this->get(route('wallets.show', $wallet))
            ->assertOk()
            ->assertSee($wallet->customer->name)
            ->assertSee('حركات المحفظة')
            ->assertSee('الرصيد الحالي')
            ->assertSee('إيداع رصيد');
    }

    public function test_wallet_transactions_can_be_filtered_by_type(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $wallet = \App\Models\CustomerWallet::whereHas('transactions')->firstOrFail();
        $type = $wallet->transactions()->value('type');

        $this->get(route('wallets.show', [$wallet, 'type' => $type]))
            ->assertOk()
            ->assertSee('حركات المحفظة');
    }

    public function test_wallet_deposit_from_wallet_page_returns_to_wallet(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $wallet = \App\Models\CustomerWallet::whereHas('customer')->firstOrFail();
        $before = (float) $wallet->balance;

        $this->post(route('wallets.deposit', $wallet), [
            'amount' => 25,
            'method' => 'cash',
            'return_to' => 'wallet',
        ])->assertRedirect(route('wallets.show', $wallet));

        $this->assertEqualsWithDelta(
            $before + 25,
            (float) $wallet->fresh()->balance,
            0.001
        );
    }

    public function test_wallet_deposit_without_return_to_goes_to_customer(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $wallet = \App\Models\CustomerWallet::whereHas('customer')->firstOrFail();
        $before = (float) $wallet->balance;

        $this->post(route('wallets.deposit', $wallet), [
            'amount' => 15,
            'method' => 'cash',
        ])->assertRedirect(route('customers.show', $wallet->customer_id));

        $this->assertEqualsWithDelta(
            $before + 15,
            (float) $wallet->fresh()->balance,
            0.001
        );
    }

    public function test_payment_deadline_index_lists_settings(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->get(route('payment-deadlines.index'))
            ->assertOk()
            ->assertSee(trans('messages.payment_deadline_page.title'));
    }

    public function test_payment_deadline_create_form_loads(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->get(route('payment-deadlines.create'))->assertOk();
    }

    public function test_admin_can_create_a_payment_deadline(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->post(route('payment-deadlines.store'), [
            'payment_type' => 'credit',
            'hours_limit' => 36,
            'applies_to' => 'all',
            'is_active' => 1,
            'notes' => 'اختبار الإنشاء',
        ])->assertRedirect(route('payment-deadlines.index'));

        $this->assertDatabaseHas('payment_deadline_settings', [
            'payment_type' => 'credit',
            'hours_limit' => 36,
            'applies_to' => 'all',
            'notes' => 'اختبار الإنشاء',
        ]);
    }

    public function test_admin_can_update_a_payment_deadline(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $setting = \App\Models\PaymentDeadlineSetting::firstOrFail();

        $this->get(route('payment-deadlines.edit', $setting))->assertOk();

        $this->put(route('payment-deadlines.update', $setting), [
            'payment_type' => 'prepaid',
            'hours_limit' => 5,
            'applies_to' => 'all',
            'is_active' => 0,
        ])->assertRedirect(route('payment-deadlines.index'));

        $this->assertDatabaseHas('payment_deadline_settings', [
            'id' => $setting->id,
            'payment_type' => 'prepaid',
            'hours_limit' => 5,
            'is_active' => 0,
        ]);
    }

    public function test_specific_deadline_requires_at_least_one_customer(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->post(route('payment-deadlines.store'), [
            'payment_type' => 'credit',
            'hours_limit' => 12,
            'applies_to' => 'specific',
            'customer_ids' => [],
        ])->assertSessionHasErrors('customer_ids');

        $this->assertDatabaseMissing('payment_deadline_settings', [
            'payment_type' => 'credit',
            'hours_limit' => 12,
        ]);
    }

    public function test_specific_deadline_stores_selected_customers(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $customers = \App\Models\Customer::orderBy('id')->take(2)->pluck('id')->all();

        $this->post(route('payment-deadlines.store'), [
            'payment_type' => 'credit',
            'hours_limit' => 8,
            'applies_to' => 'specific',
            'customer_ids' => $customers,
        ])->assertRedirect(route('payment-deadlines.index'));

        $setting = \App\Models\PaymentDeadlineSetting::where('applies_to', 'specific')
            ->where('hours_limit', 8)
            ->firstOrFail();

        $this->assertEqualsCanonicalizing(
            array_map('intval', $customers),
            array_map('intval', $setting->customer_ids)
        );
    }

    public function test_all_customers_deadline_clears_customer_ids(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $customerId = \App\Models\Customer::orderBy('id')->value('id');

        $this->post(route('payment-deadlines.store'), [
            'payment_type' => 'cash',
            'hours_limit' => 1,
            'applies_to' => 'all',
            'customer_ids' => [$customerId],
        ])->assertRedirect(route('payment-deadlines.index'));

        $setting = \App\Models\PaymentDeadlineSetting::where('applies_to', 'all')
            ->where('hours_limit', 1)
            ->firstOrFail();

        $this->assertNull(
            $setting->customer_ids,
            'A rule scoped to all customers must not keep a customer list.'
        );
    }

    public function test_payment_deadline_validates_required_fields(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->post(route('payment-deadlines.store'), [])
            ->assertSessionHasErrors(['payment_type', 'hours_limit', 'applies_to']);
    }

    public function test_payment_deadline_rejects_negative_hours(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->post(route('payment-deadlines.store'), [
            'payment_type' => 'credit',
            'hours_limit' => -5,
            'applies_to' => 'all',
        ])->assertSessionHasErrors('hours_limit');
    }
}
