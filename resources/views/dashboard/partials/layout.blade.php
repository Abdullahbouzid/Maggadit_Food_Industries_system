@include('dashboard.partials.header')

<body class="nova-app">
    <div class="nova-shell">
        <!-- Sidebar Navigation -->
        @include('dashboard.partials.sidebar')

        <!-- Main Content Area -->
        <div class="nova-content-wrapper">
            @include('dashboard.partials.navbar')

            <main class="nova-main">
                @yield('content')
            

    <!-- Modals for Data Definitions & Customer App Properties -->
    @include('dashboard.partials.modals')

    @stack('scripts')
</body>

</html>
