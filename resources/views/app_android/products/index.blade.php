@extends('app_android.partials.mobile.app_mobile')

@section('{{ __('messages.app.page_') }}', 'جميع المنتجات')

@section('content')
    @include('app_android.partials.mobile.header', ['{{ __('messages.app.page_') }}' => 'جميع المنتجات'])

    <div class="p-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white rounded-3xl shadow-lg p-4 mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-800 text-base">تصفية المنتجات</h3>
                <button id="reset-filters" class="text-xs text-purple-600 font-semibold">مسح الفلاتر</button>
            </div>
            
            {{-- Search Input --}}
            <div class="mb-3">
                <div class="relative">
                    <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="search-input" placeholder="ابحث عن منتج..." 
                           value="{{ request('search') }}"
                           class="w-full end-10 start-4 py-2.5 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition text-sm bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                {{-- Category Filter --}}
                <div>
                    <label class="form-label text-xs font-semibold text-gray-600 mb-1 block">الفئة</label>
                    <select id="category-filter" class="form-select form-select-sm w-full border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200">
                        <option value="">{{ __('messages.filter.all') }}</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Brand Filter --}}
                <div>
                    <label class="form-label text-xs font-semibold text-gray-600 mb-1 block">الماركة</label>
                    <select id="brand-filter" class="form-select form-select-sm w-full border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200">
                        <option value="">{{ __('messages.filter.all') }}</option>
                        @foreach($brands ?? [] as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button id="apply-filters" class="w-full mt-3 bg-purple-600 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-purple-700 transition">
                <i class="fas fa-filter end-1"></i> تطبيق الفلاتر
            </button>
        </div>

        {{-- Products Count --}}
        <div class="mb-3">
            <span class="text-sm text-gray-500">{{ $items->count() }} منتج</span>
        </div>

        {{-- Products Grid --}}
        <div id="products-grid" class="grid grid-cols-2 gap-4">
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
                                    data-pack-name="{{ $pack['name'] ?? '' }}"
                                    data-uom-name="{{ $pack['uom_name'] ?? '' }}">
                                <i class="fas fa-shopping-cart end-1"></i> أضف إلى السلة
                            </button>
                        </div>
                    </div>
                @endforeach
            @empty
                <p class="text-center text-gray-500 col-span-2">لا توجد منتجات متاحة حالياً</p>
            @endforelse
        </div>

        @if(method_exists($items, 'links'))
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

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
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = parseFloat(this.getAttribute('data-price')) || 0;
                let image = this.getAttribute('data-image');
                let packName = this.getAttribute('data-pack-name') || '';
                let uomName = this.getAttribute('data-uom-name') || '';
                
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
                e.stopPropagation();
                const icon = this.querySelector('i');
                const productId = this.getAttribute('data-id');
                
                fetch('{{ route("customer.favorite.toggle") }}', {
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

        // Filter functionality
        document.getElementById('apply-filters')?.addEventListener('click', function() {
            const search = document.getElementById('search-input').value.trim();
            const categoryId = document.getElementById('category-filter').value;
            const brandId = document.getElementById('brand-filter').value;

            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (categoryId) params.set('category_id', categoryId);
            if (brandId) params.set('brand_id', brandId);

            window.location.href = '{{ route('customer.products') }}?' + params.toString();
        });

        document.getElementById('reset-filters')?.addEventListener('click', function() {
            window.location.href = '{{ route('customer.products') }}';
        });

        // Allow Enter key on search input
        document.getElementById('search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('apply-filters')?.click();
            }
        });

        // Dynamic Proxy Fallback Fetcher for Products
        const productsGrid = document.getElementById('products-grid');
        if (productsGrid && productsGrid.children.length <= 1 && productsGrid.querySelector('p')) {
            fetch('/customer/proxy/products')
                .then(res => res.json())
                .then(data => {
                    if (data && data.products) {
                        const items = data.products.data || data.products;
                        if (Array.isArray(items) && items.length > 0) {
                            productsGrid.innerHTML = items.map(item => `
                                <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative">
                                    <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn text-gray-400" data-id="${item.id}">
                                        <i class="far fa-heart text-lg"></i>
                                    </button>
                                    <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        ${item.image ? `<img src="${item.image}" class="w-full h-full object-cover" onerror="this.outerHTML='<span class=\'text-5xl text-gray-400\'>📦</span>'">` : '<span class="text-5xl text-gray-400">📦</span>'}
                                    </div>
                                    <div class="p-3">
                                        <h3 class="font-bold text-gray-800 text-sm">{{ __('messages.app.page__1') }}</h3>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="text-yellow-500 text-xs">★</span>
                                            <span class="text-xs text-gray-600">{{ __('messages.app.page__2') }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-purple-700 font-bold">{{ __('messages.app.page__3') }}</span>
                                            ${item.old_price ? `<span class="text-gray-400 text-xs line-through start-1">{{ __('messages.app.page__4') }}</span>` : ''}
                                        </div>
                                        <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition"
                                                data-id="${item.id}"
                                                data-name="{{ __('messages.app.page__1') }}"
                                                data-price="${item.price || 0}"
                                                data-image="${item.image || '📦'}">
                                            <i class="fas fa-shopping-cart end-1"></i> أضف إلى السلة
                                        </button>
                                    </div>
                                </div>
                            `).join('');

                            // Re-bind click events
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
                                        cartItems.push({ id, name, price, quantity: 1, image, discount: 0 });
                                    }
                                    localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                                    showToast(`✅ تم إضافة "${name}" إلى السلة`);
                                });
                            });
                        }
                    }
                })
                .catch(err => console.error("Products proxy error:", err));
        }
    </script>
@endsection
