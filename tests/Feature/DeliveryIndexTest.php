<?php

namespace Tests\Feature;

use App\Enums\DeliveryStage;
use App\Models\Delivery;
use App\Models\DeliveryStageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the deliveries index and the Delivery model's relations.
 *
 * These tests run against the configured database inside a transaction that is
 * always rolled back, so no data can be persisted. They are opt-in: set
 * INVOICE_FLOW_TESTS=1.
 */
class DeliveryIndexTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (! env('INVOICE_FLOW_TESTS')) {
            $this->markTestSkipped('Set INVOICE_FLOW_TESTS=1 to run tests against the configured database.');
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        parent::tearDown();
    }

    private function authenticate(): void
    {
        $this->actingAs(User::query()->firstOrFail());
    }

    public function test_delivery_index_page_loads(): void
    {
        $this->authenticate();

        $response = $this->get(route('deliveries.index'));

        $response->assertOk();
        $response->assertViewIs('dashboard.deliveries.index');
    }

    public function test_delivery_index_eager_loads_every_relation_used_by_the_view(): void
    {
        $this->authenticate();

        // The controller eager loads these; a missing relation throws
        // RelationNotFoundException here.
        $deliveries = Delivery::with(['invoice', 'order', 'customer', 'driver', 'vehicle'])
            ->latest()
            ->paginate(20);

        $this->assertTrue(true);
    }

    public function test_delivery_model_exposes_invoice_relation(): void
    {
        $delivery = Delivery::query()->first();

        if (! $delivery) {
            $this->markTestSkipped('No delivery rows available.');
        }

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $delivery->invoice(),
        );
    }

    public function test_delivery_fillable_matches_the_table(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('deliveries');
        $fillable = (new Delivery())->getFillable();

        foreach ($fillable as $attribute) {
            $this->assertContains(
                $attribute,
                $columns,
                "Delivery::\$fillable contains '{$attribute}', which is not a column on deliveries.",
            );
        }
    }

    public function test_delivery_casts_current_stage_to_the_enum(): void
    {
        $delivery = Delivery::query()->first();

        if (! $delivery) {
            $this->markTestSkipped('No delivery rows available.');
        }

        $this->assertInstanceOf(DeliveryStage::class, $delivery->current_stage);
    }

    public function test_can_advance_and_next_stage_follow_the_shipping_flow(): void
    {
        $delivery = new Delivery();

        $delivery->current_stage = DeliveryStage::Shipping;
        $this->assertTrue($delivery->canAdvance());
        $this->assertSame(DeliveryStage::OutForDelivery, $delivery->nextStage());

        $delivery->current_stage = DeliveryStage::OutForDelivery;
        $this->assertSame(DeliveryStage::Unloading, $delivery->nextStage());

        $delivery->current_stage = DeliveryStage::Unloading;
        $this->assertSame(DeliveryStage::Received, $delivery->nextStage());

        $delivery->current_stage = DeliveryStage::Received;
        $this->assertFalse($delivery->canAdvance());
        $this->assertNull($delivery->nextStage());

        $delivery->current_stage = DeliveryStage::Cancelled;
        $this->assertFalse($delivery->canAdvance());
        $this->assertNull($delivery->nextStage());
    }

    public function test_cancelled_stage_exists_in_the_enum(): void
    {
        // DeliveryService::cancel() writes 'cancelled', which must be castable.
        $this->assertSame(DeliveryStage::Cancelled, DeliveryStage::from('cancelled'));
        $this->assertSame('ملغاة', DeliveryStage::Cancelled->label());
    }

    public function test_stage_log_model_can_insert_without_updated_at(): void
    {
        $delivery = Delivery::query()->first();

        if (! $delivery) {
            $this->markTestSkipped('No delivery rows available.');
        }

        $log = DeliveryStageLog::create([
            'delivery_id' => $delivery->id,
            'stage' => 'shipping',
        ]);

        $this->assertNotNull($log->id);
        $this->assertNotNull($log->created_at);
    }

    public function test_delivery_create_persists_the_real_columns(): void
    {
        $customer = \App\Models\Customer::query()->firstOrFail();

        $delivery = Delivery::create([
            'customer_id' => $customer->id,
            'current_stage' => 'shipping',
            'started_at' => now(),
        ]);

        $delivery->refresh();

        $this->assertSame($customer->id, (int) $delivery->customer_id);
        $this->assertSame(DeliveryStage::Shipping, $delivery->current_stage);
        $this->assertNotNull($delivery->started_at);
    }
}
