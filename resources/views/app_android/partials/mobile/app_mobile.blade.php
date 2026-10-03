

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Maggadit')</title>
    @vite(['resources/css/app.css'])
    {{-- Tailwind is compiled into app.css via Vite. Loading cdn.tailwindcss.com
         on top of it duplicated the framework and left every screen unstyled
         whenever the packaged app had no network access. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; padding-bottom: 70px; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            body { padding-bottom: calc(70px + env(safe-area-inset-bottom)); }
        }
    </style>
    @stack('styles')
</head>
<body class="nativephp-safe-area" >
  <main class="px-4 py-3 space-y-6">
        @yield('content')
    </main>
    
    @include('app_android.partials.mobile.bottom_nav')
    @include('app_android.partials.mobile.scripts')
    @stack('scripts')
</body>

</html>