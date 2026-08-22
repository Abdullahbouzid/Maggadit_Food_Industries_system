/**
 * Maggadit Customer App - API Client
 * ====================================
 * يدير جميع الاتصالات مع الـ API ويتعامل مع التوكن
 */

const API = (() => {
    // عنوان الـ API - يتم تحديده تلقائياً أو يدوياً
    // إذا كان التطبيق يعمل على نفس السيرفر، سيستخدم العنوان الحالي
    // إذا كان APK مستقل، غيّر هذا العنوان لعنوان السيرفر الخاص بك على الاستضافة
    let BASE_URL = 'https://blue-beaver-923812.hostingersite.com'; // تم التعديل لربط التطبيق بالاستضافة بدلاً من السيرفر المحلي

    function getBaseUrl() {
        if (BASE_URL) return BASE_URL;

        // محاولة قراءة من localStorage (يمكن للمستخدم تعيينه)
        const saved = localStorage.getItem('api_base_url');
        if (saved) return saved;

        // إذا كان يعمل على نفس السيرفر
        const origin = window.location.origin;
        // تجاهل المسارات المحلية الخاصة بتطبيقات الهاتف (مثل file:// أو http://localhost في Capacitor)
        const isLocalApp = origin.includes('file://') || origin.includes('localhost') || origin.includes('127.0.0.1') || origin.includes('capacitor://');
        
        if (origin && !isLocalApp) {
            return origin;
        }

        // الافتراضي في حالة الـ APK
        // تأكد من وضع رابط الاستضافة الصحيح الخاص بك هنا
        return 'https://blue-beaver-923812.hostingersite.com';
    }

    function setBaseUrl(url) {
        BASE_URL = url;
        localStorage.setItem('api_base_url', url);
    }

    // ===== Token Management =====
    function getToken() {
        return localStorage.getItem('customer_token');
    }

    function setToken(token) {
        localStorage.setItem('customer_token', token);
    }

    function removeToken() {
        localStorage.removeItem('customer_token');
    }

    function isAuthenticated() {
        return !!getToken();
    }

    // ===== Customer Data =====
    function getCustomerData() {
        const data = localStorage.getItem('customer_data');
        return data ? JSON.parse(data) : null;
    }

    function setCustomerData(data) {
        localStorage.setItem('customer_data', JSON.stringify(data));
    }

    function removeCustomerData() {
        localStorage.removeItem('customer_data');
    }

    // ===== HTTP Request Helper =====
    async function request(endpoint, options = {}) {
        const url = `${getBaseUrl()}/api${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers,
        };

        const token = getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers,
            });

            // إذا كان الرد 401 (غير مصرح)، حذف التوكن وإعادة التوجيه لصفحة الدخول
            if (response.status === 401) {
                removeToken();
                removeCustomerData();
                if (!window.location.pathname.includes('index.html') && !window.location.pathname.endsWith('/customer-app/')) {
                    window.location.href = 'index.html';
                }
                throw new Error('غير مصرح - يرجى تسجيل الدخول');
            }

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.message || data.errors?.username?.[0] || 'حدث خطأ غير متوقع';
                throw new Error(errorMsg);
            }

            return data;
        } catch (error) {
            if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
                throw new Error('لا يمكن الاتصال بالسيرفر. تحقق من اتصال الإنترنت.');
            }
            throw error;
        }
    }

    // ===== Auth API =====
    async function login(username, password) {
        const data = await request('/customer/login', {
            method: 'POST',
            body: JSON.stringify({ username, password }),
        });

        if (data.success && data.token) {
            setToken(data.token);
            setCustomerData(data.customer);
        }

        return data;
    }

    async function logout() {
        try {
            await request('/customer/logout', { method: 'POST' });
        } catch (e) {
            // حتى لو فشل الطلب، نحذف التوكن محلياً
        }
        removeToken();
        removeCustomerData();
    }

    async function getProfile() {
        return await request('/customer/profile');
    }

    // ===== Dashboard API =====
    async function getDashboard() {
        return await request('/customer/dashboard');
    }

    // ===== Categories API =====
    async function getCategories(page = 1) {
        return await request(`/customer/categories?page=${page}`);
    }

    async function getCategory(id) {
        return await request(`/customer/categories/${id}`);
    }

    async function getCategoryProducts(id, page = 1) {
        return await request(`/customer/categories/${id}?page=${page}`);
    }

    // ===== Brands API =====
    async function getBrands(page = 1) {
        return await request(`/customer/brands?page=${page}`);
    }

    async function getBrand(id) {
        return await request(`/customer/brands/${id}`);
    }

    async function getBrandProducts(id, page = 1) {
        return await request(`/customer/brands/${id}?page=${page}`);
    }

    // ===== Products API =====
    async function getProducts(page = 1, params = {}) {
        const queryParams = new URLSearchParams({ page, ...params });
        return await request(`/customer/products?${queryParams}`);
    }

    async function getProduct(id) {
        return await request(`/customer/products/${id}`);
    }

    // ===== Favorites API =====
    async function getFavorites() {
        return await request('/customer/favorites');
    }

    async function toggleFavorite(itemId) {
        return await request('/customer/favorites/toggle', {
            method: 'POST',
            body: JSON.stringify({ item_id: itemId }),
        });
    }

    // ===== Invoices (Orders) API =====
    async function getInvoices(page = 1) {
        return await request(`/customer/invoices?page=${page}`);
    }

    async function getInvoice(id) {
        return await request(`/customer/invoices/${id}`);
    }

    // ===== Privacy Policy API =====
    async function getPrivacyPolicy() {
        return await request('/customer/privacy-policy');
    }

    async function getTermsOfService() {
        return await request('/customer/terms-of-service');
    }

    // ===== Offers API =====
    async function getOffers() {
        return await request('/api/customer/offers');
    }

    // ===== Contact API =====
    async function getContactInfo(organizationId) {
        const url = organizationId ? `/api/customer/contact-info?organization_id=${organizationId}` : '/api/customer/contact-info';
        return await request(url);
    }

    async function sendContactMessage(data, organizationId) {
        const url = organizationId ? `/api/customer/contact-us?organization_id=${organizationId}` : '/api/customer/contact-us';
        return await request(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // ===== Public API =====
    return {
        getBaseUrl,
        setBaseUrl,
        getToken,
        isAuthenticated,
        getCustomerData,
        login,
        logout,
        getProfile,
        getDashboard,
        getCategories,
        getCategory,
        getCategoryProducts,
        getBrands,
        getBrand,
        getBrandProducts,
        getProducts,
        getProduct,
        getFavorites,
        toggleFavorite,
        getInvoices,
        getInvoice,
        getPrivacyPolicy,
        getTermsOfService,
        getOffers,
        getContactInfo,
        sendContactMessage,
    };
})();
