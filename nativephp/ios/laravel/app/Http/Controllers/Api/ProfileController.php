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

        if (! Hash::check($request->current_password, $customer->password)) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $customer->password = Hash::make($request->new_password);
        $customer->save();

        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];

        if ($request->filled('old_password') || $request->filled('password')) {
            $rules['old_password'] = 'required';
            $rules['password'] = 'required|min:6|confirmed';
        }

        $request->validate($rules);

        if ($request->filled('old_password')) {
            if (! Hash::check($request->old_password, $customer->password)) {
                return response()->json(['errors' => ['old_password' => ['كلمة المرور القديمة غير صحيحة']]], 422);
            }
            $customer->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);

            if ($customer->avatar && file_exists(public_path('uploads/customers/'.$customer->avatar))) {
                @unlink(public_path('uploads/customers/'.$customer->avatar));
            }

            $customer->avatar = $filename;
        }

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->username = $request->username;
        $customer->save();

        return response()->json(['success' => true, 'customer' => $customer]);
    }
}
