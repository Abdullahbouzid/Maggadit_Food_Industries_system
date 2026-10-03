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
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ReturnRequest;
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
        $customer = $request->user();

        // Get wallet and transactions
        $wallet = \App\Models\CustomerWallet::where('customer_id', $customer->id)->first();
        
        $transactions = $wallet
            ? \App\Models\WalletTransaction::with(['reference'])
                ->where('wallet_id', $wallet->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(200)
                ->get()
            : collect();

        $invoices = Invoice::with(['items'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        // Format transactions
        $statement = $transactions->map(function ($transaction) use ($invoices) {
            $amount = (float) $transaction->amount;
            $direction = $amount < 0 ? 'debit' : 'credit';

            $invoice = null;
            if ($transaction->reference_type) {
                $short = class_basename($transaction->reference_type);
                if ($short === 'Invoice' && $transaction->reference_id) {
                    $invoice = $invoices->firstWhere('id', $transaction->reference_id)
                        ?? Invoice::find($transaction->reference_id);
                } elseif ($short === 'Payment' && $transaction->reference_id) {
                    $payment = \App\Models\Payment::find($transaction->reference_id);
                    $invoice = $payment ? Invoice::with('items')->find($payment->invoice_id) : null;
                }
            }

            return [
                'id' => $transaction->id,
                'date' => $transaction->created_at?->format('Y-m-d'),
                'type' => (string) $transaction->type,
                'label' => match ((string) $transaction->type) {
                    'deposit' => 'إيداع',
                    'purchase' => $direction === 'debit' ? 'دفع فاتورة' : 'استرداد',
                    'withdrawal' => 'سحب',
                    'refund' => 'استرداد',
                    default => (string) $transaction->type,
                },
                'amount' => abs($amount),
                'direction' => $direction,
                'signed' => $direction === 'debit' ? -abs($amount) : abs($amount),
                'balance_after' => (float) $transaction->balance_after,
                'method' => match ($transaction->method) {
                    'cash' => 'نقداً',
                    'bank_transfer' => 'تحويل بنكي',
                    'card' => 'بطاقة',
                    'online' => 'دفع إلكتروني',
                    'wallet' => 'محفظة',
                    'check' => 'شيك',
                    default => $transaction->method ?: '—',
                },
                'reference' => $transaction->reference_number ?: ('#' . $transaction->id),
                'notes' => $transaction->notes,
                'invoice_id' => $invoice?->id,
                'invoice_number' => $invoice?->invoice_number,
            ];
        })->values();

        $totals = [
            'deposits' => (float) $statement->where('direction', 'credit')->sum('amount'),
            'debits' => abs((float) $statement->where('direction', 'debit')->sum('amount')),
            'balance' => (float) ($wallet->balance ?? 0),
            'invoiced' => (float) $invoices->sum('total_amount'),
            'paid' => (float) $invoices->sum('paid_amount'),
        ];
        $totals['outstanding'] = round($totals['invoiced'] - $totals['paid'], 2);

        return response()->json([
            'success' => true,
            'statement' => $statement,
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                    'total_amount' => (float) $invoice->total_amount,
                    'paid_amount' => (float) $invoice->paid_amount,
                    'status' => $invoice->status,
                    'order_id' => $invoice->order_id,
                    'items' => $invoice->items->map(function ($item) {
                        return [
                            'name' => $item->item->name_ar ?? $item->item->name_en ?? 'Product',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                ];
            }),
            'totals' => $totals,
        ]);
    }

    /**
     * Get order details for customer (for APK order details page)
     */
    public function orderDetails(Request $request, $id)
    {
        $customer = $request->user();

        $order = \App\Models\Order::where('customer_id', $customer->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id);
                if (is_numeric($id)) {
                    $q->orWhere('order_number', $id);
                }
            })
            ->with([
                'items.item',
                'items.pack.packType',
                'items.pack.packCapacity.uom',
                'items.warehouse',
                'quotation.items.item',
                'quotation.items.pack',
                'invoices',
            ])
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'الطلبية غير موجودة'], 404);
        }

        $quotation = $order->quotation->sortByDesc('version')->first();
        $invoice = $order->invoices->first();

        $orderItems = $order->items->map(function ($item) {
            $packLabel = 'وحدة';
            if ($item->pack) {
                $uom = $item->pack->packCapacity?->uom;
                $packLabel = $item->pack->packType?->name
                    ?: ($uom?->name_ar ?: $uom?->name_en ?: ('عبوة '.(float) $item->pack->quantity_in_pack));
            }

            return [
                'name' => $item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج'),
                'code' => $item->item?->code ?: '—',
                'pack' => $packLabel,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) ($item->line_total ?: $item->subtotal ?: ((float) $item->unit_price * (float) $item->quantity)),
                'warehouse' => $item->warehouse?->name_ar ?: ($item->warehouse?->name_en ?: 'غير محدد'),
            ];
        })->values();

        $quotationItems = $quotation
            ? $quotation->items->map(function ($item) {
                return [
                    'name' => $item->item_name_snapshot ?: ($item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج')),
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount_percent,
                    'line_total' => (float) ($item->line_total ?: $item->subtotal ?: 0),
                ];
            })->values()
            : [];

        // Build steps and timeline similar to OrderStepPresenter
        $steps = $this->buildOrderSteps($order);
        $timeline = $this->buildOrderTimeline($order);

        $statusMeta = $this->getOrderStatusMeta($order);
        $progress = $this->calculateProgress($order);
        $canRespond = $this->canCustomerRespond($order, $quotation);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'date' => $order->created_at?->format('Y-m-d'),
                'total' => (float) $order->total,
                'status' => $this->getOrderStatusValue($order),
                'status_text' => $statusMeta['label'],
                'status_class' => $statusMeta['class'],
                'progress' => $progress,
                'items_count' => $order->items->count(),
                'has_quotation' => (bool) $quotation,
                'has_invoice' => (bool) $invoice,
                'invoice_number' => $invoice?->invoice_number,
                'quotation_number' => $quotation?->quotation_number,
                'can_respond' => $canRespond,
                'delivery_address' => $order->delivery_address,
                'delivery_phone' => $order->delivery_phone,
                'customer_name' => $order->customer?->name,
                'items' => $orderItems,
                'quotation' => $quotation ? [
                    'id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'status' => $quotation->status,
                    'customer_response' => $quotation->customer_response,
                    'total' => (float) $quotation->total,
                    'items' => $quotationItems,
                ] : null,
                'invoice' => $invoice ? [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'total_amount' => (float) $invoice->total_amount,
                ] : null,
            ],
            'steps' => $steps,
            'timeline' => $timeline,
        ]);
    }

    /**
     * List all orders for customer (alternative to orderHistory which returns invoices)
     */
    public function ordersIndex(Request $request)
    {
        $customer = $request->user();

        $orders = \App\Models\Order::where('customer_id', $customer->id)
            ->with([
                'items.item:id,name_ar,name_en',
                'quotation:id,order_id,quotation_number,status,customer_response,total',
                'invoices:id,order_id,invoice_number,status,total_amount',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($order) {
                $quotation = $order->quotation->sortByDesc('id')->first();
                $invoice = $order->invoices->first();

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'date' => $order->created_at?->format('Y-m-d'),
                    'total' => (float) ($order->total ?? 0),
                    'status' => $this->getOrderStatusValue($order),
                    'status_text' => $this->getOrderStatusMeta($order)['label'],
                    'status_class' => $this->getOrderStatusMeta($order)['class'],
                    'progress' => $this->calculateProgress($order),
                    'items_count' => $order->items->count(),
                    'has_quotation' => (bool) $quotation,
                    'has_invoice' => (bool) $invoice,
                    'invoice_number' => $invoice?->invoice_number,
                    'quotation_number' => $quotation?->quotation_number,
                    'can_respond' => $this->canCustomerRespond($order, $quotation),
                    'items' => $order->items->map(fn ($item) => [
                        'name' => $item->item?->name_ar ?: ($item->item?->name_en ?: 'منتج'),
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                    ])->values(),
                ];
            })->values();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    // Helper methods for order status/steps
    private function getOrderStatusValue($order): string
    {
        $status = $order->status;
        if ($status instanceof \App\Enums\OrderStatus) {
            return $status->value;
        }
        return (string) $status;
    }

    private function getOrderStatusMeta($order): array
    {
        $value = $this->getOrderStatusValue($order);
        $enum = \App\Enums\OrderStatus::tryFrom($value);
        $label = $enum?->label() ?? $value;

        $badge = match ($value) {
            \App\Enums\OrderStatus::New->value, \App\Enums\OrderStatus::PendingReview->value, \App\Enums\OrderStatus::QuotationSent->value,
            \App\Enums\OrderStatus::PendingApproval->value => 'nova-badge-warning',
            \App\Enums\OrderStatus::AdminEdited->value, \App\Enums\OrderStatus::InvoiceGenerated->value, \App\Enums\OrderStatus::InDelivery->value => 'nova-badge-info',
            \App\Enums\OrderStatus::CustomerApproved->value, \App\Enums\OrderStatus::Approved->value, \App\Enums\OrderStatus::Delivered->value => 'nova-badge-success',
            \App\Enums\OrderStatus::CustomerRejected->value, \App\Enums\OrderStatus::Cancelled->value,
            \App\Enums\OrderStatus::Rejected->value, \App\Enums\OrderStatus::Expired->value, \App\Enums\OrderStatus::Returned->value => 'nova-badge-danger',
            default => 'nova-badge-secondary',
        };

        $class = match (true) {
            $enum === null => 'text-gray-600 bg-gray-100',
            str_contains($badge, 'success') => 'text-emerald-700 bg-emerald-50',
            str_contains($badge, 'danger') => 'text-red-700 bg-red-50',
            str_contains($badge, 'warning') => 'text-amber-700 bg-amber-50',
            str_contains($badge, 'info') => 'text-blue-700 bg-blue-50',
            default => 'text-gray-700 bg-gray-100',
        };

        return ['label' => $label, 'class' => $class, 'badge' => $badge];
    }

    private function calculateProgress($order): int
    {
        $status = $this->getOrderStatusValue($order);
        return match ($status) {
            \App\Enums\OrderStatus::New->value => 10,
            \App\Enums\OrderStatus::PendingReview->value => 20,
            \App\Enums\OrderStatus::AdminEdited->value => 30,
            \App\Enums\OrderStatus::QuotationSent->value => 40,
            \App\Enums\OrderStatus::CustomerApproved->value => 60,
            \App\Enums\OrderStatus::Approved->value => 60,
            \App\Enums\OrderStatus::InvoiceGenerated->value => 70,
            \App\Enums\OrderStatus::InDelivery->value => 80,
            \App\Enums\OrderStatus::Delivered->value => 100,
            \App\Enums\OrderStatus::CustomerRejected->value => 100,
            \App\Enums\OrderStatus::Cancelled->value => 100,
            \App\Enums\OrderStatus::Rejected->value => 100,
            \App\Enums\OrderStatus::Expired->value => 100,
            \App\Enums\OrderStatus::Returned->value => 100,
            default => 10,
        };
    }

    private function canCustomerRespond($order, $quotation): bool
    {
        return $this->getOrderStatusValue($order) === \App\Enums\OrderStatus::QuotationSent->value
            && $quotation
            && $quotation->status === 'sent'
            && in_array($quotation->customer_response, [null, 'pending'], true);
    }

    private function buildOrderSteps($order): array
    {
        $status = $this->getOrderStatusValue($order);
        
        // Determine current step index
        $stepStatuses = [
            'new' => 0,
            'pending_review' => 1,
            'admin_edited' => 2,
            'quotation_sent' => 2,
            'customer_approved' => 3,
            'approved' => 3,
            'delivered' => 4,
            'cancelled' => 5,
            'rejected' => 5,
            'returned' => 5,
            'expired' => 5,
            'customer_rejected' => 5,
        ];
        
        $currentStepIndex = $stepStatuses[$status] ?? 0;
        $isTerminal = in_array($status, ['cancelled', 'rejected', 'returned', 'expired', 'customer_rejected']);

        $steps = [
            ['key' => 'submitted', 'label' => 'تم إرسال الطلب', 'icon' => 'fa-send', 'color' => 'purple'],
            ['key' => 'review', 'label' => 'قيد المراجعة', 'icon' => 'fa-magnifying-glass', 'color' => 'blue'],
            ['key' => 'quotation', 'label' => 'عرض السعر', 'icon' => 'fa-file-invoice-dollar', 'color' => 'indigo'],
            ['key' => 'approval', 'label' => 'موافقة العميل', 'icon' => 'fa-user-check', 'color' => 'emerald'],
            ['key' => 'delivery', 'label' => 'التسليم', 'icon' => 'fa-truck', 'color' => 'green'],
        ];

        if ($isTerminal) {
            $steps[] = ['key' => 'cancelled', 'label' => 'ملغي/مرفوض', 'icon' => 'fa-ban', 'color' => 'red'];
        }

        // Add state to each step
        foreach ($steps as $index => &$step) {
            if ($index < $currentStepIndex) {
                $step['state'] = 'done';
            } elseif ($index === $currentStepIndex) {
                $step['state'] = $isTerminal ? 'rejected' : 'current';
            } else {
                $step['state'] = 'pending';
            }
        }

        return $steps;
    }

    private function buildOrderTimeline($order): array
    {
        $timeline = [];
        $status = $this->getOrderStatusValue($order);
        $createdAt = $order->created_at?->format('Y-m-d');

        $timeline[] = ['to' => 'طلب جديد', 'by' => 'النظام', 'date' => $createdAt];

        if (in_array($status, ['admin_edited', 'quotation_sent', 'customer_approved', 'approved', 'delivered', 'cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'قيد المراجعة', 'by' => 'الإدارة', 'date' => $createdAt];
        }
        if (in_array($status, ['quotation_sent', 'customer_approved', 'approved', 'delivered', 'cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'عرض السعر', 'by' => 'الإدارة', 'date' => $createdAt];
        }
        if (in_array($status, ['customer_approved', 'approved', 'delivered'])) {
            $timeline[] = ['to' => 'موافقة العميل', 'by' => $order->customer?->name ?? 'العميل', 'date' => $createdAt];
        }
        if ($status === 'delivered') {
            $timeline[] = ['to' => 'تم التسليم', 'by' => 'التوصيل', 'date' => $createdAt];
        }
        if (in_array($status, ['cancelled', 'rejected', 'returned', 'expired', 'customer_rejected'])) {
            $timeline[] = ['to' => 'ملغي/مرفوض', 'by' => 'النظام', 'date' => $createdAt];
        }

        return $timeline;
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
            'message' => 'تم تسجيل الرد بنجاح',
            'response' => $validated['response'],
        ]);
    }

    /**
     * Get customer's return requests
     */
    public function returnsIndex(Request $request)
    {
        $customer = $request->user();

        $returns = ReturnRequest::with([
            'invoice',
            'items.item',
            'items.pack',
        ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(20);

        $summary = ReturnRequest::where('customer_id', $customer->id)
            ->toBase()
            ->selectRaw('status, COUNT(*) as aggregate, COALESCE(SUM(total_refund), 0) as refund')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pendingCount = (int) ($summary['pending']->aggregate ?? 0);

        return response()->json([
            'success' => true,
            'returns' => [
                'data' => $returns->getCollection()->map(function ($return) {
                    return [
                        'id' => $return->id,
                        'return_number' => $return->return_number,
                        'status' => $return->status,
                        'type' => $return->type,
                        'reason' => $return->reason,
                        'total_refund' => (float) $return->total_refund,
                        'created_at' => $return->created_at?->format('Y-m-d'),
                        'invoice' => $return->invoice ? [
                            'id' => $return->invoice->id,
                            'invoice_number' => $return->invoice->invoice_number,
                        ] : null,
                        'items' => $return->items->map(function ($line) {
                            return [
                                'id' => $line->id,
                                'item' => $line->item ? [
                                    'name_ar' => $line->item->name_ar,
                                    'name_en' => $line->item->name_en,
                                ] : null,
                                'quantity_returned' => (float) $line->quantity_returned,
                                'pack' => $line->pack ? [
                                    'display_name' => $line->pack->display_name,
                                ] : null,
                            ];
                        }),
                    ];
                }),
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
            'summary' => $summary->mapWithKeys(function ($item) {
                return [$item->status => [
                    'aggregate' => (int) $item->aggregate,
                    'refund' => (float) $item->refund,
                ]];
            }),
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Get customer's products with batch quantities
     */
    public function myProducts(Request $request)
    {
        $customer = $request->user();

        $totals = \DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('items', 'items.id', '=', 'invoice_items.item_id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy('items.id', 'items.name_ar', 'items.name_en', 'items.code')
            ->selectRaw(
                'items.id as item_id, items.name_ar, items.name_en, items.code,
                 SUM(invoice_items.quantity) as total_quantity,
                 COUNT(DISTINCT invoices.id) as invoice_count,
                 SUM(invoice_items.line_total) as total_value'
            )
            ->orderByDesc('total_quantity')
            ->get();

        $batches = \DB::table('invoice_item_batches')
            ->join('invoice_items', 'invoice_items.id', '=', 'invoice_item_batches.invoice_item_id')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('batch_items', 'batch_items.id', '=', 'invoice_item_batches.batch_item_id')
            ->leftJoin('production_batches', 'production_batches.id', '=', 'batch_items.batch_id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy(
                'invoice_items.item_id',
                'batch_items.id',
                'production_batches.batch_number',
                'batch_items.expiry_date'
            )
            ->selectRaw(
                'invoice_items.item_id as item_id,
                 batch_items.id as batch_item_id,
                 COALESCE(production_batches.batch_number, "—") as batch_number,
                 batch_items.expiry_date as expiry_date,
                 SUM(invoice_item_batches.quantity) as quantity'
            )
            ->orderBy('batch_items.expiry_date')
            ->get()
            ->groupBy('item_id');

        $rows = $totals->map(function ($row) use ($batches) {
            return [
                'item_id' => (int) $row->item_id,
                'name' => $row->name_ar ?: ($row->name_en ?: 'منتج'),
                'code' => $row->code ?: '—',
                'total_quantity' => (float) $row->total_quantity,
                'invoice_count' => (int) $row->invoice_count,
                'total_value' => (float) $row->total_value,
                'batches' => $batches->get((int) $row->item_id, collect())->map(function ($batch) {
                    return [
                        'batch_item_id' => (int) $batch->batch_item_id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'quantity' => (float) $batch->quantity,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'rows' => $rows,
            'total_items' => $rows->count(),
            'total_quantity' => round($rows->sum('total_quantity'), 2),
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

    /**
     * Fetch the quotation for a specific order (customer-facing).
     */
    public function orderQuotation(Request $request, $order)
    {
        $customer = $request->user();

        $quotation = Quotation::where('order_id', $order)
            ->where('customer_id', $customer->id)
            ->with(['items.item', 'items.itemPack'])
            ->latest()
            ->first();

        if (! $quotation) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد عروض أسعار لهذا الطلب',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'quotation' => [
                'id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'order_id' => $quotation->order_id,
                'customer_id' => $quotation->customer_id,
                'version' => $quotation->version,
                'status' => $quotation->status,
                'subtotal' => (float) $quotation->subtotal,
                'discount_total' => (float) $quotation->discount_total,
                'tax_total' => (float) $quotation->tax_total,
                'total' => (float) $quotation->total,
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'sent_at' => $quotation->sent_at?->toDateTimeString(),
                'customer_response' => $quotation->customer_response,
                'customer_response_at' => $quotation->customer_response_at?->toDateTimeString(),
                'customer_notes' => $quotation->customer_notes,
                'notes' => $quotation->notes,
                'created_at' => $quotation->created_at->toDateTimeString(),
                'items' => $quotation->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_name_ar' => $item->item?->name_ar,
                        'item_name_en' => $item->item?->name_en,
                        'item_pack_id' => $item->item_pack_id,
                        'pack_name' => $item->itemPack?->getDisplayNameAttribute(),
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'discount_percent' => (float) $item->discount_percent,
                        'discount_amount' => (float) $item->discount_amount,
                        'tax_percent' => (float) $item->tax_percent,
                        'tax_amount' => (float) $item->tax_amount,
                        'line_total' => (float) $item->line_total,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Record customer response to a quotation.
     */
    public function respondToQuotation(Request $request, $order)
    {
        $customer = $request->user();

        $validated = $request->validate([
            'response' => 'required|in:approved,rejected,changes_requested',
            'notes' => 'nullable|string|max:500',
        ]);

        $quotation = Quotation::where('order_id', $order)
            ->where('customer_id', $customer->id)
            ->latest()
            ->first();

        if (! $quotation) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد عروض أسعار لهذا الطلب',
            ], 404);
        }

        try {
            app(\App\Contracts\QuotationServiceInterface::class)
                ->recordCustomerResponse($quotation, $validated['response'], $validated['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الرد بنجاح',
                'response' => $validated['response'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download the order PDF for customer
     */
    public function downloadOrderPdf(Request $request, $id)
    {
        $customer = $request->user();

        $order = Order::where('customer_id', $customer->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id);
                if (is_numeric($id)) {
                    $q->orWhere('order_number', $id);
                }
            })
            ->with(['customer', 'currency', 'warehouse', 'createdBy', 'items.item', 'items.pack', 'items.warehouse', 'quotation'])
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'الطلبية غير موجودة'], 404);
        }

        $pdf = \App\Services\PdfGenerator::generate('pdf.order', compact('order'));

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="order-' . $order->order_number . '.pdf"');
    }

    /**
     * Download the invoice PDF for customer
     */
    public function downloadInvoicePdf(Request $request, $id)
    {
        $customer = $request->user();

        $invoice = Invoice::where('customer_id', $customer->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id);
                if (is_numeric($id)) {
                    $q->orWhere('invoice_number', $id)->orWhere('order_id', $id);
                }
            })
            ->with(['customer', 'priceList', 'currency', 'items.item', 'items.itemPack.packType', 'items.priceList'])
            ->first();

        if (! $invoice) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة'], 404);
        }

        $lang = $request->query('lang', 'bilingual');
        $pdf = \App\Services\PdfGenerator::generate('pdf.invoice', compact('invoice', 'lang'));

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="invoice-' . $invoice->invoice_number . '.pdf"');
    }

    /**
     * Download the quotation PDF for customer
     */
    public function downloadQuotationPdf(Request $request, $id)
    {
        $customer = $request->user();

        $quotation = Quotation::where('order_id', $id)
            ->where('customer_id', $customer->id)
            ->with(['customer', 'createdBy', 'order.customer', 'order.warehouse', 'order.currency', 'items.item', 'items.pack', 'items.warehouse'])
            ->latest('version')
            ->first();

        if (! $quotation) {
            return response()->json(['success' => false, 'message' => 'لا توجد عروض أسعار لهذا الطلب'], 404);
        }

        $pdf = \App\Services\PdfGenerator::generate('pdf.quotation', compact('quotation'));

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }
}
