@extends('app_android.partials.mobile.app_mobile')

@section('title', 'كشف حساب مالي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'كشف حساب مالي'])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="bg-white rounded-2xl shadow-md p-3 text-center border-r-4 border-green-500">
                <p class="text-xs text-gray-400">إجمالي الإيداعات</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($totals['deposits'], 2) }} ر.س</p>
                <i class="fas fa-arrow-up text-green-500 text-sm"></i>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center border-r-4 border-red-500">
                <p class="text-xs text-gray-400">إجمالي الصرف والسحب</p>
                <p class="text-xl font-bold text-red-600">{{ number_format($totals['debits'], 2) }} ر.س</p>
                <i class="fas fa-arrow-down text-red-500 text-sm"></i>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-3 text-center col-span-2 border-t-2 border-purple-200">
                <p class="text-xs text-gray-400">الرصيد الحالي</p>
                <p class="text-2xl font-bold text-purple-700">{{ number_format($totals['balance'], 2) }} ر.س</p>
            </div>
        </div>

        <form method="GET" action="{{ route('customer.financial_statement', [], false) }}" class="bg-white rounded-2xl shadow-md p-3 mb-4">
            <div class="flex gap-2">
                <input type="date" name="from" value="{{ $from }}" class="text-sm border border-gray-200 rounded-full px-3 py-1.5 w-1/2 focus:outline-none focus:border-purple-400">
                <input type="date" name="to" value="{{ $to }}" class="text-sm border border-gray-200 rounded-full px-3 py-1.5 w-1/2 focus:outline-none focus:border-purple-400">
            </div>
            <div class="flex gap-2 mt-3">
                <button type="submit" class="flex-1 rounded-xl bg-purple-600 text-white py-2 text-sm font-bold">تطبيق</button>
                <a href="{{ route('customer.financial_statement', [], false) }}" class="flex-1 rounded-xl bg-gray-100 text-gray-700 py-2 text-sm font-bold text-center">إعادة تعيين</a>
            </div>
        </form>

        <div class="flex gap-2 overflow-x-auto pb-2 mb-3">
            <button data-filter="all" class="filter-btn active px-4 py-1.5 rounded-full text-sm font-semibold bg-purple-600 text-white">الكل</button>
            <button data-filter="credit" class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">إيداع</button>
            <button data-filter="debit" class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">صرف وسحب</button>
        </div>

        <h3 class="text-sm font-bold text-gray-800 mb-2">الحركات</h3>

        <div class="space-y-3" id="transactions-container">
            @forelse ($rows as $row)
                <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 transaction-item"
                    data-type="{{ $row['direction'] }}" data-date="{{ $row['date'] }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $row['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</p>
                        </div>
                        <span class="font-bold {{ $row['direction'] === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $row['signed'] < 0 ? '-' : '+' }}{{ number_format($row['amount'], 2) }} ر.س
                        </span>
                    </div>

                    @if ($row['invoice_number'])
                        <div class="mt-2 text-[11px] text-purple-700 bg-purple-50 rounded-lg px-2 py-1 inline-block">
                            <i class="fas fa-file-invoice ml-1"></i>فاتورة: {{ $row['invoice_number'] }}
                        </div>
                    @endif

                    <button class="view-details text-purple-600 text-xs font-semibold mt-2 block"
                        data-desc="{{ $row['label'] }}"
                        data-amount="{{ $row['signed'] < 0 ? '-' : '+' }}{{ number_format($row['amount'], 2) }}"
                        data-date="{{ $row['date'] }}"
                        data-method="{{ $row['method'] }}"
                        data-ref="{{ $row['reference'] }}">
                        عرض التفاصيل <i class="fas fa-chevron-left mr-1 text-xs"></i>
                    </button>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-md p-8 text-center">
                    <i class="fas fa-receipt text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">لا توجد حركات في هذه الفترة</p>
                </div>
            @endforelse
        </div>

        <h3 class="text-sm font-bold text-gray-800 mt-6 mb-2">الفواتير</h3>

        <div class="bg-white rounded-2xl shadow-md p-3 mb-3 grid grid-cols-3 gap-2 text-center">
            <div>
                <div class="text-sm font-bold text-gray-800">{{ number_format($totals['invoiced'], 2) }}</div>
                <div class="text-[10px] text-gray-400">إجمالي الفواتير</div>
            </div>
            <div>
                <div class="text-sm font-bold text-green-600">{{ number_format($totals['paid'], 2) }}</div>
                <div class="text-[10px] text-gray-400">المسدّد</div>
            </div>
            <div>
                <div class="text-sm font-bold text-amber-600">{{ number_format($totals['outstanding'], 2) }}</div>
                <div class="text-[10px] text-gray-400">المستحق</div>
            </div>
        </div>

        @forelse ($invoices as $invoice)
            @php
                $status = \App\Enums\InvoiceStatus::tryFrom((string) ($invoice->status->value ?? $invoice->status));
            @endphp
            <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 mb-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-400">{{ $invoice->invoice_date }}</p>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-gray-800">{{ number_format((float) $invoice->total_amount, 2) }} ر.س</p>
                        <p class="text-[10px] text-gray-400">{{ $status?->label() ?? '—' }}</p>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px]">
                    <span class="text-gray-500">المسدّد: {{ number_format((float) $invoice->paid_amount, 2) }}</span>
                    <a href="{{ route('customer.order_invoice_pdf', $invoice->order_id, false) }}"
                        class="text-purple-700 font-bold">
                        <i class="fas fa-file-pdf ml-1"></i>عرض
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm text-gray-500">لا توجد فواتير</p>
            </div>
        @endforelse

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <div id="transaction-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl w-11/12 max-w-md p-6 transform scale-95 transition-all duration-300 shadow-2xl" dir="rtl">
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
        const filterBtns = document.querySelectorAll('.filter-btn');
        const transactions = document.querySelectorAll('.transaction-item');

        function filterTransactions() {
            const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';

            transactions.forEach((tx) => {
                const type = tx.getAttribute('data-type');
                tx.style.display = (activeFilter === 'all' || type === activeFilter) ? '' : 'none';
            });
        }

        filterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                filterBtns.forEach((b) => {
                    b.classList.remove('bg-purple-600', 'text-white', 'active');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                btn.classList.add('bg-purple-600', 'text-white', 'active');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                filterTransactions();
            });
        });

        const modal = document.getElementById('transaction-modal');
        document.querySelectorAll('.view-details').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('modal-desc').textContent = btn.dataset.desc;
                document.getElementById('modal-amount').textContent = btn.dataset.amount + ' ر.س';
                document.getElementById('modal-date').textContent = btn.dataset.date;
                document.getElementById('modal-method').textContent = btn.dataset.method;
                document.getElementById('modal-ref').textContent = btn.dataset.ref;
                modal.classList.remove('opacity-0', 'pointer-events-none');
            });
        });
        document.getElementById('close-modal').addEventListener('click', () => {
            modal.classList.add('opacity-0', 'pointer-events-none');
        });
    </script>
@endsection
