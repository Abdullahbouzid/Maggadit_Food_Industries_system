@include('dashboard.partials.header')

<body class="nativephp-safe-area" >
    <!-- Layout wrapper -->
  	<div class="wrapper">
                  @include('dashboard.partials.sidebar')

		<div class="main">
		


                              @include('dashboard.partials.navbar')

            <!-- / Menu -->

            <!-- Layout container -->
         
                <!-- Navbar -->
                <!-- / Navbar -->   
			<main class="content">
            <!-- Menu -->

                    @yield('content')
                    <!-- Footer -->


           <!-- / Layout container -->
                </main>
            <!-- / Layout container -->
                    @include('dashboard.partials.footer')
                    <!-- / Footer -->
        </div>

        <!-- / Layout wrapper -->
    </div>
        <!-- / Layout wrapper -->   
  

    @include('dashboard.partials.script')
@stack('scripts')
</body>

</html>