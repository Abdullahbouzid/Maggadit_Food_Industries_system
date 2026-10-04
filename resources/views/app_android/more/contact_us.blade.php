@extends('app_android.partials.mobile.app_mobile')
@section('title', 'التواصل معنا')
@section('content')
    @include('app_android.partials.mobile.header', ['title' => 'التواصل معنا'])
    
    <div class="p-4" dir="rtl">
        <button onclick="window.history.back()" 
                class="flex items-center gap-2 text-purple-600 bg-purple-50 px-4 py-2 rounded-full text-sm font-semibold transition hover:bg-purple-100 mb-5">
            <i class="fas fa-arrow-right"></i> 
            <span>رجوع</span>
        </button>

        <div id="contact-info-container">
            <div class="grid grid-cols-1 gap-4 mb-6">
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                        <i class="fas fa-phone-alt text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">اتصل بنا</p>
                        <p class="font-semibold text-gray-800" id="contact-phone">جاري التحميل...</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                        <i class="fas fa-envelope text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">بريد إلكتروني</p>
                        <p class="font-semibold text-gray-800" id="contact-email">جاري التحميل...</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">واتساب</p>
                        <p class="font-semibold text-gray-800" id="contact-whatsapp">جاري التحميل...</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-5 mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-paper-plane text-purple-600"></i>
                    <span>أرسل رسالتك</span>
                </h3>
                <p class="text-sm text-gray-500 mb-4">سنقوم بالرد عليك في أقرب وقت ممكن</p>
                <form id="contact-form" action="{{ route('customer.proxy.contact_submit_proxy', [], false) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم الكامل <span class="text-danger">*</span></label>
                            <input type="text" id="contact-name" name="name" placeholder="أدخل اسمك" required
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">رقم الهاتف</label>
                            <input type="tel" id="contact-phone-input" name="phone" placeholder="رقم هاتفك"
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" id="contact-email-input" name="email" placeholder="بريدك الإلكتروني" required
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">الموضوع</label>
                            <input type="text" id="contact-subject" name="subject" placeholder="موضوع الرسالة"
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">الرسالة <span class="text-danger">*</span></label>
                            <textarea id="contact-message" name="message" rows="4" placeholder="اكتب رسالتك هنا..." required
                                      class="w-full p-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition"></textarea>
                        </div>
                        <button type="submit" id="contact-submit-btn"
                                class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition transform hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane ml-2"></i> <span id="contact-btn-text">إرسال الرسالة</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-3 text-center" id="contact-address-container" style="display:none;">
                <p class="text-sm text-gray-500 mb-1">📍 موقعنا</p>
                <p class="text-gray-700 font-semibold" id="contact-address"></p>
                <div id="contact-map" style="height: 350px; border-radius: 8px; margin-top: 8px; display: none;"></div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    
  

    <div id="contact-toast" 
         class="fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-5 py-3 rounded-full shadow-lg text-sm z-50 transition-all duration-300 opacity-0 translate-y-10 pointer-events-none">
    </div>
    
    <script>
        const toast = document.getElementById('contact-toast');
        function showToast(msg, isError = false) {
            toast.innerText = msg;
            toast.style.background = isError ? '#dc2626' : '#1f2937';
            toast.classList.remove('opacity-0', 'translate-y-10');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const orgId = localStorage.getItem('active_organization_id');
            const url = orgId
                ? `{{ route('customer.contact.info', [], false) }}?organization_id=${encodeURIComponent(orgId)}`
                : `{{ route('customer.contact.info', [], false) }}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'include',
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data) {
                        const d = res.data;
                        const phoneEl = document.getElementById('contact-phone');
                        const emailEl = document.getElementById('contact-email');
                        const waEl = document.getElementById('contact-whatsapp');
                        const addrEl = document.getElementById('contact-address');
                        const addrContainer = document.getElementById('contact-address-container');

                        if (d.phone) { phoneEl.innerText = d.phone; phoneEl.parentElement.style.display = ''; }
                        if (d.email) { emailEl.innerText = d.email; emailEl.parentElement.style.display = ''; }
                        if (d.whatsapp) { waEl.innerText = d.whatsapp; waEl.parentElement.style.display = ''; }
                        if (d.address) { addrEl.innerText = d.address; addrContainer.style.display = ''; }
                        if (d.latitude && d.longitude) {
                            const mapEl = document.getElementById('contact-map');
                            mapEl.style.display = '';
                            setTimeout(() => {
                                const cMap = L.map('contact-map').setView([parseFloat(d.latitude), parseFloat(d.longitude)], 15);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap',
                                    maxZoom: 19,
                                }).addTo(cMap);
                                L.marker([parseFloat(d.latitude), parseFloat(d.longitude)]).addTo(cMap);
                            }, 100);
                        }

                        if (!d.phone && !d.email && !d.whatsapp && !d.address) {
                            document.getElementById('contact-info-container').innerHTML = '<p class="text-center text-gray-400 py-4">لا توجد معلومات تواصل متاحة حالياً</p>';
                        }
                    }
                })
                .catch(() => {
                    document.getElementById('contact-phone').innerText = 'غير متاح';
                    document.getElementById('contact-email').innerText = 'غير متاح';
                    document.getElementById('contact-whatsapp').innerText = 'غير متاح';
                });
        });

        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('contact-submit-btn');
            const btnText = document.getElementById('contact-btn-text');
            const name = document.getElementById('contact-name').value.trim();
            const phone = document.getElementById('contact-phone-input').value.trim();
            const email = document.getElementById('contact-email-input').value.trim();
            const subject = document.getElementById('contact-subject').value.trim();
            const message = document.getElementById('contact-message').value.trim();
            const token = document.querySelector('input[name="_token"]').value;

            if (!name || !email || !message) {
                showToast('❌ الرجاء ملء جميع الحقول المطلوبة', true);
                return;
            }
            if (!email.includes('@')) {
                showToast('❌ البريد الإلكتروني غير صحيح', true);
                return;
            }

            btn.disabled = true;
            btnText.innerText = 'جاري الإرسال...';

            const orgId = localStorage.getItem('active_organization_id');
            const contactUrl = orgId
                ? `{{ route('customer.proxy.contact_submit_proxy', [], false) }}?organization_id=${encodeURIComponent(orgId)}`
                : `{{ route('customer.proxy.contact_submit_proxy', [], false) }}`;

            fetch(contactUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify({ name, email, phone, subject, message }),
            })
            .then(r => r.json().catch(() => ({ success: false, message: 'فشل الإرسال (' + r.status + ')' })))
            .then(res => {
                if (res.success) {
                    showToast('✅ ' + (res.message || 'تم إرسال رسالتك بنجاح'));
                    document.getElementById('contact-form').reset();
                } else {
                    let msg = res.message || 'فشل الإرسال';
                    if (res.errors) {
                        const first = Object.values(res.errors)[0];
                        if (Array.isArray(first) && first.length) msg = first[0];
                    }
                    showToast('❌ ' + msg, true);
                }
            })
            .catch(() => showToast('❌ حدث خطأ في الاتصال', true))
            .finally(() => {
                btn.disabled = false;
                btnText.innerText = 'إرسال الرسالة';
            });
        });
    </script>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
