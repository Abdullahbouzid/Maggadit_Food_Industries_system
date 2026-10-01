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

    public function show(\App\Models\CustomerWallet $wallet)
    {
        $wallet->load(['customer', 'currency', 'transactions' => fn ($q) => $q->latest()->limit(20)]);

        return view('dashboard.wallets.show', compact('wallet'));
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
        ], [], [
            'customer_id' => 'العميل',
            'currency_id' => 'العملة',
            'opening_balance' => 'الرصيد الافتتاحي',
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
        ]);

        $service = app(\App\Contracts\WalletServiceInterface::class);
        $service->deposit(
            $wallet->customer,
            (float) $validated['amount'],
            $validated['method'],
            $validated['reference_number'] ?? null,
            auth()->id(),
            $validated['notes'] ?? null,
        );

        return redirect()->route('wallets.index')->with('success', 'تم إيداع الرصيد');
    }
}
