@extends('app_android.partials.mobile.app_mobile')

@section('title', $category->name)

@section('content')
    @include('app_android.partials.mobile.header', ['title' => $category->name])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-5 mb-5 text-center">
            <div class="w-20 h-20 mx-auto rounded-full overflow-hidden bg-gray-100 flex items-center justify-center mb-3 shadow-md">
                @if($category->image)
                    <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-4xl">📁</span>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-800">{{ $category->name }}</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-full">
                    <i class="fas fa-box-open text-sm"></i>
                    <span class="text-sm font-semibold">{{ $category->products_count ?? ($items->count() ?? 0) }} منتج</span>
                </div>
                <a href="{{ route('customer.products', ['category_id' => $category->id], false) }}"
                   class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                    <i class="fas fa-grip text-sm"></i>
                    <span class="text-sm font-semibold">عرض وتصفية</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @forelse($items as $item)
                @php
                    $itemData = is_array($item) ? $item : (array) $item;
                    $packs = is_array($itemData['packs'] ?? null) ? $itemData['packs'] : [];
                    $hasMultiplePacks = count($packs) > 1;
                    $itemId = $itemData['id'] ?? ($item->id ?? 0);
                    $itemName = $itemData['name'] ?? ($item->name ?? 'منتج');
                    $itemImage = $itemData['image'] ?? ($item->image ?? '');
                    $itemFavorite = $itemData['favorite'] ?? false;
                @endphp
                @foreach($packs as $pack)
                    <a href="{{ route('customer.product.show', $itemId, false) }}" class="block">
                        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative">
                            @if($hasMultiplePacks)
                                <div class="absolute top-2 right-2 z-10">
                                    <span class="bg-purple-600 text-white text-[10px] px-2 py-1 rounded-full font-semibold">
                                        {{ $pack['name'] ?? '' }}
                                    </span>
                                </div>
                            @endif
                            <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn transition-colors {{ $itemFavorite ? 'text-red-500' : 'text-gray-400' }}" data-id="{{ $itemId }}">
                                <i class="{{ $itemFavorite ? 'fas' : 'far' }} fa-heart text-lg"></i>
                            </button>
                            <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($itemImage)
                                    <img src="{{ $itemImage }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-5xl text-gray-400">📦</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="font-bold text-gray-800 text-sm">{{ $itemName }}</h3>
                                @if($hasMultiplePacks)
                                    <span class="inline-block mt-1 bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5 rounded-full">
                                        {{ $pack['name'] ?? '' }}
                                    </span>
                                @endif
                                <div class="mt-2">
                                    <span class="text-purple-700 font-bold">{{ number_format($pack['price'] ?? 0) }} ر.س</span>
                                </div>
                                <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition"
                                        data-id="{{ $itemId }}"
                                        data-name="{{ $itemName }}"
                                        data-price="{{ $pack['price'] ?? 0 }}"
                                        data-image="{{ $itemImage ?? '📦' }}"
                                        data-pack-id="{{ $pack['id'] ?? '' }}"
                                        data-pack-name="{{ $pack['name'] ?? '' }}"
                                        data-uom-name="{{ $pack['uom_name'] ?? '' }}">
                                    <i class="fas fa-shopping-cart ml-1"></i> أضف إلى السلة
                                </button>
                            </div>
                        </div>
                    </a>
                @endforeach
            @empty
                <p class="text-center text-gray-500 col-span-2">لا توجد منتجات في هذه الفئة حالياً</p>
            @endforelse
        </div>

        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <!-- Toast container (bottom) -->
    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <!-- Centered popup for Add to Cart confirmation -->
    <div id="add-success-popup" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xs w-full mx-4 text-center transform transition-all duration-300 scale-90">
            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-4xl shadow-lg">
                <i class="fas fa-check text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">تمت الإضافة للسلة!</h3>
            <p id="add-success-msg" class="text-gray-600 text-sm mb-4"></p>
        </div>
    </div>

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

        // Show centered popup for Add to Cart confirmation
        function showAddSuccess(msg) {
            const popup = document.getElementById('add-success-popup');
            const msgEl = document.getElementById('add-success-msg');
            if (!popup || !msgEl) return;
            msgEl.innerText = msg;
            popup.classList.remove('opacity-0');
            popup.classList.add('opacity-100');
            popup.querySelector('.bg-white').classList.remove('scale-90');
            popup.querySelector('.bg-white').classList.add('scale-100');
            setTimeout(() => {
                popup.classList.remove('opacity-100');
                popup.classList.add('opacity-0');
                popup.querySelector('.bg-white').classList.remove('scale-100');
                popup.querySelector('.bg-white').classList.add('scale-90');
            }, 2000);
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = parseFloat(this.getAttribute('data-price')) || 0;
                let image = this.getAttribute('data-image');
                let packName = this.getAttribute('data-pack-name') || '';
                let uomName = this.getAttribute('data-uom-name') || '';
                let packId = this.getAttribute('data-pack-id') || null;
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id && item.pack_name == packName);
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        name: name,
                        pack_name: packName,
                        uom_name: uomName,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0,
                        pack_id: packId
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                updateCartCount();
                let displayText = packName ? `${name} (${packName})` : name;
                showAddSuccess(displayText);
            });
        });

        document.querySelectorAll('.toggle-favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const icon = this.querySelector('i');
                const productId = this.getAttribute('data-id');
                
                fetch("{{ route('customer.proxy.favorites.toggle', [], false) }}", {
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
                    if(data.success) {
                        if(data.is_favorite) {
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
                .catch(err => console.error(err));
            });
        });
    </script>
@endsection
