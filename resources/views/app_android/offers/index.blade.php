@extends('app_android.partials.mobile.app_mobile')

@section('title', 'العروض الحصرية')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'عروض حصرية'])

    <div class="p-4 space-y-4" dir="rtl">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        @php
            $offersList = [
                ['id' => 1, 'title' => 'تخفيضات الصيف الكبرى', 'description' => 'خصم يصل إلى 50% على جميع الملابس الصيفية', 'discount' => '50%', 'expiry' => '31 يوليو 2025', 'products' => [
                    ['id' => 201, 'name' => 'قميص صيفي', 'price' => 49, 'old_price' => 99, 'image' => '👕'],
                    ['id' => 202, 'name' => 'شورت', 'price' => 39, 'old_price' => 79, 'image' => '🩳'],
                ]],
                ['id' => 2, 'title' => 'عرض الإلكترونيات', 'description' => 'خصم 30% على الأجهزة اللوحية والهواتف', 'discount' => '30%', 'expiry' => '15 أغسطس 2025', 'products' => [
                    ['id' => 101, 'name' => 'تابلت', 'price' => 799, 'old_price' => 1149, 'image' => '📱'],
                ]],
            ];
        @endphp
        @foreach($offersList as $offer)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-3 text-white">
                    <div class="flex justify-between items-center">
                        <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">خصم {{ $offer['discount'] }}</span>
                        <span class="text-sm">⏰ حتى {{ $offer['expiry'] }}</span>
                    </div>
                    <h3 class="font-bold text-lg mt-1">{{ $offer['title'] }}</h3>
                    <p class="text-xs opacity-90">{{ $offer['description'] }}</p>
                </div>
                <div class="p-3 space-y-3">
                    @foreach($offer['products'] as $product)
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-2xl">{{ $product['image'] }}</div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">{{ $product['name'] }}</p>
                                <div>
                                    <span class="text-purple-700 font-bold">{{ $product['price'] }} ر.س</span>
                                    <span class="text-gray-400 text-xs line-through mr-1">{{ $product['old_price'] }} ر.س</span>
                                </div>
                            </div>
                            <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-2 rounded-full" data-name="{{ $product['name'] }}">شراء</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        function showToast(msg, isError = false) {
            const toast = document.getElementById('cart-toast');
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
            btn.addEventListener('click', function() {
                let name = this.getAttribute('data-name');
                showToast(`✅ تم إضافة "${name}" إلى السلة`);
            });
        });
    </script>
@endsection