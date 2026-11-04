@extends('app_android.partials.mobile.app_mobile')

@section('title', $product->name ?? 'منتج')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => $product->name ?? 'تفاصيل المنتج'])
    <div class="p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 p-4">
            <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden mb-4 relative">
                @if($product->image)
                    <img src="{{ $product->image }}" class="w-full h-full object-cover" alt="{{ $product->name }}" id="main-product-image">
                @else
                    <span class="text-5xl text-gray-400">📦</span>
                @endif
                @if(isset($product->images) && count($product->images) > 1)
                    <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex gap-1" id="image-thumbnails">
                        @foreach($product->images as $index => $img)
                            <button type="button" 
                                    onclick="document.getElementById('main-product-image').src='{{ $img }}'"
                                    class="w-10 h-10 rounded overflow-hidden border-2 {{ $index === 0 ? 'border-purple-500' : 'border-transparent' }} hover:border-purple-300 transition"
                                    aria-label="صورة {{ $index + 1 }}">
                                <img src="{{ $img }}" class="w-full h-full object-cover" alt="صورة {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <h3 class="font-bold text-gray-800 text-xl mb-2">{{ $product->name }}</h3>
            <p class="text-purple-700 font-bold text-lg mb-2">{{ number_format($product->price) }} ر.س</p>
            @if(isset($product->old_price) && $product->old_price)
                <p class="text-gray-400 line-through mb-2">{{ number_format($product->old_price) }} ر.س</p>
            @endif
            <p class="text-gray-600 mb-4">{{ $product->description ?? '' }}</p>

            @if(isset($product->packs) && count($product->packs) > 0)
            <div class="mb-5">
                <h4 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-1">
                    <i class="fas fa-boxes text-purple-600"></i>
                    <span>العبوات ووحدات القياس المتاحة:</span>
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse border border-gray-200 rounded-xl overflow-hidden">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="border border-purple-500 px-3 py-2 text-right">العبوة</th>
                                <th class="border border-purple-500 px-3 py-2 text-right">وحدة القياس</th>
                                <th class="border border-purple-500 px-3 py-2 text-center">السعة</th>
                                <th class="border border-purple-500 px-3 py-2 text-center">السعر</th>
                                <th class="border border-purple-500 px-3 py-2 text-center">إضافة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->packs as $pack)
                                <tr class="hover:bg-purple-50/50 transition">
                                    <td class="border border-gray-200 px-3 py-2 font-medium text-gray-800">{{ $pack['name'] }}</td>
                                    <td class="border border-gray-200 px-3 py-2">
                                        @if(isset($pack['uom_name']) && $pack['uom_name'])
                                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $pack['uom_name'] }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-200 px-3 py-2 text-center">{{ (float)$pack['quantity'] }} قطعة</td>
                                    <td class="border border-gray-200 px-3 py-2 text-center font-bold text-purple-700">{{ number_format($pack['price'], 2) }} ر.س</td>
                                    <td class="border border-gray-200 px-3 py-2 text-center">
                                        <button class="add-to-cart-pack bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1 shadow-sm active:scale-95"
                                                data-id="{{ $product->id }}"
                                                data-pack-id="{{ $pack['id'] }}"
                                                data-name="{{ $product->name }}"
                                                data-pack-name="{{ $pack['name'] }}"
                                                data-uom-name="{{ $pack['uom_name'] ?? '' }}"
                                                data-price="{{ $pack['price'] }}"
                                                data-image="{{ $product->image ?? '📦' }}">
                                            <i class="fas fa-cart-plus"></i> أضف
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="mb-5 bg-purple-50/50 border border-purple-100 rounded-2xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-gray-800 text-sm">الوحدة القياسية</span>
                    <span class="text-purple-700 font-extrabold text-base block mt-0.5">{{ number_format($product->price, 2) }} ر.س</span>
                </div>
                <button class="add-to-cart-pack bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition flex items-center gap-1 shadow-sm active:scale-95"
                        data-id="{{ $product->id }}"
                        data-pack-id=""
                        data-name="{{ $product->name }}"
                        data-pack-name="الوحدة القياسية"
                        data-uom-name=""
                        data-price="{{ $product->price }}"
                        data-image="{{ $product->image ?? '📦' }}">
                    <i class="fas fa-cart-plus"></i> أضف للسلة
                </button>
            </div>
            @endif

            <button class="toggle-favorite-btn mt-2 w-full flex items-center justify-center py-2.5 rounded-xl border border-gray-300 transition text-sm font-semibold {{ in_array($product->id, $favoriteItemIds ?? []) ? 'text-red-500 border-red-200 bg-red-50' : 'text-gray-600 hover:bg-gray-50' }}"
                    data-id="{{ $product->id }}">
                <i class="{{ in_array($product->id, $favoriteItemIds ?? []) ? 'fas' : 'far' }} fa-heart"></i>
                <span class="mr-2">{{ in_array($product->id, $favoriteItemIds ?? []) ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}</span>
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

        document.querySelectorAll('.add-to-cart-pack').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let id = this.getAttribute('data-id');
                let packId = this.getAttribute('data-pack-id') || null;
                let name = this.getAttribute('data-name');
                let packName = this.getAttribute('data-pack-name') || '';
                let uomName = this.getAttribute('data-uom-name') || '';
                let price = parseFloat(this.getAttribute('data-price')) || 0;
                let image = this.getAttribute('data-image');
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id && (item.pack_id == packId || (!item.pack_id && !packId)));
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        pack_id: packId,
                        name: name,
                        pack_name: packName,
                        uom_name: uomName,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                showToast(`✅ تم إضافة "${name} (${packName})" إلى السلة`);
            });
        });

        document.querySelectorAll('.toggle-favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const icon = this.querySelector('i');
                const productId = this.getAttribute('data-id');
                fetch('{{ route('customer.proxy.favorites.toggle', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include',
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
