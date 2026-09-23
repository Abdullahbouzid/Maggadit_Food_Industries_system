<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
