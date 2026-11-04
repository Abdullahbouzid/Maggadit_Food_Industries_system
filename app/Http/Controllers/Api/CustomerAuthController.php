<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    // تسجيل الدخول وإرجاع توكن Sanctum
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $customer = Customer::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'username' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        // حفظ FCM token إذا تم إرساله
        if ($request->filled('fcm_token')) {
            $customer->fcm_token = $request->fcm_token;
            $customer->save();
        }

        // حذف التوكنات القديمة
        $customer->tokens()->delete();

        $token = $customer->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'username' => $customer->username,
                'email' => $customer->email,
                'active_organization_id' => $customer->active_organization_id,
            ],
            'organizations' => $customer->organizations()->get()->map(function ($org) use ($customer) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'is_active' => $customer->active_organization_id == $org->id,
                ];
            }),
        ]);
    }

    // تسجيل جديد (إذا أردت)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:customers',
            'email' => 'required|email|unique:customers',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $customer->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'customer' => $customer->only(['id', 'name', 'username', 'email']),
        ], 201);
    }

    // تسجيل الخروج (حذف التوكن الحالي)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    // جلب بيانات الملف الشخصي
    public function profile(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'username', 'email']));
    }
}
