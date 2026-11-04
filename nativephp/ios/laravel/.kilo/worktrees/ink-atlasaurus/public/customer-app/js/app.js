/**
 * Maggadit Customer App - Main Application Logic
 * =================================================
 * دوال مساعدة مشتركة بين جميع الصفحات
 */

const App = (() => {

    // ===== Toast Notification =====
    function showToast(message, type = 'info') {
        let toast = document.getElementById('app-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'app-toast';
            toast.className = 'toast';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.className = 'toast';
        if (type === 'error') toast.classList.add('error');
        if (type === 'success') toast.classList.add('success');

        // Force reflow
        void toast.offsetWidth;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ===== Loading Overlay =====
    function showLoading() {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="loading-spinner"></div>';
            document.body.appendChild(overlay);
        }
        overlay.classList.remove('hidden');
    }

    function hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
    }

    // ===== Auth Guard =====
    function requireAuth() {
        if (!API.isAuthenticated()) {
            window.location.href = 'index.html';
            return false;
        }
        return true;
    }

    // ===== Navigation =====
    function navigate(page, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${page}?${queryString}` : page;
        window.location.href = url;
    }

    function getQueryParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    function goBack() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            navigate('dashboard.html');
        }
    }

    // ===== Render Bottom Navigation =====
    function renderBottomNav(activePage) {
        const navItems = [
            { id: 'home', icon: '🏠', label: 'الرئيسية', page: 'dashboard.html' },
            { id: 'categories', icon: '📂', label: 'الفئات', page: 'categories.html' },
            { id: 'products', icon: '📦', label: 'المنتجات', page: 'products.html' },
            { id: 'brands', icon: '🏷️', label: 'الماركات', page: 'brands.html' },
            { id: 'more', icon: '⋯', label: 'المزيد', page: '#' },
        ];

        const nav = document.createElement('nav');
        nav.className = 'bottom-nav';

        navItems.forEach(item => {
            const div = document.createElement('div');
            div.className = `nav-item ${item.id === activePage ? 'active' : ''}`;
            div.innerHTML = `
                <span class="nav-icon">${item.icon}</span>
                <span class="nav-label">${item.label}</span>
            `;
            if (item.id === 'more') {
                div.addEventListener('click', () => {
                    showToast('قريباً...', 'info');
                });
            } else {
                div.addEventListener('click', () => navigate(item.page));
            }
            nav.appendChild(div);
        });

        document.querySelector('.app-container').appendChild(nav);
    }

    // ===== Render Header =====
    function renderHeader(title, showBack = false, actions = '') {
        const header = document.createElement('header');
        header.className = 'app-header';
        header.innerHTML = `
            ${showBack ? '<button class="header-back" onclick="App.goBack()">→</button>' : '<div></div>'}
            <h1 class="header-title">${title}</h1>
            <div class="header-actions">${actions}</div>
        `;
        return header;
    }

    // ===== Render Product Card =====
    function renderProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.addEventListener('click', () => navigate('product-details.html', { id: product.id }));

        card.innerHTML = `
            <div class="product-image">
                ${product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name_en}" loading="lazy">`
                    : '<span class="placeholder">📦</span>'
                }
                ${product.is_new ? '<span class="product-badge">جديد</span>' : ''}
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name_ar || product.name_en}</h3>
                <div class="product-rating">
                    <span class="star">★</span>
                    <span class="value">${(product.rate || 0).toFixed(1)}</span>
                </div>
                <div class="product-footer">
                    <div>
                        <span class="product-price">${formatPrice(product.price)}</span>
                        ${product.old_price ? `<span class="product-old-price">${formatPrice(product.old_price)}</span>` : ''}
                    </div>
                    <button class="btn-buy" onclick="event.stopPropagation(); App.showToast('تمت الإضافة للسلة ✓', 'success')">شراء</button>
                </div>
            </div>
        `;

        return card;
    }

    // ===== Render Category Card =====
    function renderCategoryCard(category, size = 'small') {
        const card = document.createElement('div');
        card.className = size === 'small' ? 'category-card' : 'list-card';
        card.addEventListener('click', () => navigate('category-details.html', { id: category.id }));

        if (size === 'small') {
            card.innerHTML = `
                <div class="category-avatar">
                    ${category.image_url
                        ? `<img src="${category.image_url}" alt="${category.name}">`
                        : '<span class="emoji">📁</span>'
                    }
                </div>
                <p class="category-name">${category.name}</p>
            `;
        } else {
            card.innerHTML = `
                <div class="list-card-image">
                    ${category.image_url
                        ? `<img src="${category.image_url}" alt="${category.name}">`
                        : '<span class="emoji">📁</span>'
                    }
                </div>
                <p class="list-card-name">${category.name}</p>
            `;
        }

        return card;
    }

    // ===== Render Brand Card =====
    function renderBrandCard(brand, size = 'small') {
        const card = document.createElement('div');
        card.className = size === 'small' ? 'brand-card' : 'list-card';
        card.addEventListener('click', () => navigate('brand-details.html', { id: brand.id }));

        if (size === 'small') {
            card.innerHTML = `
                <div class="brand-logo">
                    ${brand.logo_url
                        ? `<img src="${brand.logo_url}" alt="${brand.name}">`
                        : '<span class="emoji">🏷️</span>'
                    }
                </div>
                <p class="brand-name">${brand.name}</p>
            `;
        } else {
            card.innerHTML = `
                <div class="list-card-image">
                    ${brand.logo_url
                        ? `<img src="${brand.logo_url}" alt="${brand.name}" style="object-fit: contain; padding: 8px;">`
                        : '<span class="emoji">🏷️</span>'
                    }
                </div>
                <p class="list-card-name">${brand.name}</p>
            `;
        }

        return card;
    }

    // ===== Render Pagination =====
    function renderPagination(paginationData, onPageClick) {
        if (!paginationData || paginationData.last_page <= 1) return null;

        const container = document.createElement('div');
        container.className = 'pagination';

        // Previous
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.textContent = '←';
        prevBtn.disabled = paginationData.current_page <= 1;
        prevBtn.addEventListener('click', () => onPageClick(paginationData.current_page - 1));
        container.appendChild(prevBtn);

        // Page info
        const info = document.createElement('span');
        info.style.cssText = 'font-size: 0.8rem; color: var(--text-secondary);';
        info.textContent = `${paginationData.current_page} / ${paginationData.last_page}`;
        container.appendChild(info);

        // Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.textContent = '→';
        nextBtn.disabled = paginationData.current_page >= paginationData.last_page;
        nextBtn.addEventListener('click', () => onPageClick(paginationData.current_page + 1));
        container.appendChild(nextBtn);

        return container;
    }

    // ===== Render Empty State =====
    function renderEmptyState(emoji, message) {
        const div = document.createElement('div');
        div.className = 'empty-state';
        div.innerHTML = `
            <div class="emoji">${emoji}</div>
            <p>${message}</p>
        `;
        return div;
    }

    // ===== Helpers =====
    function formatPrice(price) {
        if (!price && price !== 0) return '';
        return `${Number(price).toLocaleString()} ر.س`;
    }

    // ===== Carousel =====
    function initCarousel(carouselEl, dotsEl) {
        if (!carouselEl || !dotsEl) return;

        const dots = dotsEl.querySelectorAll('.carousel-dot');
        let autoScrollInterval = null;
        let isInteracting = false;
        let interactionTimeout = null;

        function updateDots() {
            const pos = carouselEl.scrollLeft;
            const width = carouselEl.clientWidth;
            if (width === 0) return;
            const active = Math.round(pos / width);
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === active);
            });
        }

        function scrollTo(index) {
            const width = carouselEl.clientWidth;
            if (width === 0) return;
            carouselEl.scrollTo({ left: index * width, behavior: 'smooth' });
        }

        function autoScroll() {
            if (isInteracting) return;
            const width = carouselEl.clientWidth;
            if (width === 0) return;
            const current = Math.round(carouselEl.scrollLeft / width);
            const next = (current + 1) % dots.length;
            scrollTo(next);
        }

        function pause() {
            isInteracting = true;
            if (interactionTimeout) clearTimeout(interactionTimeout);
            interactionTimeout = setTimeout(() => { isInteracting = false; }, 5000);
        }

        carouselEl.addEventListener('scroll', () => { updateDots(); pause(); });
        carouselEl.addEventListener('touchstart', pause);

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => { scrollTo(i); pause(); });
        });

        updateDots();
        autoScrollInterval = setInterval(autoScroll, 4000);

        return () => clearInterval(autoScrollInterval);
    }

    return {
        showToast,
        showLoading,
        hideLoading,
        requireAuth,
        navigate,
        getQueryParam,
        goBack,
        renderBottomNav,
        renderHeader,
        renderProductCard,
        renderCategoryCard,
        renderBrandCard,
        renderPagination,
        renderEmptyState,
        formatPrice,
        initCarousel,
    };
})();
