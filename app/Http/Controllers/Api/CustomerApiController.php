<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    // التصنيفات
    public function categoriesIndex()
    {
        $categories = Category::where('show_in_customer_app', true)
            ->select('id', 'name', 'image')
            ->get();
        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function categoriesShow($id)
    {
        $category = Category::where('show_in_customer_app', true)
            ->with(['items' => function($q) {
                $q->where('is_inactive', false);
            }])
            ->findOrFail($id);
        return response()->json(['success' => true, 'category' => $category]);
    }

    // الماركات
    public function brandsIndex()
    {
        $brands = Brand::where('show_in_customer_app', true)
            ->select('id', 'name', 'logo')
            ->get();
        return response()->json(['success' => true, 'brands' => $brands]);
    }

    public function brandsShow($id)
    {
        $brand = Brand::with(['items' => function($q) {
                $q->where('is_inactive', false);
            }])->findOrFail($id);
        return response()->json(['success' => true, 'brand' => $brand]);
    }

    // المنتجات مع بعض البيانات الأساسية
    public function productsIndex(Request $request)
    {
        $query = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs']); // packs علاقة مهمة للسعر

        // تصفية حسب التصنيف
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        // تصفية حسب الماركة
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->paginate(20);

        // تنسيق البيانات كما يحتاجها التطبيق
        $products->getCollection()->transform(function ($item) {
            $defaultPack = $item->packs->where('is_default_pack', true)->first() ?? $item->packs->first();
            return [
                'id' => $item->id,
                'name' => $item->name_ar ?: $item->name_en,
                'image' => $item->main_image_url,
                'price' => $defaultPack ? $defaultPack->price : 0,
                'old_price' => null,
                'rate' => 0,
                'is_favorite' => false, // يمكن لاحقاً جلبه من المفضلة إذا كان المستخدم مسجلاً
            ];
        });

        return response()->json(['success' => true, 'products' => $products]);
    }

    public function productsShow($id)
    {
        $item = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs', 'images'])
            ->findOrFail($id);

        $packs = $item->packs->map(function ($pack) {
            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'price' => $pack->price,
                'quantity' => $pack->quantity,
                'is_default' => $pack->is_default_pack,
            ];
        });

        $images = $item->images->pluck('image_url');

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $item->id,
                'name' => $item->name_ar ?: $item->name_en,
                'description' => $item->definition,
                'images' => $images,
                'packs' => $packs,
                'category' => $item->category?->name,
                'brand' => $item->brand?->name,
            ]
        ]);
    }

    public function offersIndex()
    {
        // إذا كان لديك نموذج Offer، قم بجلبه. حالياً نرجع منتجات عليها عروض افتراضية (مثلاً last 5 items)
        $offers = Item::where('is_inactive', false)
            ->where('is_new', true) // أو أي شرط للعرض
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $defaultPack = $item->packs->first();
                return [
                    'id' => $item->id,
                    'name' => $item->name_ar ?: $item->name_en,
                    'image' => $item->main_image_url,
                    'price' => $defaultPack ? $defaultPack->price : 0,
                    'old_price' => $defaultPack ? $defaultPack->price * 1.2 : 0, // وهمي
                ];
            });
        return response()->json(['success' => true, 'offers' => $offers]);
    }

    // باقي الدوال (dashboard, financialStatement, surveys, notifications, etc.)
    // ستكتب لاحقاً عند توفير النماذج المناسبة

    public function dashboard(Request $request)
    {
        $categories = Category::where('show_in_customer_app', true)
            ->select('id', 'name', 'image')
            ->take(8)
            ->get();

        $brands = Brand::where('show_in_customer_app', true)
            ->select('id', 'name', 'logo')
            ->take(8)
            ->get();

        $products = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs'])
            ->take(10)
            ->get()
            ->map(function ($item) {
                $defaultPack = $item->packs->where('is_default_pack', true)->first() ?? $item->packs->first();
                return [
                    'id' => $item->id,
                    'name' => $item->name_ar ?: $item->name_en,
                    'image' => $item->main_image_url,
                    'price' => $defaultPack ? $defaultPack->price : 0,
                    'old_price' => null,
                    'rate' => 0,
                    'is_favorite' => false,
                ];
            });

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'dashboard' => [
                'total_orders' => 0,
                'total_favorites' => 0,
            ]
        ]);
    }

    public function financialStatement(Request $request)
    {
        return response()->json(['success' => true, 'statement' => []]);
    }

    public function surveys(Request $request)
    {
        return response()->json(['success' => true, 'surveys' => []]);
    }

    public function submitSurvey(Request $request, $id)
    {
        return response()->json(['success' => true, 'message' => 'تم استلام الرد']);
    }

    public function notifications(Request $request)
    {
        return response()->json(['success' => true, 'notifications' => []]);
    }

    public function markNotificationRead(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function faq(Request $request)
    {
        $faqs = \App\Models\Faq::all();
        return response()->json(['success' => true, 'faq' => $faqs]);
    }

    public function privacyPolicy(Request $request)
    {
        return response()->json(['success' => true, 'content' => 'سياسة الخصوصية...']);
    }

    public function aboutUs(Request $request)
    {
        return response()->json(['success' => true, 'content' => 'من نحن...']);
    }

    public function contactUs(Request $request)
    {
        // تخزين رسالة الاتصال
        return response()->json(['success' => true, 'message' => 'تم إرسال رسالتك']);
    }

    public function organizationsList(Request $request)
    {
        $customer = $request->user();
        // افترضنا علاقة many-to-many بين العميل والمنظمات
        // إذا لم تكن موجودة، أرجع قائمة فارغة
        $organizations = $customer->organizations ?? collect();
        return response()->json(['success' => true, 'organizations' => $organizations]);
    }

    public function switchOrganization(Request $request)
    {
        $request->validate(['organization_id' => 'required|exists:organizations,id']);
        $customer = $request->user();
        // تحديث المنظمة النشطة (قد تحتاج عمود active_organization_id في جدول customers)
        // customer->update(['active_organization_id' => $request->organization_id]);
        return response()->json(['success' => true, 'message' => 'تم تغيير المنظمة']);
    }
}