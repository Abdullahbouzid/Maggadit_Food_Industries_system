



<body class="nativephp-safe-area" >
  <main class="px-4 py-3 space-y-6">
        @yield('content')
    </main>
    
    @include('app_android.partials.mobile.bottom_nav')
    @include('app_android.partials.mobile.scripts')
    @stack('scripts')
</body>

</html>