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
    private $baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

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

        return view('app_android.customer_dashboard', compact('banners', 'categories', 'brands', 'products'));
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

        // جلب المنتجات التابعة لهذه الفئة
        $items = Item::where('category_id', $id)->get()->map(function ($item) {
            return $this->applyImagePath($item, 'image');
        });

        return view('app_android.categories.show', compact('category', 'items'));
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

        // جلب المنتجات التابعة لهذه الماركة
        $items = Item::where('brand_id', $id)->get()->map(function ($item) {
            return $this->applyImagePath($item, 'image');
        });

        return view('app_android.brands.show', compact('brand', 'items'));
    }

    /**
     * عرض جميع المنتجات
     */
    public function productsIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/products");
            if ($response->successful()) {
                $data = $response->json();

                // جلب المصفوفة الصحيحة من الرد
                $rawData = $data['products'] ?? ($data['data'] ?? $data);

                // إذا كان الرد يحتوي على success ولم نجد مفتاحاً للمنتجات، فهذا يعني أننا حصلنا على الرد كاملاً
                if (isset($data['success']) && !isset($data['data']) && !isset($data['products'])) {
                    $rawData = [];
                }

                // معالجة حالة الترقيم (Pagination)
                if (is_array($rawData) && isset($rawData['data']) && is_array($rawData['data'])) {
                    $rawData = $rawData['data'];
                }

                $items = $this->parseData(is_array($rawData) ? $rawData : []);

                if ($items->isNotEmpty()) {
                    return view('app_android.products.index', compact('items'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Products API Error: " . $e->getMessage());
        }

        // Fallback: جلب من قاعدة البيانات المحلية
        $items = Item::all()->map(function ($item) {
            return $this->applyImagePath($item, 'image');
        });

        return view('app_android.products.index', compact('items'));
    }
    /**
     * عرض العروض
     */
    public function offersIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/offers");
            if ($response->successful()) {
                $data = $response->json();
                // جلب المصفوفة الصحيحة من الرد
                $rawData = $data['offers'] ?? ($data['data'] ?? $data);

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
   public function aboutUs(Request $request)
   {
       $customer = $request->user();
       $about = null;

       if ($customer) {
           // First try the direct active_organization_id relation
           $activeOrg = $customer->activeOrganization;
           // If not set, fallback to first active organization from pivot
           if (!$activeOrg) {
               $activeOrg = $customer->activeOrganizations()->first();
           }
           if ($activeOrg) {
               $about = $activeOrg->about;
           }
       }

   public function aboutUs(Request $request)
   {
       $customer = $request->user();
       $about = null;

       if ($customer) {
           // First try the direct active_organization_id relation
           $activeOrg = $customer->activeOrganization;
           // If not set, fallback to first active organization from pivot
           if (!$activeOrg) {
               $activeOrg = $customer->activeOrganizations()->first();
           }
           if ($activeOrg) {
               $about = $activeOrg->about;
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
