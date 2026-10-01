@extends('app_android.partials.mobile.app_mobile')

@section('title', 'استرجاعاتي')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'استرجاعاتي'])

    <div class="p-4" dir="rtl">
        @if (session('success'))
            <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-gray-800">{{ $returns->total() }}</div>
                <div class="text-[11px] text-gray-400">كل الاسترجاعات</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-amber-600">{{ $pendingCount }}</div>
                <div class="text-[11px] text-gray-400">قيد المراجعة</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-3 text-center">
                <div class="text-lg font-bold text-emerald-600">
                    {{ (int) ($summary['processed']->aggregate ?? 0) }}
                </div>
                <div class="text-[11px] text-gray-400">تمت المعالجة</div>
            </div>
        </div>

        <a href="{{ route('customer.my_products', false) }}"
            class="flex items-center justify-between bg-white rounded-2xl shadow-sm p-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                    <i class="fas fa-box text-purple-600"></i>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-800">منتجاتي وكل باتش</div>
                    <div class="text-[11px] text-gray-400">الكميات المستلمة من كل دفعة إنتاج</div>
                </div>
            </div>
            <i class="fas fa-chevron-left text-gray-300 text-xs"></i>
        </a>

        @forelse ($returns as $return)
            @php
                $status = $return->status instanceof \App\Enums\ReturnStatus
                    ? $return->status
                    : \App\Enums\ReturnStatus::tryFrom((string) ($return->status->value ?? $return->status));
                $type = $return->type instanceof \App\Enums\ReturnType
                    ? $return->type
                    : \App\Enums\ReturnType::tryFrom((string) ($return->type->value ?? $return->type));
            @endphp

            <div class="bg-white rounded-2xl shadow-sm p-4 mb-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-800">{{ $return->return_number ?? $return->id }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            {{ $return->created_at?->format('Y-m-d') }}
                            @if ($return->invoice)
                                — {{ $return->invoice->invoice_number }}
                            @endif
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $status?->badgeColor() ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $status?->label() ?? '—' }}
                    </span>
                </div>

                <div class="mt-2 flex items-center gap-2 text-[11px]">
                    <span class="px-2 py-1 rounded-full bg-purple-50 text-purple-700 font-bold">
                        {{ $type?->label() ?? '—' }}
                    </span>
                    <span class="text-gray-500">{{ $return->reason }}</span>
                </div>

                @if ($return->items->isNotEmpty())
                    <div class="mt-3 border-t border-gray-100 pt-3 space-y-1">
                        @foreach ($return->items as $line)
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                <span>{{ $line->item?->name_ar ?: ($line->item?->name_en ?: 'منتج') }}</span>
                                <span class="font-bold text-gray-700">
                                    {{ rtrim(rtrim(number_format((float) $line->quantity_returned, 2), '0'), '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-500">المبلغ المسترد</span>
                    <span class="text-sm font-bold text-gray-800">
                        {{ number_format((float) $return->total_refund, 2) }} ر.س
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <i class="fas fa-undo text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">لا توجد طلبات استرجاع حتى الآن</p>
                <p class="text-xs text-gray-400 mt-1">يمكنك طلب الاسترجاع من صفحة تفاصيل أي فاتورة</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
