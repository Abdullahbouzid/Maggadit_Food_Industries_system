@extends('app_android.partials.mobile.app_mobile')

@section('title', 'طلب استرجاع')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'طلب استرجاع'])

    <div class="p-4" dir="rtl">
        @if (session('error'))
            <div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-800">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
            <div class="text-xs text-gray-400">الفاتورة</div>
            <div class="text-sm font-bold text-gray-800">{{ $invoice->invoice_number }}</div>
            <div class="text-xs text-gray-500 mt-1">
                الإجمالي: {{ number_format((float) $invoice->total_amount, 2) }} ر.س
            </div>
        </div>

        @if ($lines->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <i class="fas fa-check-circle text-4xl text-emerald-300 mb-3"></i>
                <p class="text-sm text-gray-500">لا توجد كميات متاحة للاسترجاع من هذه الفاتورة</p>
                <a href="{{ route('customer.returns', false) }}"
                    class="mt-4 inline-block rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700">
                    العودة إلى الاسترجاعات
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('customer.returns.store', $invoice->id, false) }}">
                @csrf

                <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                    <div class="text-sm font-bold text-gray-800 mb-1">نوع الاسترجاع</div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        @foreach ($types as $type)
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2">
                                <input type="radio" name="type" value="{{ $type->value }}"
                                    {{ $loop->first ? 'checked' : '' }} class="accent-purple-600">
                                <span class="text-xs text-gray-700">{{ $type->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                    <div class="text-sm font-bold text-gray-800 mb-1">سبب الاسترجاع</div>
                    <input type="text" name="reason" required minlength="3" maxlength="500"
                        placeholder="مثال: منتج تالف أو غير مطابق"
                        class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-200">
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                    <div class="text-sm font-bold text-gray-800 mb-1">الكميات المراد استرجاعها</div>
                    <div class="text-[11px] text-gray-400 mb-3">اترك الحقل فارغاً أو صفراً للكمميات التي لا تريد استرجاعها</div>

                    @foreach ($lines as $line)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 last:border-0">
                            <div class="flex-1">
                                <div class="text-sm text-gray-800 font-semibold">{{ $line['name'] }}</div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $line['pack'] }} — متاح للاسترجاع: {{ rtrim(rtrim(number_format($line['returnable'], 2), '0'), '.') }}
                                    @if ($line['returned'] > 0)
                                        (سبق استرجاع {{ rtrim(rtrim(number_format($line['returned'], 2), '0'), '.') }})
                                    @endif
                                </div>
                            </div>
                            <input type="number" name="quantities[{{ $line['id'] }}]" step="0.01" min="0"
                                max="{{ $line['returnable'] }}" placeholder="0"
                                class="w-24 rounded-xl border border-gray-200 px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-200">
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full rounded-xl bg-purple-600 text-white py-3 text-sm font-bold shadow-sm">
                    <i class="fas fa-undo ml-2"></i>إرسال طلب الاسترجاع
                </button>
            </form>
        @endif
    </div>
@endsection
