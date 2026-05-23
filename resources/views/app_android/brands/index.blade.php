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
            @php
                $brands = [
                    ['id' => 1, 'name' => 'نيكي', 'logo' => '👟', 'bg' => 'bg-gray-100', 'product_count' => 15],
                    ['id' => 2, 'name' => 'أديداس', 'logo' => '⚡', 'bg' => 'bg-gray-100', 'product_count' => 12],
                    ['id' => 3, 'name' => 'ابل', 'logo' => '🍎', 'bg' => 'bg-gray-100', 'product_count' => 20],
                    ['id' => 4, 'name' => 'سامسونج', 'logo' => '📱', 'bg' => 'bg-gray-100', 'product_count' => 18],
                    ['id' => 5, 'name' => 'هوندا', 'logo' => '🏍️', 'bg' => 'bg-gray-100', 'product_count' => 5],
                ];
            @endphp
            @foreach($brands as $brand)
                {{-- استخدم اسم الراوت الصحيح --}}
                <a href="{{ route('customer.brands.show', $brand['id']) }}" class="block bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg">
                    <div class="w-20 h-20 mx-auto {{ $brand['bg'] }} rounded-2xl flex items-center justify-center text-4xl">
                        {{ $brand['logo'] }}
                    </div>
                    <h3 class="font-bold text-gray-800 mt-2">{{ $brand['name'] }}</h3>
                    <p class="text-xs text-gray-400">{{ $brand['product_count'] }} منتج</p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection