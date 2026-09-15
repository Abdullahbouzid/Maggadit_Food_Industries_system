@extends('app_android.partials.mobile.app_mobile')

@section('{{ __('messages.app.page_') }}', 'كشف حساب مالي')

@section('content')
    @include('app_android.partials.mobile.header', ['{{ __('messages.app.page_') }}' => 'كشف حساب مالي'])

    <div class="p-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <!-- بطاقات الإحصائيات السريعة -->
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="bg-white rounded-2xl shadow-md p-3 text-center border-r-4 border-green-500">
                <p class="text-xs text-gray-400">إجمالي الدخل</p>
                <p class="text-xl font-bold text-green-600">+5,089 ر.س</p>
                <i class="fas fa-arrow-up text-green-500 text-sm"></i>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center border-r-4 border-red-500">
                <p class="text-xs text-gray-400">إجمالي المصروفات</p>
                <p class="text-xl font-bold text-red-600">-1,598 ر.س</p>
                <i class="fas fa-arrow-down text-red-500 text-sm"></i>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center col-span-2 border-t-2 border-purple-200">
                <p class="text-xs text-gray-400">الرصيد الحالي</p>
                <p class="text-2xl font-bold text-purple-700">3,491 ر.س</p>
            </div>
        </div>

        <!-- شريط الفلترة -->
        <div class="bg-white rounded-2xl shadow-md p-3 mb-4">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button data-filter="all" class="filter-btn active px-4 py-1.5 rounded-full text-sm font-semibold bg-purple-600 text-white">الكل</button>
                <button data-filter="income" class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">وارد</button>
                <button data-filter="expense" class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">منصرف</button>
            </div>
            <div class="flex gap-2 mt-3">
                <input type="date" id="start_date" class="text-sm border border-gray-200 rounded-full px-3 py-1.5 w-1/2 focus:outline-none focus:border-purple-400">
                <input type="date" id="end_date" class="text-sm border border-gray-200 rounded-full px-3 py-1.5 w-1/2 focus:outline-none focus:border-purple-400">
            </div>
        </div>

        <!-- قائمة الحركات -->
        <div class="space-y-3" id="transactions-container">
            @php
                $transactions = [
                    ['date' => '2025-05-20', 'desc' => 'شراء حذاء رياضي', 'amount' => -299, 'type' => 'expense', 'payment_method' => 'بطاقة ائتمان', 'ref' => 'TXN-1001'],
                    ['date' => '2025-05-18', 'desc' => 'استرداد طلب ملغي', 'amount' => 89, 'type' => 'income', 'payment_method' => 'محفظة رقمية', 'ref' => 'TXN-1002'],
                    ['date' => '2025-05-15', 'desc' => 'شراء ساعة ذكية', 'amount' => -1299, 'type' => 'expense', 'payment_method' => 'بطاقة مدى', 'ref' => 'TXN-1003'],
                    ['date' => '2025-05-10', 'desc' => 'إيداع رصيد', 'amount' => 5000, 'type' => 'income', 'payment_method' => 'تحويل بنكي', 'ref' => 'TXN-1004'],
                ];
            @endphp
            @foreach($transactions as $txn)
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 transaction-item" data-type="{{ $txn['type'] }}" data-date="{{ $txn['date'] }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $txn['desc'] }}</p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($txn['date'])->format('d M Y') }}</p>
                        </div>
                        <span class="font-bold {{ $txn['amount'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $txn['amount'] < 0 ? '-' : '+' }}{{ number_format(abs($txn['amount'])) }} ر.س
                        </span>
                    </div>
                    <button class="view-details text-purple-600 text-xs font-semibold mt-2 block" data-desc="{{ $txn['desc'] }}" data-amount="{{ $txn['amount'] }}" data-date="{{ $txn['date'] }}" data-method="{{ $txn['payment_method'] }}" data-ref="{{ $txn['ref'] }}">
                        عرض التفاصيل <i class="fas fa-chevron-left start-1 text-xs"></i>
                    </button>
                </div>
            @endforeach
        </div>
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3  ">
            <br><br>
        </div>
    </div>

    <!-- مودال تفاصيل الحركة -->
    <div id="transaction-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-6 transform scale-95 transition-all duration-300 shadow-2xl" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-2xl">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 text-center mb-2" id="modal-desc">تفاصيل الحركة</h3>
            <div class="space-y-2 text-sm mt-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">المبلغ:</span>
                    <span id="modal-amount" class="font-bold text-purple-700">0 ر.س</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">التاريخ:</span>
                    <span id="modal-date" class="text-gray-700"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">طريقة الدفع:</span>
                    <span id="modal-method" class="text-gray-700"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">رقم المرجع:</span>
                    <span id="modal-ref" class="text-gray-700"></span>
                </div>
            </div>
            <button id="close-modal" class="w-full mt-6 bg-purple-600 text-white py-2 rounded-xl">إغلاق</button>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        // فلترة الحركات
        const filterBtns = document.querySelectorAll('.filter-btn');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const transactions = document.querySelectorAll('.transaction-item');

        function filterTransactions() {
            let activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
            let startDate = startDateInput.value ? new Date(startDateInput.value) : null;
            let endDate = endDateInput.value ? new Date(endDateInput.value) : null;

            transactions.forEach(tx => {
                let type = tx.getAttribute('data-type');
                let txDate = new Date(tx.getAttribute('data-date'));
                let typeMatch = (activeFilter === 'all' || type === activeFilter);
                let dateMatch = true;
                if (startDate) dateMatch = dateMatch && txDate >= startDate;
                if (endDate) dateMatch = dateMatch && txDate <= endDate;
                tx.style.display = (typeMatch && dateMatch) ? 'block' : 'none';
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('active', 'bg-purple-600', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                btn.classList.add('active', 'bg-purple-600', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                filterTransactions();
            });
        });

        startDateInput.addEventListener('change', filterTransactions);
        endDateInput.addEventListener('change', filterTransactions);

        // مودال التفاصيل
        const modal = document.getElementById('transaction-modal');
        const modalDesc = document.getElementById('modal-desc');
        const modalAmount = document.getElementById('modal-amount');
        const modalDate = document.getElementById('modal-date');
        const modalMethod = document.getElementById('modal-method');
        const modalRef = document.getElementById('modal-ref');
        const closeModalBtn = document.getElementById('close-modal');

        document.querySelectorAll('.view-details').forEach(btn => {
            btn.addEventListener('click', () => {
                let desc = btn.getAttribute('data-desc');
                let amount = parseFloat(btn.getAttribute('data-amount'));
                let date = btn.getAttribute('data-date');
                let method = btn.getAttribute('data-method');
                let ref = btn.getAttribute('data-ref');

                modalDesc.innerText = desc;
                modalAmount.innerText = (amount < 0 ? '-' : '+') + Math.abs(amount).toFixed(2) + ' ر.س';
                modalAmount.className = amount < 0 ? 'font-bold text-red-600' : 'font-bold text-green-600';
                modalDate.innerText = new Date(date).toLocaleDateString('ar-EG');
                modalMethod.innerText = method;
                modalRef.innerText = ref;

                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100');
                document.querySelector('#transaction-modal .bg-white').classList.remove('scale-95');
                document.querySelector('#transaction-modal .bg-white').classList.add('scale-100');
            });
        });

        closeModalBtn.addEventListener('click', () => {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.querySelector('#transaction-modal .bg-white').classList.remove('scale-100');
            document.querySelector('#transaction-modal .bg-white').classList.add('scale-95');
        });
    </script>
@endsection