@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تفاصيل الطلب')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تفاصيل الطلب'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        @php
            // بيانات تجريبية للطلب حسب الـ id (في الحقيقة ستجلب من قاعدة البيانات)
            $ordersData = [
                '#1001' => [
                    'id' => '#1001',
                    'date' => '2025-05-20',
                    'total' => 299,
                    'status' => 'delivered',
                    'status_text' => 'تم التوصيل',
                    'payment_method' => 'بطاقة ائتمان',
                    'payment_status' => 'مدفوع',
                    'address' => 'الرياض - حي النخيل، شارع الأمير محمد بن سلمان، مبنى 12، شقة 5',
                    'phone' => '05xxxxxxxx',
                    'customer_name' => 'أحمد محمد',
                    'items' => [
                        ['name' => 'حذاء رياضي نيكي', 'qty' => 1, 'price' => 299, 'total' => 299],
                    ],
                    'shipping_cost' => 15,
                    'discount' => 0,
                ],
                '#1002' => [
                    'id' => '#1002',
                    'date' => '2025-05-15',
                    'total' => 1299,
                    'status' => 'pending',
                    'status_text' => 'قيد المعالجة',
                    'payment_method' => 'تحويل بنكي',
                    'payment_status' => 'قيد المراجعة',
                    'address' => 'جدة - شارع الأندلس، حي الروضة، عمارة 8، شقة 3',
                    'phone' => '05yyyyyyyy',
                    'customer_name' => 'سارة خالد',
                    'items' => [
                        ['name' => 'ساعة ابل', 'qty' => 1, 'price' => 1299, 'total' => 1299],
                    ],
                    'shipping_cost' => 20,
                    'discount' => 0,
                ],
                '#1003' => [
                    'id' => '#1003',
                    'date' => '2025-05-10',
                    'total' => 89,
                    'status' => 'cancelled',
                    'status_text' => 'تم الإلغاء',
                    'payment_method' => 'بطاقة مدى',
                    'payment_status' => 'ملغي',
                    'address' => 'الدمام - حي الفيصلية، شارع الملك عبدالعزيز، مبنى 45',
                    'phone' => '05zzzzzzzz',
                    'customer_name' => 'محمد علي',
                    'items' => [
                        ['name' => 'تي شيرت قطني', 'qty' => 1, 'price' => 89, 'total' => 89],
                    ],
                    'shipping_cost' => 10,
                    'discount' => 0,
                ],
                '#1004' => [
                    'id' => '#1004',
                    'date' => '2025-05-05',
                    'total' => 459,
                    'status' => 'delivered',
                    'status_text' => 'تم التوصيل',
                    'payment_method' => 'محفظة رقمية',
                    'payment_status' => 'مدفوع',
                    'address' => 'مكة المكرمة - العزيزية، شارع المنصور، بناية 22',
                    'phone' => '05wwwwwwww',
                    'customer_name' => 'نورة عبدالله',
                    'items' => [
                        ['name' => 'حذاء رياضي اديداس', 'qty' => 1, 'price' => 359, 'total' => 359],
                        ['name' => 'جورب رياضي', 'qty' => 2, 'price' => 50, 'total' => 100],
                    ],
                    'shipping_cost' => 20,
                    'discount' => 20,
                ],
            ];

            $order = $ordersData[$orderId] ?? null;
        @endphp

        @if($order)
            <!-- حالة الطلب وشريط التقدم -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-800">حالة الطلب</h3>
                    <span class="text-xs px-3 py-1 rounded-full 
                        {{ $order['status'] == 'delivered' ? 'bg-green-100 text-green-700' : 
                           ($order['status'] == 'pending' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">
                        {{ $order['status_text'] }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>تم الطلب</span>
                    <span>تم التجهيز</span>
                    <span>تم الشحن</span>
                    <span>تم التوصيل</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    @php
                        $progress = $order['status'] == 'delivered' ? 100 : ($order['status'] == 'pending' ? 25 : 0);
                    @endphp
                    <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <!-- معلومات الطلب الأساسية -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-2">
                    <span class="text-gray-500 text-sm">رقم الطلب</span>
                    <span class="font-bold text-gray-800">{{ $order['id'] }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-2">
                    <span class="text-gray-500 text-sm">تاريخ الطلب</span>
                    <span class="text-gray-800">{{ \Carbon\Carbon::parse($order['date'])->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-2">
                    <span class="text-gray-500 text-sm">طريقة الدفع</span>
                    <span class="text-gray-800">{{ $order['payment_method'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 text-sm">حالة الدفع</span>
                    <span class="text-gray-800">{{ $order['payment_status'] }}</span>
                </div>
            </div>

            <!-- المنتجات -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-4">
                <h3 class="font-bold text-gray-800 mb-3">المنتجات</h3>
                <div class="space-y-3">
                    @foreach($order['items'] as $item)
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-400">الكمية: {{ $item['qty'] }}</p>
                            </div>
                            <span class="font-bold text-purple-700">{{ $item['total'] }} ر.س</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- عنوان التوصيل وجهة الاتصال -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-4">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-map-marker-alt text-purple-600"></i> عنوان التوصيل</h3>
                <p class="text-gray-700 text-sm leading-relaxed">{{ $order['address'] }}</p>
                <div class="mt-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-phone text-purple-600"></i>
                    <span class="text-gray-700">{{ $order['phone'] }}</span>
                </div>
            </div>

            <!-- خلاصة الفواتير -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-4">
                <h3 class="font-bold text-gray-800 mb-3">خلاصة الفاتورة</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">المجموع الفرعي</span>
                        <span class="text-gray-700">{{ number_format($order['total'] - $order['shipping_cost'] + $order['discount']) }} ر.س</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">رسوم التوصيل</span>
                        <span class="text-gray-700">{{ $order['shipping_cost'] }} ر.س</span>
                    </div>
                    @if($order['discount'] > 0)
                        <div class="flex justify-between text-green-600">
                            <span>الخصم</span>
                            <span>- {{ $order['discount'] }} ر.س</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                        <span class="font-bold text-gray-800">الإجمالي</span>
                        <span class="font-bold text-purple-700">{{ $order['total'] }} ر.س</span>
                    </div>
                </div>
            </div>

            <!-- أزرار إضافية (إعادة الطلب، مساعدة) -->
            <div class="flex gap-3">
                @if($order['status'] != 'cancelled')
                    <button class="flex-1 bg-purple-600 text-white py-2 rounded-xl text-sm font-semibold">إعادة الطلب</button>
                @endif
                <button class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl text-sm font-semibold">اتصل بالدعم</button>
            </div>
        @else
            <div class="text-center py-10">
                <i class="fas fa-exclamation-triangle text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">الطلب غير موجود</p>
                <button onclick="window.history.back()" class="mt-4 bg-purple-600 text-white px-6 py-2 rounded-xl">رجوع</button>
            </div>
        @endif
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection