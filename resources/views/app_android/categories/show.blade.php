@extends('app_android.partials.mobile.app_mobile')

@section('title', 'جميع المنتجات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'جميع المنتجات'])

    <div class="p-4" dir="rtl">
        <!-- زر العودة إلى الخلف -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @php
                // دمج جميع المنتجات من كل الفئات في مصفوفة واحدة
                $allProducts = [
                    ['id' => 101, 'name' => 'هاتف ذكي', 'price' => 1999, 'old_price' => 2499, 'image' => '📱', 'rate' => 4.7],
                    ['id' => 102, 'name' => 'لابتوب', 'price' => 3499, 'old_price' => 3999, 'image' => '💻', 'rate' => 4.5],
                    ['id' => 103, 'name' => 'سماعات لاسلكية', 'price' => 199, 'old_price' => 299, 'image' => '🎧', 'rate' => 4.3],
                    ['id' => 201, 'name' => 'قميص قطني', 'price' => 89, 'old_price' => 129, 'image' => '👕', 'rate' => 4.2],
                    ['id' => 202, 'name' => 'بنطلون جينز', 'price' => 159, 'old_price' => 199, 'image' => '👖', 'rate' => 4.4],
                    ['id' => 203, 'name' => 'سترة شتوية', 'price' => 249, 'old_price' => 349, 'image' => '🧥', 'rate' => 4.6],
                    ['id' => 301, 'name' => 'حذاء رياضي', 'price' => 299, 'old_price' => 399, 'image' => '👟', 'rate' => 4.6],
                    ['id' => 302, 'name' => 'صندل جلدي', 'price' => 129, 'old_price' => 179, 'image' => '👡', 'rate' => 4.1],
                    ['id' => 401, 'name' => 'عطر فرنسي', 'price' => 249, 'old_price' => 349, 'image' => '🌸', 'rate' => 4.8],
                    ['id' => 402, 'name' => 'عطر خشبي', 'price' => 189, 'old_price' => 259, 'image' => '🌿', 'rate' => 4.5],
                    ['id' => 501, 'name' => 'رواية أدبية', 'price' => 45, 'old_price' => 65, 'image' => '📖', 'rate' => 4.3],
                    ['id' => 502, 'name' => 'كتاب تطوير ذات', 'price' => 55, 'old_price' => 85, 'image' => '📘', 'rate' => 4.7],
                    ['id' => 601, 'name' => 'بلاي ستيشن 5', 'price' => 1999, 'old_price' => 2299, 'image' => '🎮', 'rate' => 4.9],
                    ['id' => 602, 'name' => 'لعبة فيفا', 'price' => 199, 'old_price' => 299, 'image' => '⚽', 'rate' => 4.4],
                ];
            @endphp

            @forelse($allProducts as $product)
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
                        <div class="mt-2">
                            <span class="text-purple-700 font-bold">{{ number_format($product['price']) }} ر.س</span>
                            @if($product['old_price'])
                                <span class="text-gray-400 text-xs line-through mr-1">{{ number_format($product['old_price']) }} ر.س</span>
                            @endif
                        </div>
                        <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition" 
                                data-name="{{ $product['name'] }}" 
                                data-price="{{ $product['price'] }}">
                            <i class="fas fa-shopping-cart ml-1"></i> أضف إلى السلة
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 col-span-2">لا توجد منتجات لعرضها حالياً</p>
            @endforelse
        </div>
    </div>

    <!-- Toast الإشعار -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function showToast(msg, isError = false) {
            const toast = document.getElementById('cart-toast');
            if (!toast) return;
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2000);
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let name = this.getAttribute('data-name');
                showToast(`✅ تم إضافة "${name}" إلى السلة`);
            });
        });
    </script>
@endsection