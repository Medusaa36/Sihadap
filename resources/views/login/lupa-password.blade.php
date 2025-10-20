<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupa Password | SI HADAP</title>

  <link rel="icon" type="image/png" href="{{ asset('master/images/logokumham.jpg') }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('master/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('master/dist/css/adminlte.min.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('models/dist/face-api.min.js') }}"></script>

  <style>
    body.login-page {
      background: url("{{ asset('master/images/logokumham.jpg') }}") repeat;
      background-size: 100px 100px;
    }
    #kameraSection { display: none; text-align: center; }
    video { border-radius: 8px; width: 100%; max-width: 400px; transform: scaleX(-1); }
    canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); }
    #pegawaiInfo { display: none; color: white; background: rgba(0,0,0,0.6); padding: 6px; border-radius: 5px; margin-top: 10px; }
    #passwordForm { display: none; }
    .eye-icon {
      cursor: pointer;
      color: #6c757d;
    }
  </style>
</head>

<body class="hold-transition login-page">

<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>SI</b>HADAP</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">🔐 Lupa Password Admin</p>

      <div id="nipSection">
        <div class="input-group mb-3">
          <input type="text" id="nip" class="form-control" placeholder="Masukkan NIP Anda" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-id-card"></span></div>
          </div>
        </div>
        <button id="cekNipBtn" class="btn btn-primary btn-block">Cek NIP</button>
      </div>

      <div id="kameraSection" class="mt-3">
        <h6 class="text-muted">Verifikasi wajah sedang berjalan...</h6>
        <div style="position: relative; display: inline-block;">
          <video id="video" autoplay muted playsinline></video>
          <canvas id="overlay"></canvas>
        </div>
        <div id="pegawaiInfo" class="mt-2">
          <p id="pegawaiNama"></p>
          <p id="pegawaiNip"></p>
        </div>
        <p id="status" class="mt-2 text-secondary fw-bold">Menunggu deteksi wajah...</p>
      </div>

      <form id="passwordForm" action="{{ route('password.reset') }}" method="POST">
        @csrf
        <input type="hidden" id="nipHidden" name="nip">
        <div class="input-group mb-3 mt-3">
          <input type="password" id="password" name="password" class="form-control" placeholder="Password Baru" required>
          <div class="input-group-append">
            <div class="input-group-text eye-icon"><i class="fas fa-eye" id="togglePassword"></i></div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
          <div class="input-group-append">
            <div class="input-group-text eye-icon"><i class="fas fa-eye" id="toggleConfirmPassword"></i></div>
          </div>
        </div>
        <button type="submit" class="btn btn-success btn-block">Ubah Password</button>
      </form>

      <div class="mt-3 text-center">
        <a href="{{ route('login.index') }}">← Kembali ke Login</a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('master/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('master/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/dist/js/adminlte.min.js') }}"></script>

<script>
$(document).ready(() => {
  const video = document.getElementById('video');
  const overlay = document.getElementById('overlay');
  const ctx = overlay.getContext('2d');
  const statusText = document.getElementById('status');
  const pegawaiInfo = document.getElementById('pegawaiInfo');
  const pegawaiNama = document.getElementById('pegawaiNama');
  const pegawaiNip = document.getElementById('pegawaiNip');
  let stream = null;

  $('#cekNipBtn').on('click', function() {
    const nip = $('#nip').val().trim();
    if (!nip) {
      Swal.fire('Perhatian', 'Masukkan NIP terlebih dahulu!', 'warning');
      return;
    }

    $.post("{{ route('password.checkAdmin') }}", { nip, _token: "{{ csrf_token() }}" }, function(res) {
      if (!res.success) {
        Swal.fire('Gagal', res.message, 'error');
        return;
      }

      Swal.fire({
        title: 'Konfirmasi NIP',
        html: `<p>NIP: <b>${res.nip}</b><br>Nama: <b>${res.nama}</b></p>Apakah data ini benar?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, benar',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $('#nipSection').hide();
          $('#kameraSection').show();
          $('#nipHidden').val(res.nip);
          mulaiVerifikasiWajah(res.nip);
        }
      });
    });
  });

  async function mulaiVerifikasiWajah(nip) {
    try {
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('{{ asset("models/weights") }}'),
        faceapi.nets.faceLandmark68Net.loadFromUri('{{ asset("models/weights") }}'),
        faceapi.nets.faceRecognitionNet.loadFromUri('{{ asset("models/weights") }}')
      ]);

      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
      video.srcObject = stream;
      await video.play();

      overlay.width = video.videoWidth;
      overlay.height = video.videoHeight;

      statusText.innerText = "🎥 Kamera aktif, memindai wajah...";
      detectLoop(nip);
    } catch (err) {
      Swal.fire('Error', 'Tidak dapat mengakses kamera: ' + err.message, 'error');
      kembaliKeNipForm();
    }
  }

  async function detectLoop(nip) {
    const detectionOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.3 });
    const detections = await faceapi
      .detectAllFaces(video, detectionOptions)
      .withFaceLandmarks()
      .withFaceDescriptors();

    ctx.clearRect(0, 0, overlay.width, overlay.height);

    if (detections.length > 0) {
      const resized = faceapi.resizeResults(detections, { width: overlay.width, height: overlay.height });
      const descriptor = Array.from(resized[0].descriptor);

      $.ajax({
        url: "{{ route('password.verifikasiWajah') }}",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          nip: nip,
          descriptor: descriptor
        },
        success: function(res) {
          if (res.success) {
            stopKamera();
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'NIP dan wajah sesuai. Silakan ganti password baru.'
            }).then(() => {
              $('#kameraSection').hide();
              $('#passwordForm').show();
            });
          } else {
            stopKamera();
            Swal.fire({
              icon: 'error',
              title: 'Verifikasi Gagal!',
              text: res.message
            }).then(() => {
              kembaliKeNipForm();
            });
          }
        },
        error: function() {
          stopKamera();
          Swal.fire('Error', 'Terjadi kesalahan saat verifikasi.', 'error');
          kembaliKeNipForm();
        }
      });
    } else {
      statusText.innerText = "⏳ Menunggu wajah...";
      requestAnimationFrame(() => detectLoop(nip));
    }
  }

  function stopKamera() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
  }

  function kembaliKeNipForm() {
    $('#kameraSection').hide();
    $('#passwordForm').hide();
    $('#nipSection').show();
    $('#nip').val('');
    statusText.innerText = "Menunggu deteksi wajah...";
  }

  // 👁️ Toggle show/hide password
  $('#togglePassword').on('click', function() {
    const input = $('#password');
    const icon = $(this);
    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  $('#toggleConfirmPassword').on('click', function() {
    const input = $('#password_confirmation');
    const icon = $(this);
    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });
});
</script>
</body>
</html>
