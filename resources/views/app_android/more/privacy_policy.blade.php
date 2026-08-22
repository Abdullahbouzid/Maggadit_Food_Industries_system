@extends('app_android.partials.mobile.app_mobile')

@section('title', 'سياسة الخصوصية')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'سياسة الخصوصية'])

    <div class="p-4" dir="rtl">
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-5 text-white mb-5 text-center">
            <i class="fas fa-shield-alt text-4xl mb-2"></i>
            <h2 class="text-xl font-bold">خصوصيتك تهمنا</h2>
            @if($policy && $policy->title)
                <p class="text-sm opacity-90 mt-1">{{ $policy->title }} - نحن نلتزم بحماية بياناتك الشخصية وخصوصيتك</p>
            @else
                <p class="text-sm opacity-90 mt-1">نحن في ماجاديت نلتزم بحماية بياناتك الشخصية وخصوصيتك</p>
            @endif
        </div>

        @if($policy)
            <div class="prose prose-lg max-w-none">
                {!! nl2br(e($policy->content)) !!}
            </div>
        @else
            <div class="space-y-4">
                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <i class="fas fa-database"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">جمع المعلومات</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">نقوم بجمع المعلومات التي تقدمها لنا طواعية عند التسجيل، مثل الاسم، البريد الإلكتروني، رقم الجوال، وعنوان التوصيل. كما قد نجمع معلومات غير شخصية مثل نوع الجهاز ونظام التشغيل لتحسين أداء التطبيق.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">كيف نستخدم بياناتك</h3>
                    </div>
                    <ul class="space-y-2 text-gray-600 text-sm">
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500 text-xs"></i> معالجة طلباتك وتوصيلها</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500 text-xs"></i> تحسين تجربتك وتخصيص المحتوى</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500 text-xs"></i> التواصل معك بشأن العروض والتحديثات (بموافقتك)</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500 text-xs"></i> دعم العملاء وحل المشكلات</li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">حماية معلوماتك</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">نستخدم إجراءات أمنية متقدمة (تشفير SSL، جدران حماية) لحماية بياناتك من الوصول غير المصرح به أو التعديل أو الكشف. يتم تخزين المعلومات في بيئة آمنة مع تقييد الوصول إلى الموظفين المخولين فقط.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">مشاركة البيانات مع أطراف خارجية</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">لا نقوم ببيع أو تأجير بياناتك الشخصية. قد نشاركها فقط مع شركات النقل والشحن لتوصيل طلباتك، أو مع مزودي خدمات الدفع، أو عند اقتضاء القانون ذلك. نلتزم بعدم مشاركة بياناتك لأغراض تسويقية دون موافقتك الصريحة.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">حقوقك</h3>
                    </div>
                    <ul class="space-y-2 text-gray-600 text-sm">
                        <li class="flex items-center gap-2"><i class="fas fa-eye text-purple-500 text-xs"></i> الوصول إلى بياناتك الشخصية المخزنة لدينا</li>
                        <li class="flex items-center gap-2"><i class="fas fa-pen text-purple-500 text-xs"></i> تعديل أو تحديث معلوماتك</li>
                        <li class="flex items-center gap-2"><i class="fas fa-trash-alt text-purple-500 text-xs"></i> طلب حذف حسابك وبياناتك</li>
                        <li class="flex items-center gap-2"><i class="fas fa-envelope text-purple-500 text-xs"></i> إلغاء الاشتراك في الرسائل التسويقية</li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                            <i class="fas fa-cookie-bite"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">ملفات تعريف الارتباط (Cookies)</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">نستخدم ملفات تعريف الارتباط لتحسين أداء التطبيق وتذكر تفضيلاتك. يمكنك تعطيلها من إعدادات متصفحك، لكن ذلك قد يؤثر على بعض الوظائف.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-base">تحديثات سياسة الخصوصية</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">قد نقوم بتحديث هذه السياسة من وقت لآخر. سنعلمك بأي تغييرات جوهرية عبر البريد الإلكتروني أو من خلال إشعار في التطبيق. تاريخ آخر تحديث: <span class="font-semibold">23 مايو 2025</span></p>
                </div>
            </div>
        @endif

        <div class="mt-6 bg-gray-50 rounded-2xl p-4 text-center border border-gray-200">
            <p class="text-sm text-gray-600">إذا كان لديك أي استفسار حول سياسة الخصوصية، يرجى التواصل معنا</p>
            <a href="{{ route('customer.contact') }}" class="inline-block mt-2 text-purple-600 font-semibold text-sm">
                <i class="fas fa-headset ml-1"></i> صفحة التواصل
            </a>
        </div>

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection
