@php
    // تحديد العنصر النشط بناءً على اسم الـ route الحالي
    $activeRoute = request()->route()?->getName() ?? '';
@endphp

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
