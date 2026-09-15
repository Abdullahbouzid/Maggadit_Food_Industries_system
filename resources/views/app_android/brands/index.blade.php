@extends('app_android.partials.mobile.app_mobile')

@section('{{ __('messages.app.page_') }}', 'الماركات')

@section('content')
    @include('app_android.partials.mobile.header', ['{{ __('messages.app.page_') }}' => 'ماركات شهيرة'])

    <div class="p-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @forelse($brands as $brand)
                <a href="{{ route('customer.brands.show', $brand->id) }}" class="block bg-white rounded-3xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 group">
                    <div class="relative h-28 bg-gradient-to-br from-amber-50 to-orange-50 flex items-center justify-center overflow-hidden">
                        @if($brand->logo)
                            <img src="{{ $brand->logo }}" alt="{{ $brand->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-3xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                🏷️
                            </div>
                        @endif
                        <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm rounded-full px-2.5 py-0.5 shadow-sm">
                            <span class="text-xs font-bold text-gray-700">{{ $brand->products_count ?? 0 }}</span>
                            <span class="text-xs text-gray-500">منتج</span>
                        </div>
                    </div>
                    <div class="p-3 text-center">
                        <h3 class="font-bold text-gray-800 text-sm leading-tight">{{ $brand->name }}</h3>
                    </div>
                </a>
            @empty
                <div class="col-span-2 text-center py-12">
                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-4xl mb-3">📭</div>
                    <p class="text-gray-500">لا توجد ماركات متاحة حالياً</p>
                </div>
            @endforelse
        </div>

        @if($brands instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $brands->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection
