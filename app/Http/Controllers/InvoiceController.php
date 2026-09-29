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
            'status' => 'nullable|in:pending,completed,cancelled',
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
            'status' => 'nullable|in:pending,completed,cancelled',
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
     * Download invoice PDF.
     */
    public function downloadPdf(Invoice $invoice, Request $request)
    {
        $invoice->load(['customer', 'priceList', 'items.item', 'items.itemPack.uom', 'items.priceList', 'items.priceHistory']);

        $lang = $request->query('lang', 'bilingual');

        $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadView('pdf.invoice', compact('invoice', 'lang'))
            ->setPaper('a4', 'portrait', [
                'encoding' => 'UTF-8',
                'enable-local-file-access' => true,
            ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
