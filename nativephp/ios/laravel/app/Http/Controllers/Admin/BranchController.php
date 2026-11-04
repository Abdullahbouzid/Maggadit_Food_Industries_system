<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customerId = $request->query('customer_id');

        $query = Branch::with('customer');

        if ($customerId) {
            $query->where('customer_id', $customerId);
            $customer = Customer::findOrFail($customerId);
        }

        $branches = $query->latest()->paginate(20);

        $customers = Customer::select('id', 'name')->get();

        return view('dashboard.branches.index', compact('branches', 'customers', 'customerId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customerId = $request->query('customer_id');
        $customer = $customerId ? Customer::findOrFail($customerId) : null;

        $customers = Customer::select('id', 'name')->get();

        return view('dashboard.branches.create', compact('customer', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours_from' => ['nullable', 'date_format:H:i'],
            'working_hours_to' => ['nullable', 'date_format:H:i'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::create($validated);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'تم إضافة الفرع بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {

        $branch->load('customer');

        return view('dashboard.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {

        $customers = Customer::select('id', 'name')->get();

        return view('dashboard.branches.edit', compact('branch', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours_from' => ['nullable', 'date_format:H:i'],
            'working_hours_to' => ['nullable', 'date_format:H:i'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update($validated);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'تم تحديث الفرع بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'تم حذف الفرع بنجاح.');
    }
}
