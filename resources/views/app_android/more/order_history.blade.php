@extends('app_android.partials.mobile.app_mobile')

@section('title', 'طلباتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'طلباتي'])

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl">
            <div class="flex items-center gap-2 text-green-800">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            @if (session('order_number'))
                <p class="text-sm text-green-700 mt-1">رقم الطلبية: <span class="font-bold">{{ session('order_number') }}</span></p>
            @endif
        </div>
    @endif

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
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-3 transition hover:shadow-md">

                <a href="{{ route('customer.order_details', $row['id'], false) }}" class="block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-800">{{ $row['number'] }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $row['date'] }} — {{ $row['items_count'] }} منتج
                        </div>
                        @if (!empty($row['branch_name']))
                            <div class="text-[10px] text-purple-600 mt-1 flex items-center gap-1">
                                <i class="fas fa-store"></i> {{ $row['branch_name'] }}
                            </div>
                        @endif
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
                    <div class="text-sm font-bold text-gray-800">{{ number_format((float)$row['total'], 2) }} ر.س</div>
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

                {{-- أزرار الرد على عرض السعر: موافق / طلب تعديل / مرفوض --}}
                @if ($row['can_respond'])
                    <div class="mt-3 pt-3 border-t border-gray-100 respond-panel"
                         data-respond-url="{{ route('customer.order.quotation.respond', $row['id'], false) }}"
                         data-order-number="{{ $row['number'] }}">

                        <p class="text-[11px] font-bold text-amber-800 mb-2">
                            <i class="fas fa-file-invoice-dollar ml-1"></i>
                            عرض السعر {{ $row['quotation_number'] }} جاهز للمراجعة
                        </p>

                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" class="respond-btn bg-emerald-600 text-white text-[11px] font-bold py-2 rounded-xl"
                                    data-response="approved">
                                <i class="fas fa-check ml-1"></i> موافق
                            </button>
                            <button type="button" class="respond-btn bg-purple-600 text-white text-[11px] font-bold py-2 rounded-xl"
                                    data-response="changes_requested">
                                <i class="fas fa-pen-to-square ml-1"></i> طلب تعديل
                            </button>
                            <button type="button" class="respond-btn bg-red-500 text-white text-[11px] font-bold py-2 rounded-xl"
                                    data-response="rejected">
                                <i class="fas fa-xmark ml-1"></i> مرفوض
                            </button>
                        </div>

                        {{-- حقل الملاحظات يظهر فقط عند اختيار "طلب تعديل" --}}
                        <div class="respond-notes-wrap hidden mt-2">
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">
                                تفاصيل التعديل المطلوب <span class="text-red-500">*</span>
                            </label>
                            <textarea rows="3" class="respond-notes w-full border border-gray-300 rounded-xl px-3 py-2 text-xs bg-white"
                                      placeholder="اكتب التعديلات التي تريدها على العرض..."></textarea>
                            <p class="respond-notes-error hidden text-[10px] text-red-600 mt-1">
                                                يرجى كتابة تفاصيل التعديل المطلوب.
                                            </p>
                        </div>
                    </div>
                @endif
            </div>
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

@push('scripts')
<script>
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                    || '{{ csrf_token() }}';

    function toast(message, isError) {
        if (typeof showToast === 'function') { showToast(message, isError ? 'error' : 'success'); return; }
        if (isError) { alert(message); }
    }

    // لكل لوحة رد: إظهار حقل الملاحظات عند اختيار "طلب تعديل" فقط.
    document.querySelectorAll('.respond-panel').forEach(function (panel) {
        const wrap = panel.querySelector('.respond-notes-wrap');
        const textarea = panel.querySelector('.respond-notes');
        const error = panel.querySelector('.respond-notes-error');

        panel.querySelectorAll('.respond-btn').forEach(function (btn) {
            btn.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();

                const response = this.dataset.response;

                // طلب التعديل يتطلب ملاحظات
                if (response === 'changes_requested') {
                    if (wrap && wrap.classList.contains('hidden')) {
                        wrap.classList.remove('hidden');
                        if (textarea) { textarea.focus(); return; }
                    }
                    const value = (textarea?.value || '').trim();
                    if (!value) {
                        error?.classList.remove('hidden');
                        textarea?.focus();
                        toast('يرجى كتابة تفاصيل التعديل المطلوب.', true);
                        return;
                    }
                }

                const notes = response === 'changes_requested' ? (textarea?.value || '').trim() : '';
                const buttons = panel.querySelectorAll('.respond-btn');
                buttons.forEach(function (b) { b.disabled = true; });
                error?.classList.add('hidden');

                const labels = {
                    approved: 'تم اعتماد عرض السعر وسيتم إصدار الفاتورة.',
                    rejected: 'تم رفض عرض السعر.',
                    changes_requested: 'تم إرسال طلب التعديلات إلى الإدارة.'
                };

                try {
                    const resp = await fetch(panel.dataset.respondUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ response: response, notes: notes })
                    });
                    const data = await resp.json();

                    if (resp.ok && data.success) {
                        toast(data.message || labels[response], false);
                        setTimeout(function () { window.location.reload(); }, 900);
                    } else {
                        toast(data.message || 'تعذّر إرسال الرد', true);
                        buttons.forEach(function (b) { b.disabled = false; });
                    }
                } catch (e) {
                    toast('حدث خطأ في الاتصال', true);
                    buttons.forEach(function (b) { b.disabled = false; });
                }
            });
        });
    });
})();
</script>
@endpush
