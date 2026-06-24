<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'username' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $customer->tokens()->delete();
        $token = $customer->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'customer' => $customer->only(['id', 'name', 'username', 'email']),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:customers',
            'email' => 'required|email|unique:customers',
            'password' => 'required|string|min:6|confirmed', // نترك confirmed إذا أضفنا حقل confirm
        ]);

        // إنشاء العميل (سيتم تشفير كلمة المرور تلقائياً بسبب $casts)
        $customer = Customer::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password, // لا نستخدم Hash::make
        ]);

        $token = $customer->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'customer' => $customer->only(['id', 'name', 'username', 'email']),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'username', 'email']));
    }
}