@extends('app_android.partials.mobile.app_mobile')

@section('title', 'طلباتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'طلباتي'])

    <div class="p-4" dir="rtl">
        {{-- ملخص سريع --}}
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-gray-800">{{ $stats['all'] }}</div>
                <div class="text-[11px] text-gray-400">كل الطلبات</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-purple-600">{{ $stats['pending'] }}</div>
                <div class="text-[11px] text-gray-400">قيد التنفيذ</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-emerald-600">{{ $stats['delivered'] }}</div>
                <div class="text-[11px] text-gray-400">تم التوصيل</div>
            </div>
        </div>

        @forelse ($rows as $row)
            <a href="{{ route('customer.order_details', $row['id'], false) }}"
               class="block bg-white rounded-2xl shadow-sm p-4 mb-3 transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-800">{{ $row['number'] }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $row['date'] }} — {{ $row['items_count'] }} منتج
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $row['status_class'] }}">{{ $row['status_text'] }}</span>
                </div>

                {{-- شريط التقدم --}}
                <div class="mt-3">
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-600 rounded-full" style="width: {{ $row['progress'] }}%"></div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="text-sm font-bold text-gray-800">{{ number_format($row['total'], 2) }} ر.س</div>
                    <div class="flex items-center gap-2">
                        @if ($row['can_respond'])
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-full">بانتظار ردّك</span>
                        @endif
                        @if ($row['has_invoice'])
                            <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-1 rounded-full">فاتورة</span>
                        @endif
                        <i class="fas fa-chevron-left text-gray-300 text-xs"></i>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-16">
                <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-gray-600 font-bold">لا توجد طلبات بعد</h3>
                <p class="text-gray-400 text-sm mt-1">ابدأ بتصفح المنتجات وأضف طلبك الأول.</p>
                <a href="{{ route('customer.products', [], false) }}"
                   class="inline-block mt-4 text-xs font-bold text-white bg-purple-600 px-4 py-2 rounded-full">
                    تصفّح المنتجات
                </a>
            </div>
        @endforelse
    </div>
@endsection
