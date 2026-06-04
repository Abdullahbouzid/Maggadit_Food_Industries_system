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

        <!-- قائمة الفئات ديناميكية -->
        <div class="grid grid-cols-2 gap-4">
            @forelse($categories as $category)
                <a href="{{ route('customer.categories.show', $category->id) }}" class="block bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg">
                    <div class="w-16 h-16 mx-auto rounded-full overflow-hidden bg-gray-100 flex items-center justify-center text-3xl shadow-sm">
                        @if($category->image && file_exists(public_path('uploads/' . $category->image)))
                            <img src="{{ asset('uploads/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $category->icon ?? '📁' }}</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-800 mt-2">{{ $category->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $category->products_count ?? 0 }} منتج</p>
                </a>
            @empty
                <div class="col-span-2 text-center text-gray-500 py-8">
                    لا توجد فئات متاحة حالياً
                </div>
            @endforelse
        </div>

        <!-- روابط التصفح (Pagination) إذا كنت تستخدم paginate -->
        @if(method_exists($categories, 'links'))
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <!-- إضافة Fontawesome لظهور الأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection