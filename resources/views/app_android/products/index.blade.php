@extends('app_android.partials.mobile.app_mobile')

@section('title', 'أفضل المنتجات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'أفضل المنتجات'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @php
                $allProducts = [
                    ['id' => 1, 'name' => 'حذاء رياضي نيكي', 'price' => 299, 'old_price' => 399, 'image' => '👟', 'rate' => 4.5],
                    ['id' => 2, 'name' => 'تي شيرت قطني', 'price' => 89, 'old_price' => 129, 'image' => '👕', 'rate' => 4.2],
                    ['id' => 3, 'name' => 'ساعة ابل', 'price' => 1299, 'old_price' => 1599, 'image' => '⌚', 'rate' => 4.8],
                    ['id' => 4, 'name' => 'سماعات لاسلكية', 'price' => 199, 'old_price' => 299, 'image' => '🎧', 'rate' => 4.3],
                    ['id' => 5, 'name' => 'عطر فرنسي', 'price' => 249, 'old_price' => 349, 'image' => '🌸', 'rate' => 4.7],
                    ['id' => 6, 'name' => 'حذاء رياضي اديداس', 'price' => 359, 'old_price' => 459, 'image' => '👟', 'rate' => 4.6],
                ];
            @endphp
            @foreach($allProducts as $product)
                <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
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
                            <span class="text-purple-700 font-bold">{{ $product['price'] }} ر.س</span>
                            @if($product['old_price'])
                                <span class="text-gray-400 text-xs line-through mr-1">{{ $product['old_price'] }} ر.س</span>
                            @endif
                        </div>
                        <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition" data-name="{{ $product['name'] }}">أضف إلى السلة</button>
                    </div>
                </div>
            @endforeach
        </div>
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
                showToast(`تم إضافة ${name} إلى السلة`);
            });
        });
    </script>
@endsection