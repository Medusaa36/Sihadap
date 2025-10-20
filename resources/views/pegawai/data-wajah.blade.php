@extends('layout.pegawai')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Tambah Data Wajah Pegawai</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
          <li class="breadcrumb-item active">Tambah Data Wajah Pegawai</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid">
  <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Tambah Data Wajah Pegawai</h3></div>

    <form id="pegawaiForm" action="{{ route('pegawai.createDataWajah', $pegawai->nip) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body">
        
        <div class="form-group">
          <label>Nama</label>
          <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai->nama) }}" readonly>
        </div>

        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai->nip) }}" readonly>
        </div>

        <div class="form-group">
          <button type="button" class="btn btn-primary" id="openCamera">🎥 Ambil / Perbarui Data Wajah</button>
        </div>

        <div class="form-group" id="retakeWrapper" style="display:none;">
          <button type="button" class="btn btn-warning" id="retakeCamera">🔄 Ambil Ulang Data Wajah</button>
        </div>

        {{-- disesuaikan: name="deskriptor" agar cocok dengan controller --}}
        <input type="hidden" name="deskriptor" id="deskriptor" value="{{ old('verifikasi_wajah', $pegawai->verifikasi_wajah) }}">

        <button type="submit" class="btn btn-success mt-3" id="simpanBtn">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<style>
#video {
  transform: scaleX(-1);
}
#overlayCanvas {
  transform: scaleX(-1);
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('models/dist/face-api.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  let video, overlayCanvas, stream;
  const maxPhotos = 5;
  let capturedDescriptors = [];
  let isCapturing = false;
  let stopDetecting = false;

  const tinyFaceOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 192, scoreThreshold: 0.5 });

  Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri('{{ asset("models/weights") }}'),
    faceapi.nets.faceLandmark68Net.loadFromUri('{{ asset("models/weights") }}'),
    faceapi.nets.faceRecognitionNet.loadFromUri('{{ asset("models/weights") }}')
  ]).then(() => {
    document.getElementById('openCamera').disabled = false;
  });

  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
      video.srcObject = stream;
      await video.play();

      overlayCanvas.width = video.videoWidth;
      overlayCanvas.height = video.videoHeight;
      faceapi.matchDimensions(overlayCanvas, { width: video.videoWidth, height: video.videoHeight });

      stopDetecting = false;
      detectFaces();
    } catch (err) {
      Swal.fire('Error', 'Tidak dapat mengakses kamera: ' + err.message, 'error');
    }
  }

  async function detectFaces() {
    if (stopDetecting) return;

    const ctx = overlayCanvas.getContext('2d');
    ctx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

    const detection = await faceapi.detectSingleFace(video, tinyFaceOptions)
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (detection) {
      const box = detection.detection.box;
      ctx.strokeStyle = 'lime';
      ctx.lineWidth = 2;
      ctx.strokeRect(box.x, box.y, box.width, box.height);

      if (!isCapturing && capturedDescriptors.length < maxPhotos) {
        isCapturing = true;
        await captureDescriptor(detection);
      }
    }

    await new Promise(r => setTimeout(r, 300)); 
    requestAnimationFrame(detectFaces);
  }

  async function captureDescriptor(detection) {
    capturedDescriptors.push(Array.from(detection.descriptor));

    const progressBar = Swal.getPopup().querySelector('#captureProgress');
    if (progressBar) progressBar.value = capturedDescriptors.length;

    if (capturedDescriptors.length < maxPhotos) {
      await new Promise(res => setTimeout(res, 1200));
      isCapturing = false;
    } else {
      stopDetecting = true;
      stopCamera();
      document.getElementById('deskriptor').value = JSON.stringify(capturedDescriptors);
      document.getElementById('retakeWrapper').style.display = 'block';
      await Swal.fire('Selesai', 'Semua data wajah berhasil diperbarui!', 'success');
    }
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
    if (video) video.srcObject = null;
  }

  function initCameraCapture() {
    capturedDescriptors = [];
    document.getElementById('deskriptor').value = '';
    stopDetecting = false;

    Swal.fire({
      title: 'Ambil / Perbarui Data Wajah Pegawai',
      html: `
        <div style="text-align:center;">
          <div style="position:relative;width:100%;max-width:480px;margin:0 auto;">
            <video id="video" autoplay muted playsinline style="width:100%;border-radius:5px;"></video>
            <canvas id="overlayCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
          </div>
          <p id="statusText">Mendeteksi wajah...</p>
          <progress id="captureProgress" value="0" max="${maxPhotos}" style="width:100%;margin-top:10px;"></progress><br>
          <button id="cancelBtn" class="btn btn-danger mt-2">Batal</button>
        </div>
      `,
      width: 500,
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        video = Swal.getPopup().querySelector('#video');
        overlayCanvas = Swal.getPopup().querySelector('#overlayCanvas');
        Swal.getPopup().querySelector('#cancelBtn').addEventListener('click', () => {
          stopDetecting = true;
          stopCamera();
          Swal.close();
        });
        startCamera();
      },
      willClose: () => stopCamera()
    });
  }

  document.getElementById('openCamera').addEventListener('click', initCameraCapture);
  document.getElementById('retakeCamera').addEventListener('click', () => {
    document.getElementById('retakeWrapper').style.display = 'none';
    initCameraCapture();
  });

  document.getElementById('openCamera').disabled = true;

  document.getElementById('pegawaiForm').addEventListener('submit', (e) => {
    const wajah = document.getElementById('deskriptor').value.trim();
    if (!wajah) {
      e.preventDefault();
      Swal.fire('Peringatan', 'Data wajah belum diambil!', 'warning');
    }
  });
});
</script>
@endsection
