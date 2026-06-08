<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * تسجيل دخول العميل وإرجاع token
     * POST /api/customer/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('username', $request->username)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'username' => ['بيانات الاعتماد غير صحيحة.'],
            ]);
        }

        // حذف التوكنات القديمة (اختياري - لتسجيل دخول من جهاز واحد فقط)
        // $customer->tokens()->delete();

        $token = $customer->createToken('customer-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * تسجيل خروج العميل (حذف التوكن الحالي)
     * POST /api/customer/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * جلب بيانات العميل الحالي
     * GET /api/customer/profile
     */
    public function profile(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'username' => $customer->username,
                'email' => $customer->email,
            ],
        ]);
    }
}
