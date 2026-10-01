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
 * Covers the delivery show page and the advance-stage action.
 *
 * Opt-in against the configured database, wrapped in a transaction that is
 * always rolled back: set INVOICE_FLOW_TESTS=1.
 */
class DeliveryShowTest extends TestCase
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

    private function delivery(): Delivery
    {
        return Delivery::query()
            ->with(['invoice', 'order', 'customer', 'driver', 'vehicle', 'stageLogs'])
            ->orderByDesc('id')
            ->firstOrFail();
    }

    public function test_show_page_renders_with_stage_and_timeline(): void
    {
        $this->authenticate();

        $response = $this->get(route('deliveries.show', $this->delivery()->id));

        $response->assertOk();
        $response->assertViewIs('dashboard.deliveries.show');
        $response->assertViewHas('delivery');
        $response->assertSee(trans('messages.delivery_page.delivery_details'));
    }

    public function test_index_action_column_links_to_the_show_page(): void
    {
        $this->authenticate();

        $response = $this->get(route('deliveries.index'));

        $response->assertOk();
        $response->assertSee(route('deliveries.show', $this->delivery()->id), false);
    }

    public function test_advance_stage_moves_to_the_next_stage_and_logs_it(): void
    {
        $this->authenticate();

        $delivery = $this->delivery();
        $delivery->update(['current_stage' => DeliveryStage::Shipping->value]);

        $expected = DeliveryStage::Shipping->next();

        $response = $this->post(route('deliveries.advance-stage', $delivery->id), [
            'notes' => 'تم تسليم الطرد',
        ]);

        $response->assertRedirect();

        $this->assertSame($expected->value, $delivery->fresh()->current_stage->value);

        $this->assertDatabaseHas('delivery_stage_logs', [
            'delivery_id' => $delivery->id,
            'stage' => $expected->value,
            'notes' => 'تم تسليم الطرد',
        ]);
    }

    public function test_advance_stage_rejects_an_unknown_stage_on_a_closed_delivery(): void
    {
        $this->authenticate();

        $delivery = $this->delivery();
        $delivery->update(['current_stage' => DeliveryStage::Received->value]);

        $logsBefore = DeliveryStageLog::where('delivery_id', $delivery->id)->count();

        $response = $this->post(route('deliveries.advance-stage', $delivery->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(DeliveryStage::Received->value, $delivery->fresh()->current_stage->value);
        $this->assertSame(
            $logsBefore,
            DeliveryStageLog::where('delivery_id', $delivery->id)->count(),
            'A closed delivery must not gain a stage log.'
        );
    }

    public function test_advance_stage_rejects_a_deleted_delivery(): void
    {
        $this->authenticate();

        $delivery = $this->delivery();
        $delivery->update(['current_stage' => DeliveryStage::Shipping->value]);
        $delivery->delete();

        // Soft-deleted rows are excluded from route model binding.
        $this->post(route('deliveries.advance-stage', $delivery->id))->assertNotFound();
        $this->assertSame(
            DeliveryStage::Shipping->value,
            $delivery->fresh()->current_stage->value,
            'A soft-deleted delivery must not change stage.'
        );
    }

    public function test_advance_stage_validates_the_note_length(): void
    {
        $this->authenticate();

        $delivery = $this->delivery();
        $delivery->update(['current_stage' => DeliveryStage::Shipping->value]);

        $response = $this->post(route('deliveries.advance-stage', $delivery->id), [
            'notes' => str_repeat('ط', 1200),
        ]);

        $response->assertSessionHasErrors('notes');
        $this->assertSame(DeliveryStage::Shipping->value, $delivery->fresh()->current_stage->value);
    }

    public function test_show_page_uses_arabic_labels_for_every_stage(): void
    {
        $this->authenticate();

        $delivery = $this->delivery();

        foreach (DeliveryStage::cases() as $case) {
            $delivery->update(['current_stage' => $case->value]);

            $this->get(route('deliveries.show', $delivery->id))
                ->assertOk()
                ->assertSee($case->label());
        }
    }
}
