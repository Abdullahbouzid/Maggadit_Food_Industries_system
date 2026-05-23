@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الفئات')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'الفئات'])

    <div class="p-4" dir="rtl">
        <!-- زر العودة إلى الخلف -->
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @php
                $categories = [
                    ['id' => 1, 'name' => 'إلكترونيات', 'icon' => '📱', 'color' => 'bg-blue-100', 'product_count' => 24],
                    ['id' => 2, 'name' => 'ملابس', 'icon' => '👕', 'color' => 'bg-green-100', 'product_count' => 18],
                    ['id' => 3, 'name' => 'أحذية', 'icon' => '👟', 'color' => 'bg-yellow-100', 'product_count' => 12],
                    ['id' => 4, 'name' => 'عطور', 'icon' => '🌸', 'color' => 'bg-pink-100', 'product_count' => 8],
                    ['id' => 5, 'name' => 'كتب', 'icon' => '📚', 'color' => 'bg-purple-100', 'product_count' => 15],
                    ['id' => 6, 'name' => 'ألعاب', 'icon' => '🎮', 'color' => 'bg-red-100', 'product_count' => 10],
                ];
            @endphp
            @foreach($categories as $cat)
                <a href="{{ route('categories.show', $cat['id']) }}" class="block bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg">
                    <div class="w-16 h-16 mx-auto {{ $cat['color'] }} rounded-full flex items-center justify-center text-3xl">
                        {{ $cat['icon'] }}
                    </div>
                    <h3 class="font-bold text-gray-800 mt-2">{{ $cat['name'] }}</h3>
                    <p class="text-xs text-gray-400">{{ $cat['product_count'] }} منتج</p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- إضافة Fontawesome لظهور الأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection