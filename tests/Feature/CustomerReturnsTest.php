<?php

namespace Tests\Feature;

use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ReturnRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the customer-facing return register, the per-invoice return form and
 * the per-batch product breakdown.
 *
 * Opt-in against the configured database: set INVOICE_FLOW_TESTS=1.
 */
class CustomerReturnsTest extends TestCase
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

    private function loginAsCustomer(): Customer
    {
        $customer = Customer::query()->orderBy('id')->firstOrFail();

        $this->withSession(['customer_id' => $customer->id])
            ->get('/'); // establish the session

        return $customer;
    }

    public function test_returns_register_page_loads_for_a_customer(): void
    {
        $this->loginAsCustomer();

        $this->get(route('customer.returns'))->assertOk();
    }

    public function test_returns_register_shows_arabic_status_labels(): void
    {
        $customer = $this->loginAsCustomer();

        foreach (ReturnStatus::cases() as $status) {
            ReturnRequest::create([
                'return_number' => 'RET-TEST-'.$status->value,
                'invoice_id' => Invoice::where('customer_id', $customer->id)->value('id'),
                'customer_id' => $customer->id,
                'type' => ReturnType::Partial->value,
                'reason' => 'اختبار',
                'status' => $status->value,
                'total_refund' => 0,
            ]);

            $this->get(route('customer.returns'))
                ->assertOk()
                ->assertSee($status->label());
        }
    }

    public function test_return_create_form_lists_the_invoice_lines(): void
    {
        $customer = $this->loginAsCustomer();

        $invoice = Invoice::with('items')
            ->where('customer_id', $customer->id)
            ->whereHas('items')
            ->orderByDesc('id')
            ->firstOrFail();

        $this->get(route('customer.returns.create', $invoice->id))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_customer_can_return_a_specific_quantity_from_an_invoice(): void
    {
        $customer = $this->loginAsCustomer();

        $invoice = Invoice::with('items')
            ->where('customer_id', $customer->id)
            ->whereHas('items')
            ->orderByDesc('id')
            ->firstOrFail();

        $line = $invoice->items->first();
        $quantity = 1.0;

        $response = $this->post(route('customer.returns.store', $invoice->id), [
            'type' => ReturnType::Partial->value,
            'reason' => 'منتج تالف',
            'quantities' => [$line->id => $quantity],
        ]);

        $response->assertRedirect(route('customer.returns'));
        $response->assertSessionHas('success');

        $return = ReturnRequest::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->firstOrFail();

        $this->assertSame(ReturnStatus::Pending->value, $return->status->value);
        $this->assertSame(ReturnType::Partial->value, $return->type->value);
        $this->assertSame('منتج تالف', $return->reason);
        $this->assertDatabaseHas('return_items', [
            'return_request_id' => $return->id,
            'invoice_item_id' => $line->id,
            'quantity_returned' => $quantity,
        ]);
    }

    public function test_a_return_without_any_quantity_is_rejected(): void
    {
        $customer = $this->loginAsCustomer();

        $invoice = Invoice::with('items')
            ->where('customer_id', $customer->id)
            ->whereHas('items')
            ->orderByDesc('id')
            ->firstOrFail();

        $response = $this->post(route('customer.returns.store', $invoice->id), [
            'type' => ReturnType::Partial->value,
            'reason' => 'سبب',
            'quantities' => [$invoice->items->first()->id => 0],
        ]);

        $response->assertSessionHas('error');
    }

    public function test_customer_cannot_return_more_than_the_invoice_line_quantity(): void
    {
        $customer = $this->loginAsCustomer();

        $invoice = Invoice::with('items')
            ->where('customer_id', $customer->id)
            ->whereHas('items')
            ->orderByDesc('id')
            ->firstOrFail();

        $line = $invoice->items->first();
        $returnsBefore = ReturnRequest::where('customer_id', $customer->id)->count();

        $this->post(route('customer.returns.store', $invoice->id), [
            'type' => ReturnType::Partial->value,
            'reason' => 'سبب',
            'quantities' => [$line->id => (float) $line->quantity + 1000],
        ])->assertSessionHas('error');

        $this->assertSame(
            $returnsBefore,
            ReturnRequest::where('customer_id', $customer->id)->count(),
            'An over-quantity return must not be recorded.'
        );
    }

    public function test_customer_cannot_return_against_another_customers_invoice(): void
    {
        $this->loginAsCustomer();

        $otherInvoice = Invoice::where('customer_id', '!=', Customer::query()->value('id'))
            ->with('items')
            ->whereHas('items')
            ->orderByDesc('id')
            ->first();

        if (! $otherInvoice) {
            $this->markTestSkipped('No invoice belonging to another customer is available.');
        }

        $this->post(route('customer.returns.store', $otherInvoice->id), [
            'type' => ReturnType::Partial->value,
            'reason' => 'محاولة غير مصرح بها',
            'quantities' => [$otherInvoice->items->first()->id => 1],
        ])->assertRedirect(route('customer.returns'));

        $this->assertDatabaseMissing('return_requests', [
            'invoice_id' => $otherInvoice->id,
            'reason' => 'محاولة غير مصرح بها',
        ]);
    }

    public function test_return_reason_is_required(): void
    {
        $customer = $this->loginAsCustomer();

        $invoice = Invoice::where('customer_id', $customer->id)->orderByDesc('id')->firstOrFail();

        $this->post(route('customer.returns.store', $invoice->id), [
            'type' => ReturnType::Full->value,
            'reason' => '',
            'quantities' => [],
        ])->assertSessionHasErrors(['reason', 'quantities']);
    }

    public function test_my_products_page_breaks_quantities_down_per_batch(): void
    {
        $this->loginAsCustomer();

        $this->get(route('customer.my_products'))->assertOk();
    }

    public function test_my_products_shows_the_batch_numbers_of_the_customer(): void
    {
        $customer = $this->loginAsCustomer();

        $response = $this->get(route('customer.my_products'));

        $response->assertOk();

        $batchNumbers = DB::table('invoice_item_batches')
            ->join('invoice_items', 'invoice_items.id', '=', 'invoice_item_batches.invoice_item_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('batch_items', 'batch_items.id', '=', 'invoice_item_batches.batch_item_id')
            ->leftJoin('production_batches', 'production_batches.id', '=', 'batch_items.batch_id')
            ->where('invoices.customer_id', $customer->id)
            ->distinct()
            ->pluck('production_batches.batch_number')
            ->filter()
            ->values();

        foreach ($batchNumbers as $batchNumber) {
            $response->assertSee($batchNumber);
        }
    }
}
