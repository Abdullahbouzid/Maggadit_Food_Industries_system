<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', Rule::unique('customers')->ignore($customer->id)],
            'email' => ['sometimes', 'email', Rule::unique('customers')->ignore($customer->id)],
        ]);

        $customer->update($request->only(['name', 'username', 'email']));

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $customer = $request->user();

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $customer->password = Hash::make($request->new_password);
        $customer->save();

        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
    }
}