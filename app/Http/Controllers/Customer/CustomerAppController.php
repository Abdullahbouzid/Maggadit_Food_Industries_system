<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;

use App\Models\Customer;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomerAppController extends Controller
{
<<<<<<< HEAD
    use JsonFieldParser;

    private $baseUrl;

    public function __construct(private ImagePathService $imagePathService)
    {
        $this->baseUrl = rtrim(env('CUSTOMER_APP_BASE_URL', env('APP_URL', url('/'))), '/');
    }

=======
    private $baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

>>>>>>> parent of 039010c (العروض و المخازن و فواتير و تاريخ الطلبيات و سلة المشتريات)
    /**
     * عرض لوحة التحكم الرئيسية (Dashboard)
     */
    public function dashboard()
    {
        $banners = collect();
        $categories = collect();
        $brands = collect();
        $products = collect();

        try {
            $token = Session::get('api_token');
            $response = Http::withToken($token)->withoutVerifying()->get("{$this->baseUrl}/customer/dashboard");

            if ($response->successful()) {
                $data = $response->json();

                // تحويل البيانات إلى كائنات مع ضمان وجود الحقول المطلوبة
                $banners = $this->parseData($data['banners'] ?? []);
                $categories = $this->parseData($data['categories'] ?? []);
                $brands = $this->parseData($data['brands'] ?? []);
                $products = $this->parseData($data['products'] ?? []);
            } else {
                Log::warning("Dashboard API returned non-success status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Dashboard API Error: " . $e->getMessage());
        }

        // إذا كانت البيانات فارغة، نستخدم البيانات المحلية كـ Fallback
        if ($banners->isEmpty()) {
            $banners = Banner::where('show_in_customer_app', true)->orderBy('order', 'asc')->get()->map(function ($banner) {
                return $this->applyImagePath($banner, 'image');
            });
        }
        if ($categories->isEmpty()) {
            $categories = Category::all()->map(function ($category) {
                return $this->applyImagePath($category, 'image');
            });
        }
        if ($brands->isEmpty()) {
            $brands = Brand::all()->map(function ($brand) {
                return $this->applyImagePath($brand, 'logo');
            });
        }
        if ($products->isEmpty()) {
            $products = Item::all()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'products', 'favoriteItemIds'));
    }

    /**
     * عرض جميع الفئات
     */
    public function categoriesIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/categories");
            if ($response->successful()) {
                $data = $response->json();
                // جلب المصفوفة الصحيحة من الرد
                $rawData = $data['categories'] ?? ($data['data'] ?? $data);

                // التأكد أننا لا نمرر المصفوفة الكاملة إذا كانت تحتوي على success
                if (isset($data['success']) && !isset($data['data']) && !isset($data['categories'])) {
                     $rawData = []; // أو تعامل خاص
                }

                $categories = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($categories->isNotEmpty()) {
                    return view('app_android.categories.index', compact('categories'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Categories API Error: " . $e->getMessage());
        }

        // Fallback إلى قاعدة البيانات المحلية
        $categories = Category::all()->map(function ($category) {
            return $this->applyImagePath($category, 'image');
        });

        return view('app_android.categories.index', compact('categories'));
    }

    /**
     * عرض فئة محددة مع المنتجات التابعة لها
     */
    public function categoriesShow($id)
    {
        $category = null;
        $items = collect();

        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/categories/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                // جلب بيانات الفئة من المفتاح الصحيح
                $categoryData = $data['category'] ?? $data;

                // التأكد من أنها ليست الاستجابة الكاملة
                if (isset($data['success']) && !isset($data['category'])) {
                     // محاولة إيجاد البيانات إذا كانت الاستجابة غير مباشرة
                }

                $category = (object) $categoryData;
                $category = $this->applyImagePath($category, 'image');

                if (isset($data['products'])) {
                    $items = $this->parseData($data['products']);
                } elseif (isset($data['items'])) {
                    $items = $this->parseData($data['items']);
                }
            }
        } catch (\Exception $e) {
            Log::error("Category Show API Error: " . $e->getMessage());
        }

        if (!$category) {
            $category = Category::find($id);
            if (!$category) {
                abort(404, 'الفئة غير موجودة');
            }
            $category = $this->applyImagePath($category, 'image');
        }

        if ($items->isEmpty()) {
            // جلب المنتجات التابعة لهذه الفئة محلياً
            $items = Item::where('category_id', $id)->get()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.categories.show', compact('category', 'items', 'favoriteItemIds'));
    }

    /**
     * عرض جميع الماركات
     */
    public function brandsIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/brands");
            if ($response->successful()) {
                $data = $response->json();
                // جلب المصفوفة الصحيحة من الرد
                $rawData = $data['brands'] ?? ($data['data'] ?? $data);

                // إذا كان الرد يحتوي على success ولم نجد مفتاحاً للماركات، فهذا يعني أننا حصلنا على الرد كاملاً
                if (isset($data['success']) && !isset($data['data']) && !isset($data['brands'])) {
                    $rawData = [];
                }

                $brands = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($brands->isNotEmpty()) {
                    return view('app_android.brands.index', compact('brands'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Brands API Error: " . $e->getMessage());
        }

        // Fallback إلى قاعدة البيانات المحلية
        $brands = Brand::all()->map(function ($brand) {
            return $this->applyImagePath($brand, 'logo');
        });

        return view('app_android.brands.index', compact('brands'));
    }

    /**
     * عرض ماركة محددة مع المنتجات التابعة لها
     */
    public function brandsShow($id)
    {
        $brand = null;
        $items = collect();

        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/brands/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                // جلب بيانات الماركة من المفتاح الصحيح
                $brandData = $data['brand'] ?? $data;

                if (isset($data['success']) && !isset($data['brand'])) {
                    // معالجة
                }

                $brand = (object) $brandData;
                $brand = $this->applyImagePath($brand, 'logo');

                if (isset($data['products'])) {
                    $items = $this->parseData($data['products']);
                } elseif (isset($data['items'])) {
                    $items = $this->parseData($data['items']);
                }
            }
        } catch (\Exception $e) {
            Log::error("Brand Show API Error: " . $e->getMessage());
        }

        if (!$brand) {
            $brand = Brand::find($id);
            if (!$brand) {
                abort(404, 'الماركة غير موجودة');
            }
            $brand = $this->applyImagePath($brand, 'logo');
        }

        if ($items->isEmpty()) {
            // جلب المنتجات التابعة لهذه الماركة محلياً
            $items = Item::where('brand_id', $id)->get()->map(function ($item) {
                return $this->applyImagePath($item, 'image');
            });
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.brands.show', compact('brand', 'items', 'favoriteItemIds'));
    }

    /**
     * عرض جميع المنتجات
     */
    public function productsIndex(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');

        $categories = Category::where('show_in_customer_app', true)->get();
        $brands = Brand::where('show_in_customer_app', true)->get();

        try {
            $url = "{$this->baseUrl}/customer/products";
            $queryParams = [];
            if ($search) {
                $queryParams['search'] = $search;
            }
            if ($categoryId) {
                $queryParams['category_id'] = $categoryId;
            }
            if ($brandId) {
                $queryParams['brand_id'] = $brandId;
            }

            $response = Http::withoutVerifying()->get($url, $queryParams);
            if ($response->successful()) {
                $data = $response->json();
                $rawData = $data['products'] ?? ($data['data'] ?? $data);

<<<<<<< HEAD
                if (isset($data['success']) && ! isset($data['data']) && ! isset($data['products'])) {
=======
                // إذا كان الرد يحتوي على success ولم نجد مفتاحاً للمنتجات، فهذا يعني أننا حصلنا على الرد كاملاً
                if (isset($data['success']) && !isset($data['data']) && !isset($data['products'])) {
>>>>>>> parent of 039010c (العروض و المخازن و فواتير و تاريخ الطلبيات و سلة المشتريات)
                    $rawData = [];
                }

                if (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                    $rawData = $rawData['data'];
                }

                $items = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($items->isNotEmpty()) {
                    $customer = Auth::guard('customer')->user();
                    $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];
<<<<<<< HEAD

                    return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
=======
                    return view('app_android.products.index', compact('items', 'favoriteItemIds'));
>>>>>>> parent of 039010c (العروض و المخازن و فواتير و تاريخ الطلبيات و سلة المشتريات)
                }
            }
        } catch (\Exception $e) {
            Log::error("Products API Error: " . $e->getMessage());
        }

        // Fallback: جلب من قاعدة البيانات المحلية
<<<<<<< HEAD
        $query = Item::where('is_inactive', false);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $items = $query->limit(50)->get()->map(function ($item) {
=======
        $items = Item::all()->map(function ($item) {
>>>>>>> parent of 039010c (العروض و المخازن و فواتير و تاريخ الطلبيات و سلة المشتريات)
            return $this->applyImagePath($item, 'image');
        });

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.products.index', compact('items', 'favoriteItemIds', 'categories', 'brands'));
    }
    /**
     * عرض العروض
     */
    public function offersIndex()
    {
        $offers = Offer::with(['item', 'itemPack.packType'])
            ->active()
            ->get()
            ->map(function ($offer) {
                $item = $offer->item;
                $defaultPack = $item ? $item->packs->first() : null;
                $offerPack = $offer->itemPack;

<<<<<<< HEAD
                return (object) [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : 'منتج محذوف',
                    'pack_type' => $offerPack ? ($offerPack->packType->name_ar ?? $offerPack->packType->name_en ?? '') : ($defaultPack ? ($defaultPack->packType->name_ar ?? $defaultPack->packType->name_en ?? '') : ''),
                    'image' => $offer->display_image ?? ($item ? $item->main_image_url : null),
                    'old_price' => (float) $offer->old_price,
                    'new_price' => (float) $offer->new_price,
                    'discount_percentage' => (float) $offer->discount_percentage,
                    'start_date' => $offer->start_date?->format('Y-m-d H:i'),
                    'end_date' => $offer->end_date?->format('Y-m-d H:i'),
                ];
            });

=======
                if (isset($data['success']) && !isset($data['data']) && !isset($data['offers'])) {
                    $rawData = [];
                }

                $offers = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($offers->isNotEmpty()) {
                    return view('app_android.offers.index', compact('offers'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Offers API Error: " . $e->getMessage());
        }

        // Fallback: بيانات افتراضية
        $offers = collect();
>>>>>>> parent of 039010c (العروض و المخازن و فواتير و تاريخ الطلبيات و سلة المشتريات)
        return view('app_android.offers.index', compact('offers'));
    }

    /**
     * تحويل البيانات الخام إلى كولكشن من الكائنات مع معالجة الصور
     */
    private function parseData($items)
    {
        return collect($items)->filter(function($item) {
            // التأكد من أن العنصر هو مصفوفة أو كائن ويحتوي على معرف (id)
            if (is_array($item)) {
                return isset($item['id']);
            }
            if (is_object($item)) {
                return isset($item->id);
            }
            return false;
        })->map(function ($item) {
            $item = (object) $item;

            // معالجة الصور حسب الحقل المتاح
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }
            if (isset($item->logo)) {
                $item->logo = $this->normalizeImagePath($item->logo);
            }

            return $item;
        });
    }

    /**
     * تطبيق تطبيع مسار الصورة على نموذج أو كائن معين
     * @param mixed $model  النموذج أو الكائن
     * @param string $field اسم الحقل الذي يحوي المسار (image, logo, ...)
     * @return mixed
     */
    private function applyImagePath($model, string $field)
    {
        if (is_object($model)) {
            // التحقق مما إذا كان كائناً عادياً أو نموذج Eloquent
            if (isset($model->$field) || (method_exists($model, 'getAttribute') && $model->getAttribute($field) !== null)) {
                $value = $model->$field;
                if (!empty($value)) {
                    $model->$field = $this->normalizeImagePath($value);
                } else {
                    $model->$field = null;
                }
            }
        } elseif (is_array($model) && isset($model[$field])) {
            $value = $model[$field];
            if (!empty($value)) {
                $model[$field] = $this->normalizeImagePath($value);
            } else {
                $model[$field] = null;
            }
        }
        return $model;
    }

    /**
     * توحيد مسار الصورة: إذا كان رابطاً كاملاً نستخدمه، وإلا نضيفه إلى مجلد uploads أو نربطه بالسيرفر الرئيسي
     */
    private function normalizeImagePath($path)
    {
        if (empty($path)) {
            return null;
        }

        // 1. إذا كان رابطاً كاملاً (http/https)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            // إذا كان الرابط يشير للسيرفر الرئيسي بمسار storage للصور التي نعلم أنها في uploads، نحاول تصحيحه
            if (str_contains($path, 'hostingersite.com/storage/')) {
                if (str_contains($path, '/items/') || str_contains($path, '/categories/') || str_contains($path, '/brands/')) {
                    return str_replace('/storage/', '/uploads/', $path);
                }
            }
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        $baseUrlDomain = 'https://blue-beaver-923812.hostingersite.com';

        // 2. معالجة المسارات التي تبدأ بـ 'storage/'
        if (str_starts_with($cleanPath, 'storage/')) {
            $relativePath = substr($cleanPath, 8);
            if (Storage::disk('public')->exists($relativePath)) {
                return asset('storage/' . $relativePath);
            }
            // إذا لم يوجد محلياً، نرجعه من السيرفر الرئيسي
            return $baseUrlDomain . '/storage/' . $relativePath;
        }

        // 3. معالجة المسارات التي تبدأ بـ 'uploads/'
        if (str_starts_with($cleanPath, 'uploads/')) {
            $relativePath = substr($cleanPath, 8);
            if (file_exists(public_path('uploads/' . $relativePath))) {
                return asset('uploads/' . $relativePath);
            }
            return $baseUrlDomain . '/uploads/' . $relativePath;
        }

        // 4. البحث الافتراضي في uploads ثم storage
        if (file_exists(public_path('uploads/' . $cleanPath))) {
            return asset('uploads/' . $cleanPath);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // 5. الحل الأخير: ربط المسار بالسيرفر الرئيسي (بما أن معظم الصور مخزنة هناك في uploads)
        // إذا كان المسار يحتوي على 'items' أو 'categories' أو 'brands'، نرجحه من uploads السيرفر
        if (str_contains($cleanPath, 'items/') || str_contains($cleanPath, 'categories/') || str_contains($cleanPath, 'brands/')) {
            return $baseUrlDomain . '/uploads/' . $cleanPath;
        }

        return $baseUrlDomain . '/storage/' . $cleanPath;
    }

    /**
     * عرض صفحة بياناتي
     */
    public function myInfo()
    {
        $customer = Auth::guard('customer')->user();
        return view('app_android.more.my_info', compact('customer'));
    }

    /**
     * تحديث بيانات العميل
     */
    public function updateInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $token = Session::get('api_token');

        // تجهيز البيانات للإرسال للسيرفر
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
            $data['password_confirmation'] = $request->password_confirmation;
        }

        try {
            $requestHttp = Http::withToken($token)->withoutVerifying();

            // إذا كان هناك ملف أفتار
            if ($request->hasFile('avatar')) {
                $response = $requestHttp->attach(
                    'avatar',
                    file_get_contents($request->file('avatar')->getRealPath()),
                    $request->file('avatar')->getClientOriginalName()
                )->post("{$this->baseUrl}/customer/profile/update", $data);
            } else {
                $response = $requestHttp->post("{$this->baseUrl}/customer/profile/update", $data);
            }

            if ($response->successful()) {
                $remoteUser = $response->json('customer');

                // تحديث المستخدم محلياً
                // معالجة صورة الأفتار محلياً إذا تغيرت
                if ($request->hasFile('avatar')) {
                    $file = $request->file('avatar');
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/customers'), $filename);

                    if ($customer->avatar && file_exists(public_path('uploads/customers/' . $customer->avatar))) {
                        @unlink(public_path('uploads/customers/' . $customer->avatar));
                    }

                    $customer->avatar = $filename;
                } elseif ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar)) {
                    $extension = explode('/', explode(':', substr($request->avatar, 0, strpos($request->avatar, ';')))[1])[1];
                    $dataBase64 = substr($request->avatar, strpos($request->avatar, ',') + 1);
                    $decodedData = base64_decode($dataBase64);
                    $filename = $remoteUser['avatar'] ?? (time() . '_' . uniqid() . '.' . $extension);
                    file_put_contents(public_path('uploads/customers/' . $filename), $decodedData);

                    if ($customer->avatar && file_exists(public_path('uploads/customers/' . $customer->avatar))) {
                        @unlink(public_path('uploads/customers/' . $customer->avatar));
                    }

                    $customer->avatar = $filename;
                } elseif (isset($remoteUser['avatar'])) {
                    $customer->avatar = $remoteUser['avatar'];
                }

                $customer->name = $remoteUser['name'];
                $customer->email = $remoteUser['email'];
                $customer->username = $remoteUser['username'];

                if ($request->filled('password')) {
                    $customer->password = \Illuminate\Support\Facades\Hash::make($request->password);
                }

                $customer->save();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'message' => 'تم تحديث البيانات بنجاح', 'customer' => $customer]);
                }
                return redirect()->back()->with('success', 'تم تحديث البيانات بنجاح');
            }

            $errors = $response->json('errors') ?? ['error' => ['فشل تحديث البيانات في السيرفر']];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $errors], 422);
            }
            return redirect()->back()->withErrors($errors)->withInput();

        } catch (\Exception $e) {
            Log::error("Update Profile Error: " . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => ['error' => ['حدث خطأ أثناء الاتصال بالسيرفر']]], 500);
            }
            return redirect()->back()->with('error', 'حدث خطأ أثناء الاتصال بالسيرفر');
        }
    }

    /**
     * عرض صفحة الأسئلة الشائعة
     */
    public function faqIndex()
    {
        $faqs = collect();
        try {
            $token = Session::get('api_token');
            $response = Http::withToken($token)->withoutVerifying()->get("{$this->baseUrl}/customer/faq");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['faq'])) {
                    $faqs = collect($data['faq'])->map(function($item) {
                        return (object) $item;
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error("FAQ API Error: " . $e->getMessage());
        }

        // Fallback إلى قاعدة البيانات المحلية
        if ($faqs->isEmpty()) {
            $faqs = \App\Models\Faq::all();
        }

        // تجميع الأسئلة حسب الفئة
        $faqsByCategory = $faqs->groupBy('category');

        return view('app_android.more.faq', compact('faqsByCategory'));
    }

    /**
     * عرض صفحة الإشعارات
     */
    public function notificationsIndex()
    {
        $notifications = collect();
        $unreadCount   = 0;
        $token         = Session::get('api_token');

        // محاولة جلب الإشعارات عبر API
        if ($token) {
            try {
                $response = Http::withToken($token)
                    ->withoutVerifying()
                    ->timeout(8)
                    ->get("{$this->baseUrl}/customer/notifications");

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['notifications'])) {
                        $notifications = collect($data['notifications'])->map(fn($item) => (object) $item);
                        $unreadCount   = $data['unread_count'] ?? count($notifications);
                    }
                } else {
                    Log::warning("Notifications API non-success: " . $response->status() . ' body: ' . substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                Log::error("Notifications API Error: " . $e->getMessage());
            }
        }

        // Fallback: جلب مباشرة من قاعدة البيانات المحلية إذا كانت النتيجة فارغة
        if ($notifications->isEmpty()) {
            $customer = Auth::guard('customer')->user();
            $customerId = $customer?->id;

            $dbNotifs = \App\Models\Notification::where(function($query) use ($customerId) {
                $query->whereJsonContains('customers', 'all')
                      ->orWhere('channel', 'all');
                if ($customerId) {
                    $query->orWhere(function($q) use ($customerId) {
                        $q->whereJsonContains('customers', (string)$customerId)
                          ->orWhereJsonContains('customers', $customerId);
                    });
                }
            })
            ->where('is_sent', true)
            ->latest()
            ->get();

            $notifications = $dbNotifs->map(function($n) {
                return (object) [
                    'id'         => $n->id,
                    'subject'    => $n->subject_ar ?: $n->subject_en,
                    'body'       => $n->body_ar ?: $n->body_en,
                    'image'      => null,
                    'icon_type'  => $n->icon_type ?? 'notification',
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });
            $unreadCount = count($notifications);
        }

        return view('app_android.more.notifications', compact('notifications', 'unreadCount'));
    }

    public function favorites(Request $request)
    {
        $customer = $request->user('customer') ?: Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('mobile.login');
        }

        // Get favorite item IDs from local DB
        $favoriteItemIds = $customer->favorites()->pluck('item_id')->toArray();

        $favoriteItems = collect();
        if (!empty($favoriteItemIds)) {
            // We fetch the items from local DB as fallback
            $favoriteItems = Item::whereIn('id', $favoriteItemIds)->get()->map(function ($item) {
                return collect([
                    'id' => $item->id,
                    'name_ar' => $item->name,
                    'name_en' => $item->name_en ?? $item->name,
                    'price' => $item->price,
                    'image' => $this->normalizeImagePath($item->image),
               

                    'inStock' => true // Placeholder
                ]);
            });
            
            // Try fetching from API to get fresh data if possible
            try {
                $token = Session::get('api_token');
                if ($token) {
                    $response = Http::withToken($token)->withoutVerifying()->get("{$this->baseUrl}/customer/products");
                    if ($response->successful()) {
                        $data = $response->json();
                        $rawData = $data['products'] ?? ($data['data'] ?? $data);
                        if (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                            $rawData = $rawData['data'];
                        }
                        $items = $this->parseData(is_array($rawData) ? $rawData : []);
                        
                        if ($items->isNotEmpty()) {
                            // Filter API items by favorite IDs
                            $favoriteItems = $items->filter(function($item) use ($favoriteItemIds) {
                                return in_array($item->id, $favoriteItemIds);
                            })->map(function($item) {
                                return collect([
                                    'id' => $item->id,
                                    'name_ar' => $item->name ?? $item->name_en,
                                    'name_en' => $item->name_en ?? $item->name,
                                    'price' => $item->price ?? 0,
                                    'image' => $item->image,
                                    'discount' => $item->discount ?? 0,
                                    'inStock' => true // Placeholder if API doesn't return stock
                                ]);
                            })->values();
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Favorites API Error: " . $e->getMessage());
            }
        }

        return view('app_android.favorite', compact('favoriteItems'));
    }

    public function toggleFavorite(Request $request)
    {
        $customer = $request->user('customer') ?: Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'item_id' => 'required|integer'
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

    /**
     * عرض تفاصيل منتج محدد (للـ APK)
     */
    public function productShow($id)
    {
        $product = null;
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/products/{$id}");
            if ($response->successful()) {
                $data = $response->json();
                $productData = $data['product'] ?? $data;
                $product = (object) $productData;
                $product = $this->applyImagePath($product, 'image');
            }
        } catch (\Exception $e) {
            Log::error("Product Show API Error: " . $e->getMessage());
        }

        if (!$product) {
            $product = Item::find($id);
            if ($product) {
                $product = $this->applyImagePath($product, 'image');
            } else {
                abort(404, 'المنتج غير موجود');
            }
        }

        $customer = Auth::guard('customer')->user();
        $favoriteItemIds = $customer ? $customer->favorites()->pluck('item_id')->toArray() : [];

        return view('app_android.product.show', compact('product', 'favoriteItemIds'));
    }

    public function more(Request $request)
    {
        $customer = $request->user('customer') ?: Auth::guard('customer')->user();
        return view('app_android.more', compact('customer'));
    }

    public function aboutUsWeb(Request $request)
    {
        $about = null;
        $customer = $request->user('customer')
            ?: $request->user('api')
            ?: Auth::guard('customer')->user()
            ?: Auth::guard('api')->user();

        if ($customer) {
            $activeOrg = $customer->activeOrganization;
            if (!$activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $about = $activeOrg->about()->where('is_active', true)->first() ?: $activeOrg->about;
            }
        } else {
            $about = \App\Models\OrganizationAbout::where('is_active', true)->first();
        }

        return view('app_android.more.about_us', compact('about'));
    }

    public function aboutUs(Request $request)
    {
        $customer = $request->user('customer')
            ?: $request->user('api')
            ?: Auth::guard('customer')->user()
            ?: Auth::guard('api')->user();
        $about = null;

        if ($customer) {
            $activeOrg = $customer->activeOrganization;
            if (!$activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $about = $activeOrg->about()->where('is_active', true)->first() ?: $activeOrg->about;
            }
        }

        // Return the about object with proper JSON parsing
        return response()->json([
            'success' => true,
            'content' => $about ? [
                'id' => $about->id,
                'title' => $about->title,
                'subtitle' => $about->subtitle,
                'description' => $about->description,
                'vision' => $about->vision,
                'mission' => $about->mission,
                'values' => $this->parseJsonField($about->values),
                'statistics' => $this->parseStatsField($about->statistics),
                'image_url' => $about->image_url,
                'cover_image_url' => $about->cover_image_url,
            ] : null
        ]);
    }

   private function parseJsonField($field)
   {
       if (empty($field)) {
           return [];
       }

       if (is_array($field)) {
           return $field;
       }

       if (is_string($field)) {
           $decoded = json_decode($field, true);
           if (json_last_error() === JSON_ERROR_NONE) {
               return $decoded ?? [];
           }
       }

       return [];
   }

   private function parseStatsField($field)
   {
       if (empty($field)) {
           return [];
       }

       $stats = [];

       if (is_array($field)) {
           $stats = $field;
       } elseif (is_string($field)) {
           $decoded = json_decode($field, true);
           if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
               $stats = $decoded;
           }
       }

       // If stats is a flat array, convert to key-value pairs
       if (!empty($stats) && !isset($stats[0]['key']) && !isset($stats[0]['value'])) {
           $formatted = [];
           $keys = array_keys($stats);
           foreach ($keys as $key) {
               if (!is_numeric($key) && !is_null($stats[$key])) {
                   $formatted[] = ['key' => $key, 'value' => $stats[$key]];
               }
           }
           return $formatted;
       }

       return $stats;
   }
}
