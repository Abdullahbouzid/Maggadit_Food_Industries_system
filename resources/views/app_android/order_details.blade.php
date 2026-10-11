@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تفاصيل الطلب')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تفاصيل الطلب'])

    <div class="px-4 py-4 mb-5 space-y-4" dir="rtl">
        @if(!$order)
            <div class="bg-white rounded-2xl shadow-md p-10 text-center border border-gray-100">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-circle text-4xl text-red-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">الطلب غير موجود</h3>
                <p class="text-gray-400 text-sm mb-6">لا يمكن العثور على هذا الطلب أو ليس لديك الصلاحية لعرضه</p>
                <a href="{{ route('customer.order_history', [], false) }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                    <i class="fas fa-arrow-left ml-2"></i> العودة إلى سجل الطلبيات
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 mb-4">
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
            </div>

            @if($order->delivery)
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 mb-4">
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-truck text-purple-600"></i> تفاصيل التوصيل
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <span>العنوان:</span>
                            <span class="font-medium">{{ $order->delivery->address ?? 'غير محدد' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <span>رقم الهاتف:</span>
                            <span class="font-medium">{{ $order->delivery->phone ?? 'غير محدد' }}</span>
                        </div>
                        @if($order->delivery->scheduled_date)
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>موعد التوصيل المفضل:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($order->delivery->scheduled_date)->format('Y-m-d') ?? '' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($order->items && $order->items->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 mb-4">
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-box text-purple-600"></i> عناصر الطلب
                    </h4>
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-xl">
                                <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg flex items-center justify-center">
                                    @if($item->item && $item->item->image)
                                        <img src="{{ $item->item->image }}" alt="{{ $item->item->name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://via.placeholder.com/64?text=📦';">
                                    @else
                                        <span class="text-2xl text-gray-400">📦</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <h5 class="font-medium text-gray-800">{{ $item->item->name ?? $item->item->name_en ?? 'منتج' }}</h5>
                                        @if($item->pack)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-medium">
                                                {{ $item->pack->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex justify-between items-start text-sm text-gray-600">
                                        <span>الكمية:</span>
                                        <span class="font-medium">{{ $item->quantity }} {{ $item->uom_name ?? 'قطعة' }}</span>
                                    </div>
                                    <div class="flex justify-between items-start text-sm text-gray-600">
                                        <span>السعر:</span>
                                        <span class="font-medium">{{ number_format($item->price ?? 0, 2) }} ر.س</span>
                                    </div>
                                    <div class="flex justify-between items-start text-sm text-gray-600">
                                        <span>الإجمالي:</span>
                                        <span class="font-medium text-purple-600">{{ number_format(($item->quantity * ($item->price ?? 0)), 2) }} ر.س</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($order->notes)
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 mb-4">
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-purple-600"></i> ملاحظات
                    </h4>
                    <p class="text-gray-600">{{ $order->notes }}</p>
                </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('customer.order_history', [], false) }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-full shadow-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i> العودة إلى سجل الطلبيات
                </a>
            </div>
        @endif
    </div>
@endsection