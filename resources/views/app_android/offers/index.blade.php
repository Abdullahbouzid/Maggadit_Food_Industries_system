@extends('app_android.partials.mobile.app_mobile')

@section('title', 'العروض الحصرية')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'عروض حصرية'])

    <style>
        .offer-card {
            position: relative;
            overflow: hidden;
        }
        .offer-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 8px 14px;
            border-radius: 0 0 0 20px;
            font-size: 13px;
            font-weight: bold;
            box-shadow: -2px 2px 8px rgba(0,0,0,0.2);
            z-index: 10;
            transform: rotate(0deg);
        }
        .offer-badge::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 25px 25px 0;
            border-color: transparent rgba(0,0,0,0.1) transparent transparent;
        }
    </style>

    <div id="offers-container" class="p-4 space-y-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        @forelse($offers as $offer)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card">
                <div class="offer-badge">
                    -{{ $offer->discount_percentage ?? 0 }}%
                </div>
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-3 text-white">
                    <br>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">⏰ حتى {{ \Carbon\Carbon::parse($offer->end_date)->format('d M Y') }}</span>
                    </div>
                    <h3 class="font-bold text-lg mt-1"><a href="{{ $offer->item_id ? route('customer.product.show', $offer->item_id, false) : '#' }}" class="hover:underline">{{ $offer->title ?: ($offer->name ?? 'عرض') }}</a></h3>
                    @if($offer->description)
                        <p class="text-xs opacity-90">{{ $offer->description }}</p>
                    @endif
                </div>
                <div class="p-3 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-2xl overflow-hidden flex-shrink-0">
                            @if($offer->image)
                                <img src="{{ $offer->image }}" alt="{{ $offer->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl">🛍️</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm"><a href="{{ $offer->item_id ? route('customer.product.show', $offer->item_id, false) : '#' }}" class="hover:underline">{{ $offer->name }}</a></p>
                            @if($offer->pack_type)
                                <small class="text-muted">{{ $offer->pack_type }}</small>
                            @endif
                            <div>
                                <span class="text-purple-700 font-bold">{{ number_format($offer->new_price, 2) }} ر.س</span>
                                <span class="text-gray-400 text-xs line-through mr-1">{{ number_format($offer->old_price, 2) }} ر.س</span>
                            </div>
                        </div>
                        <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-2 rounded-full flex-shrink-0" 
                                data-id="{{ $offer->item_id }}" 
                                data-name="{{ $offer->name }}" 
                                data-price="{{ $offer->new_price }}"
                                data-image="{{ $offer->image ?? '' }}"
                                data-pack-id="{{ $offer->itemPack->id ?? '' }}"
                                data-pack-name="{{ $offer->pack_type ?? '' }}"
                                data-uom-name="{{ $offer->pack_type ?? '' }}">
                            إضافة إلى السلة
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <i class="fas fa-tag text-gray-300 text-5xl mb-3"></i>
                <p class="text-gray-500">لا توجد عروض متاحة حالياً</p>
            </div>
        @endforelse
    </div>

    <div id="cart-toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>

    <!-- Centered popup for Add to Cart confirmation -->
    <div id="add-success-popup" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xs w-full mx-4 text-center transform transition-all duration-300 scale-90">
            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-4xl shadow-lg">
                <i class="fas fa-check text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">تمت الإضافة للسلة!</h3>
            <p id="add-success-msg" class="text-gray-600 text-sm mb-4"></p>
        </div>
    </div></div>
    
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

        // Dynamic Proxy Fallback Fetcher for Offers
        const offersContainer = document.getElementById('offers-container');
        const customerProductBase = @json(rtrim(url('/'), '/'));
        const customerProductUrl = (id) => `${customerProductBase}/customer/product/${encodeURIComponent(id)}`;
        if (offersContainer && !offersContainer.querySelector('.offer-card')) {
            fetch('{{ route('customer.proxy.offers', [], false) }}')
                .then(res => res.json())
                .then(data => {
                    if (data && data.offers) {
                        const items = data.offers.data || data.offers;
                        if (Array.isArray(items) && items.length > 0) {
                            offersContainer.innerHTML = items.map(offer => `
                                <div class="bg-white rounded-2xl shadow-lg overflow-hidden offer-card">
                                    <div class="offer-badge">-${offer.discount_percentage || 0}%</div>
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-3 text-white">
                                        <br>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm">⏰ عرض خاص</span>
                                        </div>
                                        <h3 class="font-bold text-lg mt-1">${offer.item_id ? `<a href="${customerProductUrl(offer.item_id)}" class="hover:underline">${offer.title || offer.name || 'عرض'}</a>` : (offer.title || offer.name || 'عرض')}</h3>
                                        ${offer.description ? `<p class="text-xs opacity-90">${offer.description}</p>` : ''}
                                    </div>
                                    <div class="p-3 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center text-2xl overflow-hidden flex-shrink-0">
                                                ${offer.image ? `<img src="${offer.image}" alt="${offer.name || ''}" class="w-full h-full object-cover" onerror="this.outerHTML='<span class=\\'text-4xl\\'>🛍️</span>'">` : '<span class="text-4xl">🛍️</span>'}
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-sm">${offer.name || offer.title || 'عرض'}</p>
                                                <div>
                                                    <span class="text-purple-700 font-bold">${parseFloat(offer.new_price || 0).toFixed(2)} ر.س</span>
                                                    ${offer.old_price ? `<span class="text-gray-400 text-xs line-through mr-1">${parseFloat(offer.old_price).toFixed(2)} ر.س</span>` : ''}
                                                </div>
                                            </div>
                                            <button class="add-to-cart bg-purple-600 text-white text-xs px-3 py-2 rounded-full flex-shrink-0" 
                                                    data-id="${offer.item_id || offer.id}" 
                                                    data-name="${offer.name || offer.title || 'عرض'}" 
                                                    data-price="${offer.new_price || 0}"
                                                    data-image="${offer.image || ''}"
                                                    data-pack-id="${offer.item_pack_id || offer.itemPack?.id || ''}"
                                                    data-pack-name="${offer.pack_type || ''}"
                                                    data-uom-name="${offer.pack_type || ''}">
                                                إضافة إلى السلة
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('');

                            // Re-bind events
                            document.querySelectorAll('.add-to-cart').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
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
                                        cartItems.push({ id, name, pack_name: packName, uom_name: uomName, price, quantity: 1, image, discount: 0, pack_id: packId });
                                    }
                                    localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                                    updateCartCount();
                                    let displayText = packName ? `${name} (${packName})` : name;
                                    showAddSuccess(displayText);
                                });
                            });
                        }
                    }
                })
                .catch(err => console.error("Offers proxy error:", err));
        }
    </script>
@endsection
