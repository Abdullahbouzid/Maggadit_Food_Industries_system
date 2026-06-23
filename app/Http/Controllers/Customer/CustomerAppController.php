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
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/customer/dashboard");
            
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
            $categories = Category::all();
        }
        if ($brands->isEmpty()) {
            $brands = Brand::all();
        }
        if ($products->isEmpty()) {
            $products = Item::all();
        }

        return view('app_android.customer_dashboard', compact('categories', 'brands', 'products'));
    }

    /**
     * عرض جميع الفئات
     */
    public function categoriesIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/categories");
            if ($response->successful()) {
                $data = $response->json();
                // افترض أن البيانات تأتي في مفتاح 'data' أو مباشرة كمصفوفة
                $categories = $this->parseData($data['data'] ?? $data);
                $categories = $categories->map(function ($category) {
                    if (isset($category->image)) {
                        $category->image = $this->normalizeImagePath($category->image);
                    }
                    return $category;
                });
                if ($categories->isNotEmpty()) {
                    return view('app_android.categories.index', compact('categories'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Categories API Error: " . $e->getMessage());
        }

        // Fallback إلى قاعدة البيانات المحلية
        $categories = Category::all()->map(function ($category) {
            if (isset($category->image)) {
                $category->image = $this->normalizeImagePath($category->image);
            }
            return $category;
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
            // جلب بيانات الفئة من الـ API
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/categories/{$id}");
            if ($response->successful()) {
                $categoryData = $response->json();
                $category = (object) $categoryData;
                // تطبيع مسار الصورة
                if (isset($category->image)) {
                    $category->image = $this->normalizeImagePath($category->image);
                }
                // محاولة جلب المنتجات من API إن وجدت، وإلا سنستخدم المحلي
                // يمكن إضافة endpoint مخصص لجلب منتجات الفئة، لكننا نعتمد على المحلي كبديل
            }
        } catch (\Exception $e) {
            Log::error("Category Show API Error: " . $e->getMessage());
        }

        // إذا لم يتم جلب الفئة من API، نستخدم المحلي
        if (!$category) {
            $category = Category::find($id);
            if (!$category) {
                abort(404, 'الفئة غير موجودة');
            }
        }

        // جلب المنتجات التابعة لهذه الفئة (من قاعدة البيانات المحلية)
        $items = Item::where('category_id', $id)->get();

        // تطبيع مسارات الصور للمنتجات
        $items = $items->map(function ($item) {
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }
            return $item;
        });

        return view('app_android.categories.show', compact('category', 'items'));
    }

    /**
     * عرض جميع الماركات
     */
    public function brandsIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/brands");
            if ($response->successful()) {
                $data = $response->json();
                $brands = $this->parseData($data['data'] ?? $data);
                $brands = $brands->map(function ($brand) {
                    if (isset($brand->logo)) {
                        $brand->logo = $this->normalizeImagePath($brand->logo);
                    }
                    return $brand;
                });
                if ($brands->isNotEmpty()) {
                    return view('app_android.brands.index', compact('brands'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Brands API Error: " . $e->getMessage());
        }

        $brands = Brand::all()->map(function ($brand) use ($this) {
            if (isset($brand->logo)) {
                $brand->logo = $this->normalizeImagePath($brand->logo);
            }
            return $brand;
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
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/brands/{$id}");
            if ($response->successful()) {
                $brandData = $response->json();
                $brand = (object) $brandData;
                if (isset($brand->logo)) {
                    $brand->logo = $this->normalizeImagePath($brand->logo);
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
        }

        // جلب المنتجات التابعة لهذه الماركة (من المحلي)
        $items = Item::where('brand_id', $id)->get();
        $items = $items->map(function ($item) {
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }
            return $item;
        });

        return view('app_android.brands.show', compact('brand', 'items'));
    }

    /**
     * عرض جميع المنتجات
     */
    public function productsIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/products");
            if ($response->successful()) {
                $data = $response->json();
                $products = $this->parseData($data['data'] ?? $data);
                if ($products->isNotEmpty()) {
                    return view('app_android.products.index', compact('products'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Products API Error: " . $e->getMessage());
        }

        $products = Item::all()->map(function ($item) {
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }
            return $item;
        });
        return view('app_android.products.index', compact('products'));
    }

    /**
     * عرض العروض
     */
    public function offersIndex()
    {
        try {
            $response = Http::withoutVerifying()->get("{$this->baseUrl}/offers");
            if ($response->successful()) {
                $data = $response->json();
                $offers = $this->parseData($data['data'] ?? $data);
                if ($offers->isNotEmpty()) {
                    return view('app_android.offers.index', compact('offers'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Offers API Error: " . $e->getMessage());
        }

        // بيانات عروض وهمية (أو يمكن جلبها من نموذج Offer إن وُجد)
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
        return collect($items)->map(function ($item) {
            $item = (object) $item;

            // معالجة صورة الفئة (category)
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }

            // معالجة شعار الماركة (brand)
            if (isset($item->logo)) {
                $item->logo = $this->normalizeImagePath($item->logo);
            }

            // معالجة صورة المنتج (product)
            if (isset($item->image)) {
                $item->image = $this->normalizeImagePath($item->image);
            }

            return $item;
        });
    }

    /**
     * توحيد مسار الصورة: إذا كان رابطاً كاملاً نستخدمه، وإلا نضيفه إلى مجلد uploads
     */
    private function normalizeImagePath($path)
    {
        if (empty($path)) {
            return null;
        }

        // إذا كان المسار يبدأ بـ http أو https فهو رابط كامل
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // نعتبره اسم ملف داخل مجلد uploads
        return asset('uploads/' . ltrim($path, '/'));
    }
}