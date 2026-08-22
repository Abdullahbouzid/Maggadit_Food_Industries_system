@extends('app_android.partials.mobile.app_mobile')

@section('title', $product->name ?? 'منتج')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => $product->name ?? 'تفاصيل المنتج'])
    <div class="p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 p-4">
            <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden mb-4">
                @if($product->image)
                    <img src="{{ $product->image }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                @else
                    <span class="text-5xl text-gray-400">📦</span>
                @endif
            </div>
            <h3 class="font-bold text-gray-800 text-xl mb-2">{{ $product->name }}</h3>
            <p class="text-purple-700 font-bold text-lg mb-2">{{ number_format($product->price) }} ر.س</p>
            @if(isset($product->old_price) && $product->old_price)
                <p class="text-gray-400 line-through mb-2">{{ number_format($product->old_price) }} ر.س</p>
            @endif
            <p class="text-gray-600 mb-4">{{ $product->description ?? '' }}</p>
            <button class="add-to-cart w-full bg-purple-600 text-white text-sm py-2 rounded-full hover:bg-purple-700 transition"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-image="{{ $product->image ?? '📦' }}">
                <i class="fas fa-shopping-cart ml-1"></i> أضف إلى السلة
            </button>
            <button class="toggle-favorite-btn mt-3 w-full flex items-center justify-center py-2 rounded-full border border-gray-300 transition"
                    data-id="{{ $product->id }}"
                    @if(in_array($product->id, $favoriteItemIds ?? []))
                        class="text-red-500"
                    @else
                        class="text-gray-400"
                    @endif>
                <i class="{{ in_array($product->id, $favoriteItemIds ?? []) ? 'fas' : 'far' }} fa-heart"></i>
                <span class="ml-2">{{ in_array($product->id, $favoriteItemIds ?? []) ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}</span>
            </button>
        </div>
    </div>

    <!-- Toast container -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

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
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = this.getAttribute('data-price');
                let image = this.getAttribute('data-image');
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id);
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                showToast(`✅ تم إضافة "${name}" إلى السلة`);
            });
        });

        document.querySelectorAll('.toggle-favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const icon = this.querySelector('i');
                const productId = this.getAttribute('data-id');
                fetch('{{ route("customer.favorite.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ item_id: productId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_favorite) {
                            this.classList.remove('text-gray-400');
                            this.classList.add('text-red-500');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            showToast('تمت الإضافة للمفضلة');
                        } else {
                            this.classList.remove('text-red-500');
                            this.classList.add('text-gray-400');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            showToast('تمت الإزالة من المفضلة');
                        }
                    }
                })
                .catch(() => showToast('خطأ في الاتصال', true));
            });
        });
    </script>
@endsection
