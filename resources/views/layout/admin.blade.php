<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SI HADAP - Data Admin</title>
  <link rel="icon" type="image/png" href="{{ asset('master/images/logokumham.jpg') }}">
  <link rel="stylesheet" href="{{ asset('master/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/jsgrid/jsgrid.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/jsgrid/jsgrid-theme.min.css') }}">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  @include('navbar.index')
  
  @include('preloader.index')

  @include('sidebar.admin')

  <div class="content-wrapper">
    @yield('content')
  </div>

  <footer class="main-footer">
    <strong>&copy; 2025 SI HADAP</strong> - All rights reserved.
  </footer>
</div>

<script src="{{ asset('master/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="{{ asset('master/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('master/plugins/jsgrid/demos/db.js') }}"></script>
<script src="{{ asset('master/plugins/jsgrid/jsgrid.min.js') }}"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
  $(document).ready(function(){

    $("#nama").autocomplete({
      minLength: 0, 
      source: function(request, response){
        $.ajax({
          url: "{{ route('pegawai.searchByName') }}",
          type: "GET",
          data: { term: request.term },
          success: function(data){
            response($.map(data, function(item){
              return {
                label: item.nama,
                value: item.nama,
                nip: item.nip
              }
            }));
          }
        });
      },
      select: function(event, ui){
        $("#nip").val(ui.item.nip);
      }
    }).focus(function () {
      $(this).autocomplete("search", ""); 
    });

    $("#nip").autocomplete({
      minLength: 0,
      source: function(request, response){
        $.ajax({
          url: "{{ route('pegawai.searchByNip') }}",
          type: "GET",
          data: { term: request.term },
          success: function(data){
            response($.map(data, function(item){
              return {
                label: item.nip,
                value: item.nip,
                nama: item.nama
              }
            }));
          }
        });
      },
      select: function(event, ui){
        $("#nama").val(ui.item.nama);
      }
    }).focus(function () {
      $(this).autocomplete("search", "");
    });

  });

</script>

@stack('scripts')
</body>
</html>
