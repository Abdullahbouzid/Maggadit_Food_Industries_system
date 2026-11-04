<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Support\Stamped;

/**
 * Customer-facing content: FAQs, banners, about/privacy pages, push
 * notifications, offers, item images and the pack pivot tables.
 */
class ContentDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->faqs();
        $this->banners();
        $this->organizationAbout();
        $this->privacyPolicies();
        $this->notifications();
        $this->offers();
        $this->itemImages();
        $this->packPivots();
    }

    private function faqs(): void
    {
        $faqs = [
            ['category' => 'orders', 'question' => 'كيف أطلب من التطبيق؟', 'answer' => 'اختر المنتجات وأضفها إلى السلة، ثم أكمل بيانات التوصيل وأرسل الطلب.'],
            ['category' => 'orders', 'question' => 'هل يمكن تعديل الطلب بعد الإرسال؟', 'answer' => 'نعم، يمكن تعديل الطلب ما لم يدخل مرحلة التجهيز.'],
            ['category' => 'orders', 'question' => 'كيف أعرف حالة طلبي؟', 'answer' => 'من صفحة "طلباتي"يمكن رؤية حالة كل طلب لحظة بلحظة.'],
            ['category' => 'payment', 'question' => 'ما طرق الدفع المتاحة؟', 'answer' => 'نقدًا، محفظة العميل، بطاقة، تحويل بنكي، شيك، أو دفع إلكتروني.'],
            ['category' => 'payment', 'question' => 'ما هي مهلة سداد الفاتورة؟', 'answer' => 'الفواتير النقدية تُسدد فورًا، والآجلة لها مهلة حسب نوع العميل.'],
            ['category' => 'account', 'question' => 'كيف أغيّر كلمة المرور؟', 'answer' => 'من صفحة "معلوماتي" ثم "تغيير كلمة المرور".'],
        ];

        foreach ($faqs as $faq) {
            Stamped::upsert('faqs', 
                ['question' => $faq['question']], 
                $faq);
        }
    }

    private function banners(): void
    {
        $banners = [
            ['title' => 'خصم على المشروبات', 'image' => 'banners/beverages.jpg', 'type' => 'promo', 'url' => null, 'show_in_customer_app' => true, 'order' => 1],
            ['title' => 'أحدث المنتجات', 'image' => 'banners/new-arrivals.jpg', 'type' => 'category', 'url' => null, 'show_in_customer_app' => true, 'order' => 2],
            ['title' => 'مطعم الشرق', 'image' => 'banners/restaurant.jpg', 'type' => 'promo', 'url' => null, 'show_in_customer_app' => true, 'order' => 3],
            ['title' => 'توصيل مجاني', 'image' => 'banners/free-delivery.jpg', 'type' => 'announcement', 'url' => null, 'show_in_customer_app' => true, 'order' => 4],
            ['title' => 'عروض الأسبوع', 'image' => 'banners/weekly-offers.jpg', 'type' => 'promo', 'url' => null, 'show_in_customer_app' => true, 'order' => 5],
        ];

        foreach ($banners as $banner) {
            Stamped::upsert('banners', ['title' => $banner['title']],  $banner);
        }
    }

    private function organizationAbout(): void
    {
        $orgId = DB::table('organizations')->value('id');

        if (! $orgId) {
            return;
        }

        $pages = [
            [
                'title' => 'عن مجدية للصناعات الغذائية',
                'description' => 'شركة مجدية للصناعات الغذائية توزّع منتجات غذائية ومشروبات إلى التجار والمطاعم في جميع أنحاء ليبيا.',
                'vision' => 'أن نكون الخيار الأول في توزيع المنتجات الغذائية بالجودة والخدمة.',
                'mission' => 'توفير منتجات غذائية آمنة وطازجة بأسعار عادلة وتوصيل في الموعد.',
            ],
            [
                'title' => 'تاريخ الشركة',
                'description' => 'انطلقت الشركة عام 2011 بفريق بخبرات تراكمية في سوق التوزيع الغذائي.',
                'vision' => 'الوصول إلى جميع ولايات ليبيا.',
                'mission' => 'توسيع شبكة التوزيع逐年ًا.',
            ],
            [
                'title' => 'منهجية العمل',
                'description' => 'نعمل وفق نظام إدارة جودة معتمد يغطي سلسلة التوريد من المصنع إلى العميل.',
                'vision' => 'الحفاظ على أعلى معايير الجودة.',
                'mission' => 'تطبيق معايير السلامة الغذائية في كل المراحل.',
            ],
            [
                'title' => 'فروعنا',
                'description' => 'تغطي شبكة الفروع Tripoli وبنغازي ومصراتة والزاوية وسبها.',
                'vision' => 'تغطية كامل التراب.',
                'mission' => 'خدمة قريبة من العميل في كل منطقة.',
            ],
            [
                'title' => 'شراكاتنا',
                'description' => 'نعمل مع أفضل المصانع المحلية/عالميةفي توفير منتجات تنافسية.',
                'vision' => 'توسيع شبكة الشراكات.',
                'mission' => 'توفير تشكيلة تلبي حاجة السوق.',
            ],
        ];

        foreach ($pages as $page) {
            Stamped::upsert('organization_abouts', 
                ['organization_id' => $orgId, 'title' => $page['title']], 
                $page + [
                    'organization_id' => $orgId,
                    // Both `values` and `statistics` have a json_valid() CHECK.
                    'values' => json_encode(
                        ['الجودة', 'الالتزام', 'خدمة العميل', 'الشفافية'],
                        JSON_UNESCAPED_UNICODE,
                    ),
                    'statistics' => json_encode([
                        'customers' => DB::table('customers')->count(),
                        'products' => DB::table('items')->count(),
                        'warehouses' => DB::table('warehouses')->count(),
                        'years' => 15,
                    ], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                ]);
        }
    }

    private function privacyPolicies(): void
    {
        $orgId = DB::table('organizations')->value('id');

        if (! $orgId) {
            return;
        }

        $policies = [
            ['type' => 'privacy', 'title' => 'سياسة الخصوصية', 'content' => 'نحترم خصوصية بياناتك ونستخدمها لتنفيذ الطلبات فقط.'],
            ['type' => 'terms', 'title' => 'الشروط والأحكام', 'content' => 'استخدام التطبيق يخضع لهذه الشروط والأحكام.'],
            ['type' => 'returns', 'title' => 'سياسة الإرجاع', 'content' => 'يجب الإرجاع خلال 48 ساعة من الاستلام مع إثبات الفاتورة.'],
            ['type' => 'shipping', 'title' => 'سياسة التوصيل', 'content' => 'يتم التوصيل خلال 24 إلى 48 ساعة داخل المدن الرئيسية.'],
            ['type' => 'warranty', 'title' => 'ضمان الجودة', 'content' => 'نضمن جودة جميع المنتجات ونستبدل التالف فورًا.'],
        ];

        foreach ($policies as $policy) {
            Stamped::upsert('privacy_policies', 
                ['organization_id' => $orgId, 'type' => $policy['type']], 
                $policy + ['organization_id' => $orgId, 'is_active' => true]);
        }
    }

    private function notifications(): void
    {
        $notifications = [
            ['subject_ar' => 'عروض جديدة', 'subject_en' => 'New Offers', 'body_ar' => 'تفضل بتصفح أحدث العروض.', 'body_en' => 'Browse the latest offers.', 'group' => 'promo', 'channel' => 'push'],
            ['subject_ar' => 'تأكيد الطلب', 'subject_en' => 'Order Confirmation', 'body_ar' => 'تم استلام طلبك بنجاح.', 'body_en' => 'We received your order.', 'group' => 'order', 'channel' => 'push'],
            ['subject_ar' => 'تحديث حالة الطلب', 'subject_en' => 'Order Status Update', 'body_ar' => 'تم تحديث حالة طلبك.', 'body_en' => 'Your order status changed.', 'group' => 'order', 'channel' => 'push'],
            ['subject_ar' => 'تذكير بالسداد', 'subject_en' => 'Payment Reminder', 'body_ar' => 'لديك فاتورة مستحقة السداد.', 'body_en' => 'You have an invoice due.', 'group' => 'payment', 'channel' => 'push'],
            ['subject_ar' => 'وصلت منتجات جديدة', 'subject_en' => 'New Products', 'body_ar' => 'أضفنا منتجات جديدة إلى الكتالوج.', 'body_en' => 'New products were added.', 'group' => 'catalog', 'channel' => 'push'],
        ];

        foreach ($notifications as $notification) {
            $existing = DB::table('notifications')->where('subject_ar', $notification['subject_ar'])->first();

            if ($existing) {
                $id = $existing->id;
            } else {
                $id = Stamped::insertGetId('notifications', $notification + [
                    'icon_type' => 'bell',
                    // `customers` is a json_valid() column.
                    'customers' => json_encode(['all']),
                    'is_sent' => true,
                    'scheduled_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notification read state, one row per notification.
            $customers = DB::table('customers')->orderBy('id')->limit(5)->pluck('id');
            foreach ($customers as $customerId) {
                Stamped::upsert('notification_reads', 
                    ['notification_id' => $id, 'customer_id' => $customerId], 
                    [
                        'notification_id' => $id,
                        'customer_id' => $customerId,
                        'read_at' => $customerId % 2 === 0 ? now() : null,
                        'is_starred' => $customerId === 1,
                        'is_archived' => false,
                    ]);
            }
        }
    }

    private function offers(): void
    {
        $items = DB::table('items')->orderBy('id')->pluck('id');

        if ($items->isEmpty()) {
            return;
        }

        $offers = [
            ['title' => 'خصم 10% على المشروبات', 'old_price' => 10.00, 'new_price' => 9.00, 'discount_percentage' => 10],
            ['title' => 'خصم 15% على الألبان', 'old_price' => 20.00, 'new_price' => 17.00, 'discount_percentage' => 15],
            ['title' => 'عرض الافتتاح', 'old_price' => 50.00, 'new_price' => 42.50, 'discount_percentage' => 15],
            ['title' => 'خصم على المنظفات', 'old_price' => 30.00, 'new_price' => 27.00, 'discount_percentage' => 10],
            ['title' => 'عرض أسبوعي', 'old_price' => 80.00, 'new_price' => 68.00, 'discount_percentage' => 15],
        ];

        foreach ($offers as $index => $offer) {
            $itemId = $items->get($index % $items->count());

            Stamped::upsert('offers', 
                ['item_id' => $itemId, 'title' => $offer['title']], 
                $offer + [
                    'item_id' => $itemId,
                    'description' => $offer['title'],
                    'image' => 'offers/offer-'.($index + 1).'.jpg',
                    'start_date' => now()->subWeek(),
                    'end_date' => now()->addMonth(),
                    'is_active' => true,
                ]);
        }
    }

    private function itemImages(): void
    {
        $items = DB::table('items')->orderBy('id')->get();

        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            Stamped::upsert('item_images', 
                ['item_id' => $item->id, 'image_path' => 'items/'.$item->code.'.jpg'], 
                [
                    'item_id' => $item->id,
                    'image_path' => 'items/'.$item->code.'.jpg',
                    'alt_text' => $item->name_ar,
                    'sort_order' => 1,
                ]);
        }
    }

    private function packPivots(): void
    {
        $packs = DB::table('item_packs')->orderBy('id')->pluck('id');
        $groups = DB::table('pack_groups')->pluck('id');
        $operations = DB::table('pack_operations')->pluck('id');

        if ($packs->isEmpty() || $groups->isEmpty() || $operations->isEmpty()) {
            return;
        }

        $groupList = $groups->values();
        $operationList = $operations->values();

        foreach ($packs as $packId) {
            Stamped::upsert('item_pack_pack_group', 
                ['item_pack_id' => $packId, 'pack_group_id' => $groupList->get($packId % $groupList->count())], 
                []);

            Stamped::upsert('item_pack_pack_operation', 
                ['item_pack_id' => $packId, 'pack_operation_id' => $operationList->get($packId % $operationList->count())], 
                []);
        }
    }
}
