Route::post('/mobile/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|unique:customers',
        'email' => 'required|email|unique:customers',
        'password' => 'required|string|min:6',
    ]);

    try {
        $response = \Illuminate\Support\Facades\Http::post('https://blue-beaver-923812.hostingersite.com/api/customer/register', [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response.json();
            $remoteUser = $data['customer'];

            // إنشاء أو تحديث المستخدم محلياً في الـ APK لمزامنة الجلسة
            $customer = \App\Models\Customer::updateOrCreate(
                ['username' => $remoteUser['username']],
                [
                    'name' => $remoteUser['name'],
                    'email' => $remoteUser['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                ]
            );

            \Illuminate\Support\Facades\Auth::guard('customer')->login($customer);
            $request->session()->regenerate();
            $request->session()->put('api_token', $data['token']); // حفظ التوكن للطلبات القادمة

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => route('customer.dashboard', [], false)]);
            }
            return redirect()->route('customer.dashboard');
        }

        // إذا فشل التسجيل على الخادم البعيد
        if ($request->expectsJson()) {
            return response()->json(['message' => 'فشل التسجيل على الخادم'], 422);
        }
        return back()->withInput()->withErrors(['username' => 'فشل التسجيل على الخادم']);
    } catch (\Exception $e) {
        // خطأ في الاتصال
        if ($request->expectsJson()) {
            return response()->json(['message' => 'فشل الاتصال بالخادم'], 500);
        }
        return back()->withInput()->withErrors(['username' => 'فشل الاتصال بالخادم']);
    }
})->name('mobile.register.submit');