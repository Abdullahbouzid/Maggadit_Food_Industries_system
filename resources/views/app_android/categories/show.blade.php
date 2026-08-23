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
            <div class="mt-2 inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-full">
                <i class="fas fa-box-open text-sm"></i>
                <span class="text-sm font-semibold">{{ is_array($category->items) ? count($category->items) : ($category->items->count() ?? 0) }} منتج</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @forelse($items as $item)
                <a href="{{ route('customer.product.show', $item->id) }}" class="block"><div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg relative">
                    <button class="absolute top-2 left-2 z-10 p-1.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm toggle-favorite-btn transition-colors {{ in_array($item->id, $favoriteItemIds ?? []) ? 'text-red-500' : 'text-gray-400' }}" data-id="{{ $item->id }}">
                        <i class="{{ in_array($item->id, $favoriteItemIds ?? []) ? 'fas' : 'far' }} fa-heart text-lg"></i>
                    </button>
                    <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($item->image)
                            <img src="{{ $item->image }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl text-gray-400">📦</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-bold text-gray-800 text-sm">{{ $item->name ?? ($item->name_ar ?? $item->name_en) }}</h3>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="text-yellow-500 text-xs">★</span>
                            <span class="text-xs text-gray-600">{{ number_format($item->rate ?? 0, 1) }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-purple-700 font-bold">{{ number_format($item->price ?? 0) }} ر.س</span>
                            @if(isset($item->old_price) && $item->old_price)
                                <span class="text-gray-400 text-xs line-through mr-1">{{ number_format($item->old_price) }} ر.س</span>
                            @endif
                        </div>
                        <button class="add-to-cart mt-2 w-full bg-purple-600 text-white text-xs py-2 rounded-full hover:bg-purple-700 transition"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name ?? ($item->name_ar ?? $item->name_en)  }}"
                                data-price="{{ $item->price ?? 0  }}"
                                data-image="{{ $item->image ?? '📦' }}">
                            <i class="fas fa-shopping-cart ml-1"></i> أضف إلى السلة
                        </button>
                    </div>
                </div></a>
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
                // يمكن إضافة AJAX هنا
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
                    credentials: 'same-origin',
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
