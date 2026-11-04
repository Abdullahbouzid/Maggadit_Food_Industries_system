@php
    // تحديد العنصر النشط بناءً على اسم الـ route الحالي
    $activeRoute = request()->route()?->getName() ?? '';
@endphp

@push('styles')
<style>
    .bottom-nav-premium {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        will-change: transform;
    }
    .bottom-nav-hidden {
        transform: translateY(100%) !important;
    }
</style>
@endpush

<div class="bottom-nav-premium">
    <a href="{{ route('customer.dashboard', [], false) }}" class="nav-item-premium {{ $activeRoute === 'customer.dashboard' ? 'active' : '' }}" data-nav="home">
        <div class="nav-icon"><i class="fas fa-home"></i></div>
        <span class="nav-text" id="nav-home">Home</span>
        <div class="nav-indicator"></div>
    </a>

    <a href="{{ route('customer.offers', [], false) }}" class="nav-item-premium {{ $activeRoute === 'customer.offers' ? 'active' : '' }}" data-nav="offers">
        <div class="nav-icon"><i class="fas fa-tag"></i></div>
        <span class="nav-text" id="nav-offers">Offers</span>
        <div class="nav-indicator"></div>
    </a>

    <a href="{{ route('customer.cart', [], false) }}" class="nav-item-premium {{ $activeRoute === 'customer.cart' ? 'active' : '' }}" data-nav="cart">
        <div class="nav-icon"><i class="fas fa-shopping-cart"></i></div>
        <span class="nav-text" id="nav-cart">Cart</span>
        <div class="nav-indicator"></div>
    </a>

    <a href="{{ route('customer.favorite', [], false) }}" class="nav-item-premium {{ $activeRoute === 'customer.favorite' ? 'active' : '' }}" data-nav="favorite">
        <div class="nav-icon"><i class="fas fa-heart"></i></div>
        <span class="nav-text" id="nav-favorite">Favorite</span>
        <div class="nav-indicator"></div>
    </a>

    <a href="{{ route('customer.more', [], false) }}" class="nav-item-premium {{ $activeRoute === 'customer.more' ? 'active' : '' }}" data-nav="more">
        <div class="nav-icon"><i class="fas fa-bars"></i></div>
        <span class="nav-text" id="nav-more">More</span>
        <div class="nav-indicator"></div>
    </a>
</div>

@push('scripts')
<script>
    (function () {
        'use strict';
        var bottomNav = document.querySelector('.bottom-nav-premium');
        if (!bottomNav) return;
        var lastScrollY = window.scrollY;
        var ticking = false;
        function onScroll() {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    var currentScrollY = window.scrollY;
                    var diff = currentScrollY - lastScrollY;
                    // threshold of 10px to avoid jitter
                    if (Math.abs(diff) > 10) {
                        if (diff > 0) {
                            // Scrolling down → hide
                            bottomNav.classList.add('bottom-nav-hidden');
                        } else {
                            // Scrolling up → show
                            bottomNav.classList.remove('bottom-nav-hidden');
                        }
                        lastScrollY = currentScrollY;
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
</script>
@endpush
