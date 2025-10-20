<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SI HADAP - Data Pegawai</title>
  <link rel="icon" type="image/png" href="{{ asset('master/images/logokumham.jpg') }}">
  <link rel="stylesheet" href="{{ asset('master/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/jsgrid/jsgrid.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/jsgrid/jsgrid-theme.min.css') }}">

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  @include('navbar.index')
  
  @include('preloader.index')

  @include('sidebar.pegawai')

  <div class="content-wrapper">
    @yield('content')
  </div>

  {{-- Footer --}}
  <footer class="main-footer">
    <strong>&copy; 2025 SI HADAP</strong> - All rights reserved.
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('master/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/dist/js/adminlte.min.js') }}"></script>
<script src=".{{ asset('master/plugins/jsgrid/demos/db.js') }}"></script>
<script src="{{ asset('master/plugins/jsgrid/jsgrid.min.js') }}"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
@stack('scripts')
</body>
</html>
