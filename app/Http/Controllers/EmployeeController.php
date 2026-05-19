<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Country;
use App\Models\SecurityGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['organization', 'manager', 'branch', 'contactCountry']);

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($orgId = $request->get('organization_id')) {
            $query->where('organization_id', $orgId);
        }

        if ($type = $request->get('employee_type')) {
            $query->where('employee_type', $type);
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $employees = $query->get();
            $employeesPaginated = new \Illuminate\Pagination\LengthAwarePaginator($employees, $employees->count(), $employees->count(), 1);
        } else {
            $employeesPaginated = $query->paginate((int)$perPage);
        }

        $organizations = Organization::all();
        return view('employees.index', compact('employeesPaginated', 'organizations'));
    }

    public function create()
    {
        $organizations = Organization::all();
        $branches = Branch::all();
        $countries = Country::all();
        $securityGroups = SecurityGroup::all();
        $managers = Employee::all();
        return view('employees.form', compact('organizations', 'branches', 'countries', 'securityGroups', 'managers'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request, null);
        $employee = Employee::create($validated);
        if ($request->has('security_groups')) {
            $employee->securityGroups()->sync($request->security_groups);
        }
        return redirect()->route('employees.index')->with('success', 'تم إنشاء الموظف بنجاح');
    }

    public function show(Employee $employee)
    {
        $employee->load(['organization', 'manager', 'branch', 'contactCountry', 'securityGroups']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $organizations = Organization::all();
        $branches = Branch::all();
        $countries = Country::all();
        $securityGroups = SecurityGroup::all();
        $managers = Employee::where('id', '!=', $employee->id)->get();
        return view('employees.form', compact('employee', 'organizations', 'branches', 'countries', 'securityGroups', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        $employee->update($validated);
        if ($request->has('security_groups')) {
            $employee->securityGroups()->sync($request->security_groups);
        }
        return redirect()->route('employees.index')->with('success', 'تم تحديث الموظف بنجاح');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف بنجاح');
    }

    private function validateEmployee(Request $request, $ignoreId = null)
    {
        $uniqueCode = Rule::unique('employees', 'employee_code');
        $uniqueUser = Rule::unique('employees', 'user_name');
        if ($ignoreId) {
            $uniqueCode->ignore($ignoreId);
            $uniqueUser->ignore($ignoreId);
        }

        return $request->validate([
            'employee_name'       => 'required|string|max:255',
            'employee_code'       => ['required', 'string', 'max:100', $uniqueCode],
            'organization_id'     => 'required|exists:organizations,id',
            'employee_type'       => 'required|string|max:50',
            'phone'               => 'nullable|string|max:20',
            'mobile'              => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255',
            'national_id'         => 'nullable|string|max:50',
            'on_hold'             => 'boolean',
            'inactive'            => 'boolean',
            'out_source'          => 'boolean',
            'organization_access' => 'nullable|string',
            'manager_level'       => 'nullable|string|max:100',
            'manager_id'          => 'nullable|exists:employees,id',
            'divisions'           => 'nullable|array',
            'default_division'    => 'nullable|string|max:255',
            'device'              => 'nullable|string|max:255',
            'vehicle'             => 'nullable|string|max:255',
            'territory'           => 'nullable|string|max:255',
            'driver'              => 'nullable|string|max:255',
            'helper'              => 'nullable|string|max:255',
            'region'              => 'nullable|string|max:255',
            'channel'             => 'nullable|string|max:255',
            'items_list'          => 'nullable|array',
            'employee_branch_id'  => 'nullable|exists:branches,id',
            'contact_country_id'  => 'nullable|exists:countries,id',
            'user_name'           => ['nullable', 'string', 'max:255', $uniqueUser],
            'password'            => $ignoreId ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            'currency'            => 'nullable|string|max:3',
            'balance'             => 'nullable|numeric',
            'credit_limit'        => 'nullable|numeric',
            'security_groups'     => 'nullable|array',
            'security_groups.*'   => 'exists:security_groups,id',
        ] + $this->documentSequenceValidationRules());
    }

    private function documentSequenceValidationRules()
    {
        $fields = [
            'invoice_id', 'transaction_credit_note', 'transaction_debit_note', 'payment_id',
            'warehouse_transaction_id', 'return_id', 'order_id', 'sample_id', 'applied_payment_id',
            'replenish_id', 'remove_id', 'new_customer_code', 'settlement_id', 'order_invoice_id',
            'return_order_id', 'contracted_foc_id', 'unlimited_foc_id', 'fixed_incentive_id',
            'variable_incentive_id', 'organization_credit_note', 'organization_debit_note',
            'purchase_order_id', 'prison_foc_id', 'guarantees_foc_id', 'gift_voucher_id',
            'icash_transaction_id', 'icash_reversal_id', 'delivery_note_id', 'promotion_transaction_id',
            'max_customer_code', 'credit_note_request', 'customer_app_order_id', 'applied_e_payment_id',
            'customer_app_credit_note_id', 'customer_app_down_payment_id'
        ];
        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = 'nullable|integer|min:0';
        }
        return $rules;
    }
}