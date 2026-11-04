<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\ReturnServiceInterface;
use App\Enums\ReturnType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceReturnableService;
use Illuminate\Http\Request;

class CustomerReturnRecordController extends Controller
{
    /**
     * Show the return form for one invoice of a customer, so staff can log a
     * full or a partial return on the customer's behalf.
     */
    public function create(Customer $customer, Invoice $invoice, InvoiceReturnableService $returnable)
    {
        abort_unless($invoice->customer_id === $customer->id, 404);

        $lines = $returnable->lines($invoice);

        if ($lines->isEmpty()) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('error', 'لا توجد كميات قابلة للاسترجاع في هذه الفاتورة.');
        }

        return view('dashboard.customers.return_create', [
            'customer' => $customer,
            'invoice' => $invoice,
            'lines' => $lines,
            'types' => ReturnType::cases(),
        ]);
    }

    /**
     * Persist the return, capping every line at what is still returnable.
     */
    public function store(
        Request $request,
        Customer $customer,
        Invoice $invoice,
        InvoiceReturnableService $returnable,
        ReturnServiceInterface $returnService
    ) {
        abort_unless($invoice->customer_id === $customer->id, 404);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:full,partial'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $allowed = $returnable->lines($invoice)->keyBy('id');
        $items = [];
        $totalRefund = 0.0;

        foreach ($validated['quantities'] as $invoiceItemId => $quantity) {
            $quantity = (float) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $line = $allowed->get((int) $invoiceItemId);

            // Skip anything already returned in full, and cap a line that asks
            // for more than the invoice line actually holds.
            if (! $line) {
                continue;
            }

            $quantity = min($quantity, (float) $line['returnable']);

            $items[] = [
                'invoice_item_id' => (int) $invoiceItemId,
                'batch_item_id' => $line['batch_item_id'],
                'quantity' => $quantity,
                'unit_price' => (float) $line['unit_price'],
            ];

            $totalRefund += $quantity * (float) $line['unit_price'];
        }

        if ($items === []) {
            return back()->with('error', 'يجب اختيار كمية واحدة على الأقل للاسترجاع.');
        }

        $returnRequest = $returnService->createRequest(
            $invoice,
            $customer,
            $validated['type'],
            $validated['reason'],
            $items,
        );

        $returnRequest->update(['total_refund' => round($totalRefund, 2)]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'تم تسجيل طلب الاسترجاع رقم ' . $returnRequest->return_number . ' بنجاح.');
    }
}