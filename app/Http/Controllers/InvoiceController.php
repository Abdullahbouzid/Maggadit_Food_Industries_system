<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\Warehouse\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request) {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') $perPage = 999999;

        $query = Invoice::with('customer');
        if ($request->filled('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('invoice_number', 'like', "%{$search}%");
        }

        $invoices = $query->latest()->paginate((int) $perPage);

        return view('dashboard.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        $items = Item::with(['images', 'category', 'brand'])->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.invoices.create', compact('customers', 'items', 'warehouses'));
    }

    public function store(Request $request, InvoiceService $invoiceService)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'status' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\InvoiceStatus::class)],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $invoice = $invoiceService->create(
            $request->only(['customer_id', 'invoice_date', 'status', 'notes']),
            $request->input('items')
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.item', 'items.warehouse']);

        return view('dashboard.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['items.item', 'items.warehouse']);
        $customers = Customer::all();
        $items = Item::with(['images', 'category', 'brand'])->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name_en')->get();

        return view('dashboard.invoices.edit', compact('invoice', 'customers', 'items', 'warehouses'));
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'status' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\InvoiceStatus::class)],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $invoice = $invoiceService->update(
            $invoice,
            $request->only(['customer_id', 'invoice_date', 'status', 'notes']),
            $request->input('items')
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            InvoiceItem::where('invoice_id', $invoice->id)->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }

    public function getWarehousesForItem(Request $request, $itemId, InvoiceService $invoiceService)
    {
        $warehouses = $invoiceService->getItemWarehouses((int) $itemId);

        return response()->json($warehouses);
    }

    /**
     * Approve an invoice.
     *
     * Commits stock, creates the delivery and applies the payment deadline
     * (cash invoices get payment_deadline_at = now + configured hours).
     */
    public function approve(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ], [], ['notes' => 'ملاحظات']);

        try {
            $result = app(\App\Contracts\InvoiceLifecycleServiceInterface::class)->approve(
                $invoice,
                $request->user(),
                $validated['notes'] ?? null,
            );
        } catch (\App\Exceptions\InvalidTransitionException $e) {
            return back()->withErrors(['invoice' => $this->invalidTransitionMessage($invoice)]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['invoice' => 'تعذّرت الموافقة على الفاتورة: ' . $e->getMessage()]);
        }

        $message = 'تمت الموافقة على الفاتورة ' . $result->invoice->invoice_number . '.';

        if ($result->deadline) {
            $message .= ' مهلة الدفع حتى ' . $result->deadline->format('Y-m-d H:i') . '.';
        }

        return redirect()
            ->route('invoices.show', $result->invoice)
            ->with('success', $message);
    }

    /**
     * Reject an invoice (customer or pricing issue) and release its stock.
     */
    public function reject(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'يرجى كتابة سبب الرفض.',
        ], ['reason' => 'سبب الرفض']);

        try {
            $updated = app(\App\Contracts\InvoiceLifecycleServiceInterface::class)->reject(
                $invoice,
                $request->user(),
                $validated['reason'],
            );
        } catch (\App\Exceptions\InvalidTransitionException $e) {
            return back()->withErrors(['invoice' => $this->invalidTransitionMessage($invoice)]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['invoice' => 'تعذّر رفض الفاتورة: ' . $e->getMessage()]);
        }

        return redirect()
            ->route('invoices.show', $updated)
            ->with('success', 'تم رفض الفاتورة ' . $updated->invoice_number . '.');
    }

    /**
     * Record a payment against an invoice.
     *
     * When the method is "wallet", PaymentService debits the customer wallet in
     * the same transaction, so an insufficient balance rolls the whole payment
     * back instead of leaving a payment row behind.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,cheque,online,wallet',
            'reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'يرجى إدخال المبلغ.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقمًا.',
            'amount.min' => 'المبلغ يجب أن يكون 0.01 على الأقل.',
            'method.required' => 'يرجى اختيار طريقة الدفع.',
            'method.in' => 'طريقة الدفع غير صحيحة.',
        ], [
            'amount' => 'المبلغ',
            'method' => 'طريقة الدفع',
        ]);

        $remaining = (float) $invoice->total_amount - (float) $invoice->paid_amount;

        if ($remaining <= 0) {
            return back()->withErrors(['payment' => 'هذه الفاتورة مسددة بالكامل.']);
        }

        if ((float) $validated['amount'] > $remaining) {
            return back()->withErrors([
                'payment' => 'المبلغ أكبر من المتبقي (' . number_format($remaining, 2) . ').',
            ]);
        }

        if ($validated['method'] === 'wallet') {
            $wallet = $invoice->customer?->wallets()->first();

            if (! $wallet) {
                return back()->withErrors(['payment' => 'لا توجد محفظة لهذا العميل.']);
            }

            $walletBalance = (float) $wallet->balance;
            $customerType = $invoice->customer?->type ?? 'cash';
            $creditLimit = (float) ($invoice->customer?->credit_limit ?? 0);

            // The wallet can cover the gap only for credit customers.
            $available = $customerType === 'credit'
                ? $walletBalance + $creditLimit
                : $walletBalance;

            if ($available < (float) $validated['amount']) {
                return back()->withErrors([
                    'payment' => 'رصيد المحفظة غير كافٍ. المتاح: ' . number_format($available, 2)
                        . ' والمطلوب: ' . number_format((float) $validated['amount'], 2) . '.',
                ]);
            }
        }

        try {
            $payment = app(\App\Contracts\PaymentServiceInterface::class)->record(
                $invoice,
                (float) $validated['amount'],
                $validated['method'],
                $validated['reference'] ?? null,
                $request->user()?->id,
                $validated['notes'] ?? null,
            );
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withErrors(['payment' => 'رصيد المحفظة غير كافٍ: ' . $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['payment' => 'تعذّر تسجيل الدفعة: ' . $e->getMessage()]);
        }

        $invoice->refresh();

        $message = 'تم تسجيل الدفعة ' . $payment->payment_number . ' بمبلغ '
            . number_format((float) $payment->amount, 2) . '.';

        $message .= $invoice->status === \App\Enums\InvoiceStatus::Paid->value
            ? ' سُدّدت الفاتورة بالكامل.'
            : ' المتبقي على الفاتورة: ' . number_format(
                (float) $invoice->total_amount - (float) $invoice->paid_amount, 2
            ) . '.';

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', $message);
    }

    /**
     * Build an Arabic message explaining which transitions are currently allowed,
     * instead of surfacing the raw English domain exception.
     */
    private function invalidTransitionMessage(Invoice $invoice): string
    {
        $status = $invoice->status instanceof \App\Enums\InvoiceStatus
            ? $invoice->status
            : \App\Enums\InvoiceStatus::tryFrom((string) $invoice->status);

        $current = $status?->label() ?? (string) $invoice->status;

        $message = "لا يمكن تنفيذ الإجراء: حالة الفاتورة الحالية هي «{$current}».";

        // A status outside the enum (e.g. legacy "completed") has no transitions.
        if (! $status) {
            return $message;
        }

        $allowed = collect(
            app(\App\Contracts\InvoiceLifecycleServiceInterface::class)->getAllowedTransitions($invoice)
        )->map(fn ($s) => $s->label())->all();

        return $allowed
            ? $message . ' الحالات المسموح بها حاليًا: ' . implode('، ', $allowed) . '.'
            : $message . ' هذه حالة نهائية لا يمكن تغييرها.';
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(Invoice $invoice, Request $request)
    {
        $invoice->load(['customer', 'priceList', 'items.item', 'items.itemPack.packCapacity.uom', 'items.priceList', 'items.priceHistory']);

        $lang = $request->query('lang', 'bilingual');

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.invoice', compact('invoice', 'lang'))
            ->setPaper('a4', 'portrait', [
                'encoding' => 'UTF-8',
                'enable-local-file-access' => true,
            ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
