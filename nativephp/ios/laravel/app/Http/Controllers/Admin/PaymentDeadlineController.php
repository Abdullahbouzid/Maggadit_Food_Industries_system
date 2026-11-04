<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $this->validateSetting($request);

        try {
            \App\Models\PaymentDeadlineSetting::create(
                $validated + ['updated_by' => $request->user()?->id]
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', trans('messages.payment_deadline_page.error_create'));
        }

        return redirect()
            ->route('payment-deadlines.index')
            ->with('success', trans('messages.payment_deadline_page.success_create'));
    }

    public function edit(\App\Models\PaymentDeadlineSetting $paymentDeadline)
    {
        return view('dashboard.settings.payment-deadlines.edit', [
            'setting' => $paymentDeadline,
            'customers' => \App\Models\Customer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, \App\Models\PaymentDeadlineSetting $paymentDeadline)
    {
        $validated = $this->validateSetting($request);

        try {
            $paymentDeadline->update($validated + ['updated_by' => $request->user()?->id]);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', trans('messages.payment_deadline_page.error_update'));
        }

        return redirect()
            ->route('payment-deadlines.index')
            ->with('success', trans('messages.payment_deadline_page.success_update'));
    }

    /**
     * Shared rules for storing and updating a deadline.
     *
     * customer_ids is only meaningful when the rule is scoped to specific
     * customers, so it is cleared otherwise rather than left dangling behind an
     * "all customers" setting.
     */
    protected function validateSetting(Request $request): array
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'string', 'max:60'],
            'hours_limit' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'applies_to' => ['required', 'string', 'max:60'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_type.required' => trans('messages.payment_deadline_page.required_payment_type'),
            'hours_limit.required' => trans('messages.payment_deadline_page.required_hours_limit'),
            'hours_limit.numeric' => trans('messages.payment_deadline_page.invalid_hours'),
            'hours_limit.min' => trans('messages.payment_deadline_page.invalid_hours'),
            'applies_to.required' => trans('messages.payment_deadline_page.required_applies_to'),
        ]);

        if (($validated['applies_to'] ?? null) === 'specific') {
            $request->validate([
                'customer_ids' => ['required', 'array', 'min:1'],
                'customer_ids.*' => ['integer', 'exists:customers,id'],
            ], [
                'customer_ids.required' => trans('messages.payment_deadline_page.required_customers'),
                'customer_ids.min' => trans('messages.payment_deadline_page.required_customers'),
            ]);
        } else {
            $validated['customer_ids'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
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
