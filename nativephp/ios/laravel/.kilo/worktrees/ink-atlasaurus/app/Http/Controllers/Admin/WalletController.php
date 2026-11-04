<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        try {
            $wallets = \App\Models\CustomerWallet::with(['customer', 'currency'])
                ->when(request('search'), function ($q, $search) {
                    $q->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                })
                ->when(request('currency_id'), function ($q, $currencyId) {
                    $q->where('currency_id', $currencyId);
                })
                ->when(request('status'), function ($q, $status) {
                    $q->where('is_active', $status === 'active' ? true : false);
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();

            $currencies = \App\Models\Currency::all();
            $totals = [
                'total_balance' => \App\Models\CustomerWallet::sum('balance'),
                'count' => \App\Models\CustomerWallet::count(),
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            $wallets = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $currencies = \App\Models\Currency::all() ?? collect();
            $totals = ['total_balance' => 0, 'count' => 0];
        }

        return view('dashboard.wallets.index', compact('wallets', 'currencies', 'totals'));
    }

    /**
     * Wallet details: the customer's wallet summary plus the full transaction
     * history, filterable by type, method and date range. Transactions are
     * paginated rather than capped, so a long-lived wallet stays navigable.
     */
    public function show(Request $request, \App\Models\CustomerWallet $wallet)
    {
        $wallet->load(['customer', 'currency']);

        $filters = [
            'type' => $request->string('type')->toString() ?: null,
            'method' => $request->string('method')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

        $query = $wallet->transactions()->with(['createdBy', 'reference']);

        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }

        if ($filters['method']) {
            $query->where('method', $filters['method']);
        }

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        // Totals come from the wallet itself, not the filtered page, so the
        // summary keeps describing the wallet as a whole.
        $stats = [
            'count' => $wallet->transactions()->count(),
            'deposits' => (float) $wallet->transactions()->where('type', 'deposit')->sum('amount'),
            'withdrawals' => (float) $wallet->transactions()->where('type', 'withdrawal')->sum('amount'),
            'purchases' => (float) $wallet->transactions()->where('type', 'purchase')->sum('amount'),
        ];

        return view('dashboard.wallets.show', [
            'wallet' => $wallet,
            'transactions' => $query->latest('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'stats' => $stats,
            'types' => \App\Enums\WalletTransactionType::cases(),
            'methods' => $wallet->transactions()->whereNotNull('method')->distinct()->pluck('method'),
        ]);
    }

    /**
     * Create a wallet for a customer.
     *
     * The customer_wallets table enforces one wallet per customer, so an
     * existing wallet is reported as a validation error instead of crashing on
     * the unique constraint. An optional opening balance is recorded through
     * WalletService::deposit so it appears in the transaction history like any
     * other deposit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'customer_id.required' => 'يرجى اختيار العميل.',
            'customer_id.exists' => 'العميل المحدد غير موجود.',
            'currency_id.required' => 'يرجى اختيار العملة.',
            'currency_id.exists' => 'العملة المحددة غير موجودة.',
            'opening_balance.numeric' => 'الرصيد الافتتاحي يجب أن يكون رقمًا.',
            'opening_balance.min' => 'الرصيد الافتتاحي لا يمكن أن يكون سالبًا.',
            'notes.max' => 'الملاحظات يجب أن تكون 255 حرفًا كحد أقصى.',
        ]);

        $customer = \App\Models\Customer::findOrFail($validated['customer_id']);

        if ($customer->wallets()->exists()) {
            return redirect()
                ->route('customers.show', $customer)
                ->withErrors(['wallet' => 'هذا العميل لديه محفظة بالفعل.']);
        }

        $openingBalance = (float) ($validated['opening_balance'] ?? 0);

        $wallet = DB::transaction(function () use ($customer, $validated) {
            return \App\Models\CustomerWallet::create([
                'customer_id' => $customer->id,
                'currency_id' => $validated['currency_id'],
                'balance' => 0,
                'total_deposited' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);
        });

        if ($openingBalance > 0) {
            app(\App\Contracts\WalletServiceInterface::class)->deposit(
                $customer,
                $openingBalance,
                $validated['method'] ?? 'cash',
                null,
                auth()->id(),
                $validated['notes'] ?? 'رصيد افتتاحي عند إنشاء المحفظة',
            );
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'تم إنشاء المحفظة بنجاح' . ($openingBalance > 0 ? ' برصيد افتتاحي ' . number_format($openingBalance, 2) : '') . '.');
    }

    public function deposit(\Illuminate\Http\Request $request, \App\Models\CustomerWallet $wallet)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,cheque,online',
            'reference_number' => 'nullable|string|max:120',
            'notes' => 'nullable|string',
            'return_to' => 'nullable|in:wallet',
        ], [
            'amount.required' => 'يرجى إدخال المبلغ.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقمًا.',
            'amount.min' => 'المبلغ يجب أن يكون 0.01 على الأقل.',
            'method.required' => 'يرجى اختيار طريقة الإيداع.',
            'method.in' => 'طريقة الإيداع غير صحيحة.',
            'reference_number.max' => 'رقم المرجع يجب أن يكون 120 حرفًا كحد أقصى.',
            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
        ]);

        try {
            $transaction = app(\App\Contracts\WalletServiceInterface::class)->deposit(
                $wallet->customer,
                (float) $validated['amount'],
                $validated['method'],
                $validated['reference_number'] ?? null,
                auth()->id(),
                $validated['notes'] ?? null,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('customers.show', $wallet->customer)
                ->withErrors(['wallet' => 'تعذّر تسجيل حركة الإيداع: ' . $e->getMessage()]);
        }

        // Deposits made from the wallet page stay on the wallet page; those made
        // from the customer page go back to the customer.
        $destination = ($validated['return_to'] ?? null) === 'wallet'
            ? route('wallets.show', $wallet)
            : route('customers.show', $wallet->customer);

        return redirect()
            ->to($destination)
            ->with('success', 'تم تسجيل إيداع بمبلغ ' . number_format((float) $transaction->amount, 2)
                . ' — رقم الحركة #' . $transaction->id
                . ' — الرصيد بعد الإيداع: ' . number_format((float) $transaction->balance_after, 2) . '.');
    }
}
