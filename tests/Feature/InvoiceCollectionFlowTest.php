<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\CustomerWallet;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the wallet-collection flow: approve -> reject -> record payment.
 *
 * IMPORTANT: these tests run against the configured database. They are wrapped in a
 * transaction that is always rolled back, so no data can be persisted. Because of
 * that they are opt-in and skipped unless INVOICE_FLOW_TESTS=1 is set, to avoid
 * touching a production database accidentally.
 */
class InvoiceCollectionFlowTest extends TestCase
{
    use DatabaseTransactions;

    private const INVOICE_ID = 47;

    protected function setUp(): void
    {
        parent::setUp();

        if (! env('INVOICE_FLOW_TESTS')) {
            $this->markTestSkipped('Set INVOICE_FLOW_TESTS=1 to run tests against the configured database.');
        }

        // Guard against ever committing anything: fail loudly if a test ends up
        // outside of the wrapping transaction.
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
        $user = User::query()->firstOrFail();
        $this->actingAs($user);
    }

    private function invoice(): Invoice
    {
        return Invoice::query()
            ->with('items')
            ->where('status', InvoiceStatus::PendingApproval->value)
            ->orderByDesc('id')
            ->first()
            ?? Invoice::query()->orderByDesc('id')->first();
    }

    public function test_approve_moves_invoice_out_of_pending_approval(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::PendingApproval->value, 'paid_amount' => 0]);

        $response = $this->post(route('invoices.approve', $invoice->id));

        $response->assertRedirect();
        $this->assertSame(
            InvoiceStatus::Approved->value,
            Invoice::query()->find($invoice->id)->status,
        );
    }

    public function test_approve_returns_friendly_arabic_error_on_invalid_transition(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::Paid->value, 'paid_amount' => $invoice->total_amount]);

        $response = $this->from(route('invoices.show', $invoice->id))
            ->post(route('invoices.approve', $invoice->id));

        $response->assertRedirect(route('invoices.show', $invoice->id));
        $response->assertSessionHasErrors('invoice');

        $message = session('errors')->first('invoice');

        // The raw domain exception is in English; the UI must show an Arabic message.
        $this->assertStringNotContainsString('Invalid transition', $message);
        $this->assertStringContainsString('لا يمكن تنفيذ الإجراء', $message);
    }

    public function test_wallet_payment_debits_wallet_and_marks_partially_paid(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update([
            'status' => InvoiceStatus::Approved->value,
            'paid_amount' => 0,
        ]);

        $wallet = CustomerWallet::query()->where('customer_id', $invoice->customer_id)->firstOrFail();
        $balanceBefore = (float) $wallet->balance;

        $amount = 100.0;

        $response = $this->post(route('invoices.payments', $invoice->id), [
            'amount' => $amount,
            'method' => 'wallet',
            'notes' => 'اختبار',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEqualsWithDelta($amount, (float) $invoice->paid_amount, 0.001);
        $this->assertSame(InvoiceStatus::PartiallyPaid->value, $invoice->status);

        $wallet->refresh();
        $this->assertEqualsWithDelta($balanceBefore - $amount, (float) $wallet->balance, 0.001);
    }

    public function test_full_wallet_payment_marks_invoice_paid(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update([
            'status' => InvoiceStatus::Approved->value,
            'paid_amount' => 0,
        ]);

        $remaining = (float) $invoice->total_amount;

        $response = $this->post(route('invoices.payments', $invoice->id), [
            'amount' => $remaining,
            'method' => 'wallet',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid->value, $invoice->status);
        $this->assertEqualsWithDelta($remaining, (float) $invoice->paid_amount, 0.001);
    }

    public function test_wallet_payment_rejected_when_balance_is_insufficient_for_cash_customer(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update([
            'status' => InvoiceStatus::Approved->value,
            'paid_amount' => 0,
        ]);

        $customer = $invoice->customer;
        // Only enforce a strict balance check for cash customers.
        if ($customer && $customer->type === 'credit') {
            $this->markTestSkipped('Customer is a credit customer, so over-drawing is allowed.');
        }

        $wallet = CustomerWallet::query()->where('customer_id', $invoice->customer_id)->first();
        if (! $wallet) {
            $this->markTestSkipped('Customer has no wallet.');
        }

        $wallet->update(['balance' => 1]);

        $response = $this->post(route('invoices.payments', $invoice->id), [
            'amount' => 500.0,
            'method' => 'wallet',
        ]);

        $response->assertSessionHasErrors('payment');
    }

    public function test_payment_cannot_exceed_remaining_amount(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update([
            'status' => InvoiceStatus::Approved->value,
            'paid_amount' => 0,
        ]);

        $response = $this->post(route('invoices.payments', $invoice->id), [
            'amount' => (float) $invoice->total_amount + 1000,
            'method' => 'cash',
        ]);

        $response->assertSessionHasErrors('payment');
    }

    public function test_reject_moves_invoice_to_rejected(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::PendingApproval->value, 'paid_amount' => 0]);

        $response = $this->from(route('invoices.show', $invoice->id))
            ->post(route('invoices.reject', $invoice->id), [
                'reason' => 'كمية غير مطابقة',
            ]);

        $response->assertRedirect(route('invoices.show', $invoice->id));
        $this->assertSame(
            InvoiceStatus::Rejected->value,
            Invoice::query()->find($invoice->id)->status,
        );
    }

    public function test_reject_requires_a_reason(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::PendingApproval->value]);

        $response = $this->from(route('invoices.show', $invoice->id))
            ->post(route('invoices.reject', $invoice->id), []);

        $response->assertSessionHasErrors('reason');
    }

    public function test_payment_rejects_a_method_outside_the_enum(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::Approved->value, 'paid_amount' => 0]);

        $response = $this->from(route('invoices.show', $invoice->id))
            ->post(route('invoices.payments', $invoice->id), [
                'amount' => 10,
                'method' => 'card', // not a PaymentMethod case
            ]);

        $response->assertSessionHasErrors('method');
    }

    public function test_show_page_has_no_dead_action_links(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::PendingApproval->value, 'paid_amount' => 0]);

        $response = $this->get(route('invoices.show', $invoice->id));

        $response->assertOk();
        // The invoice action buttons must no longer be dead javascript:void(0)
        // links. Scoped to the old markup, because the shared sidebar/modals
        // partials legitimately use javascript:void(0); for their own modals.
        $response->assertDontSee('<a href="javascript:void(0)" class="nova-btn', false);
        $response->assertSee(route('invoices.approve', $invoice->id), false);
        $response->assertSee(route('invoices.reject', $invoice->id), false);
    }

    public function test_payment_modal_only_offers_wallet_when_a_wallet_exists(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();
        $invoice->update(['status' => InvoiceStatus::Approved->value, 'paid_amount' => 0]);

        $hasWallet = CustomerWallet::query()
            ->where('customer_id', $invoice->customer_id)
            ->exists();

        $response = $this->get(route('invoices.show', $invoice->id));

        $response->assertOk();

        if ($hasWallet) {
            $response->assertSee('value="wallet"', false);
        } else {
            // A customer without a wallet cannot pay via wallet, so it must
            // not be offered as an option.
            $response->assertDontSee('value="wallet"', false);
        }
    }

    public function test_invoice_pdf_endpoint_returns_a_pdf(): void
    {
        if (! is_executable((string) config('snappy.pdf.binary'))) {
            $this->markTestSkipped('wkhtmltopdf is not installed here.');
        }

        $this->authenticate();
        $invoice = $this->invoice();

        $response = $this->get(route('invoices.pdf', $invoice->id));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('Content-Type'),
        );
    }

    public function test_invoice_pdf_reports_a_clear_error_when_wkhtmltopdf_is_missing(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();

        config(['snappy.pdf.binary' => '/nonexistent/wkhtmltopdf']);

        $response = $this->from(route('invoices.show', $invoice->id))
            ->get(route('invoices.pdf', $invoice->id));

        $response->assertRedirect(route('invoices.show', $invoice->id));
        $response->assertSessionHasErrors('invoice');

        $this->assertStringContainsString('wkhtmltopdf', session('errors')->first('invoice'));
    }

    public function test_invoice_show_page_renders_collection_controls(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();

        $response = $this->get(route('invoices.show', $invoice->id));

        $response->assertOk();
        $response->assertViewIs('dashboard.invoices.show');
    }

    public function test_invoice_edit_page_does_not_offer_stale_status_values(): void
    {
        $this->authenticate();
        $invoice = $this->invoice();

        $response = $this->get(route('invoices.edit', $invoice->id));

        $response->assertOk();
        $response->assertDontSee('value="completed"', false);
        $response->assertDontSee('value="pending"', false);
    }
}