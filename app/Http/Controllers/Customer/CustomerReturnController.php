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

        // Transform local DB data to match remote API format (strings for status/type)
        // This ensures consistent handling in the view regardless of data source
        if (! $returns->isEmpty()) {
            $returns->getCollection()->transform(function ($return) {
                // Convert status enum to string value
                if ($return->status instanceof \App\Enums\ReturnStatus) {
                    $return->status = $return->status->value;
                }
                // Convert type enum to string value
                if ($return->type instanceof \App\Enums\ReturnType) {
                    $return->type = $return->type->value;
                }
                // Transform items
                if ($return->items) {
                    $return->items->transform(function ($line) {
                        // Ensure item is an object with name_ar/name_en
                        if ($line->item instanceof \App\Models\Item) {
                            $line->item = (object) [
                                'name_ar' => $line->item->name_ar,
                                'name_en' => $line->item->name_en,
                            ];
                        }
                        // Ensure pack is an object with display_name
                        if ($line->pack instanceof \App\Models\Pack) {
                            $line->pack = (object) [
                                'display_name' => $line->pack->display_name ?? $line->pack->name_ar ?? '',
                            ];
                        }
                        // Ensure quantity_returned is float
                        $line->quantity_returned = (float) $line->quantity_returned;
                        return $line;
                    });
                }
                return $return;
            });
        }

        // If local DB is empty, fetch from external API (for APK)
        if ($returns->isEmpty()) {
            $token = session('api_token') ?: $request->bearerToken();
            \Illuminate\Support\Facades\Log::info('Returns API: local DB empty, fetching from remote', [
                'has_token' => (bool)$token,
                'token_prefix' => $token ? substr($token, 0, 10) . '...' : 'none',
            ]);
            
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/returns', $token);
                \Illuminate\Support\Facades\Log::info('Returns API: remote response', [
                    'success' => $remoteData !== null,
                    'has_returns' => $remoteData && isset($remoteData['returns']),
                    'data_count' => $remoteData && isset($remoteData['returns']['data']) ? count($remoteData['returns']['data']) : 0,
                ]);
                
                if ($remoteData && isset($remoteData['returns'])) {
                    $items = collect($remoteData['returns']['data'])->map(function ($r) {
                        // Handle status from API (string or object with value)
                        $statusVal = $r['status'];
                        if (is_object($statusVal) && isset($statusVal->value)) {
                            $statusVal = $statusVal->value;
                        }
                        // Handle type from API (string or object with value)
                        $typeVal = $r['type'];
                        if (is_object($typeVal) && isset($typeVal->value)) {
                            $typeVal = $typeVal->value;
                        }

                        return (object) [
                            'id' => $r['id'],
                            'return_number' => $r['return_number'],
                            'status' => $statusVal,
                            'type' => $typeVal,
                            'reason' => $r['reason'],
                            'total_refund' => $r['total_refund'],
                            'created_at' => $r['created_at'],
                            'invoice' => $r['invoice'] ? (object) $r['invoice'] : null,
                            'items' => collect($r['items'])->map(function ($i) {
                                return (object) [
                                    'id' => $i['id'],
                                    'item' => $i['item'] ? (object) $i['item'] : null,
                                    'quantity_returned' => $i['quantity_returned'],
                                    'pack' => $i['pack'] ? (object) $i['pack'] : null,
                                ];
                            }),
                        ];
                    });

                    // Create a simple array of objects for the view (not a paginator)
                    $returns = $items->all();
                    $total = $remoteData['returns']['total'] ?? 0;

                    $summary = collect($remoteData['summary'])->mapWithKeys(function ($item, $status) {
                        return [$status => (object) [
                            'aggregate' => $item['aggregate'] ?? 0,
                            'refund' => $item['refund'] ?? 0,
                        ]];
                    });

                    $pendingCount = $remoteData['pending_count'] ?? 0;

                    \Illuminate\Support\Facades\Log::info('Returns API: returning view with data', [
                        'returns_count' => count($returns),
                        'total' => $total,
                        'pending_count' => $pendingCount,
                    ]);

                    return view('app_android.more.returns', [
                        'customer' => $customer,
                        'returns' => $returns,
                        'summary' => $summary,
                        'pendingCount' => $pendingCount,
                        'totalReturns' => $total,
                        'debug' => [
                            'source' => 'remote_api',
                            'returns_count' => count($returns),
                            'has_api_token' => true,
                        ],
                    ]);
                }
            }
        }

        // For local DB, convert paginator items to plain array
        $returnsArray = $returns->getCollection()->all();
        $pendingCount = (int) ($summary['pending']->aggregate ?? 0);

        \Illuminate\Support\Facades\Log::info('Returns: returning view with local DB data', [
            'returns_count' => count($returnsArray),
            'total' => $returns->total(),
            'pending_count' => $pendingCount,
        ]);

        return view('app_android.more.returns', [
            'customer' => $customer,
            'returns' => $returnsArray,
            'summary' => $summary,
            'pendingCount' => $pendingCount,
            'totalReturns' => $returns->total(),
            'debug' => [
                'source' => 'local_db',
                'returns_count' => count($returnsArray),
                'has_api_token' => (bool)session('api_token'),
            ],
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

        // If local DB is empty, fetch from external API (for APK)
        if ($rows->isEmpty()) {
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/my-products', $token);
                if ($remoteData && isset($remoteData['rows'])) {
                    $rows = collect($remoteData['rows'])->map(function ($r) {
                        return [
                            'item_id' => $r['item_id'],
                            'name' => $r['name'],
                            'code' => $r['code'],
                            'total_quantity' => (float) $r['total_quantity'],
                            'invoice_count' => (int) $r['invoice_count'],
                            'total_value' => (float) $r['total_value'],
                            'batches' => collect($r['batches'])->map(function ($b) {
                                return (object) [
                                    'batch_item_id' => $b['batch_item_id'],
                                    'batch_number' => $b['batch_number'],
                                    'expiry_date' => $b['expiry_date'],
                                    'quantity' => (float) $b['quantity'],
                                ];
                            }),
                        ];
                    });
                    $totalItems = $remoteData['total_items'] ?? $rows->count();
                    $totalQuantity = $remoteData['total_quantity'] ?? round($rows->sum('total_quantity'), 2);

                    return view('app_android.more.my_products', [
                        'customer' => $customer,
                        'rows' => $rows,
                        'totalItems' => $totalItems,
                        'totalQuantity' => $totalQuantity,
                    ]);
                }
            }
        }

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
