
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Maggadit')</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: #f8fafc;
            padding-bottom: 70px;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 8px 16px;
            display: flex;
            justify-content: space-around;
            z-index: 20;

            
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #94a3b8;
            transition: 0.2s;
            font-size: 12px;

            
        }
        .nav-item i {
            font-size: 22px;
            margin-bottom: 4px;
        }
        .nav-item.active {
            color: #9879b6;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }
        .more-link {
            color: #9879b6;
            font-weight: 500;
            font-size: 14px;
        }
        .brand-chip {
            background: white;
            padding: 8px 16px;
            border-radius: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            font-weight: 500;
            white-space: nowrap;
        }
        .category-card {
            background: white;
            border-radius: 16px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .product-card {
            background: white;
            border-radius: 20px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .heart-icon {
            cursor: pointer;
            transition: 0.2s;
        }
        .heart-icon.liked {
            color: #ef4444;
        }
        .lang-switch {
            background: white;
            padding: 6px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #9879b6;
            border: 1px solid #e2e8f0;
            cursor: pointer;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* ========== BOTTOM NAVIGATION PREMIUM ========== */
.bottom-nav-premium {
    position: fixed;
    bottom: 16px;
    left: 16px;
    right: 16px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(20px);
    border-radius: 40px;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.02);
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 12px;
    z-index: 30;
    border: 1px solid rgba(152, 121, 182, 0.2);
    transition: all 0.3s ease;
}

.nav-item-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    color: #94a3b8;
    position: relative;
    flex: 1;
    padding: 8px 0;
    border-radius: 32px;
}

.nav-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 28px;
    transition: all 0.25s ease;
    position: relative;
}

.nav-item-premium i {
    font-size: 22px;
    transition: transform 0.2s ease, color 0.2s;
}

.nav-text {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.3px;
    transition: all 0.2s;
    opacity: 0.8;
}

.nav-indicator {
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 24px;
    height: 3px;
    background: #9879b6;
    border-radius: 4px;
    transition: transform 0.25s ease;
}

/* Active state */
.nav-item-premium.active {
    color: #9879b6;
    transform: translateY(-4px);
}

.nav-item-premium.active .nav-icon {
    background: rgba(152, 121, 182, 0.12);
    transform: scale(0.98);
}

.nav-item-premium.active i {
    transform: scale(1.1);
    text-shadow: 0 2px 6px rgba(152, 121, 182, 0.3);
}

.nav-item-premium.active .nav-text {
    font-weight: 600;
    opacity: 1;
}

.nav-item-premium.active .nav-indicator {
    transform: translateX(-50%) scaleX(1);
}

/* Hover effect (for desktop/touch) */
.nav-item-premium:not(.active):hover {
    color: #9879b6;
    transform: translateY(-2px);
}

.nav-item-premium:not(.active):hover .nav-icon {
    background: rgba(152, 121, 182, 0.06);
}

/* RTL support */
[dir="rtl"] .bottom-nav-premium {
    direction: rtl;
}

/* Adjust for devices with notches / safe area */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .bottom-nav-premium {
        bottom: calc(16px + env(safe-area-inset-bottom));
    }
    body {
        padding-bottom: calc(70px + env(safe-area-inset-bottom));
    }
}
    </style>
    @stack('styles')
</head>
<!-- Header with location and language switch -->
<div class="sticky top-0 bg-white/90 backdrop-blur-sm z-10 border-b border-gray-100" style=" border-radius: 20px 20px" >
    <div class="flex justify-between items-center px-5 py-4">
        <div>
            <h1 id="location-title" class="text-xl font-bold text-gray-800">شعبية طرابلس</h1>
            <p id="welcome-text" class="text-xs text-gray-500">مرحباً، ضيفنا العزيز</p>
        </div>
            <div id="lang-switch" class="lang-switch">🇱🇾 العربية</div>
    </div>
</div>