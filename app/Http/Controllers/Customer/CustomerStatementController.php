<?php

namespace App\Http\Controllers\Customer;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWallet;
use App\Models\Invoice;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerStatementController extends Controller
{
    use CustomerAppHelpers;

    /**
     * The customer's financial statement: every wallet movement (deposits,
     * purchases, withdrawals) together with the invoices those movements refer
     * to, so a movement can always be traced back to a document.
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $from = $request->date('from');
        $to = $request->date('to');

        $wallet = CustomerWallet::where('customer_id', $customer->id)->first();

        $transactions = $wallet
            ? WalletTransaction::with(['reference'])
                ->where('wallet_id', $wallet->id)
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(200)
                ->get()
            : collect();

        $invoices = Invoice::with(['items'])
            ->where('customer_id', $customer->id)
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to))
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $rows = $transactions->map(fn (WalletTransaction $t) => $this->presentTransaction($t, $invoices));

        $totals = [
            'deposits' => (float) $rows->where('direction', 'credit')->sum('amount'),
            'debits' => abs((float) $rows->where('direction', 'debit')->sum('amount')),
            'balance' => (float) ($wallet->balance ?? 0),
            'invoiced' => (float) $invoices->sum('total_amount'),
            'paid' => (float) $invoices->sum('paid_amount'),
        ];

        $totals['outstanding'] = round($totals['invoiced'] - $totals['paid'], 2);

        return view('app_android.more.financial_statement', [
            'customer' => $customer,
            'wallet' => $wallet,
            'rows' => $rows,
            'invoices' => $invoices,
            'totals' => $totals,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
        ]);
    }

    /**
     * Normalise a wallet movement for display and attach the invoice it
     * relates to, when there is one.
     */
    private function presentTransaction(WalletTransaction $transaction, Collection $invoices): array
    {
        $amount = (float) $transaction->amount;
        $direction = $amount < 0 ? 'debit' : 'credit';

        $invoice = null;

        // A purchase points at its invoice; a deposit may point at a payment.
        if ($transaction->reference_type) {
            $short = class_basename($transaction->reference_type);

            if ($short === 'Invoice' && $transaction->reference_id) {
                $invoice = $invoices->firstWhere('id', $transaction->reference_id)
                    ?? Invoice::find($transaction->reference_id);
            } elseif ($short === 'Payment' && $transaction->reference_id) {
                $payment = \App\Models\Payment::find($transaction->reference_id);
                $invoice = $payment ? Invoice::with('items')->find($payment->invoice_id) : null;
            }
        }

        return [
            'id' => $transaction->id,
            'date' => $transaction->created_at?->format('Y-m-d'),
            'type' => (string) $transaction->type,
            'label' => $this->typeLabel((string) $transaction->type, $direction),
            'amount' => abs($amount),
            'direction' => $direction,
            'signed' => $direction === 'debit' ? -abs($amount) : abs($amount),
            'balance_after' => (float) $transaction->balance_after,
            'method' => $this->methodLabel($transaction->method),
            'reference' => $transaction->reference_number ?: ('#' . $transaction->id),
            'notes' => $transaction->notes,
            'invoice_id' => $invoice?->id,
            'invoice_number' => $invoice?->invoice_number,
        ];
    }

    private function typeLabel(string $type, string $direction): string
    {
        return match ($type) {
            'deposit' => 'إيداع',
            'purchase' => $direction === 'debit' ? 'دفع فاتورة' : 'استرداد',
            'withdrawal' => 'سحب',
            'refund' => 'استرداد',
            default => $type,
        };
    }

    private function methodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'نقداً',
            'bank_transfer' => 'تحويل بنكي',
            'card' => 'بطاقة',
            'online' => 'دفع إلكتروني',
            'wallet' => 'محفظة',
            'check' => 'شيك',
            default => $method ?: '—',
        };
    }
}
