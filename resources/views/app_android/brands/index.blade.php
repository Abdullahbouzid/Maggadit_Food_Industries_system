@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الماركات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'ماركات شهيرة'])

    <div class="p-4" dir="rtl">
        <!-- زر الرجوع -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @forelse($brands as $brand)
                <a href="{{ route('customer.brands.show', $brand->id) }}" class="block bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg">
                    <div class="w-20 h-20 mx-auto rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center text-4xl shadow-sm">
                        @if($brand->logo && file_exists(public_path('storage/' . $brand->logo)))
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-2">
                        @else
                            <span>{{ $brand->logo_emoji ?? '🏷️' }}</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-800 mt-2">{{ $brand->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $brand->products_count ?? 0 }} منتج</p>
                </a>
            @empty
                <div class="col-span-2 text-center text-gray-500 py-8">
                    لا توجد ماركات متاحة حالياً
                </div>
            @endforelse
        </div>

        <!-- روابط التصفح (Pagination) -->
        @if(method_exists($brands, 'links'))
            <div class="mt-6">
                {{ $brands->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <!-- Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection