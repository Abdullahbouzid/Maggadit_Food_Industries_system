@extends('dashboard.partials.layout')

@section('title', 'إدارة الإشعارات')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-bell" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            إدارة الإشعارات الخارجية
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">
            إرسال إشعارات فورية (Push Notifications) لعملاء التطبيق من خلال Firebase Cloud Messaging
        </p>
    </div>
    <div class="nova-actionbar-actions">
        <button onclick="document.getElementById('sendModal').classList.remove('hidden')" class="nova-btn nova-btn-primary nova-btn-sm">
            <i class="fas fa-plus"></i> إرسال إشعار جديد
        </button>
    </div>
</div>

<div class="nova-row cols-2 cols-lg-4 nova-mb-4">
    <div class="nova-stat">
        <div class="nova-stat-icon primary">
            <i class="fas fa-bell"></i>
        </div>
        <p class="nova-stat-label">إجمالي الإشعارات المرسلة</p>
        <p class="nova-stat-value" id="total-sent-stat">{{ session('total_sent', 0) }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon secondary">
            <i class="fas fa-users"></i>
        </div>
        <p class="nova-stat-label">العملاء المشتركين</p>
        <p class="nova-stat-value" id="subscribed-stat">{{ $stats['subscribed_customers'] ?? 0 }}</p>
    </div>
    <div class="nova-stat">
        <div class="nova-stat-icon accent">
            <i class="fas fa-mobile-alt"></i>
        </div>
        <p class="nova-stat-label">أجهزة التسجيل</p>
        <p class="nova-stat-value">{{ session('fcm_tokens', 0) }}</p>
    </div>
</div>

<div id="sendModal" class="fixed inset-0 z-[60] bg-black/40 flex items-center justify-center hidden backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 mx-4 shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="modal-content">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-bell text-purple-600 text-xl"></i>
            <h3 class="text-lg font-bold text-gray-800">إرسال إشعار فوري</h3>
        </div>

        <form id="notification-form" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">نوع الاستهداف</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targetType" value="customer" class="accent" onchange="toggleTargetForm('customer')"> <span>عميل محدد</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targetType" value="all" checked class="accent" onchange="toggleTargetForm('all')"> <span>جميع العملاء</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="targetType" value="branches" class="accent" onchange="toggleTargetForm('branches')"> <span>بفروع محددة</span>
                    </label>
                </div>
            </div>

            <div id="target-customer" class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">معرف العميل</label>
                    <input type="number" name="customer_id" id="customer-id-input" placeholder="أدخل معرف العميل" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200">
                </div>
            </div>

            <div id="target-all" class="space-y-3 hidden">
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-2">
                    <i class="fas fa-info-circle text-emerald-600 mr-2"></i>
                    <span class="text-sm text-emerald-700">سيتم إرسال الإشعار لجميع العملاء المشتركين في الإشعارات</span>
                </div>
            </div>

            <div id="target-branches" class="space-y-3 hidden">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">اختر الفروع</label>
                    <select name="branch_ids[]" id="branch-select" multiple class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200" size="4">
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">عنوان الإشعار</label>
                <input type="text" name="title" id="notification-title" placeholder="مثال: عرض خاص عليك" class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">نص الإشعار</label>
                <textarea name="body" id="notification-body" rows="3" placeholder="اكتب نص الإشعار هنا..." class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200"></textarea>
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeSendModal()" class="nova-btn nova-btn-secondary px-4 py-2 rounded-xl">إلغاء</button>
                <button type="button" onclick="sendNotification()" id="send-btn" class="nova-btn nova-btn-primary px-6 py-2 rounded-xl">
                    <i class="fas fa-paper-plane mr-2"></i> إرسال
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="nova-alert danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    function toggleTargetForm(type) {
        ['customer', 'all', 'branches'].forEach(t => {
            document.getElementById('target-' + t).classList.add('hidden');
        });
        document.getElementById('target-' + type).classList.remove('hidden');
    }

    function closeSendModal() {
        document.getElementById('sendModal').classList.add('hidden');
        document.getElementById('modal-content').classList.remove('scale-95', 'opacity-0');
    }

    function openSendModal() {
        document.getElementById('sendModal').classList.remove('hidden');
        document.getElementById('modal-content').classList.add('scale-95', 'opacity-0');
        requestAnimationFrame(() => {
            document.getElementById('modal-content').classList.remove('scale-95', 'opacity-0');
        });
    }

    async function sendNotification() {
        const btn = document.getElementById('send-btn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> جاري الإرسال...';

        const targetType = document.querySelector('input[name="targetType"]:checked').value;
        const formData = {
            title: document.getElementById('notification-title').value,
            body: document.getElementById('notification-body').value,
        };

        if (targetType === 'customer') {
            formData.customer_id = document.getElementById('customer-id-input').value;
            const url = '{{ route('admin.notifications.send_customer') }}';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(formData),
            });
            const result = await response.json();
            handleResult(result);
        } else if (targetType === 'all') {
            const url = '{{ route('admin.notifications.send_all') }}';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(formData),
            });
            const result = await response.json();
            handleResult(result);
        } else if (targetType === 'branches') {
            const select = document.getElementById('branch-select');
            const branchIds = Array.from(select.selectedOptions).map(opt => opt.value);
            formData.branch_ids = branchIds;
            const url = '{{ route('admin.notifications.send_branches') }}';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(formData),
            });
            const result = await response.json();
            handleResult(result);
        }
    }

    function handleResult(result) {
        const btn = document.getElementById('send-btn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> إرسال';

        if (result.success) {
            alert(result.message || 'تم الإرسال بنجاح');
            closeSendModal();
            // Update stats
            fetch('{{ route('admin.notifications.index') }}')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('total-sent-stat').textContent = data.stats?.total_sent ?? '—';
                    document.getElementById('subscribed-stat').textContent = data.stats?.subscribed_customers ?? '—';
                });
            window.location.reload();
        } else {
            alert(result.error || 'فشل الإرسال: ' + (result.message || 'خطأ غير معروف'));
        }
    }
</script>
@endsection