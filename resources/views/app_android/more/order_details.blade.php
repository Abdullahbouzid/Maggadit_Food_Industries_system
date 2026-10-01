@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تفاصيل الطلب')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تفاصيل الطلب'])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        @if (! $order)
            <div class="nova-empty text-center py-16">
                <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-gray-600 font-bold">الطلبية غير موجودة</h3>
                <p class="text-gray-400 text-sm mt-1">قد تكون حُذفت أو لا تخصك.</p>
            </div>
        @else
            {{-- ملخص الطلب --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-400">رقم الطلب</div>
                        <div class="font-bold text-gray-800 text-lg">{{ $order->order_number }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                </div>

                <div class="mt-3">
                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                        <span>تقدم الطلب</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-600 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                @if ($order->delivery_address || $order->delivery_phone)
                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500 space-y-1">
                        @if ($order->delivery_address)
                            <div><i class="fas fa-map-marker-alt text-gray-400 ml-1"></i>{{ $order->delivery_address }}</div>
                        @endif
                        @if ($order->delivery_phone)
                            <div><i class="fas fa-phone text-gray-400 ml-1"></i>{{ $order->delivery_phone }}</div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- خطوات الطلب --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                    <h3 class="font-bold text-gray-800 text-sm mb-3">خطوات الطلبية</h3>
                    @include('app_android.partials.order_steps', ['steps' => $steps ?? []])
            </div>

            {{-- رد العميل على عرض السعر --}}
            @if ($canRespond && $quotation)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4">
                    <h3 class="font-bold text-amber-900 text-sm mb-1">عرض السعر جاهز</h3>
                    <p class="text-xs text-amber-700 mb-3">{{ $quotation->quotation_number }} — يرجى مراجعة العرض واختيار رد.</p>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="respond-btn bg-emerald-600 text-white text-xs font-bold py-2 rounded-xl"
                                data-response="approved">
                            <i class="fas fa-check ml-1"></i> موافق
                        </button>
                        <button type="button" class="respond-btn bg-purple-600 text-white text-xs font-bold py-2 rounded-xl"
                                data-response="changes_requested">
                            <i class="fas fa-pen-to-square ml-1"></i> طلب تعديل
                        </button>
                        <button type="button" class="respond-btn bg-red-500 text-white text-xs font-bold py-2 rounded-xl"
                                data-response="rejected">
                            <i class="fas fa-xmark ml-1"></i> مرفوض
                        </button>
                    </div>

                    {{-- حقل الملاحظات يظهر فقط عند اختيار "طلب تعديل" --}}
                    <div class="respond-notes-wrap hidden mt-2">
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">
                            تفاصيل التعديل المطلوب <span class="text-red-500">*</span>
                        </label>
                        <textarea id="respond-notes" rows="3"
                                  placeholder="اكتب التعديلات التي تريدها على العرض..."
                                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-xs bg-white"></textarea>
                        <p class="respond-notes-error hidden text-[10px] text-red-600 mt-1">
                            يرجى كتابة تفاصيل التعديل المطلوب.
                        </p>
                    </div>
                </div>
            @elseif (\App\Enums\OrderStatus::tryFrom($order->status instanceof \App\Enums\OrderStatus ? $order->status->value : (string) $order->status) === \App\Enums\OrderStatus::AdminEdited)
                <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 mb-4">
                    <h3 class="font-bold text-sky-900 text-sm mb-1">بانتظار عرض السعر</h3>
                    <p class="text-xs text-sky-700 leading-relaxed">
                        تمت مراجعة طلبك من الإدارة. ستظهر أزرار الموافقة هنا فور إرسال عرض السعر، ويمكنك حينها الموافقة أو طلب تعديل أو الرفض.
                    </p>
                </div>
            @endif

            {{-- تفاصيل المنتجات --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <h3 class="font-bold text-gray-800 text-sm mb-3">المنتجات</h3>
                <div class="space-y-3">
                    @foreach ($orderItems as $item)
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-50 last:border-0 last:pb-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800">{{ $item['name'] }}</div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $item['code'] }} — {{ $item['pack'] }} × {{ $item['quantity'] }}
                                </div>
                            </div>
                            <div class="text-left shrink-0">
                                <div class="text-sm font-bold text-gray-800">{{ number_format($item['line_total'], 2) }}</div>
                                <div class="text-[11px] text-gray-400">{{ number_format($item['unit_price'], 2) }} للوحدة</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- الفاتورة --}}
            @if ($invoice)
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                    <h3 class="font-bold text-gray-800 text-sm mb-3">الفاتورة</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-gray-800">{{ $invoice->invoice_number }}</div>
                            <div class="text-[11px] text-gray-400">
                                {{ \App\Enums\InvoiceStatus::tryFrom($invoice->status)?->label() ?? $invoice->status }}
                            </div>
                        </div>
                        <a href="{{ route('customer.order_invoice_pdf', $order->id, false) }}"
                           class="text-xs font-bold text-purple-700 bg-purple-50 px-3 py-2 rounded-full">
                            <i class="fas fa-file-pdf ml-1"></i>تحميل
                        </a>
                    </div>

                    <a href="{{ route('customer.returns.create', $invoice->id, false) }}"
                       class="mt-3 w-full flex items-center justify-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-3 py-2 text-xs font-bold text-amber-700">
                        <i class="fas fa-undo"></i>استرجاع كميات من هذه الفاتورة
                    </a>
                </div>
            @endif

            {{-- المستندات --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <h3 class="font-bold text-gray-800 text-sm mb-3">المستندات</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('customer.order_pdf', $order->id, false) }}"
                       class="text-xs font-bold text-gray-700 bg-gray-100 px-3 py-2 rounded-full">
                        <i class="fas fa-file-pdf ml-1"></i>الطلبية
                    </a>
                    @if ($quotation)
                        <a href="{{ route('customer.order_quotation_pdf', $order->id, false) }}"
                           class="text-xs font-bold text-gray-700 bg-gray-100 px-3 py-2 rounded-full">
                            <i class="fas fa-file-invoice ml-1"></i>عرض السعر
                        </a>
                    @endif
                </div>
            </div>

            {{-- سجل التتبّع --}}
            @if (! empty($timeline))
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <h3 class="font-bold text-gray-800 text-sm mb-3">سجل تتبّع الطلبية</h3>
                    <div class="space-y-2">
                        @foreach ($timeline as $entry)
                            <div class="flex items-start justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle text-[6px] text-purple-400"></i>
                                    <span class="text-gray-700">{{ $entry['to'] ?? '' }}</span>
                                    @if (! empty($entry['by']))
                                        <span class="text-gray-400">— {{ $entry['by'] }}</span>
                                    @endif
                                    @if (! empty($entry['notes']))
                                        <span class="text-gray-400">({{ $entry['notes'] }})</span>
                                    @endif
                                </div>
                                <span class="text-gray-400 shrink-0">{{ $entry['date'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const respondUrl  = "{{ route('customer.order.quotation.respond', $order?->id ?? 0, false) }}";
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content
                    || '{{ csrf_token() }}';

    function toast(message, isError) {
        if (typeof showToast === 'function') { showToast(message, isError ? 'error' : 'success'); return; }
        alert(message);
    }

    document.querySelectorAll('.respond-btn').forEach(function (btn) {
        btn.addEventListener('click', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            const response = this.dataset.response;
            const notesWrap = document.querySelector('.respond-notes-wrap');
            const textarea = document.getElementById('respond-notes');
            const notesError = document.querySelector('.respond-notes-error');

            // طلب التعديل يتطلب كتابة الملاحظات
            if (response === 'changes_requested') {
                if (notesWrap && notesWrap.classList.contains('hidden')) {
                    notesWrap.classList.remove('hidden');
                    if (textarea) { textarea.focus(); return; }
                }
                if (!((textarea?.value || '').trim())) {
                    notesError?.classList.remove('hidden');
                    textarea?.focus();
                    toast('يرجى كتابة تفاصيل التعديل المطلوب.', true);
                    return;
                }
            }

            const notes = response === 'changes_requested' ? (textarea?.value || '').trim() : '';
            const buttons = document.querySelectorAll('.respond-btn');
            buttons.forEach(b => b.disabled = true);
            notesError?.classList.add('hidden');

            try {
                const resp = await fetch(respondUrl, {
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
                    toast(data.message || 'تم تسجيل ردّك', false);
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    toast(data.message || 'تعذّر إرسال الرد', true);
                    buttons.forEach(b => b.disabled = false);
                }
            } catch (e) {
                toast('حدث خطأ في الاتصال', true);
                buttons.forEach(b => b.disabled = false);
            }
        });
    });
})();
</script>
@endpush
