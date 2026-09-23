<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PaymentDeadlineController extends Controller
{
    public function index()
    {
        try {
            $settings = \App\Models\PaymentDeadlineSetting::when(request('search'), function ($q, $search) {
                $q->where('payment_type', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
                ->when(request('status'), function ($q, $status) {
                    $q->where('is_active', $status === 'active' ? true : false);
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } catch (\Illuminate\Database\QueryException $e) {
            $settings = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('dashboard.settings.payment-deadlines.index', compact('settings'));
    }

    public function create()
    {
        return view('dashboard.settings.payment-deadlines.create', [
            'customers' => \App\Models\Customer::orderBy('name')->get(),
        ]);
    }

    public function edit(\App\Models\PaymentDeadlineSetting $paymentDeadline)
    {
        return view('dashboard.settings.payment-deadlines.edit', [
            'setting' => $paymentDeadline,
            'customers' => \App\Models\Customer::orderBy('name')->get(),
        ]);
    }

    public function destroy(\App\Models\PaymentDeadlineSetting $paymentDeadline)
    {
        $paymentDeadline->delete();

        return redirect()->route('payment-deadlines.index')->with('success', 'تم حذف الإعداد');
    }

    public function toggle(\App\Models\PaymentDeadlineSetting $paymentDeadline)
    {
        $paymentDeadline->update(['is_active' => ! $paymentDeadline->is_active]);

        return redirect()->route('payment-deadlines.index')->with('success', 'تم تبديل الحالة');
    }
}
