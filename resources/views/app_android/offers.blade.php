<!-- @extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.dashboard_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.dashboard_title')])

    <div class="px-4 py-3" dir="rtl">
        {{-- عنوان القسم مع تأثير زخرفي --}}
        <div class="flex items-center justify-between mb-5 pr-1">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></div>
                <h2 class="text-xl font-extrabold text-gray-800 tracking-tight">عروض حصرية</h2>
            </div>
            <span class="text-[11px] font-medium text-blue-700 bg-blue-100 px-2.5 py-1 rounded-full shadow-inner">مُحدثة يومياً</span>
        </div>

        <div class="space-y-5">
            {{-- العرض الأول: تخفيضات الصيف (كارت احترافي) --}}
            <div class="group bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex flex-row">
                    {{-- منطقة الصورة الرمزية مع تدرج لوني جميل وأيقونة متحركة --}}
                    <div class="relative w-28 h-28 bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl overflow-hidden">
                        <div class="absolute inset-0 bg-white/10 group-hover:bg-white/20 transition"></div>
                        <span class="transform transition-transform group-hover:scale-110">🛍️</span>
                        {{-- شارة الخصم --}}
                        <div class="absolute top-1 left-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-md">-50%</div>
                    </div>
                    {{-- تفاصيل العرض --}}
                    <div class="flex-1 p-3 text-right">
                        <div class="flex justify-between items-start gap-1">
                            <div class="flex flex-wrap gap-1">
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">خصم كبير</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-base">تخفيضات الصيف الكبرى</h3>
                        </div>
                        <p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">
                            خصم يصل إلى 50% على جميع الملابس الصيفية والإكسسوارات الأصلية. سارع بالشراء!
                        </p>
                        <div class="flex justify-between items-center mt-3">
                            <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                <span class="text-amber-500">⏰</span>
                                <span>ينتهي: 31 يوليو 2025</span>
                            </div>
                            <a href="#" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white text-xs font-semibold px-4 py-1.5 rounded-full shadow-md transition-all duration-200 transform hover:scale-105">
                                استفد الآن
                            </a>
                        </div>
                    </div>
                </div>
            </div>

         
        </div>

        {{-- فاصل زخرفي ونص تحفيزي --}}
        <div class="mt-8 text-center">
            <div class="inline-flex items-center gap-2 bg-gray-100/70 backdrop-blur-sm px-4 py-2 rounded-full">
                <span class="text-yellow-500 text-lg">⚡</span>
                <span class="text-xs text-gray-600 font-medium">عروض حصرية للجوال · لا تفوت الفرصة</span>
            </div>
        </div>
          <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>
@endsection -->