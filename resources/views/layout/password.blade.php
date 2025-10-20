<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SI HADAP - Dashboard</title>
  <link rel="icon" type="image/png" href="{{ asset('master/images/logokumham.jpg') }}">
  <link rel="stylesheet" href="{{ asset ('master/plugins/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('master/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/fontawesome-free/css/all.min.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  @include('navbar.index')
  
  @include('preloader.index')

  @include('sidebar.password')

  <div class="content-wrapper">
    @yield('content')
  </div>

  <footer class="main-footer">
    <strong>&copy; 2025 SI HADAP</strong> - All rights reserved.
  </footer>
</div>

<script src="{{ asset('master/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('master/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('master/plugins/chart.js/Chart.bundle.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('master/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/dist/js/adminlte.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
