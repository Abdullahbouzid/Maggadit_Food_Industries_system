@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تاريخ_الطلبات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تاريخ_الطلبات'])

    <div class="px-4 py-4 mb-5 space-y-4" dir="rtl">
        @if($orders->count() === 0)
            <div class="bg-white rounded-2xl shadow-md p-10 text-center border border-gray-100">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-box text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">لا توجد طلبات سابقة</h3>
                <p class="text-gray-400 text-sm mb-6">لم تقم بطلب أي منتجات بعد</p>
                <a href="{{ route('customer.dashboard', [], false) }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                    <i class="fas fa-shopping-bag ml-2"></i> ابدأ التسوق
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($orders as $order)
                    <a href="{{ route('customer.order_details', $order->id, false) }}" class="block bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition cursor-pointer">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-gray-800">طلب رقم #{{ $order->order_number ?? $order->id }}</h3>
                                    <p class="text-xs text-gray-400 mt-1">{{ $order->created_at?->format('Y-m-d') ?? '' }} - {{ $order->created_at?->format('H:i') ?? '' }}</p>
                                </div>
                                <span class="text-xs px-3 py-1 rounded-full font-semibold
                                    @if(($order->status ?? '') == 'completed' || ($order->status ?? '') == 'delivered') bg-green-100 text-green-700
                                    @elseif(($order->status ?? '') == 'cancelled') bg-red-100 text-red-700
                                    @elseif(($order->status ?? '') == 'pending') bg-amber-100 text-amber-700
                                    @elseif(($order->status ?? '') == 'processing') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ $order->status_label ?? $order->status ?? 'مجهول' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="fas fa-box text-purple-500"></i>
                                    <span>{{ $order->items?->count() ?? 0 }} منتج</span>
                                </div>
                                <div class="text-left">
                                    <span class="text-purple-700 font-bold text-lg">{{ number_format($order->total_amount ?? 0, 2) }} ر.س</span>
                                </div>
                            </div>

                            @if($order->delivery)
                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                                    <i class="fas fa-truck text-purple-400"></i>
                                    <span>{{ $order->delivery->address ?? 'لا توجد تفاصيل توصيل' }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($orders->hasMorePages())
                <div class="mt-6 text-center">
                    <button id="load-more-orders" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                        تحميل_المزيد
                    </button>
                </div>
            @endif
        @endif
    </div>

    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <script>
        document.getElementById('load-more-orders')?.addEventListener('click', function() {
            const page = {{ $orders->currentPage() + 1 }};
            const apiToken = localStorage.getItem('api_token') || localStorage.getItem('customer_token') || '';
            const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
            if (apiToken) headers['Authorization'] = 'Bearer ' + apiToken;
            fetch('/customer/order-history?page=' + page, {
                headers: headers
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data && data.data.data && data.data.data.length > 0) {
                    const container = document.querySelector('.space-y-3');
                    data.data.data.forEach(order => {
                        const el = document.createElement('a');
                        el.href = '/customer/order/' + order.id;
                        el.className = 'block bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition cursor-pointer';
                        el.innerHTML = `
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-bold text-gray-800">طلب رقم #${order.order_number ?? order.id}</h3>
                                        <p class="text-xs text-gray-400 mt-1">${(order.created_at || '').slice(0, 16).replace(/-/g, '/')}</p>
                                    </div>
                                    <span class="text-xs px-3 py-1 rounded-full font-semibold bg-gray-100 text-gray-700">${order.status_label ?? order.status ?? 'مجهول'}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-box text-purple-500"></i>
                                        <span>${order.items?.length ?? 0} منتج</span>
                                    </div>
                                    <div class="text-left">
                                        <span class="text-purple-700 font-bold text-lg">${parseFloat(order.total_amount ?? 0).toFixed(2)} ر.س</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.appendChild(el);
                    });
                }
            })
            .catch(() => {});
        });
    </script>
@endsection