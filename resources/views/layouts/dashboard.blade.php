<!doctype html>
<html lang="en" class="light-theme">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />
  <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/light-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <title>@yield('title','Dashboard')</title>
  @stack('styles')
</head>
<body>
  <div class="wrapper d-flex flex-column min-vh-100">
    @include('components.dashboard-header')
    @include('components.dashboard-sidebar')


        @yield('content')


      @include('components.dashboard-footer')

  </div>
  <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/index.js') }}"></script>
  @stack('scripts')
</body>
</html>
