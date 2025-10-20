<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SI HADAP KANWIL KEMENKUM KEPRI</title>

  <link rel="icon" type="image/png" href="{{ asset('master/images/logokumham.jpg') }}">
  <style>
      body.login-page {
          background: url("{{ asset('master/images/logokumham.jpg') }}") repeat;
          background-size: 100px 100px; 
      }
  </style>
    
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('master/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page">

<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>SI</b>HADAP</a>
    </div>
    <div class="card-body">
      <h5 class="login-box-msg">Sistem Kehadiran Apel Pagi Kanwil Kemenkum Kepri</h5>

      <form action="{{ route('login.proses') }}" method="post">
        @csrf
        <div class="input-group mb-3">
          <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                 placeholder="Masukkan NIP Anda"
                 required autofocus>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
          @error('nip')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="input-group mb-3">
          <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                 placeholder="Masukkan Password Anda" required aria-label="Password">
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" id="togglePasswordBtn" onclick="togglePassword()" tabindex="-1" title="Tampilkan / Sembunyikan Password">
              <i id="togglePasswordIcon" class="fas fa-eye"></i>
            </button>
          </div>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="row mt-2">
          <div class="col-12 text-center">
            <a href="{{ route('password.lupa') }}">Lupa Password?</a>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('master/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/dist/js/adminlte.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function showToast(message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#fff',
            color: type === 'success' ? '#155724' : '#721c24',
            iconColor: type === 'success' ? '#28a745' : '#dc3545'
        });
    }

    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif

    @if($errors->any())
        @foreach ($errors->all() as $error)
            showToast("{{ $error }}", 'error');
        @endforeach
    @endif
</script>

</body>
</html>
