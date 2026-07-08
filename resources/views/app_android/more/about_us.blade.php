@extends('app_android.partials.mobile.app_mobile')

@section('title', 'نبذة عنا')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'نبذة عنا'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        @if($about)
            <!-- صورة الغلاف إن وجدت -->
            @if($about->cover_image_url)
                <div class="w-full h-48 rounded-2xl overflow-hidden mb-4 shadow-md">
                    <img src="{{ $about->cover_image_url }}" alt="غلاف" class="w-full h-full object-cover">
                </div>
            @endif

            <!-- الشعار والنبذة -->
            <div class="text-center mb-6">
                @if($about->image_url)
                    <img src="{{ $about->image_url }}" alt="{{ $about->title ?? 'الشعار' }}" class="w-24 h-24 rounded-2xl object-cover mx-auto shadow-lg mb-3 border-2 border-white">
                @else
                    <div class="w-24 h-24 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-3">
                        <i class="fas fa-utensils text-white text-4xl"></i>
                    </div>
                @endif
                <h1 class="text-2xl font-bold text-gray-800">{{ $about->title ?? 'ماجاديت للصناعات الغذائية' }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $about->subtitle ?? 'جودة تذوقها الثقة' }}</p>
            </div>

            <!-- قصتنا (الوصف) -->
            <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                    <i class="fas fa-book-open text-purple-600"></i> قصتنا
                </h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ $about->description ?? 'لا توجد قصة حالياً.' }}
                </p>
            </div>

            <!-- الرسالة والرؤية -->
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-bullseye text-purple-600 text-lg"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm">رؤيتنا</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $about->vision ?? 'لا توجد رؤية محددة' }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-heart text-purple-600 text-lg"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm">رسالتنا</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $about->mission ?? 'لا توجد رسالة محددة' }}</p>
                </div>
            </div>

            <!-- قيمنا -->
            <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                    <i class="fas fa-gem text-purple-600"></i> قيمنا الأساسية
                </h2>
                <div class="space-y-3">
                    @php
                        $values = is_array($about->values) ? $about->values : json_decode($about->values, true);
                    @endphp
                    @if($values && count($values))
                        @foreach($values as $value)
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span class="text-sm text-gray-700">{{ $value }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">لا توجد قيم محددة.</p>
                    @endif
                </div>
            </div>

            <!-- ========== الإحصائيات (معدلة) ========== -->
         <!-- ========== الإحصائيات ========== -->
@php
    $stats = [];
    if (!empty($about->statistics)) {
        $raw = is_array($about->statistics) ? $about->statistics : json_decode($about->statistics, true);
        if (is_array($raw)) {
            // دمج الأزواج: نبحث عن key ثم القيمة التالية لها
            $i = 0;
            $count = count($raw);
            while ($i < $count) {
                $item = $raw[$i];
                if (isset($item['key']) && isset($item['value'])) {
                    // إذا كان العنصر يحتوي على كلا المفتاح والقيمة
                    $stats[] = ['key' => $item['key'], 'value' => $item['value']];
                    $i++;
                } elseif (isset($item['key']) && !isset($item['value']) && isset($raw[$i+1]['value'])) {
                    // إذا كان العنصر الحالي يحتوي على مفتاح والعنصر التالي يحتوي على قيمة
                    $stats[] = ['key' => $item['key'], 'value' => $raw[$i+1]['value']];
                    $i += 2;
                } else {
                    // تخطي العناصر غير المفهومة
                    $i++;
                }
            }
        }
    }
@endphp
@if(!empty($stats))
    <div class="grid grid-cols-2 gap-4 mb-5">
        @foreach($stats as $stat)
            @php
                $key = $stat['key'] ?? '';
                $value = $stat['value'] ?? '';
                $icon = 'fa-chart-simple';
                if (str_contains(strtolower($key), 'عام') || str_contains(strtolower($key), 'سنة') || str_contains(strtolower($key), 'year')) $icon = 'fa-calendar-alt';
                elseif (str_contains(strtolower($key), 'منتج') || str_contains(strtolower($key), 'product')) $icon = 'fa-box';
                elseif (str_contains(strtolower($key), 'عميل') || str_contains(strtolower($key), 'customer')) $icon = 'fa-users';
                elseif (str_contains(strtolower($key), 'شريك') || str_contains(strtolower($key), 'partner')) $icon = 'fa-handshake';
                elseif (str_contains(strtolower($key), 'طلب') || str_contains(strtolower($key), 'order')) $icon = 'fa-shopping-cart';
            @endphp
            <div class="bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg hover:-translate-y-1">
                <div class="text-3xl font-bold text-purple-600 mb-1">
                    {{ is_numeric($value) ? number_format($value) : $value }}
                </div>
                <div class="text-xs text-gray-500 flex items-center justify-center gap-1">
                    <i class="fas {{ $icon }} text-purple-400"></i>
                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white rounded-2xl shadow-md p-4 text-center">
        <p class="text-gray-500 text-sm">لا توجد إحصائيات متاحة</p>
    </div>
@endif

            <!-- خريطة تواصل -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-5 text-white text-center">
                <i class="fas fa-headset text-3xl mb-2"></i>
                <h3 class="font-bold text-lg">نحن هنا لخدمتك</h3>
                <p class="text-sm opacity-90 mt-1">لأي استفسار أو اقتراح، لا تتردد في التواصل مع فريق الدعم</p>
                <a href="{{ route('customer.contact') }}" class="inline-block mt-3 bg-white text-purple-600 font-semibold px-5 py-2 rounded-full text-sm shadow-md transition hover:scale-105">
                    تواصل معنا
                </a>
            </div>
        @else
            <!-- في حال عدم وجود نبذة -->
            <div class="bg-white rounded-2xl shadow-md p-5 text-center">
                <i class="fas fa-info-circle text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-bold text-gray-800">لا توجد نبذة</h3>
                <p class="text-gray-500 text-sm">لم يتم إضافة نبذة لهذه المنظمة بعد.</p>
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection