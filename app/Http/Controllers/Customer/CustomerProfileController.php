<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Notification;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CustomerProfileController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function myInfo(Request $request)
    {
        $customer = $request->user('customer')
            ?: $request->user('api')
            ?: Auth::guard('customer')->user()
            ?: Auth::guard('api')->user();

        if (! $customer && $request->bearerToken()) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($personalToken && $personalToken->tokenable) {
                $customer = $personalToken->tokenable;
            }
        }

        if (! $customer) {
            $customerId = session('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
        }

        if (! $customer) {
            $customer = Customer::latest('updated_at')->first() ?: Customer::first();
        }

        return view('app_android.more.my_info', compact('customer'));
    }

    public function updateInfo(Request $request)
    {
        $customer = $request->user('customer')
            ?: $request->user('api')
            ?: Auth::guard('customer')->user()
            ?: Auth::guard('api')->user();

        if (! $customer && $request->bearerToken()) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($personalToken && $personalToken->tokenable) {
                $customer = $personalToken->tokenable;
            }
        }

        if (! $customer) {
            $customerId = session('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
        }

        if (! $customer) {
            $customer = Customer::latest('updated_at')->first() ?: Customer::first();
        }

        if (! $customer) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => ['error' => ['غير مصرح']]], 401);
            }

            return redirect()->back()->with('error', 'غير مصرح');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'nullable|string|max:255',
            'avatar' => 'nullable',
        ]);

        if ($request->filled('name')) {
            $customer->name = $request->name;
        }
        if ($request->filled('email')) {
            $customer->email = $request->email;
        }
        if ($request->filled('username')) {
            $customer->username = $request->username;
        }
        if ($request->filled('password')) {
            $customer->password = Hash::make($request->password);
        }

        $uploadDir = public_path('uploads/customers');

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            if ($customer->avatar && file_exists($uploadDir.'/'.$customer->avatar)) {
                @unlink($uploadDir.'/'.$customer->avatar);
            }

            $customer->avatar = $filename;
        } elseif ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar)) {
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = explode('/', explode(':', substr($request->avatar, 0, strpos($request->avatar, ';')))[1])[1];
            $dataBase64 = substr($request->avatar, strpos($request->avatar, ',') + 1);
            $decodedData = base64_decode($dataBase64);
            $filename = time().'_'.uniqid().'.'.$extension;
            file_put_contents($uploadDir.'/'.$filename, $decodedData);

            if ($customer->avatar && file_exists($uploadDir.'/'.$customer->avatar)) {
                @unlink($uploadDir.'/'.$customer->avatar);
            }

            $customer->avatar = $filename;
        }

        $customer->save();

        $token = Session::get('api_token') ?? $request->bearerToken();
        if ($token) {
            try {
                $data = [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'username' => $customer->username,
                ];
                if ($request->filled('password')) {
                    $data['password'] = $request->password;
                    $data['password_confirmation'] = $request->password_confirmation;
                }
                $requestHttp = Http::withToken($token)->withoutVerifying();
                if ($request->hasFile('avatar')) {
                    $requestHttp->attach(
                        'avatar',
                        file_get_contents($request->file('avatar')->getRealPath()),
                        $request->file('avatar')->getClientOriginalName()
                    )->post("{$this->baseUrl}/customer/profile/update", $data);
                } else {
                    $requestHttp->post("{$this->baseUrl}/customer/profile/update", $data);
                }
            } catch (\Exception $e) {
                Log::warning('Remote Profile Sync Warning: '.$e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث البيانات بنجاح', 'customer' => $customer]);
        }

        return redirect()->back()->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function favorites(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $favoriteItemIds = $customer->favorites()->pluck('item_id')->toArray();

        $favoriteItems = [];
        if (! empty($favoriteItemIds)) {
            $favoriteItems = \App\Models\Item::whereIn('id', $favoriteItemIds)->with('packs')->get()->map(function ($item) {
                $defaultPack = $item->packs->first();
                $price = (float) ($defaultPack ? $defaultPack->price : ($item->price ?? 0));

                return [
                    'id' => $item->id,
                    'name_ar' => $item->name_ar ?: ($item->name_en ?: 'منتج بدون اسم'),
                    'name_en' => $item->name_en ?: ($item->name_ar ?: 'Product'),
                    'price' => $price,
                    'image' => $item->main_image_url,
                    'discount' => 0,
                    'inStock' => true,
                ];
            })->values()->all();
        }

        return view('app_android.favorite', compact('favoriteItems'));
    }

    public function toggleFavorite(Request $request)
    {
        $customer = $this->getCustomer($request);
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'item_id' => 'required|integer',
        ]);

        $itemId = $request->item_id;

        $favorite = $customer->favorites()->where('item_id', $itemId)->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json(['success' => true, 'is_favorite' => false, 'message' => 'تمت الإزالة من المفضلة']);
        } else {
            $customer->favorites()->create(['item_id' => $itemId]);

            return response()->json(['success' => true, 'is_favorite' => true, 'message' => 'تمت الإضافة للمفضلة']);
        }
    }

    public function notificationsIndex()
    {
        $notifications = collect();
        $unreadCount = 0;
        $token = Session::get('api_token');

        if ($token) {
            try {
                $response = Http::withToken($token)
                    ->withoutVerifying()
                    ->timeout(8)
                    ->get("{$this->baseUrl}/customer/notifications");

                if ($response->successful()) {
                    $data = $response->json();
                    if (! empty($data['notifications'])) {
                        $notifications = collect($data['notifications'])->map(fn ($item) => (object) $item);
                        $unreadCount = $data['unread_count'] ?? count($notifications);
                    }
                } else {
                    Log::warning('Notifications API non-success: '.$response->status().' body: '.substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                Log::error('Notifications API Error: '.$e->getMessage());
            }
        }

        if ($notifications->isEmpty()) {
            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $dbNotifs = Notification::where(function ($query) use ($customerId) {
                $query->whereJsonContains('customers', 'all')
                    ->orWhere('channel', 'all');
                if ($customerId) {
                    $query->orWhere(function ($q) use ($customerId) {
                        $q->whereJsonContains('customers', (string) $customerId)
                            ->orWhereJsonContains('customers', $customerId);
                    });
                }
            })
                ->where('is_sent', true)
                ->latest()
                ->get();

            $notifications = $dbNotifs->map(function ($n) {
                return (object) [
                    'id' => $n->id,
                    'subject' => $n->subject_ar ?: $n->subject_en,
                    'body' => $n->body_ar ?: $n->body_en,
                    'image' => null,
                    'icon_type' => $n->icon_type ?? 'notification',
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });
            $unreadCount = count($notifications);
        }

        return view('app_android.more.notifications', compact('notifications', 'unreadCount'));
    }
}
