<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index(Request $request)
{
    $query = Customer::query();

    // البحث
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // عدد السجلات لكل صفحة
    $perPage = $request->input('per_page', 15);
    if ($perPage === 'all') {
        $perPage = 999999; // عدد كبير جداً لعرض الكل مع بقاء pagination
    }

    $customers = $query->paginate($perPage);

    // الإحصائيات
    $totalCustomers = Customer::count();
    $withAvatar = Customer::whereNotNull('avatar')->count();
    $withActiveOrganization = Customer::whereNotNull('active_organization_id')->count();
    $withoutOrganization = Customer::whereNull('active_organization_id')->count();

    return view('dashboard.customers.index', compact(
        'customers',
        'totalCustomers',
        'withAvatar',
        'withActiveOrganization',
        'withoutOrganization'
    ));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = Organization::pluck('name', 'id');
        return view('dashboard.customers.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customers',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active_organization_id' => 'nullable|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();

        // معالجة الصورة
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/customers'), $avatarName);
            $input['avatar'] = $avatarName;
        }

        $input['password'] = Hash::make($input['password']);

        $customer = Customer::create($input);

        // إذا تم تحديد منظمة نشطة، نربط العميل بها
        if ($request->filled('active_organization_id')) {
            $customer->organizations()->attach($request->active_organization_id, ['is_active' => true]);
            $customer->active_organization_id = $request->active_organization_id;
            $customer->save();
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('dashboard.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $organizations = Organization::pluck('name', 'id');
        return view('dashboard.customers.edit', compact('customer', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customers,username,' . $customer->id,
            'email' => 'required|string|email|max:255|unique:customers,email,' . $customer->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active_organization_id' => 'nullable|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();

        // معالجة الصورة
        if ($request->hasFile('avatar')) {
            // حذف الصورة القديمة إذا وجدت
            if ($customer->avatar && file_exists(public_path('uploads/customers/' . $customer->avatar))) {
                unlink(public_path('uploads/customers/' . $customer->avatar));
            }
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/customers'), $avatarName);
            $input['avatar'] = $avatarName;
        } else {
            // الحفاظ على الصورة القديمة
            $input['avatar'] = $customer->avatar;
        }

        // معالجة كلمة المرور
        if ($request->filled('password')) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        // تحديث بيانات العميل
        $customer->update($input);

        // تحديث العلاقة مع المنظمة النشطة
        if ($request->filled('active_organization_id')) {
            // التأكد من إضافة العميل إلى هذه المنظمة في الجدول الوسيط
            if (!$customer->organizations()->where('organization_id', $request->active_organization_id)->exists()) {
                $customer->organizations()->attach($request->active_organization_id, ['is_active' => false]);
            }
            // تعيين المنظمة النشطة باستخدام الدالة المعدة
            $customer->setActiveOrganization($request->active_organization_id);
        } else {
            // إذا لم يتم اختيار منظمة، نقوم بإلغاء تفعيل جميع المنظمات
            $customer->organizations()->updateExistingPivot(
                $customer->organizations->pluck('id')->toArray(),
                ['is_active' => false]
            );
            $customer->active_organization_id = null;
            $customer->save();
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // حذف الصورة إذا وجدت
        if ($customer->avatar && file_exists(public_path('uploads/customers/' . $customer->avatar))) {
            unlink(public_path('uploads/customers/' . $customer->avatar));
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}