@extends('app_android.partials.mobile.app_mobile')

@section('title', 'نبذة عنا')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'نبذة عنا'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <!-- الشعار والنبذة -->
        <div class="text-center mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-3">
                <i class="fas fa-utensils text-white text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">ماجاديت للصناعات الغذائية</h1>
            <p class="text-gray-500 text-sm mt-1">جودة تذوقها الثقة</p>
        </div>

        <!-- قصتنا -->
        <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                <i class="fas fa-book-open text-purple-600"></i> قصتنا
            </h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                تأسست ماجاديت للصناعات الغذائية عام 2010 بهدف تقديم منتجات غذائية عالية الجودة للمستهلكين في المملكة العربية السعودية والمنطقة. 
                بدأنا بخط صغير لإنتاج الحلويات، ثم توسعنا لنشمل الوجبات الخفيفة والمعلبات والحبوب، وأصبحنا علامة موثوقة في كل بيت سعودي.
            </p>
        </div>

        <!-- الرسالة والرؤية -->
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-bullseye text-purple-600 text-lg"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm">رؤيتنا</h3>
                <p class="text-xs text-gray-500 mt-1">أن نكون الخيار الأول في الأغذية الصحية واللذيذة في الشرق الأوسط</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-heart text-purple-600 text-lg"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm">رسالتنا</h3>
                <p class="text-xs text-gray-500 mt-1">توفير غذاء آمن وممتع بأسعار معقولة، مع الالتزام بأعلى معايير الجودة</p>
            </div>
        </div>

        <!-- قيمنا -->
        <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                <i class="fas fa-gem text-purple-600"></i> قيمنا الأساسية
            </h2>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    <span class="text-sm text-gray-700">الجودة أولاً – نختار أفضل المكونات</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-handshake text-green-500 text-sm"></i>
                    <span class="text-sm text-gray-700">الثقة والشفافية – نضع العميل في قلب اهتمامنا</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-leaf text-green-500 text-sm"></i>
                    <span class="text-sm text-gray-700">الاستدامة – نحرص على البيئة والمجتمع</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-chart-line text-green-500 text-sm"></i>
                    <span class="text-sm text-gray-700">الابتكار – نطور منتجاتنا باستمرار</span>
                </div>
            </div>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="bg-white rounded-2xl shadow-md p-3 text-center">
                <p class="text-2xl font-bold text-purple-600">+15</p>
                <p class="text-xs text-gray-500">عاماً من التميز</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center">
                <p class="text-2xl font-bold text-purple-600">+200</p>
                <p class="text-xs text-gray-500">منتج متنوع</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center">
                <p class="text-2xl font-bold text-purple-600">+1.5M</p>
                <p class="text-xs text-gray-500">عميل سعيد</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center">
                <p class="text-2xl font-bold text-purple-600">+50</p>
                <p class="text-xs text-gray-500">شريك موزع</p>
            </div>
        </div>

        <!-- خريطة تواصل -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-5 text-white text-center">
            <i class="fas fa-headset text-3xl mb-2"></i>
            <h3 class="font-bold text-lg">نحن هنا لخدمتك</h3>
            <p class="text-sm opacity-90 mt-1">لأي استفسار أو اقتراح، لا تتردد في التواصل مع فريق الدعم</p>
            <a href="{{ route('customer.contact') }}" class="inline-block mt-3 bg-white text-purple-600 font-semibold px-5 py-2 rounded-full text-sm shadow-md transition hover:scale-105">
                تواصل معنا
            </a>
        </div>
            <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection