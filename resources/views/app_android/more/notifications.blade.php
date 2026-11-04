@extends('app_android.partials.mobile.app_mobile')

@section('title', 'الإشعارات')

@push('styles')
<style>
    .notif-card { transition: all 0.25s ease; border-right: 4px solid transparent; }
    .notif-card.unread { border-right-color: #9879b6; background: #faf5ff; }
    .notif-card:active { transform: scale(0.98); }
    .unread-dot { width: 8px; height: 8px; background: #9879b6; border-radius: 50%; flex-shrink: 0; }
    .notif-modal-overlay { backdrop-filter: blur(6px); }
    .slide-in { animation: slideInUp 0.3s ease forwards; }
    @keyframes slideInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .badge-type { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
</style>
@endpush

@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'الإشعارات'])

    <div class="p-4" dir="rtl">

        {{-- رأس الصفحة --}}
        <div class="flex justify-between items-center mb-5">
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()"
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 transition">
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">الإشعارات</h1>
                    <p class="text-xs text-gray-400" id="notif-count-label">جاري التحميل...</p>
                </div>
            </div>
            <button id="mark-all-btn"
                    class="text-xs text-purple-600 font-semibold bg-purple-50 px-3 py-1.5 rounded-full hover:bg-purple-100 transition hidden">
                <i class="fas fa-check-double ml-1"></i> تحديد الكل مقروء
            </button>
        </div>

        {{-- Loading State --}}
        <div id="loading-state" class="text-center py-10">
            <i class="fas fa-spinner fa-spin text-purple-400 text-3xl mb-3"></i>
            <p class="text-gray-400 text-sm">جاري تحميل الإشعارات...</p>
        </div>

        {{-- Filter Tabs --}}
        <div id="filter-tabs" class="hidden flex space-x-2 space-x-reverse mb-4 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width: none;">
            <button data-tab="all" class="tab-btn active bg-purple-600 text-white px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition-colors">الكل</button>
            <button data-tab="unread" class="tab-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition-colors">غير مقروءة</button>
            <button data-tab="starred" class="tab-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition-colors"><i class="fas fa-star text-amber-500 mr-1"></i> هامة</button>
            <button data-tab="archived" class="tab-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition-colors"><i class="fas fa-archive text-gray-500 mr-1"></i> مؤرشفة</button>
        </div>

        {{-- حاوية الإشعارات --}}
        <div id="notifications-list" class="space-y-3 hidden"></div>

        {{-- لا يوجد إشعارات --}}
        <div id="empty-state" class="hidden text-center py-16">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-gray-500 font-semibold mb-1">لا توجد إشعارات</h3>
            <p class="text-gray-400 text-sm">ستظهر هنا إشعاراتك عند وصولها</p>
        </div>

        <div class="h-8"></div>
    </div>

    {{-- مودال تفاصيل الإشعار --}}
    <div id="notif-modal"
         class="notif-modal-overlay fixed inset-0 bg-black/50 flex items-end justify-center z-50 transition-all duration-300 opacity-0 pointer-events-none">
        <div id="notif-modal-box"
             class="bg-white w-full max-w-lg rounded-t-3xl p-6 transform translate-y-full transition-all duration-300 shadow-2xl max-h-[85vh] overflow-y-auto" dir="rtl">
            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-5"></div>
            <img id="modal-image" src="" alt="صورة الإشعار" class="w-full h-48 object-cover rounded-xl mb-4 hidden">
            <div class="text-center mb-4">
                <div id="modal-icon-wrap"
                     class="w-16 h-16 mx-auto rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-2xl mb-3">
                    <i class="fas fa-bell"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800" id="modal-title"></h2>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed text-center mb-2" id="modal-body"></p>
            <p class="text-xs text-gray-400 text-center mb-5" id="modal-date"></p>
            <button id="close-modal-btn"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-2xl font-semibold transition">
                إغلاق
            </button>
        </div>
    </div>
@endsection

@push('scripts')
@php
    // بيانات الإشعارات محقونة من قاعدة البيانات المحلية (المصدر الموثوق).
    // لا حاجة لجلبها من الـ API الخارجي لأن التطبيق يعمل على 127.0.0.1.
    $fallbackNotifications = $notifications->map(function ($n) {
        return [
            'id'         => $n->id,
            'subject'    => $n->subject,
            'body'       => $n->body,
            'image'      => $n->image ?? null,
            'icon_type'  => $n->icon_type ?? 'notification',
            'group'      => $n->group ?? null,
            'is_read'    => (bool) ($n->is_read ?? false),
            'is_starred' => (bool) ($n->is_starred ?? false),
            'is_archived'=> (bool) ($n->is_archived ?? false),
            'created_at' => $n->created_at,
        ];
    })->values()->toArray();
@endphp
<script>
(function () {
    'use strict';

    // ===== إعدادات =====
    const ROUTES = {
        read:        id => `{{ route('customer.notifications.read', '__ID__', false) }}`.replace('__ID__', id),
        toggleRead:  id => `{{ route('customer.notifications.toggle_read', '__ID__', false) }}`.replace('__ID__', id),
        toggleStar:  id => `{{ route('customer.notifications.toggle_star', '__ID__', false) }}`.replace('__ID__', id),
        toggleArchive: id => `{{ route('customer.notifications.toggle_archive', '__ID__', false) }}`.replace('__ID__', id),
    };
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const LS_LAST   = 'mgd_last_notif_id';
    const LS_READ   = 'mgd_read_ids';      // قائمة IDs المقروءة
    const LS_UNREAD = 'mgd_unread_count';
    const API_TOKEN = localStorage.getItem('api_token') || localStorage.getItem('customer_token') || '';

    // طلب JSON محمي بجلسة العميل (Bearer token للتطبيق الأصلي APK)
    async function postJson(url, body) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (API_TOKEN) {
            headers['Authorization'] = 'Bearer ' + API_TOKEN;
        }
        const resp = await fetch(url, {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : undefined
        });

        let data = {};
        try { data = await resp.json(); } catch (e) { /* استجابة غير JSON */ }
        return { ok: resp.ok, status: resp.status, data };
    }

    // ===== عناصر DOM =====
    const listEl     = document.getElementById('notifications-list');
    const emptyEl    = document.getElementById('empty-state');
    const loadingEl  = document.getElementById('loading-state');
    const filterTabs = document.getElementById('filter-tabs');
    const tabBtns    = document.querySelectorAll('.tab-btn');
    const countLabel = document.getElementById('notif-count-label');
    const markAllBtn = document.getElementById('mark-all-btn');
    const modal      = document.getElementById('notif-modal');
    const modalBox   = document.getElementById('notif-modal-box');
    const modalTitle = document.getElementById('modal-title');
    const modalBody  = document.getElementById('modal-body');
    const modalDate  = document.getElementById('modal-date');
    const modalIcon  = document.getElementById('modal-icon-wrap');
    const modalImg   = document.getElementById('modal-image');
    const closeBtn   = document.getElementById('close-modal-btn');

    let allNotifications = [];
    let currentTab = 'all';

    // ===== إعدادات التبويبات =====
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('bg-purple-600', 'text-white', 'active');
                b.classList.add('bg-gray-100', 'text-gray-600');
            });
            btn.classList.remove('bg-gray-100', 'text-gray-600');
            btn.classList.add('bg-purple-600', 'text-white', 'active');
            currentTab = btn.dataset.tab;
            renderList();
        });
    });

    // ===== LocalStorage helpers =====
    function getReadIds()  { try { return new Set(JSON.parse(localStorage.getItem(LS_READ) || '[]')); } catch { return new Set(); } }
    function saveReadIds(s){ localStorage.setItem(LS_READ, JSON.stringify([...s])); }
