<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Customer\JsonFieldParser;
use App\Mail\ContactMessageMail;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Organization;
use App\Models\OrganizationAbout;
use App\Models\PrivacyPolicy;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CustomerApiController extends Controller
{
    use JsonFieldParser;

    // التصنيفات
    public function categoriesIndex()
    {
        $categories = Category::where('show_in_customer_app', true)
            ->select('id', 'name', 'image')
            ->withCount(['items as products_count' => function ($q) {
                $q->where('is_inactive', false);
            }])
            ->get();

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function categoriesShow($id)
    {
        $category = Category::where('show_in_customer_app', true)
            ->with(['items' => function ($q) {
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
            ->withCount(['items as products_count' => function ($q) {
                $q->where('is_inactive', false);
            }])
            ->get();

        return response()->json(['success' => true, 'brands' => $brands]);
    }

    public function brandsShow($id)
    {
        $brand = Brand::with(['items' => function ($q) {
            $q->where('is_inactive', false);
        }])->findOrFail($id);

        return response()->json(['success' => true, 'brand' => $brand]);
    }

    // المنتجات مع بعض البيانات الأساسية
    public function productsIndex(Request $request)
    {
        $query = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs']);

        // تصفية حسب التصنيف
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        // تصفية حسب الماركة
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        // بحث بالاسم
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
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
                'is_favorite' => false,
            ];
        });

        return response()->json(['success' => true, 'products' => $products]);
    }

    public function productsShow($id)
    {
        $item = Item::where('is_inactive', false)
            ->with(['category', 'brand', 'packs.packType', 'packs.packCapacity.uom', 'images'])
            ->findOrFail($id);

        $packs = $item->packs->map(function ($pack) {
            return [
                'id' => $pack->id,
                'name' => $pack->display_name,
                'price' => $pack->price,
                'quantity' => $pack->quantity_in_pack,
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
            ],
        ]);
    }

    public function offersIndex()
    {
        $offers = Offer::with(['item', 'itemPack.packType', 'itemPack.packCapacity.uom'])
            ->active()
            ->get()
            ->map(function ($offer) {
                $item = $offer->item;
                $defaultPack = $item ? $item->packs->first() : null;
                $offerPack = $offer->itemPack;

                $packUomName = function ($pack) {
                    if (!$pack) {
                        return '';
                    }
                    $uom = $pack->packCapacity?->uom;
                    if ($uom) {
                        return $uom->name_ar ?? $uom->name_en ?? '';
                    }
                    return $pack->packType?->name ?? '';
                };

                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'item_id' => $offer->item_id,
                    'item_pack_id' => $offer->item_pack_id,
                    'name' => $item ? ($item->name_ar ?: $item->name_en) : 'منتج محذوف',
                    'pack_type' => $packUomName($offerPack) ?: $packUomName($defaultPack),
                    'image' => $offer->display_image,
                    'old_price' => (float) $offer->old_price,
                    'new_price' => (float) $offer->new_price,
                    'discount_percentage' => (float) $offer->discount_percentage,
                    'start_date' => $offer->start_date?->format('Y-m-d H:i'),
                    'end_date' => $offer->end_date?->format('Y-m-d H:i'),
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
            ],
        ]);
    }

    public function financialStatement(Request $request)
    {
        return response()->json(['success' => true, 'statement' => []]);
    }

    public function orderHistory(Request $request)
    {
        $customer = $request->user();

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with(['items.item' => function ($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->orderBy('invoice_date', 'desc')
            ->get()->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date->format('Y-m-d'),
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'items_count' => $invoice->items->count(),
                    'items' => $invoice->items->map(function ($item) {
                        return [
                            'name' => $item->item->name_ar ?? $item->item->name_en ?? 'Product',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $invoices,
        ]);
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
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'estimated_minutes' => $survey->estimated_minutes,
                    'questions_count' => $survey->questions_count,
                    'is_answered' => $answered,
                    'starts_at' => $survey->starts_at?->toDateTimeString(),
                    'ends_at' => $survey->ends_at?->toDateTimeString(),
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
                'message' => 'لقد أجبت على هذا الاستبيان مسبقاً',
            ], 409);
        }

        $questions = $survey->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'question_type' => $q->question_type,
                'options' => $q->options,
                'is_required' => $q->is_required,
                'sort_order' => $q->sort_order,
            ];
        });

        return response()->json([
            'success' => true,
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
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
                'message' => 'لقد أجبت على هذا الاستبيان مسبقاً',
            ], 409);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($request, $survey, $customer) {
            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'customer_id' => $customer->id,
                'submitted_at' => now(),
            ]);

            foreach ($survey->questions as $question) {
                $answerValue = $request->answers[$question->id] ?? null;
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $question->id,
                    'answer_text' => $answerValue,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال إجاباتك بنجاح! شكراً لمشاركتك.',
        ]);
    }

    public function notifications(Request $request)
    {
        $customer = $request->user();

        $notificationsQuery = Notification::where(function ($query) use ($customer) {
            // إشعارات للجميع: customers تحتوي على "all"
            $query->whereJsonContains('customers', 'all')
                  // أو channel = 'all'
                ->orWhere('channel', 'all')
                  // أو العميل محدد بالاسم أو ID في مصفوفة customers
                ->orWhere(function ($q) use ($customer) {
                    $q->whereJsonContains('customers', (string) $customer->id)
                        ->orWhereJsonContains('customers', $customer->id);
                });
        })
            ->where('is_sent', true)
            ->latest();

        $notificationsList = $notificationsQuery->get();

        // Get read notifications and their pivot data for this customer
        $readNotifications = $customer->readNotifications()->get()->keyBy('id');

        $unreadCount = 0;

        $notifications = $notificationsList->map(function ($notif) use ($readNotifications, &$unreadCount) {
            $isRead = $readNotifications->has($notif->id);
            if (! $isRead) {
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
            'unread_count' => $unreadCount,
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $customer = $request->user();
        $customer->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true, 'message' => 'FCM Token updated successfully']);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $customer = $request->user();

        // التحقق من وجود الإشعار
        $notification = Notification::find($id);
        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        // إضافة الإشعار إلى قائمة الإشعارات المقروءة إذا لم يكن مقروءاً بالفعل
        if (! $customer->readNotifications()->where('notification_id', $id)->exists()) {
            $customer->readNotifications()->attach($id, ['read_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'تم تحديد الإشعار كمقروء']);
    }

    public function toggleNotificationRead(Request $request, $id)
    {
        $customer = $request->user();

        $notification = Notification::find($id);
        if (! $notification) {
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
        $notification = Notification::find($id);
        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        $pivotRow = $customer->readNotifications()->where('notification_id', $id)->first();
        $newState = $pivotRow ? ! $pivotRow->pivot->is_starred : true;

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
        $notification = Notification::find($id);
        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        $pivotRow = $customer->readNotifications()->where('notification_id', $id)->first();
        $newState = $pivotRow ? ! $pivotRow->pivot->is_archived : true;

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
        $faqs = Faq::all();

        return response()->json(['success' => true, 'faq' => $faqs]);
    }

    public function privacyPolicy(Request $request)
    {
        $customer = $request->user();

        $organizationId = null;
        if ($customer) {
            $activeOrg = $customer->activeOrganization;
            if (! $activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $organizationId = $activeOrg->id;
            }
        }

        $policy = PrivacyPolicy::where('type', 'privacy_policy')
            ->where('is_active', true)
            ->when($organizationId, function ($q) use ($organizationId) {
                $q->where(function ($sub) use ($organizationId) {
                    $sub->whereNull('organization_id')
                        ->orWhere('organization_id', $organizationId);
                });
            })
            ->latest()
            ->first();

        if (! $policy) {
            return response()->json([
                'success' => true,
                'content' => null,
                'message' => 'سياسة الخصوصية غير متوفرة حالياً',
            ]);
        }

        return response()->json([
            'success' => true,
            'content' => $policy->content,
            'title' => $policy->title,
        ]);
    }

    public function aboutUs(Request $request)
    {
        $customer = $request->user();
        $about = null;

        if ($customer) {
            // First try the direct active_organization_id relation
            $activeOrg = $customer->activeOrganization;
            // If not set, fallback to first active organization from pivot
            if (! $activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $about = $activeOrg->about;
            }
        } else {
            // If no authenticated user, get the first active organization's about
            $about = OrganizationAbout::where('is_active', true)->first();
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

    public function contactInfo(Request $request)
    {
        $organizationId = $request->query('organization_id');

        if (! $organizationId) {
            $customer = $request->user();
            if ($customer) {
                $organizationId = $customer->active_organization_id;
                if (! $organizationId) {
                    $activeOrg = $customer->activeOrganizations()->first();
                    if ($activeOrg) {
                        $organizationId = $activeOrg->id;
                    }
                }
            }
        }

        $organization = null;
        if ($organizationId) {
            $organization = Organization::find($organizationId);
        }

        if (! $organization) {
            $organization = Organization::first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'phone' => $organization?->phone ?: '+966500000000',
                'whatsapp' => $organization?->whatsapp ?: '+966500000000',
                'email' => $organization?->email ?: 'info@maggadit.com',
                'website' => $organization?->website ?: 'https://maggadit.com',
                'address' => $organization?->address ?: 'المملكة العربية السعودية - الرياض',
                'latitude' => (float) ($organization?->latitude ?: 24.7136),
                'longitude' => (float) ($organization?->longitude ?: 46.6753),
            ],
        ]);
    }

    public function contactUs(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $organizationId = $request->query('organization_id');

        if (! $organizationId) {
            $customer = $request->user();
            if ($customer) {
                $organizationId = $customer->active_organization_id;
                if (! $organizationId) {
                    $activeOrg = $customer->activeOrganizations()->first();
                    if ($activeOrg) {
                        $organizationId = $activeOrg->id;
                    }
                }
            }
        }

        $contactMessage = ContactMessage::create([
            'organization_id' => $organizationId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'new',
        ]);

        $organization = null;
        if ($organizationId) {
            $organization = Organization::find($organizationId);
        }

        if ($organization && $organization->forward_contact_messages_to_email) {
            try {
                $recipient = $organization->contact_email_recipient ?? $organization->email;
                if ($recipient) {
                    Mail::to($recipient)->send(new ContactMessageMail($contactMessage));
                    $contactMessage->update([
                        'forwarded_to_email' => true,
                        'forwarded_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to forward contact message: '.$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رسالتك بنجاح',
        ]);
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
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $customer = $request->user();
        $organizationId = $request->organization_id;

        // التحقق من أن العميل مرتبط بهذه المنظمة
        if (! $customer->organizations()->where('organization_id', $organizationId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير مرتبط بهذه المنظمة',
            ], 403);
        }

        // تعيين المنظمة النشطة
        $customer->setActiveOrganization($organizationId);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير المنظمة بنجاح',
            'active_organization_id' => $organizationId,
        ]);
    }

    public function favorites(Request $request)
    {
        $customer = $request->user();
        $favoriteItemIds = $customer->favorites()->pluck('item_id')->toArray();

        $favorites = Item::whereIn('id', $favoriteItemIds)
            ->with(['images', 'category', 'brand'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name_ar' => $item->name_ar,
                    'name_en' => $item->name_en ?? $item->name_ar,
                    'price' => $item->price,
                    'image' => $item->image ? asset('uploads/'.$item->image) : ($item->images->isNotEmpty() ? asset('uploads/'.$item->images->first()->image_path) : null),
                    'category_id' => $item->category_id,
                    'brand_id' => $item->brand_id,
                    'is_favorite' => true,
                ];
            });

        return response()->json(['success' => true, 'data' => $favorites]);
    }

    public function toggleFavorite(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
        ]);

        $itemId = $request->item_id;
        $favorite = $customer->favorites()->where('item_id', $itemId)->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json(['success' => true, 'is_favorite' => false, 'message' => 'تمت الإزالة من المفضلة']);
        }

        $customer->favorites()->create(['item_id' => $itemId]);

        return response()->json(['success' => true, 'is_favorite' => true, 'message' => 'تمت الإضافة للمفضلة']);
    }

    public function invoices(Request $request)
    {
        $customer = $request->user();

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('items.item')
            ->latest()
            ->paginate(10);

        $data = $invoices->items();

        return response()->json([
            'success' => true,
            'data' => collect($data)->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'total_amount' => (float) $invoice->total_amount,
                    'status' => $invoice->status,
                    'items_count' => $invoice->items->count(),
                    'notes' => $invoice->notes,
                    'created_at' => $invoice->created_at->toDateTimeString(),
                ];
            }),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function invoiceDetail(Request $request, $id)
    {
        $customer = $request->user();

        $invoice = Invoice::where('customer_id', $customer->id)
            ->with(['items.item', 'items.warehouse'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'total_amount' => (float) $invoice->total_amount,
                'status' => $invoice->status,
                'notes' => $invoice->notes,
                'created_at' => $invoice->created_at->toDateTimeString(),
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_name_ar' => $item->item->name_ar ?? null,
                        'item_name_en' => $item->item->name_en ?? null,
                        'item_image' => $item->item->image
                            ? asset('uploads/'.$item->item->image)
                            : ($item->item->images->isNotEmpty()
                                ? asset('uploads/'.$item->item->images->first()->image_path)
                                : null),
                        'warehouse_id' => $item->warehouse_id,
                        'warehouse_name' => $item->warehouse->name ?? null,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ],
        ]);
    }

    public function checkNewNotifications(Request $request)
    {
        $customer = $request->user();
        $lastId = $request->input('last_id', 0);

        $notificationsQuery = Notification::where(function ($query) use ($customer) {
            // إشعارات للجميع: customers تحتوي على "all"
            $query->whereJsonContains('customers', 'all')
                  // أو channel = 'all'
                ->orWhere('channel', 'all')
                  // أو العميل محدد بالـ ID في مصفوفة customers
                ->orWhere(function ($q) use ($customer) {
                    $q->whereJsonContains('customers', (string) $customer->id)
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
            if (! $isRead) {
                $unreadCount++;
            }

            // إذا كان هذا الإشعار أحدث من آخر إشعار يعلمه التطبيق
            if ($notif->id > (int) $lastId) {
                $isStarred = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_starred : false;
                $isArchived = $isRead ? (bool) $readNotifications[$notif->id]->pivot->is_archived : false;

                $newNotifications->push([
                    'id' => $notif->id,
                    'subject' => $notif->subject_ar ?: $notif->subject_en,
                    'body' => $notif->body_ar ?: $notif->body_en,
                    'image' => $notif->image_url ?? null,
                    'icon_type' => $notif->icon_type ?? 'notification',
                    'is_read' => $isRead,
                    'is_starred' => $isStarred,
                    'is_archived' => $isArchived,
                    'created_at' => $notif->created_at->diffForHumans(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'new_notifications' => $newNotifications,
            'count' => $newNotifications->count(),
            'unread_count' => $unreadCount,
        ]);
    }
}
