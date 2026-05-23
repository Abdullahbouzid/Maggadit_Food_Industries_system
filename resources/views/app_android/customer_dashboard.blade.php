@extends('app_android.partials.mobile.app_mobile')

@section('title', __('messages.dashboard_title'))

@section('content')
    @include('app_android.partials.mobile.header', ['title' => __('messages.dashboard_title')])

    <div class="p-4 space-y-6 mb-5" dir="rtl">
        {{-- قسم الفئات (Categories) --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">الفئات</h2>
                <a href="{{ route('categories.index') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2">
                @php
                    $categories = [
                        ['id' => 1, 'name' => 'إلكترونيات', 'icon' => '📱', 'color' => 'bg-blue-100'],
                        ['id' => 2, 'name' => 'ملابس', 'icon' => '👕', 'color' => 'bg-green-100'],
                        ['id' => 3, 'name' => 'أحذية', 'icon' => '👟', 'color' => 'bg-yellow-100'],
                        ['id' => 4, 'name' => 'عطور', 'icon' => '🌸', 'color' => 'bg-pink-100'],
                        ['id' => 5, 'name' => 'كتب', 'icon' => '📚', 'color' => 'bg-purple-100'],
                        ['id' => 6, 'name' => 'ألعاب', 'icon' => '🎮', 'color' => 'bg-red-100'],
                    ];
                @endphp
                @foreach($categories as $category)
                    <div class="flex-shrink-0 text-center w-20 cursor-pointer">
                        <div class="w-16 h-16 mx-auto {{ $category['color'] }} rounded-full flex items-center justify-center text-2xl shadow-sm transition hover:scale-105">
                            {{ $category['icon'] }}
                        </div>
                        <p class="text-xs mt-1 text-gray-700">{{ $category['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- قسم الماركات (Brands) --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">ماركات شهيرة</h2>
                <a href="{{ route('customer.brands') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="flex overflow-x-auto space-x-3 space-x-reverse pb-2">
                @php
                    $brands = [
                        ['id' => 1, 'name' => 'نيكي', 'logo' => '👟', 'bg' => 'bg-gray-100'],
                        ['id' => 2, 'name' => 'أديداس', 'logo' => '⚡', 'bg' => 'bg-gray-100'],
                        ['id' => 3, 'name' => 'ابل', 'logo' => '🍎', 'bg' => 'bg-gray-100'],
                        ['id' => 4, 'name' => 'سامسونج', 'logo' => '📱', 'bg' => 'bg-gray-100'],
                        ['id' => 5, 'name' => 'هوندا', 'logo' => '🏍️', 'bg' => 'bg-gray-100'],
                    ];
                @endphp
                @foreach($brands as $brand)
                    <div class="flex-shrink-0 text-center w-24 cursor-pointer">
                        <div class="w-20 h-20 {{ $brand['bg'] }} rounded-2xl flex items-center justify-center text-3xl shadow-sm transition hover:shadow-md">
                            {{ $brand['logo'] }}
                        </div>
                        <p class="text-xs mt-1 font-semibold text-gray-800">{{ $brand['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- قسم المنتجات (Products) --}}
        <div class="mb-5">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">أفضل المنتجات</h2>
                <a href="{{ route('customer.products') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @php
                    $products = [
                        ['id' => 1, 'name' => 'حذاء رياضي نيكي', 'price' => 299, 'old_price' => 399, 'image' => '👟', 'rate' => 4.5],
                        ['id' => 2, 'name' => 'تي شيرت قطني', 'price' => 89, 'old_price' => 129, 'image' => '👕', 'rate' => 4.2],
                        ['id' => 3, 'name' => 'ساعة ابل', 'price' => 1299, 'old_price' => 1599, 'image' => '⌚', 'rate' => 4.8],
                        ['id' => 4, 'name' => 'سماعات لاسلكية', 'price' => 199, 'old_price' => 299, 'image' => '🎧', 'rate' => 4.3],
                    ];
                @endphp
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg">
                        <div class="h-32 bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center text-5xl">
                            {{ $product['image'] }}
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm">{{ $product['name'] }}</h3>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="text-yellow-500 text-xs">★</span>
                                <span class="text-xs text-gray-600">{{ $product['rate'] }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div>
                                    <span class="text-purple-700 font-bold">{{ $product['price'] }} ر.س</span>
                                    @if($product['old_price'])
                                        <span class="text-gray-400 text-xs line-through mr-1">{{ $product['old_price'] }} ر.س</span>
                                    @endif
                                </div>
                                <button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition">شراء</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- قسم العروض (Offers) --}}
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-bold text-gray-800">عروض حصرية</h2>
                <a href="{{ route('customer.offer') }}" class="text-xs text-purple-600 font-semibold">عرض الكل</a>
            </div>
            <div class="space-y-4">
                @php
                    $offers = [
                        ['id' => 1, 'title' => 'تخفيضات الصيف الكبرى', 'description' => 'خصم يصل إلى 50% على جميع الملابس الصيفية والإكسسوارات الأصلية. سارع بالشراء!', 'discount' => '-50%', 'expiry' => '31 يوليو 2025'],
                        ['id' => 2, 'title' => 'عرض خاص على الإلكترونيات', 'description' => 'احصل على خصم 30% على أحدث الأجهزة الإلكترونية لفترة محدودة.', 'discount' => '-30%', 'expiry' => '15 أغسطس 2025'],
                    ];
                @endphp
                @foreach($offers as $offer)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="flex flex-row">
                            <div class="relative w-28 h-28 bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white text-4xl overflow-hidden">
                                <div class="absolute inset-0 bg-white/10 group-hover:bg-white/20 transition"></div>
                                <span class="transform transition-transform group-hover:scale-110">🛍️</span>
                                <div class="absolute top-1 left-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-md">{{ $offer['discount'] }}</div>
                            </div>
                            <div class="flex-1 p-3 text-right">
                                <div class="flex justify-between items-start gap-1">
                                    <h3 class="font-bold text-gray-800 text-base">{{ $offer['title'] }}</h3>
                                </div>
                                <p class="text-gray-500 text-xs mt-1 leading-relaxed line-clamp-2">
                                    {{ $offer['description'] }}
                                </p>
                                <div class="flex justify-between items-center mt-3">
                                    <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                        <span class="text-amber-500">⏰</span>
                                        <span>ينتهي: {{ $offer['expiry'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <br><br><br>
        </div>

    {{-- إضافة Fontawesome (إذا لم تكن موجودة) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // الكود الموجود مسبقاً للـ bottom navigation
        document.querySelectorAll('.nav-item-premium').forEach(item => {
            item.addEventListener('click', function() {
                const nav = this.getAttribute('data-nav');
                if (nav === 'home') {
                    // بالفعل في الصفحة الرئيسية
                } else if (nav === 'more') {
                    window.location.href = "{{ route('customer.more') }}";
                } else {
                    alert('سيتم توجيهك قريباً');
                }
            });
        });

        // التنقل الثاني (للتكامل)
        document.querySelectorAll('.nav-item-premium').forEach(item => {
            item.addEventListener('click', function() {
                const target = this.getAttribute('data-nav');
                const routes = {
                    home: "{{ route('customer.dashboard') }}",
                    offers: "{{ route('customer.offers') }}",
                    more: "{{ route('customer.more') }}"
                };
                if (routes[target]) {
                    window.location.href = routes[target];
                } else {
                    alert('سيتم إضافة هذه الصفحة قريباً');
                }
            });
        });
    </script>
@endsection