function markRead(id)  {
         const s = getReadIds(); s.add(parseInt(id)); saveReadIds(s);

         const card = document.querySelector(`.notif-card[data-id="${id}"]`);
         if (card) {
             card.classList.remove('unread');
             const dot = card.querySelector('.unread-dot');
             if (dot) dot.remove();
         }

         postJson(ROUTES.read(id), null)
             .then(({ ok, data }) => {
                 if (!ok) {
                     console.warn('markRead server error:', { id, status: data });
                 }
                 if (ok && data.unread_count !== undefined) refreshUnreadBadge();
             })
             .catch((e) => console.error('markRead network error:', e));
     }
    function isRead(id)    { return getReadIds().has(parseInt(id)); }
    
     // ===== تبديل حالة القراءة يدوياً =====
     async function toggleReadStatus(id, cardEl, notifObj) {
         try {
             const { ok, data } = await postJson(ROUTES.toggleRead(id), null);
             if (!ok || !data.success) {
                 console.warn('Failed to toggle read status:', { id, response: data });
                 return;
             }

             // الخادم يعيد النتيجة الجديدة كـ result
             const nowRead = !!data.result;
             notifObj.is_read = nowRead;

             // Update local storage
             const s = getReadIds();
             if (nowRead) s.add(parseInt(id));
             else s.delete(parseInt(id));
             saveReadIds(s);

             // Update UI
             const dot = cardEl.querySelector('.unread-dot');
             if (nowRead) {
                 cardEl.classList.remove('unread');
                 if (dot) dot.remove();
             } else {
                 cardEl.classList.add('unread');
                 if (!dot) {
                     const badgeContainer = cardEl.querySelector('.flex.items-center.gap-1.flex-shrink-0');
                     if (badgeContainer) badgeContainer.innerHTML += '<span class="unread-dot"></span>';
                 }
             }

             // Update button icon
             const toggleBtnIcon = cardEl.querySelector('.toggle-read-btn i');
             if (toggleBtnIcon) {
                 toggleBtnIcon.className = nowRead ? 'fas fa-envelope-open-text' : 'fas fa-envelope';
             }

             refreshUnreadBadge();
         } catch (e) {
             console.error("Toggle read status error:", e);
         }
     }
    
    // ===== تبديل النجمة يدوياً =====
    async function toggleStarStatus(id, btnEl, notifObj) {
        try {
            const { ok, data } = await postJson(ROUTES.toggleStar(id), null);
            if (ok && data.success) {
                notifObj.is_starred = !!data.result;
                btnEl.className = `toggle-star-btn text-${notifObj.is_starred ? 'amber-500' : 'gray-300'} hover:text-amber-500 focus:outline-none p-1 rounded-full bg-gray-50 transition-colors`;
                if(currentTab === 'starred') renderList();
            }
        } catch(e) { console.error(e); }
    }

    // ===== تبديل الأرشفة يدوياً =====
    async function toggleArchiveStatus(id, btnEl, notifObj) {
        try {
            const { ok, data } = await postJson(ROUTES.toggleArchive(id), null);
            if (ok && data.success) {
                notifObj.is_archived = !!data.result;
                btnEl.className = `toggle-archive-btn text-${notifObj.is_archived ? 'gray-600' : 'gray-300'} hover:text-gray-600 focus:outline-none p-1 rounded-full bg-gray-50 transition-colors`;
                if(currentTab === 'archived' || currentTab === 'all' || currentTab === 'unread') renderList();
                if (typeof showToast === 'function') {
                    showToast(notifObj.is_archived ? 'تمت الأرشفة' : 'تم الإلغاء', 'info', 2000);
                }
            }
        } catch(e) { console.error(e); }
    }

    // ===== أيقونات وألوان حسب نوع الإشعار =====
    const typeConfig = {
        offer:        { icon: 'fa-tags',        bg: 'bg-amber-100', text: 'text-amber-600', label: 'عرض',     labelCls: 'bg-amber-100 text-amber-700' },
        delivery:     { icon: 'fa-truck',        bg: 'bg-green-100', text: 'text-green-600', label: 'توصيل',   labelCls: 'bg-green-100 text-green-700' },
        payment:      { icon: 'fa-credit-card',  bg: 'bg-red-100',   text: 'text-red-600',   label: 'دفع',      labelCls: 'bg-red-100 text-red-700' },
        receive:      { icon: 'fa-box-open',     bg: 'bg-blue-100',  text: 'text-blue-600',  label: 'استلام',  labelCls: 'bg-blue-100 text-blue-700' },
        notification: { icon: 'fa-bell',         bg: 'bg-purple-100',text: 'text-purple-600',label: 'إشعار',   labelCls: 'bg-purple-100 text-purple-700' },
    };
    function cfg(type) { return typeConfig[type] || typeConfig.notification; }

    // ===== بناء بطاقة إشعار =====
    function buildCard(notif) {
        // Use server provided is_read if available, otherwise fallback to local storage
        const read  = notif.is_read !== undefined ? notif.is_read : isRead(notif.id);
        const c     = cfg(notif.icon_type || notif.type || 'notification');
        const div   = document.createElement('div');
        div.className = `notif-card ${read ? '' : 'unread'} bg-white rounded-2xl shadow-sm p-4 flex gap-3 items-start cursor-pointer slide-in`;
        div.dataset.id = notif.id;
        div.innerHTML = `
            <div class="flex-shrink-0 w-11 h-11 rounded-full ${c.bg} ${c.text} flex items-center justify-center text-lg">
                <i class="fas ${c.icon}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2 mb-1">
                    <h3 class="font-bold text-sm text-gray-800 truncate" onclick="openModal(this.closest('.notif-card').notifData, this.closest('.notif-card'))">${escHtml(notif.subject || notif.title || '')}</h3>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <span class="badge-type ${c.labelCls}">${c.label}</span>
                        ${!read ? '<span class="unread-dot"></span>' : ''}
                    </div>
                </div>
                <p class="text-gray-500 text-xs leading-relaxed" onclick="openModal(this.closest('.notif-card').notifData, this.closest('.notif-card'))">${escHtml(notif.body || notif.message || '')}</p>
                <div class="flex justify-between items-center mt-2">
                    <p class="text-gray-400 text-[10px]">${escHtml(notif.created_at || notif.date || '')}</p>
                    <div class="flex gap-2">
                        <button class="toggle-star-btn text-${notif.is_starred ? 'amber-500' : 'gray-300'} hover:text-amber-500 focus:outline-none p-1 rounded-full bg-gray-50 transition-colors" title="مهم">
                            <i class="fas fa-star"></i>
                        </button>
                        <button class="toggle-archive-btn text-${notif.is_archived ? 'gray-600' : 'gray-300'} hover:text-gray-600 focus:outline-none p-1 rounded-full bg-gray-50 transition-colors" title="أرشفة">
                            <i class="fas fa-archive"></i>
                        </button>
                        <button class="toggle-read-btn text-gray-400 hover:text-purple-600 focus:outline-none p-1 rounded-full bg-gray-50 transition-colors" title="تغيير حالة القراءة">
                            <i class="fas ${read ? 'fa-envelope-open-text' : 'fa-envelope'}"></i>
                        </button>
                    </div>
                </div>
            </div>`;
            
        div.notifData = notif; // Attach data to DOM element for onclick access
        
        // Setup toggle buttons click
        div.querySelector('.toggle-read-btn').addEventListener('click', (e) => {
            e.stopPropagation(); toggleReadStatus(notif.id, div, notif);
        });
        div.querySelector('.toggle-star-btn').addEventListener('click', (e) => {
            e.stopPropagation(); toggleStarStatus(notif.id, e.currentTarget, notif);
        });
        div.querySelector('.toggle-archive-btn').addEventListener('click', (e) => {
            e.stopPropagation(); toggleArchiveStatus(notif.id, e.currentTarget, notif);
        });
        
        return div;
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ===== فتح المودال =====
    function openModal(notif, cardEl) {
        const c = cfg(notif.icon_type || notif.type || 'notification');
        modalTitle.textContent = notif.subject || notif.title || '';
        modalBody.textContent  = notif.body || notif.message || '';
        modalDate.textContent  = notif.created_at || notif.date || '';
        modalIcon.className    = `w-16 h-16 mx-auto rounded-full ${c.bg} ${c.text} flex items-center justify-center text-2xl mb-3`;
        modalIcon.innerHTML    = `<i class="fas ${c.icon}"></i>`;
        
        if (notif.image) {
            modalImg.src = notif.image;
            modalImg.classList.remove('hidden');
        } else {
            modalImg.src = '';
            modalImg.classList.add('hidden');
        }

        // تعليم كمقروء
        // Check if unread (either from local state or server flag)
        const isUnread = cardEl.classList.contains('unread');
        if (isUnread) {
            markRead(notif.id);
            if (notif.is_read !== undefined) notif.is_read = true; // Update local object
            cardEl.classList.remove('unread');
            const dot = cardEl.querySelector('.unread-dot');
            if (dot) dot.remove();
            refreshUnreadBadge();
        }

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        modalBox.classList.remove('translate-y-full');
    }

    function closeModal() {
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        modalBox.classList.add('translate-y-full');
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // ===== تحديث عداد الجرس =====
    function refreshUnreadBadge() {
        const cards   = listEl.querySelectorAll('.notif-card.unread');
        const count   = cards.length;
        localStorage.setItem(LS_UNREAD, count);
        if (typeof window.updateNotificationBadge === 'function') {
            window.updateNotificationBadge(count);
        }
        const total = listEl.querySelectorAll('.notif-card').length;
        countLabel.textContent = `${total} إشعار${count > 0 ? ' • ' + count + ' غير مقروء' : ''}`;
        markAllBtn.classList.toggle('hidden', count === 0);
    }

    // ===== تحديد الكل كمقروء =====
    markAllBtn.addEventListener('click', () => {
        listEl.querySelectorAll('.notif-card.unread').forEach(card => {
            const id = parseInt(card.dataset.id);
            markRead(id);
            card.classList.remove('unread');
            const dot = card.querySelector('.unread-dot');
            if (dot) dot.remove();
        });
        refreshUnreadBadge();
    });

    // ===== تحميل الإشعارات =====
    async function loadNotifications() {
        // البيانات محقونة من السيرفر (قاعدة البيانات المحلية) في Blade،
        // فهي أسرع وأضمن من استدعاء الـ API الذي لا يعمل داخل الـ WebView.
        const serverData = {!! json_encode($fallbackNotifications) !!};

        loadingEl.classList.add('hidden');

        if (!serverData || serverData.length === 0) {
            emptyEl.classList.remove('hidden');
            filterTabs.classList.add('hidden');
            countLabel.textContent = 'لا توجد إشعارات';
            return;
        }

        allNotifications = serverData;

        // حفّظ آخر ID لمحرك الـ Polling حتى لا يُعيد عرض الإشعارات القديمة
        const maxId = Math.max(...allNotifications.map(n => parseInt(n.id, 10) || 0));
        if (maxId > 0) {
            const savedLast = parseInt(localStorage.getItem(LS_LAST) || '0', 10);
            if (maxId > savedLast) localStorage.setItem(LS_LAST, maxId);
        }

        filterTabs.classList.remove('hidden');
        renderList();
        
        // ===== بدء الـ Polling للإشعارات الجديدة =====
        startPolling();
    }
    
    // ===== Polling Mechanism =====
    let pollingInterval = null;
    let isPolling = false;
    
    function startPolling() {
        if (pollingInterval) return; // Already started
        
        // Poll every 30 seconds
        pollingInterval = setInterval(async () => {
            if (isPolling || document.hidden) return; // Don't poll if already polling or page is hidden
            
            isPolling = true;
            try {
                const lastId = parseInt(localStorage.getItem(LS_LAST) || '0', 10);
                const url = '{{ route("customer.notifications.check_new", [], false) }}';
                
                const { ok, data } = await postJson(url, { last_id: lastId });
                
                if (ok && data.success && data.new_notifications && data.new_notifications.length > 0) {
                    console.log('[Notifications] Found', data.new_notifications.length, 'new notifications');
                    
                    // Prepend new notifications to the list
                    data.new_notifications.forEach(n => {
                        // Ensure required fields exist
                        n.is_read = n.is_read !== undefined ? n.is_read : false;
                        n.is_starred = n.is_starred !== undefined ? n.is_starred : false;
                        n.is_archived = n.is_archived !== undefined ? n.is_archived : false;
                        allNotifications.unshift(n);
                    });
                    
                    // Update last ID
                    const newMaxId = Math.max(...data.new_notifications.map(n => parseInt(n.id, 10) || 0));
                    if (newMaxId > 0) {
                        localStorage.setItem(LS_LAST, newMaxId.toString());
                    }
                    
                    // Re-render the list
                    renderList();
                    
                    // Update badge count
                    if (data.unread_count !== undefined) {
                        refreshUnreadBadge();
                    }
                }
            } catch (e) {
                console.warn('[Notifications] Polling error:', e);
            } finally {
                isPolling = false;
            }
        }, 30000); // 30 seconds
    }
    
    // Stop polling when page unloads
    window.addEventListener('beforeunload', () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    });
    
    function renderList() {
        listEl.innerHTML = '';
        let filtered = allNotifications;
        
        if (currentTab === 'unread') {
            filtered = allNotifications.filter(n => !(n.is_read !== undefined ? n.is_read : isRead(n.id)));
        } else if (currentTab === 'starred') {
            filtered = allNotifications.filter(n => n.is_starred);
        } else if (currentTab === 'archived') {
            filtered = allNotifications.filter(n => n.is_archived);
        }
        
        if (currentTab !== 'archived') {
            filtered = filtered.filter(n => !n.is_archived); // Hide archived from others
        }
        
        if (filtered.length === 0) {
            listEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
        } else {
            listEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');
            filtered.forEach(n => listEl.appendChild(buildCard(n)));
        }
        refreshUnreadBadge();
    }

    // ===== دالة مكشوفة لمحرك الـ Polling لإضافة إشعارات في الأعلى =====
    window.prependNewNotifications = function(newNotifs) {
        if (!listEl) return;
        newNotifs.forEach(n => {
            allNotifications.unshift(n);
        });
        renderList();
    };

    // ابدأ
    loadNotifications();

})();
</script>
@endpush