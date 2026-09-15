@extends('app_android.partials.mobile.app_mobile')

@section('{{ __('messages.app.page_') }}', 'نبذة عنا')

@section('content')
    @include('app_android.partials.mobile.header', ['{{ __('messages.app.page_') }}' => 'نبذة عنا'])

    <div class="p-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <!-- زر الرجوع -->
        <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> رجوع
        </button>

        <div id="about-content">
            <!-- Loading state -->
            <div id="loading-state" class="bg-white rounded-2xl shadow-md p-5 text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-purple-500 mb-3"></i>
                <h3 class="text-lg font-bold text-gray-800">جاري تحميل النبذة...</h3>
                <p class="text-gray-500 text-sm">إذا كانت هناك بيانات، سيتم عرضها بعد الاتصال بالسيرفر.</p>
            </div>
        </div>

        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <script>
        (function() {
            const contentContainer = document.getElementById('about-content');
            const UPLOADS_BASE = '{{ asset("uploads/organization_about") }}';

            if (!contentContainer) {
                return;
            }

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value).replace(/[&<>\"']/g, function(match) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    }[match];
                });
            }

            function renderValues(values) {
                if (!values || (typeof values !== 'object')) {
                    return '<p class="text-gray-500 text-sm">لا توجد قيم محددة.</p>';
                }

                const items = Array.isArray(values)
                    ? values
                    : Object.values(values);

                if (items.length === 0) {
                    return '<p class="text-gray-500 text-sm">لا توجد قيم محددة.</p>';
                }

                return items.map(item => {
                    const value = typeof item === 'object'
                        ? (item.value || item.key || item.name || item.label || Object.values(item).join(' - '))
                        : item;
                    return `
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-green-500 text-sm"></i>
                            <span class="text-sm text-gray-700">{{ __('messages.app.page__6') }}</span>
                        </div>
                    `;
                }).join('');
            }

            function renderStats(stats) {
                if (!stats || (typeof stats !== 'object')) {
                    return `
                        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
                            <p class="text-gray-500 text-sm">لا توجد إحصائيات متاحة</p>
                        </div>
                    `;
                }

                const items = Array.isArray(stats)
                    ? stats
                    : Object.entries(stats).map(([key, value]) => ({ key, value }));

                if (items.length === 0) {
                    return `
                        <div class="bg-white rounded-2xl shadow-md p-4 text-center">
                            <p class="text-gray-500 text-sm">لا توجد إحصائيات متاحة</p>
                        </div>
                    `;
                }

                const cards = items.map(stat => {
                    let key = '';
                    let value = '';

                    if (typeof stat === 'object' && stat !== null) {
                        key = stat.key || stat.{{ __('messages.app.page_') }} || stat.label || '';
                        value = stat.value || stat.count || stat.number || stat.amount || '';
                    } else {
                        key = stat;
                        value = '';
                    }

                    let icon = 'fa-chart-simple';
                    const keyLower = String(key).toLowerCase();
                    if (keyLower.includes('عام') || keyLower.includes('سنة') || keyLower.includes('year')) icon = 'fa-calendar-alt';
                    else if (keyLower.includes('منتج') || keyLower.includes('product')) icon = 'fa-box';
                    else if (keyLower.includes('عميل') || keyLower.includes('customer')) icon = 'fa-users';
                    else if (keyLower.includes('شريك') || keyLower.includes('partner')) icon = 'fa-handshake';
                    else if (keyLower.includes('طلب') || keyLower.includes('order')) icon = 'fa-shopping-cart';

                    const displayValue = !isNaN(parseFloat(value)) && isFinite(value)
                        ? Number(value).toLocaleString()
                        : (value || key);

                    return `
                        <div class="bg-white rounded-2xl shadow-md p-4 text-center transition hover:shadow-lg hover:-translate-y-1">
                            <div class="text-3xl font-bold text-purple-600 mb-1">${escapeHtml(displayValue)}</div>
                            <div class="text-xs text-gray-500 flex items-center justify-center gap-1">
                                <i class="fas ${icon} text-purple-400"></i>
                                ${escapeHtml(key)}
                            </div>
                        </div>
                    `;
                });

                return `<div class="grid grid-cols-2 gap-4 mb-5">${cards.join('')}</div>`;
            }

            function renderAbout(about) {
                if (!about) {
                    contentContainer.innerHTML = `
                        <div class="bg-white rounded-2xl shadow-md p-5 text-center">
                            <i class="fas fa-info-circle text-4xl text-gray-300 mb-3"></i>
                            <h3 class="text-lg font-bold text-gray-800">لا توجد نبذة</h3>
                            <p class="text-gray-500 text-sm">لم يتم إضافة نبذة لهذه المنظمة بعد.</p>
                        </div>
                    `;
                    return;
                }

                const aboutHtml = [];

                // Cover Image
                const coverImageUrl = about.cover_image_url || (about.cover_image ? `${UPLOADS_BASE}/${escapeHtml(about.cover_image)}` : null);
                if (coverImageUrl) {
                    aboutHtml.push(`
                        <div class="w-full h-48 rounded-2xl overflow-hidden mb-4 shadow-md">
                            <img src="${coverImageUrl}" alt="غلاف" class="w-full h-full object-cover" onerror="this.style.display='none'">
                        </div>
                    `);
                }

                // Logo and Title
                const imageUrl = about.image_url || (about.image ? `${UPLOADS_BASE}/${escapeHtml(about.image)}` : null);
                aboutHtml.push(`
                    <div class="text-center mb-6">
                        ${imageUrl ? `
                            <img src="${imageUrl}" alt="${escapeHtml(about.{{ __('messages.app.page_') }} || 'الشعار')}" class="w-24 h-24 rounded-2xl object-cover mx-auto shadow-lg mb-3 border-2 border-white" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div style="display:none" class="w-24 h-24 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-3">
                                <i class="fas fa-utensils text-white text-4xl"></i>
                            </div>
                        ` : `
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-3">
                                <i class="fas fa-utensils text-white text-4xl"></i>
                            </div>
                        `}
                        <h1 class="text-2xl font-bold text-gray-800">${escapeHtml(about.{{ __('messages.app.page_') }} || 'ماجاديت للصناعات الغذائية')}</h1>
                        <p class="text-gray-500 text-sm mt-1">${escapeHtml(about.sub{{ __('messages.app.page_') }} || 'جودة تذوقها الثقة')}</p>
                    </div>
                `);

                // Story
                aboutHtml.push(`
                    <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                            <i class="fas fa-book-open text-purple-600"></i> قصتنا
                        </h2>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ __('messages.app.page__3') }}</p>
                    </div>
                `);

                // Vision & Mission
                aboutHtml.push(`
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-bullseye text-purple-600 text-lg"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 text-sm">رؤيتنا</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ __('messages.app.page__4') }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 text-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-heart text-purple-600 text-lg"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 text-sm">رسالتنا</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ __('messages.app.page__5') }}</p>
                        </div>
                    </div>
                `);

                // Values
                aboutHtml.push(`
                    <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                            <i class="fas fa-gem text-purple-600"></i> قيمنا الأساسية
                        </h2>
                        <div class="space-y-3">
                            ${renderValues(about.values)}
                        </div>
                    </div>
                `);

                // Statistics
                aboutHtml.push(renderStats(about.statistics));

                // Contact Section
                aboutHtml.push(`
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-5 text-white text-center">
                        <i class="fas fa-headset text-3xl mb-2"></i>
                        <h3 class="font-bold text-lg">نحن هنا لخدمتك</h3>
                        <p class="text-sm opacity-90 mt-1">لأي استفسار أو اقتراح، لا تتردد في التواصل مع فريق الدعم</p>
                        <a href="{{ route('customer.contact') }}" class="inline-block mt-3 bg-white text-purple-600 font-semibold px-5 py-2 rounded-full text-sm shadow-md transition hover:scale-105">
                            تواصل معنا
                        </a>
                    </div>
                `);

                contentContainer.innerHTML = aboutHtml.join('');
            }

            const aboutData = {!! $about ? json_encode([
                'id' => $about->id,
                '{{ __('messages.app.page_') }}' => $about->{{ __('messages.app.page_') }},
                'sub{{ __('messages.app.page_') }}' => $about->sub{{ __('messages.app.page_') }} ?? '',
                'description' => $about->description,
                'vision' => $about->vision,
                'mission' => $about->mission,
                'values' => is_string($about->values) ? json_decode($about->values, true) : $about->values,
                'statistics' => is_string($about->statistics) ? json_decode($about->statistics, true) : $about->statistics,
                'image' => $about->image,
                'cover_image' => $about->cover_image,
                'image_url' => $about->image_url,
                'cover_image_url' => $about->cover_image_url,
            ]) : 'null' !!};

            if (aboutData) {
                renderAbout(aboutData);
            } else {
                contentContainer.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-md p-5 text-center">
                        <i class="fas fa-info-circle text-4xl text-gray-300 mb-3"></i>
                        <h3 class="text-lg font-bold text-gray-800">لا توجد نبذة</h3>
                        <p class="text-gray-500 text-sm">لم يتم إضافة نبذة لهذه المنظمة بعد.</p>
                    </div>
                `;
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection
