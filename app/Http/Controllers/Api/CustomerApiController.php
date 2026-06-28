<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // جلب قائمة الاستبيانات النشطة مع حالة العميل
    public function surveys(Request $request)
    {
        $customer = $request->user();

        $surveys = Survey::active()
            ->withCount('questions')
            ->latest()
            ->get()
            ->map(function ($survey) use ($customer) {
                $answered = $survey->isAnsweredByCustomer($customer->id);
                return [
                    'id'                => $survey->id,
                    'title'             => $survey->title,
                    'description'       => $survey->description,
                    'estimated_minutes' => $survey->estimated_minutes,
                    'questions_count'   => $survey->questions_count,
                    'is_answered'       => $answered,
                    'starts_at'         => $survey->starts_at?->toDateTimeString(),
                    'ends_at'           => $survey->ends_at?->toDateTimeString(),
                ];
            });

        return response()->json(['success' => true, 'surveys' => $surveys]);
    }

    // جلب استبيان واحد مع أسئلته الكاملة
    public function getSurvey(Request $request, $id)
    {
        $customer = $request->user();
        $survey = Survey::active()->with('questions')->findOrFail($id);

        if ($survey->isAnsweredByCustomer($customer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'لقد أجبت على هذا الاستبيان مسبقاً'
            ], 409);
        }

        $questions = $survey->questions->map(function ($q) {
            return [
                'id'            => $q->id,
                'question_text' => $q->question_text,
                'question_type' => $q->question_type,
                'options'       => $q->options,
                'is_required'   => $q->is_required,
                'sort_order'    => $q->sort_order,
            ];
        });

        return response()->json([
            'success'  => true,
            'survey'   => [
                'id'                => $survey->id,
                'title'             => $survey->title,
                'description'       => $survey->description,
                'estimated_minutes' => $survey->estimated_minutes,
            ],
            'questions' => $questions,
        ]);
    }

    // إرسال إجابات الاستبيان
    public function submitSurvey(Request $request, $id)
    {
        $customer = $request->user();
        $survey = Survey::active()->with('questions')->findOrFail($id);

        // التحقق من عدم الإجابة مسبقاً
        if ($survey->isAnsweredByCustomer($customer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'لقد أجبت على هذا الاستبيان مسبقاً'
            ], 409);
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($request, $survey, $customer) {
            $response = SurveyResponse::create([
                'survey_id'    => $survey->id,
                'customer_id'  => $customer->id,
                'submitted_at' => now(),
            ]);

            foreach ($survey->questions as $question) {
                $answerValue = $request->answers[$question->id] ?? null;
                SurveyAnswer::create([
                    'survey_response_id'  => $response->id,
                    'survey_question_id'  => $question->id,
                    'answer_text'         => $answerValue,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال إجاباتك بنجاح! شكراً لمشاركتك.'
        ]);
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
        
        // جلب المنظمات المرتبطة بالعميل مع حالة النشاط
        $organizations = $customer->organizations()->get()->map(function ($org) use ($customer) {
            return [
                'id' => $org->id,
                'name' => $org->name,
                'code' => $org->code,
                'is_active' => $customer->active_organization_id == $org->id,
                'parent_id' => $org->parent_id,
                'organization_type' => $org->organization_type,
            ];
        });

        return response()->json([
            'success' => true,
            'organizations' => $organizations,
            'active_organization_id' => $customer->active_organization_id,
        ]);
    }

    // تبديل المنظمة النشطة
    public function switchOrganization(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $customer = $request->user();
        $organizationId = $request->organization_id;

        // التحقق من أن العميل مرتبط بهذه المنظمة
        if (!$customer->organizations()->where('organization_id', $organizationId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير مرتبط بهذه المنظمة'
            ], 403);
        }

        // تعيين المنظمة النشطة
        $customer->setActiveOrganization($organizationId);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير المنظمة بنجاح',
            'active_organization_id' => $organizationId
        ]);
    }
}