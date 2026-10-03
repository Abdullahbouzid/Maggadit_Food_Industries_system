<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Notification;
use App\Services\Customer\ImagePathService;
use App\Services\CustomerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $wallet = \App\Models\CustomerWallet::with('currency')
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->first();

        return view('app_android.more.my_info', compact('customer', 'wallet'));
    }

    public function updateInfo(Request $request)
    {
        $customer = $this->getCustomer($request);

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
            'phone' => 'nullable|string|max:20',
            'default_address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable',
        ]);

        // Fields the customer must never control: price list, credit limit and
        // account type are administrative. They are dropped explicitly so a
        // crafted request cannot change them.
        $request->request->remove('price_list_id');
        $request->request->remove('credit_limit');
        $request->request->remove('type');
        $request->request->remove('balance');

        if ($request->filled('name')) {
            $customer->name = $request->name;
        }
        if ($request->filled('email')) {
            $customer->email = $request->email;
        }
        if ($request->filled('username')) {
            $customer->username = $request->username;
        }
        if ($request->has('phone')) {
            $customer->phone = $request->phone;
        }
        if ($request->has('default_address')) {
            $customer->default_address = $request->default_address;
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
            $favoriteItems = \App\Models\Item::whereIn('id', $favoriteItemIds)
                ->with(['packs.packType', 'packs.packCapacity.uom', 'category', 'brand'])
                ->get()
                ->map(function ($item) use ($customer) {
                    $itemPrice = $this->getItemPrice($item, $customer);

                    $packs = $item->packs->map(function ($pack) use ($item, $customer) {
                        $packPrice = $this->getItemPrice($item, $customer, $pack->id);
                        $capacity = $pack->packCapacity;
                        $uom = $capacity?->uom;
                        $uomName = $uom ? ($uom->name_ar ?? $uom->name_en) : '';
                        $packName = $pack->display_name ?? ($uomName ?: ('عبوة ' . (float) $pack->quantity_in_pack));

                        return [
                            'id' => $pack->id,
                            'name' => $packName,
                            'uom_name' => $uomName,
                            'price' => $packPrice,
                            'quantity' => (float) $pack->quantity_in_pack,
                        ];
                    })->values()->all();

                    $defaultPack = $item->packs->first();
                    $defaultUom = $defaultPack?->packCapacity?->uom;
                    $uomName = $defaultUom ? ($defaultUom->name_ar ?? $defaultUom->name_en) : '';

                    return [
                        'id' => $item->id,
                        'name_ar' => $item->name_ar ?: ($item->name_en ?: 'منتج بدون اسم'),
                        'name_en' => $item->name_en ?: ($item->name_ar ?: 'Product'),
                        'price' => $itemPrice,
                        'image' => $item->main_image_url,
                        'discount' => 0,
                        'inStock' => true,
                        'uom_name' => $uomName,
                        'packs' => $packs,
                    ];
                })->values()->all();
        }

        if (empty($favoriteItems)) {
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/favorites', $token);
                if ($remoteData && isset($remoteData['favorites'])) {
                    $favoriteItems = collect($remoteData['favorites'])->map(function ($item) {
                        $i = (object) $item;
                        return [
                            'id' => $i->id,
                            'name_ar' => $i->name ?? ($i->name_ar ?? 'منتج'),
                            'name_en' => $i->name_en ?? ($i->name ?? 'Product'),
                            'price' => (float) ($i->price ?? 0),
                            'image' => isset($i->image) ? $this->imagePathService->normalize($i->image) : null,
                            'discount' => 0,
                            'inStock' => true,
                            'uom_name' => '',
                            'packs' => [],
                        ];
                    })->all();
                }
            }
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

    public function notificationsIndex(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            return redirect()->route('mobile.login');
        }

        $service = app(CustomerNotificationService::class);
        $pivots = $service->readRowsFor($customer);

        $notifications = $service->allFor($customer)
            ->map(fn ($n) => (object) $service->toPayload($n, $pivots->get($n->id)))
            ->values();

        $unreadCount = $service->unreadCount($customer);

        // If local DB is empty, fetch from external API (for APK)
        if ($notifications->isEmpty()) {
            $token = session('api_token') ?: $request->bearerToken();
            if ($token) {
                $remoteData = $this->fetchRemoteData('customer/notifications', $token);
                if ($remoteData && isset($remoteData['notifications'])) {
                    // Transform external API format to match local view expectations
                    $notifications = collect($remoteData['notifications'])->map(function ($n) {
                        return (object) [
                            'id'         => $n['id'],
                            'subject'    => $n['subject'],
                            'body'       => $n['body'],
                            'image'      => $n['image'] ?? null,
                            'icon_type'  => $n['icon_type'] ?? 'notification',
                            'group'      => $n['group'] ?? null,
                            'is_read'    => (bool) ($n['is_read'] ?? false),
                            'is_starred' => (bool) ($n['is_starred'] ?? false),
                            'is_archived'=> (bool) ($n['is_archived'] ?? false),
                            'created_at' => $n['created_at'] ?? now()->toDateTimeString(),
                        ];
                    })->values();
                    $unreadCount = $remoteData['unread_count'] ?? 0;
                }
            }
        }

        return view('app_android.more.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Polling endpoint for the in-app notification engine.
     *
     * The mobile app runs inside a WebView at http://127.0.0.1 and has no
     * Sanctum token, so it polls this session-authenticated route instead of
     * /api/customer/notifications/check-new.
     */
    public function notificationsCheckNew(Request $request)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $lastId = (int) $request->input('last_id', 0);

        return response()->json(
            app(CustomerNotificationService::class)->checkNew($customer, $lastId)
        );
    }

    public function notificationMarkRead(Request $request, $id)
    {
        return $this->mutateNotification($request, $id, function ($service, $customer, $notification) use ($request) {
            $service->markRead($customer, $notification, (bool) $request->input('is_read', true));
        });
    }

    public function notificationToggleRead(Request $request, $id)
    {
        return $this->mutateNotification($request, $id, function ($service, $customer, $notification) {
            $isRead = $notification->pivot?->read_at !== null;
            $service->markRead($customer, $notification, ! $isRead);

            // Return the new state so the client does not have to guess it.
            return ! $isRead;
        });
    }

    public function notificationToggleStar(Request $request, $id)
    {
        return $this->mutateNotification($request, $id, function ($service, $customer, $notification) {
            return $service->toggleStar($customer, $notification);
        });
    }

    public function notificationToggleArchive(Request $request, $id)
    {
        return $this->mutateNotification($request, $id, function ($service, $customer, $notification) {
            return $service->toggleArchive($customer, $notification);
        });
    }

    /**
     * Shared guard + responder for the notification mutation endpoints.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function mutateNotification(Request $request, $id, callable $callback)
    {
        $customer = $this->getCustomer($request);

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $service = app(CustomerNotificationService::class);

        $notification = $service->queryFor($customer)->where('id', $id)->first();

        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        $notification->setRelation(
            'pivot',
            $service->readRowsFor($customer)->get($notification->id)?->pivot
        );

        $result = $callback($service, $customer, $notification);

        return response()->json([
            'success' => true,
            'result' => $result,
            'unread_count' => $service->unreadCount($customer),
        ]);
    }
}
