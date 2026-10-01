@extends('app_android.partials.mobile.app_mobile')

@section('title', 'منتجاتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'منتجاتي'])

    <div class="p-4" dir="rtl">
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-gray-800">{{ $totalItems }}</div>
                <div class="text-[11px] text-gray-400">عدد المنتجات</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-purple-600">{{ rtrim(rtrim(number_format($totalQuantity, 2), '0'), '.') }}</div>
                <div class="text-[11px] text-gray-400">إجمالي الكميات</div>
            </div>
        </div>

        <div class="mb-4 rounded-2xl bg-purple-50 border border-purple-100 p-3 text-[11px] text-purple-800">
            يوضّح الجدول أدناه الكمية التي طلبتها من كل دفعة إنتاج (باتش) على حدة،
            حتى تعرف مصدر المنتج وتاريخ صلاحيته.
        </div>

        @forelse ($rows as $row)
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-800">{{ $row['name'] }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $row['code'] }} — {{ $row['invoice_count'] }} فاتورة
                        </div>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-bold text-purple-700">
                            {{ rtrim(rtrim(number_format($row['total_quantity'], 2), '0'), '.') }}
                        </div>
                        <div class="text-[10px] text-gray-400">إجمالي الكمية</div>
                    </div>
                </div>

                <div class="text-[11px] text-gray-500 mt-2">
                    القيمة الإجمالية: {{ number_format($row['total_value'], 2) }} ر.س
                </div>

                @if ($row['batches']->isNotEmpty())
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <div class="text-[11px] font-bold text-gray-600 mb-2">الكميات حسب دفعة الإنتاج</div>

                        <table class="w-full text-[11px]">
                            <thead>
                                <tr class="text-gray-400">
                                    <th class="text-right font-normal pb-1">رقم الباتش</th>
                                    <th class="text-right font-normal pb-1">تاريخ الصلاحية</th>
                                    <th class="text-left font-normal pb-1">الكمية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($row['batches'] as $batch)
                                    <tr class="border-t border-gray-50">
                                        <td class="py-1.5 text-gray-700 font-semibold">{{ $batch->batch_number }}</td>
                                        <td class="py-1.5 text-gray-500">
                                            {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('Y-m-d') : '—' }}
                                        </td>
                                        <td class="py-1.5 text-left font-bold text-purple-700">
                                            {{ rtrim(rtrim(number_format((float) $batch->quantity, 2), '0'), '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-3 border-t border-gray-100 pt-3 text-[11px] text-gray-400">
                        لم تُحدَّد دفعة إنتاج لهذه الكميات بعد
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <i class="fas fa-box text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">لا توجد منتجات في طلباتك بعد</p>
            </div>
        @endforelse
    </div>
@endsection
