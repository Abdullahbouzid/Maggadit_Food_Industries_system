<?php

namespace Tests\Feature;

use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the returns index, which eager-loads relations that must exist on
 * ReturnRequest.
 *
 * Opt-in against the configured database: set INVOICE_FLOW_TESTS=1.
 */
class ReturnsIndexTest extends TestCase
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

    public function test_returns_index_page_loads(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $this->get(route('returns.index'))->assertOk();
    }

    public function test_every_relation_eager_loaded_by_the_controller_resolves(): void
    {
        $returns = ReturnRequest::with(['invoice', 'customer', 'items'])->get();

        $this->assertNotEmpty($returns);
        $this->assertTrue($returns->every(fn ($r) => $r->invoice !== null));
    }

    public function test_type_and_status_are_cast_to_enums(): void
    {
        $return = ReturnRequest::query()->firstOrFail();

        $this->assertInstanceOf(ReturnType::class, $return->type);
        $this->assertInstanceOf(ReturnStatus::class, $return->status);
    }

    public function test_index_renders_arabic_labels_for_every_enum_value(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $return = ReturnRequest::query()->firstOrFail();

        foreach (ReturnType::cases() as $type) {
            foreach (ReturnStatus::cases() as $status) {
                $return->update(['type' => $type->value, 'status' => $status->value]);

                $this->get(route('returns.index'))
                    ->assertOk()
                    ->assertSee($type->label())
                    ->assertSee($status->label());
            }
        }
    }

    public function test_index_shows_total_refund_not_a_missing_amount_column(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $return = ReturnRequest::query()->firstOrFail();
        $return->update(['total_refund' => 123.45]);

        $this->get(route('returns.index'))
            ->assertOk()
            ->assertSee('123.45');
    }

    public function test_return_items_use_the_real_column_names(): void
    {
        $item = \App\Models\ReturnItem::query()->firstOrFail();

        $this->assertNotNull($item->quantity_returned);
        $this->assertContains('quantity_returned', (new \App\Models\ReturnItem())->getFillable());
    }
}
