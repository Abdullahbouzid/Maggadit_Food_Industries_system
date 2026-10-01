<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ReturnType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ReturnRequest;
use App\Contracts\ReturnServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    use CustomerAppHelpers;
    /**
     * The customer's own return register: every request they raised, newest
     * first, with the lines and the refund totals.
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $returns = ReturnRequest::with([
            'invoice',
            'items.item',
            'items.pack',
        ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(20);

        $summary = ReturnRequest::where('customer_id', $customer->id)
            ->toBase()
            ->selectRaw('status, COUNT(*) as aggregate, COALESCE(SUM(total_refund), 0) as refund')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('app_android.more.returns', [
            'customer' => $customer,
            'returns' => $returns,
            'summary' => $summary,
            'pendingCount' => (int) ($summary['pending']->aggregate ?? 0),
        ]);
    }

    /**
     * Show the form for returning specific quantities off one invoice.
     */
    public function create(Request $request, $invoiceId)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $invoice = Invoice::where('customer_id', $customer->id)
            ->where(function ($q) use ($invoiceId) {
                $q->where('id', $invoiceId);

                if (is_numeric($invoiceId)) {
                    $q->orWhere('invoice_number', $invoiceId);
                }
            })
            ->first();

        if (! $invoice) {
            return redirect()->route('customer.returns')
                ->with('error', 'تعذر العثور على الفاتورة المطلوبة.');
        }

        return view('app_android.more.return_create', [
            'customer' => $customer,
            'invoice' => $invoice,
            'lines' => $this->returnableLines($invoice),
            'types' => ReturnType::cases(),
        ]);
    }

    /**
     * Record a return request for the quantities the customer selected.
     */
    public function store(Request $request, $invoiceId, ReturnServiceInterface $returnService)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $invoice = Invoice::where('customer_id', $customer->id)
            ->where(function ($q) use ($invoiceId) {
                $q->where('id', $invoiceId);

                if (is_numeric($invoiceId)) {
                    $q->orWhere('invoice_number', $invoiceId);
                }
            })
            ->first();

        if (! $invoice) {
            return redirect()->route('customer.returns')
                ->with('error', 'تعذر العثور على الفاتورة المطلوبة.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:full,partial'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $allowed = $this->returnableLines($invoice)->keyBy('id');
        $items = [];
        $totalRefund = 0.0;

        foreach ($validated['quantities'] as $invoiceItemId => $quantity) {
            $quantity = (float) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $line = $allowed->get((int) $invoiceItemId);

            // Ignore any line the customer does not actually own on this
            // invoice, or asks for more than they bought.
            if (! $line || $quantity > (float) $line['returnable']) {
                continue;
            }

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

        return redirect()->route('customer.returns')
            ->with('success', 'تم تسجيل طلب الاسترجاع رقم ' . $returnRequest->return_number . ' بنجاح.');
    }

    /**
     * Products this customer trades in, with the quantity that came out of
     * each production batch, so they can see exactly which batch they received.
     */
    public function products(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $totals = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy('items.id', 'items.name_ar', 'items.name_en', 'items.code')
            ->selectRaw(
                'items.id as item_id, items.name_ar, items.name_en, items.code,
                 SUM(invoice_items.quantity) as total_quantity,
                 COUNT(DISTINCT invoices.id) as invoice_count,
                 SUM(invoice_items.line_total) as total_value'
            )
            ->orderByDesc('total_quantity')
            ->get();

        $batches = $this->quantitiesPerBatch($customer->id);

        $rows = $totals->map(function ($row) use ($batches) {
            return [
                'item_id' => (int) $row->item_id,
                'name' => $row->name_ar ?: ($row->name_en ?: 'منتج'),
                'code' => $row->code ?: '—',
                'total_quantity' => (float) $row->total_quantity,
                'invoice_count' => (int) $row->invoice_count,
                'total_value' => (float) $row->total_value,
                'batches' => $batches->get((int) $row->item_id, collect()),
            ];
        });

        return view('app_android.more.my_products', [
            'customer' => $customer,
            'rows' => $rows,
            'totalItems' => $rows->count(),
            'totalQuantity' => round($rows->sum('total_quantity'), 2),
        ]);
    }

    /**
     * Group the customer's invoiced quantities by the production batch they
     * were drawn from.
     */
    private function quantitiesPerBatch(int $customerId)
    {
        $rows = DB::table('invoice_item_batches')
            ->join('invoice_items', 'invoice_items.id', '=', 'invoice_item_batches.invoice_item_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('batch_items', 'batch_items.id', '=', 'invoice_item_batches.batch_item_id')
            ->leftJoin('production_batches', 'production_batches.id', '=', 'batch_items.batch_id')
            ->where('invoices.customer_id', $customerId)
            ->groupBy(
                'invoice_items.item_id',
                'batch_items.id',
                'production_batches.batch_number',
                'batch_items.expiry_date'
            )
            ->selectRaw(
                'invoice_items.item_id as item_id,
                 batch_items.id as batch_item_id,
                 COALESCE(production_batches.batch_number, "—") as batch_number,
                 batch_items.expiry_date as expiry_date,
                 SUM(invoice_item_batches.quantity) as quantity'
            )
            ->orderBy('batch_items.expiry_date')
            ->get();

        return $rows->groupBy('item_id');
    }

    /**
     * Invoice lines the customer may still return, with the quantity that has
     * not already been returned.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function returnableLines(Invoice $invoice)
    {
        return app(\App\Services\InvoiceReturnableService::class)->lines($invoice);
    }
}
