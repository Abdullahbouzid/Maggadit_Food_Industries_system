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
        $categories = collect();
        $brands = collect();
        $products = collect();

        try {
            $token = Session::get('api_token');
            $response = Http::withToken($token)->withoutVerifying()->get("{$this->baseUrl}/customer/dashboard");

            if ($response->successful()) {
                $data = $response->json();

                // تحويل البيانات إلى كائنات مع ضمان وجود الحقول المطلوبة
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

        return view('app_android.customer_dashboard', compact('categories', 'brands', 'products'));
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
                $rawData = $data['brands'] ?? ($data['data'] ?? $data);

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

        // Fallback: استخدام البيانات المحلية مع تطبيع المسار
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
                $brandData = $data['brand'] ?? $data;

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

        // بيانات عروض وهمية
        $offers = collect([
            (object) [
                'id' => 1,
                'title' => 'تخفيضات الصيف الكبرى',
                'description' => 'خصم يصل إلى 50% على جميع الملابس الصيفية والإكسسوارات الأصلية. سارع بالشراء!',
                'discount' => '-50%',
                'expiry_date' => '2025-07-31',
                'image' => null,
            ],
            (object) [
                'id' => 2,
                'title' => 'عرض خاص على الإلكترونيات',
                'description' => 'احصل على خصم 30% على أحدث الأجهزة الإلكترونية لفترة محدودة.',
                'discount' => '-30%',
                'expiry_date' => '2025-08-15',
                'image' => null,
            ],
        ]);

        return view('app_android.offers.index', compact('offers'));
    }

    /**
     * تحويل مصفوفة البيانات إلى مجموعة من الكائنات مع توحيد مسار الصور
     */
    private function parseData(array $items)
    {
        // إذا كانت المصفوفة تحتوي على مفتاح 'data'، فمن المحتمل أنها استجابة مغلفة أو مرقمة
        if (isset($items['data']) && is_array($items['data'])) {
            return $this->parseData($items['data']);
        }

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
        if (is_object($model) && property_exists($model, $field)) {
            $value = $model->$field;
            if (!empty($value)) {
                $model->$field = $this->normalizeImagePath($value);
            } else {
                $model->$field = null;
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
     * توحيد مسار الصورة: إذا كان رابطاً كاملاً نستخدمه، وإلا نضيفه إلى مجلد uploads
     */
  private function normalizeImagePath($path)
    {
        if (empty($path)) {
            return null;
        }

        // 1. إذا كان رابطاً كاملاً (http/https)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Remove leading slash if present for consistent processing
        $cleanPath = ltrim($path, '/');

        // 2. معالجة المسارات التي تبدأ بـ 'storage/'
        if (str_starts_with($cleanPath, 'storage/')) {
            $relativePath = substr($cleanPath, 8); // Remove 'storage/' prefix
            // التحقق من وجود الملف في القرص العام
            if (Storage::disk('public')->exists($relativePath)) {
                return asset('storage/' . $relativePath);
            } else {
                // إذا لم يوجد، نسجل خطأ ونعيد null
                Log::warning("Image not found in storage: " . $relativePath);
                return null;
            }
        }

        // 3. معالجة المسارات التي تبدأ بـ 'uploads/'
        if (str_starts_with($cleanPath, 'uploads/')) {
            $relativePath = substr($cleanPath, 8); // Remove 'uploads/' prefix
            $fullPath = public_path('uploads/' . $relativePath);
            if (file_exists($fullPath)) {
                return asset('uploads/' . $relativePath);
            } else {
                Log::warning("Image not found in uploads: " . $relativePath);
                return null;
            }
        }

        // 4. افتراضياً: نعتبره اسم ملف داخل مجلد uploads
        $fullPath = public_path('uploads/' . $cleanPath);
        if (file_exists($fullPath)) {
            return asset('uploads/' . $cleanPath);
        }

        // 5. محاولة البحث في storage أيضاً كحل أخير
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // 6. إذا لم يوجد، نسجل خطأ ونعيد null
        Log::warning("Image not found: " . $path);
        return null;
    }

    /**
     * تطبيق تطبيع مسار الصورة على نموذج أو كائن معين
     */
    /**
     * عرض صفحة بياناتي
     */
    public function myInfo()
    {
        $customer = Auth::guard('customer')->user();
        return view('app_android.more.my_info', compact('customer'));
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function updateInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $token = Session::get('api_token');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'username' => 'required|string|max:255',
        ];

        // If the user provided old_password, they want to change their password
        if ($request->filled('old_password') || $request->filled('password')) {
            $rules['old_password'] = 'required';
            $rules['password'] = 'required|min:6|confirmed';
        }

        $validator = ValidatorFacade::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // إرسال التحديث إلى السيرفر الرئيسي
        $http = Http::withToken($token)->withoutVerifying();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $http->attach(
                'avatar', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
            );
        } elseif ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar)) {
            $data = substr($request->avatar, strpos($request->avatar, ',') + 1);
            $decodedData = base64_decode($data);
            $extension = explode('/', explode(':', substr($request->avatar, 0, strpos($request->avatar, ';')))[1])[1];
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $http->attach('avatar', $decodedData, $filename);
        }

        $response = $http->post("{$this->baseUrl}/customer/profile/update", $request->except(['avatar']));

        if ($response->successful()) {
            $data = $response->json();
            $remoteUser = $data['customer'];

            // Save avatar locally too if present
            if ($request->hasFile('avatar')) {
                // If remote server returns the generated filename, use it. Otherwise use new one.
                $filename = $remoteUser['avatar'] ?? (time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
                $request->file('avatar')->move(public_path('uploads/customers'), $filename);

                if ($customer->avatar && file_exists(public_path('uploads/customers/' . $customer->avatar))) {
                    @unlink(public_path('uploads/customers/' . $customer->avatar));
                }

                $customer->avatar = $filename;
            } elseif ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar)) {
                $extension = explode('/', explode(':', substr($request->avatar, 0, strpos($request->avatar, ';')))[1])[1];
                $data = substr($request->avatar, strpos($request->avatar, ',') + 1);
                $decodedData = base64_decode($data);
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
}
