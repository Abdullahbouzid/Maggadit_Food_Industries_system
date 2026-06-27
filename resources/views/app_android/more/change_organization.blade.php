@extends('app_android.partials.mobile.app_mobile')

@section('title', 'تغيير المنظمة')

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'تغيير المنظمة'])

    <div class="p-4" dir="rtl">
        <div class="mb-4">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100">
                <i class="fas fa-arrow-right"></i>
                <span>رجوع</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-5">
            <h3 class="text-lg font-bold text-gray-800 mb-4">اختر المنظمة</h3>
            
            <div id="organizations-list" class="space-y-3">
                <!-- سيتم تحميل البيانات من API -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mx-auto"></div>
                    <p class="text-gray-400 mt-2">جاري تحميل المنظمات...</p>
                </div>
            </div>
        </div>
        
        <div id="logout-btn" class="mt-6 rounded-xl p-4 flex items-center justify-center gap-3">
            <br><br>
        </div>
    </div>

    <div id="toast" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none"></div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        // أداة عرض الرسائل
        function showToast(msg, isError = false) {
            const toast = document.getElementById('toast');
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 3000);
        }

        // الحصول على التوكن من localStorage
        function getToken() {
            return localStorage.getItem('token') || null;
        }

        // جلب المنظمات من الـ API
        async function loadOrganizations() {
            const token = getToken();
            
            if (!token) {
                document.getElementById('organizations-list').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-circle text-red-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">الرجاء تسجيل الدخول أولاً</p>
                    </div>
                `;
                return;
            }

            try {
                const response = await fetch('/api/organizations', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error('فشل في تحميل المنظمات');
                }

                const data = await response.json();
                
                if (data.success) {
                    renderOrganizations(data.organizations, data.active_organization_id);
                } else {
                    throw new Error(data.message || 'حدث خطأ');
                }
            } catch (error) {
                document.getElementById('organizations-list').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-circle text-red-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">${error.message}</p>
                        <button onclick="loadOrganizations()" class="mt-3 bg-purple-600 text-white px-4 py-2 rounded-full text-sm">
                            <i class="fas fa-sync-alt ml-2"></i> إعادة المحاولة
                        </button>
                    </div>
                `;
            }
        }

        // عرض المنظمات في الواجهة
        function renderOrganizations(organizations, activeId) {
            const container = document.getElementById('organizations-list');
            
            if (!organizations || organizations.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-building text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">لا توجد منظمات مرتبطة</p>
                    </div>
                `;
                return;
            }

            let html = '';
            organizations.forEach(org => {
                const isActive = org.id === activeId;
                html += `
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
                        <div>
                            <p class="font-semibold text-gray-800">${org.name}</p>
                            <p class="text-xs text-gray-400">${org.code || ''} ${org.organization_type ? ' - ' + org.organization_type : ''}</p>
                        </div>
                        ${isActive ? 
                            `<span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">نشط</span>` :
                            `<button class="bg-purple-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-purple-700 transition select-org" data-id="${org.id}" data-name="${org.name}">تبديل</button>`
                        }
                    </div>
                `;
            });

            container.innerHTML = html;

            // إضافة حدث للنقر على أزرار التبديل
            document.querySelectorAll('.select-org').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orgId = this.getAttribute('data-id');
                    const orgName = this.getAttribute('data-name');
                    switchOrganization(orgId, orgName);
                });
            });
        }

        // تبديل المنظمة عبر API
        async function switchOrganization(organizationId, organizationName) {
            const token = getToken();
            
            if (!token) {
                showToast('الرجاء تسجيل الدخول أولاً', true);
                return;
            }

            try {
                const response = await fetch('/api/change-organization', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        organization_id: organizationId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast(`✅ تم التبديل إلى "${organizationName}" بنجاح`);
                    // إعادة تحميل المنظمات لتحديث حالة النشاط
                    setTimeout(() => {
                        loadOrganizations();
                    }, 1000);
                } else {
                    showToast(data.message || 'فشل في تغيير المنظمة', true);
                }
            } catch (error) {
                showToast('حدث خطأ في الاتصال بالخادم', true);
            }
        }

        // تحميل المنظمات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadOrganizations();
        });
    </script>
@endsection