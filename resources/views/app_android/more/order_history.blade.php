@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تاريخ الطلبات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تاريخ الطلبات'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <!-- إحصائيات سريعة للطلبات -->
        <div class="grid grid-cols-3 gap-2 mb-5">
            <div class="bg-white rounded-xl shadow p-2 text-center">
                <p class="text-xs text-gray-400">الكل</p>
                <p class="text-xl font-bold text-gray-800">{{ $stats['all'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-2 text-center">
                <p class="text-xs text-gray-400">قيد المعالجة</p>
                <p class="text-xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-2 text-center">
                <p class="text-xs text-gray-400">تم التوصيل</p>
                <p class="text-xl font-bold text-green-600">{{ $stats['delivered'] ?? 0 }}</p>
            </div>
        </div>

        <!-- فلتر الحالة -->
        <div class="bg-white rounded-2xl shadow-md p-3 mb-4">
            <div class="flex gap-2 overflow-x-auto pb-1">
                <button data-status="all" class="status-filter active px-4 py-1.5 rounded-full text-sm font-semibold bg-purple-600 text-white">الكل</button>
                <button data-status="pending" class="status-filter px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">قيد المعالجة</button>
                <button data-status="delivered" class="status-filter px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">تم التوصيل</button>
                <button data-status="cancelled" class="status-filter px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">ملغي</button>
            </div>
        </div>

        <!-- قائمة الطلبات -->
        <div class="space-y-3" id="orders-container">

            @foreach($orders as $index => $order)
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 order-card" data-status="{{ $order['status'] }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($order['date'])->format('d M Y') }}</span>
                            <p class="font-bold text-gray-800 mt-1">{{ $order['id'] }}</p>
                        </div>
                        <span class="text-lg font-bold text-purple-700">{{ $order['total'] }} ر.س</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500">{{ count($order['items']) }} منتج</span>
                        <span class="text-xs px-2 py-1 rounded-full 
                            {{ $order['status'] == 'delivered' ? 'bg-green-100 text-green-700' : 
                              ($order['status'] == 'pending' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">
                            {{ $order['status_text'] }}
                        </span>
                    </div>
                    <button class="order-details-btn w-full mt-3 text-purple-600 text-xs font-semibold text-center flex items-center justify-center gap-1"
                            data-id="{{ $order['id'] }}"
                            data-date="{{ $order['date'] }}"
                            data-total="{{ $order['total'] }}"
                            data-status="{{ $order['status_text'] }}"
                            data-items='@json($order['items'])'
                            data-address="{{ $order['address'] }}"
                            data-phone="{{ $order['phone'] }}"
                            data-payment="{{ $order['payment_method'] }}"
                            data-delivery="{{ $order['delivery_fee'] }}"
                            data-coupon="{{ $order['coupon_discount'] }}">
                        عرض التفاصيل <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                </div>
            @endforeach
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- مودال تفاصيل الطلب المحسن -->
    <div id="order-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-5 transform scale-95 transition-all duration-300 shadow-2xl max-h-[90vh] overflow-y-auto" dir="rtl">
            <div class="text-center mb-3">
                <div class="w-14 h-14 mx-auto rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-2xl">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 text-center" id="modal-order-id">طلب #0000</h3>
            <p class="text-center text-xs text-gray-400" id="modal-order-date"></p>
            
            <div class="mt-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">الحالة:</span>
                    <span id="modal-order-status" class="font-semibold"></span>
                </div>
                
                <div class="border-t border-gray-100 pt-2">
                    <p class="font-semibold text-gray-700 mb-2">المنتجات:</p>
                    <div id="modal-order-items" class="space-y-1 text-sm"></div>
                </div>
                
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">طريقة الدفع:</span>
                    <span id="modal-payment" class="text-gray-700"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">عنوان التوصيل:</span>
                    <span id="modal-address" class="text-gray-700 text-xs"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">رقم الجوال:</span>
                    <span id="modal-phone" class="text-gray-700"></span>
                </div>
                
                <div class="border-t border-gray-100 pt-2">
                    <p class="font-semibold text-gray-700 mb-1">تفاصيل الدفع:</p>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">المجموع الفرعي:</span>
                            <span id="modal-subtotal"></span>
                        </div>
                        <div class="flex justify-between" id="delivery-fee-row" style="display: none;">
                            <span class="text-gray-500">رسوم التوصيل:</span>
                            <span id="modal-delivery"></span>
                        </div>
                        <div class="flex justify-between" id="coupon-row" style="display: none;">
                            <span class="text-gray-500">خصم القسيمة:</span>
                            <span id="modal-coupon" class="text-green-600"></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                            <span class="font-bold text-gray-800">الإجمالي المدفوع:</span>
                            <span id="modal-total" class="font-bold text-purple-700 text-lg"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <button id="close-order-modal" class="w-full mt-5 bg-purple-600 text-white py-2 rounded-xl">إغلاق</button>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        // فلترة الطلبات حسب الحالة
        const statusBtns = document.querySelectorAll('.status-filter');
        const orderCards = document.querySelectorAll('.order-card');

        function filterOrders() {
            let activeStatus = document.querySelector('.status-filter.active')?.getAttribute('data-status') || 'all';
            orderCards.forEach(card => {
                let cardStatus = card.getAttribute('data-status');
                if (activeStatus === 'all' || cardStatus === activeStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        statusBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                statusBtns.forEach(b => {
                    b.classList.remove('active', 'bg-purple-600', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                btn.classList.add('active', 'bg-purple-600', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                filterOrders();
            });
        });

        // المودال: عرض تفاصيل الطلب
        const modal = document.getElementById('order-modal');
        const closeBtn = document.getElementById('close-order-modal');

        function showOrderDetails(data) {
                document.getElementById('modal-order-id').innerText = data.id;
            document.getElementById('modal-order-date').innerText = new Date(data.date).toLocaleDateString('ar-EG');
            document.getElementById('modal-order-status').innerText = data.status;
            
            // لون الحالة
            let statusSpan = document.getElementById('modal-order-status');
            if (data.status === 'تم التوصيل') statusSpan.className = 'text-green-600 font-semibold';
            else if (data.status === 'قيد المعالجة') statusSpan.className = 'text-blue-600 font-semibold';
            else statusSpan.className = 'text-red-600 font-semibold';
            
            // عرض المنتجات
            let itemsHtml = '';
             data.items.forEach(item => {
                itemsHtml += `<div class="flex justify-between text-xs">
                                <span>${item.name} × ${item.qty}</span>
                                <span class="text-gray-500">${item.price * item.qty} ر.س</span>
                            </div>`;
                if (item.warehouse) {
                    itemsHtml += `<div class="text-xs text-gray-400 mr-4">المخزن: ${item.warehouse}</div>`;
                }
            });
            document.getElementById('modal-order-items').innerHTML = itemsHtml;
            
            // معلومات إضافية
            document.getElementById('modal-payment').innerText = data.payment_method;
            document.getElementById('modal-address').innerText = data.address;
            document.getElementById('modal-phone').innerText = data.phone;
            
            // حساب المجموع الفرعي
            let subtotal = data.total - (data.delivery_fee || 0) + (data.coupon_discount || 0);
            document.getElementById('modal-subtotal').innerText = subtotal + ' ر.س';
            
            // رسوم التوصيل
            if (data.delivery_fee > 0) {
                document.getElementById('delivery-fee-row').style.display = 'flex';
                document.getElementById('modal-delivery').innerText = data.delivery_fee + ' ر.س';
            } else {
                document.getElementById('delivery-fee-row').style.display = 'none';
            }
            
            // خصم القسيمة
            if (data.coupon_discount > 0) {
                document.getElementById('coupon-row').style.display = 'flex';
                document.getElementById('modal-coupon').innerText = '-' + data.coupon_discount + ' ر.س';
            } else {
                document.getElementById('coupon-row').style.display = 'none';
            }
            
            document.getElementById('modal-total').innerText = data.total + ' ر.س';
            
            // إظهار المودال
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            document.querySelector('#order-modal .bg-white').classList.remove('scale-95');
            document.querySelector('#order-modal .bg-white').classList.add('scale-100');
        }

        // ربط الأحداث للأزرار
        document.querySelectorAll('.order-details-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const data = {
                    id: btn.getAttribute('data-id'),
                    date: btn.getAttribute('data-date'),
                    total: parseFloat(btn.getAttribute('data-total')),
                    status: btn.getAttribute('data-status'),
                    items: JSON.parse(btn.getAttribute('data-items')),
                    address: btn.getAttribute('data-address'),
                    phone: btn.getAttribute('data-phone'),
                    payment_method: btn.getAttribute('data-payment'),
                    delivery_fee: parseFloat(btn.getAttribute('data-delivery') || 0),
                    coupon_discount: parseFloat(btn.getAttribute('data-coupon') || 0)
                };
                showOrderDetails(data);
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.querySelector('#order-modal .bg-white').classList.remove('scale-100');
            document.querySelector('#order-modal .bg-white').classList.add('scale-95');
        });

        // إغلاق المودال عند الضغط على الخلفية
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeBtn.click();
        });
    </script>
@endsection