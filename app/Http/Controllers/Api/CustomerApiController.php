<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\Banner;
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

        $banners = Banner::where('show_in_customer_app', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'image' => $banner->image_url,
                    'url' => $banner->url,
                ];
            });

        return response()->json([
            'success' => true,
            'banners' => $banners,
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
        $customer = $request->user();
        
        $notificationsQuery = \App\Models\Notification::where(function($query) use ($customer) {
            // إشعارات للجميع: customers تحتوي على "all"
            $query->whereJsonContains('customers', 'all')
                  // أو channel = 'all'
                  ->orWhere('channel', 'all')
                  // أو العميل محدد بالاسم أو ID في مصفوفة customers
                  ->orWhere(function($q) use ($customer) {
                      $q->whereJsonContains('customers', (string)$customer->id)
                        ->orWhereJsonContains('customers', $customer->id);
                  });
        })
        ->where('is_sent', true)
        ->latest();

        $notificationsList = $notificationsQuery->get();

        // Get read notifications and their pivot data for this customer
        $readNotifications = $customer->readNotifications()->get()->keyBy('id');

        $unreadCount = 0;

        $notifications = $notificationsList->map(function($notif) use ($readNotifications, &$unreadCount) {
            $isRead = $readNotifications->has($notif->id);
            if (!$isRead) {
                $unreadCount++;
            }
            
            $isStarred = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_starred : false;
            $isArchived = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_archived : false;

            return [
                'id' => $notif->id,
                'subject' => $notif->subject_ar ?: $notif->subject_en,
                'body' => $notif->body_ar ?: $notif->body_en,
                'image' => $notif->image_url ?? null,
                'icon_type' => $notif->icon_type ?? 'notification',
                'is_read' => $isRead,
                'is_starred' => $isStarred,
                'is_archived' => $isArchived,
                'created_at' => $notif->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true, 
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $customer = $request->user();
        $customer->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true, 'message' => 'FCM Token updated successfully']);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $customer = $request->user();
        
        // التحقق من وجود الإشعار
        $notification = \App\Models\Notification::find($id);
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        // إضافة الإشعار إلى قائمة الإشعارات المقروءة إذا لم يكن مقروءاً بالفعل
        if (!$customer->readNotifications()->where('notification_id', $id)->exists()) {
            $customer->readNotifications()->attach($id, ['read_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'تم تحديد الإشعار كمقروء']);
    }

    public function toggleNotificationRead(Request $request, $id)
    {
        $customer = $request->user();
        
        $notification = \App\Models\Notification::find($id);
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        $isRead = $customer->readNotifications()->where('notification_id', $id)->exists();

        if ($isRead) {
            $customer->readNotifications()->detach($id);
            $message = 'تم تحديد الإشعار كغير مقروء';
            $newState = false;
        } else {
            $customer->readNotifications()->attach($id, ['read_at' => now()]);
            $message = 'تم تحديد الإشعار كمقروء';
            $newState = true;
        }

        return response()->json(['success' => true, 'message' => $message, 'is_read' => $newState]);
    }

    public function toggleNotificationStar(Request $request, $id)
    {
        $customer = $request->user();
        $notification = \App\Models\Notification::find($id);
        if (!$notification) return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);

        $pivotRow = $customer->readNotifications()->where('notification_id', $id)->first();
        $newState = $pivotRow ? !$pivotRow->pivot->is_starred : true;

        if ($pivotRow) {
            $customer->readNotifications()->updateExistingPivot($id, ['is_starred' => $newState]);
        } else {
            // Create pivot if doesn't exist
            $customer->readNotifications()->attach($id, ['is_starred' => $newState, 'read_at' => now()]);
        }

        return response()->json(['success' => true, 'is_starred' => $newState]);
    }

    public function toggleNotificationArchive(Request $request, $id)
    {
        $customer = $request->user();
        $notification = \App\Models\Notification::find($id);
        if (!$notification) return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);

        $pivotRow = $customer->readNotifications()->where('notification_id', $id)->first();
        $newState = $pivotRow ? !$pivotRow->pivot->is_archived : true;

        if ($pivotRow) {
            $customer->readNotifications()->updateExistingPivot($id, ['is_archived' => $newState]);
        } else {
            // Create pivot if doesn't exist
            $customer->readNotifications()->attach($id, ['is_archived' => $newState, 'read_at' => now()]);
        }

        return response()->json(['success' => true, 'is_archived' => $newState]);
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

        // Return the about object with proper data transformation
        $content = null;
        if ($about) {
            $content = [
                'id' => $about->id,
                'organization_id' => $about->organization_id,
                'title' => $about->title,
                'subtitle' => $about->subtitle ?? '',
                'description' => $about->description,
                'vision' => $about->vision,
                'mission' => $about->mission,
                'values' => $this->parseJsonField($about->values),
                'statistics' => $this->parseStatsField($about->statistics),
                'image' => $about->image,
                'cover_image' => $about->cover_image,
                'image_url' => $about->image_url,
                'cover_image_url' => $about->cover_image_url,
                'is_active' => $about->is_active,
            ];
        }

        return response()->json(['success' => true, 'content' => $content]);
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

    public function checkNewNotifications(Request $request)
    {
        $customer = $request->user();
        $lastId = $request->input('last_id', 0);

        $notificationsQuery = \App\Models\Notification::where(function($query) use ($customer) {
            // إشعارات للجميع: customers تحتوي على "all"
            $query->whereJsonContains('customers', 'all')
                  // أو channel = 'all'
                  ->orWhere('channel', 'all')
                  // أو العميل محدد بالـ ID في مصفوفة customers
                  ->orWhere(function($q) use ($customer) {
                      $q->whereJsonContains('customers', (string)$customer->id)
                        ->orWhereJsonContains('customers', $customer->id);
                  });
        })
        ->where('is_sent', true)
        ->latest();

        $allNotifications = $notificationsQuery->get();

        // Get read notifications and their pivot data for this customer
        $readNotifications = $customer->readNotifications()->get()->keyBy('id');
        $unreadCount = 0;
        $newNotifications = collect();

        foreach ($allNotifications as $notif) {
            $isRead = $readNotifications->has($notif->id);
            if (!$isRead) {
                $unreadCount++;
            }

            // إذا كان هذا الإشعار أحدث من آخر إشعار يعلمه التطبيق
            if ($notif->id > (int)$lastId) {
                $isStarred = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_starred : false;
                $isArchived = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_archived : false;
                
                $newNotifications->push([
                    'id'         => $notif->id,
                    'subject'    => $notif->subject_ar ?: $notif->subject_en,
                    'body'       => $notif->body_ar ?: $notif->body_en,
                    'image'      => $notif->image_url ?? null,
                    'icon_type'  => $notif->icon_type ?? 'notification',
                    'is_read'    => $isRead,
                    'is_starred' => $isStarred,
                    'is_archived'=> $isArchived,
                    'created_at' => $notif->created_at->diffForHumans(),
                ]);
            }
        }

        return response()->json([
            'success'            => true,
            'new_notifications'  => $newNotifications,
            'count'              => $newNotifications->count(),
            'unread_count'       => $unreadCount
        ]);
    }
}