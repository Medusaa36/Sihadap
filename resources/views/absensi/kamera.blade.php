@extends('layout.absensi')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Mulai Absensi Otomatis</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></li>
          <li class="breadcrumb-item active">Mulai Absensi</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid text-center">
    <div class="card shadow-lg p-4">

      <div id="formWrapper">
        <h4 class="mb-3">Form Absensi</h4>
        <div class="row justify-content-center mb-4">
          <div class="col-md-6 text-start">
            <div class="form-group">
              <label for="keterangan" class="fw-bold">Kegiatan</label>
              <input type="text" id="keterangan" class="form-control" placeholder="Masukkan Kegiatan absensi">
            </div>
            <button id="mulaiAbsensi" class="btn btn-primary mt-3">🚀 Mulai Verifikasi Wajah / Scan Barcode</button>
          </div>
        </div>
      </div>

      <div id="kameraSection" class="d-none">
        <h4 class="mb-3">Verifikasi Wajah & Scan Barcode</h4>
        <div style="position: relative; width: 100%; max-width: 480px; margin:auto;">
          <video id="video" autoplay muted playsinline class="border border-3 rounded shadow-sm w-100"></video>
          <canvas id="overlay" style="position:absolute; top:0; left:0; width:100%; height:100%;"></canvas>

          <div id="pegawaiData" class="position-absolute w-100 p-2 text-white" 
               style="bottom: 0; left: 0; background: rgba(101,115,139,0.6); display:none; opacity:0; transition: opacity 0.6s;">
            <p id="pegawaiNama" class="mb-1">Nama : </p>
            <p id="pegawaiNip" class="mb-0">NIP  : </p>
          </div>
        </div>

        <div class="mt-3 d-flex justify-content-center gap-2">
          <button id="toggleCamera" class="btn btn-secondary">🔄 Ganti Kamera</button>
          <button id="toggleTorch" class="btn btn-warning">💡 Torch</button>
        </div>

        <div class="mt-2">
          <span id="status" class="fw-bold text-secondary">Menunggu deteksi wajah / barcode...</span>
        </div>
      </div>

    </div>
  </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('models/dist/face-api.min.js') }}"></script>
<script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {
  const video = document.getElementById('video');
  const overlay = document.getElementById('overlay');
  const ctx = overlay.getContext('2d');
  const mulaiBtn = document.getElementById('mulaiAbsensi');
  const toggleBtn = document.getElementById('toggleCamera');
  const torchBtn = document.getElementById('toggleTorch');
  const kameraSection = document.getElementById('kameraSection');
  const statusText = document.getElementById('status');
  const pegawaiDataDiv = document.getElementById('pegawaiData');
  const pegawaiNama = document.getElementById('pegawaiNama');
  const pegawaiNip = document.getElementById('pegawaiNip');
  const formWrapper = document.getElementById('formWrapper');

  let successSound = null;
  let failSound = null;
  let pegawaiDescriptors = [];
  let keterangan = "";
  let stream = null;
  let facingMode = 'environment';
  let detectionActive = false;
  let torchOn = false;
  let lastMatchedNip = null;
  let hideTimeout = null;

  const MATCH_THRESHOLD = 0.35;

  function playSound(audioObj){
    if(!audioObj) return;
    try{
      const clone = audioObj.cloneNode();
      clone.volume = 1;
      clone.play().catch(()=>{});
    }catch(e){}
  }

  async function showAlert(icon, title, soundObj=null){ 
    if(soundObj) playSound(soundObj);
    if(Swal.isVisible()) Swal.close();
    await Swal.fire({
      toast:true, position:'center', icon, title,
      showConfirmButton:false, timer:2000, timerProgressBar:true
    });
  }

  function playFailOnce(){
    playSound(failSound);
  }

  function tampilkanPegawai(p){ 
    pegawaiNama.innerText = "Nama : " + p.nama;
    pegawaiNip.innerText = "NIP  : " + p.nip; 
    pegawaiDataDiv.style.display = 'block'; 
    setTimeout(()=>pegawaiDataDiv.style.opacity = 1, 50);

    if(hideTimeout) clearTimeout(hideTimeout);
    hideTimeout = setTimeout(()=>sembunyikanPegawai(), 2000);
  }

  function sembunyikanPegawai(){ 
    pegawaiDataDiv.style.opacity = 0;
    setTimeout(()=>{
      pegawaiDataDiv.style.display = 'none';
      pegawaiNama.innerText = "Nama : ";
      pegawaiNip.innerText = "NIP  : ";
    }, 500);
  }

  function findBestMatch(descriptor){ 
    let best=null, lowest=1;
    for(const p of pegawaiDescriptors){ 
      for(const d of p.descriptors){ 
        const dist = faceapi.euclideanDistance(descriptor, d); 
        if(dist < lowest){ lowest = dist; best = p; }
      }
    } 
    return best ? {best, distance:lowest} : null; 
  }

  async function prosesAbsensi(nip, nama, similarity){
    try{
      const res = await fetch("{{ route('absensi.proses') }}", {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({nip, keterangan, similarity})
      });

      const data = await res.json();

      if(data.already_absent){
        playFailOnce(); 
        await showAlert('error', data.message || `${nama} sudah absen.`, failSound); 
      }
      else if(data.success){
        playSound(successSound); 
        await showAlert('success', data.message || `Absensi ${nama} berhasil.`, successSound); 
      }
      else{
        playFailOnce(); 
        await showAlert('error', data.message || `Gagal mencatat absensi ${nama}.`, failSound);
      }
    }catch(e){
      playFailOnce(); 
      await showAlert('error', 'Kesalahan jaringan: ' + (e.message || e), failSound);
    }
  }

  async function startCamera(){
    try{
      if(stream) stream.getTracks().forEach(t=>t.stop());
      const devices = await navigator.mediaDevices.enumerateDevices();
      const videoDevices = devices.filter(d=>d.kind==='videoinput');
      let deviceId = null;

      if(facingMode==='environment'){
        const backCam = videoDevices.find(d=>d.label.toLowerCase().includes('back')||d.label.toLowerCase().includes('rear'));
        if(backCam) deviceId = backCam.deviceId;
      }

      stream = await navigator.mediaDevices.getUserMedia({
        video: deviceId ? {deviceId:{exact:deviceId}} : {facingMode:{ideal:facingMode},width:{ideal:1280},height:{ideal:720}},
        audio:false
      });

      video.srcObject = stream;
      await video.play();
      overlay.width = video.videoWidth || 640;
      overlay.height = video.videoHeight || 480;

      const isFront = (facingMode==='user');
      video.style.transform = isFront?'scaleX(-1)':'scaleX(1)';
      overlay.style.transform = isFront?'scaleX(-1)':'scaleX(1)';

      try{
        const track = stream.getVideoTracks()[0];
        const caps = track.getCapabilities();
        if(caps.focusMode && caps.focusMode.includes("continuous")){
          await track.applyConstraints({advanced:[{focusMode:"continuous"}]});
        }
        if(caps.exposureMode && caps.exposureMode.includes("continuous")){
          await track.applyConstraints({advanced:[{exposureMode:"continuous"}]});
        }
      }catch(e){ console.warn("Autofocus not supported", e); }

      statusText.innerText = "🎥 Kamera aktif. Menunggu wajah / barcode...";
    }catch(e){
      console.error("Gagal buka kamera:", e);
      await showAlert('error', 'Tidak dapat mengakses kamera. Aktifkan izin & gunakan HTTPS.');
    }
  }

  async function toggleTorch(){
    try{
      const track = stream.getVideoTracks()[0];
      const caps = track.getCapabilities();
      if(!caps.torch){ await showAlert('warning','Torch tidak tersedia'); return; }
      torchOn = !torchOn;
      await track.applyConstraints({advanced:[{torch:torchOn}]});
      torchBtn.classList.toggle('btn-success', torchOn);
      torchBtn.classList.toggle('btn-warning', !torchOn);
    }catch(e){ console.warn("Torch error:", e); }
  }

  async function detectionLoop(){
    if(!detectionActive) return;
    ctx.clearRect(0,0,overlay.width,overlay.height);

    const detections = await faceapi.detectAllFaces(video,new faceapi.TinyFaceDetectorOptions({inputSize:224,scoreThreshold:0.35}))
      .withFaceLandmarks()
      .withFaceDescriptors();

    if(detections.length>0){
      const resized = faceapi.resizeResults(detections,{width:overlay.width,height:overlay.height});
      const face = resized[0];
      const box = face.detection.box;
      ctx.strokeStyle="lime"; ctx.lineWidth=2; ctx.strokeRect(box.x,box.y,box.width,box.height);

      const match = findBestMatch(face.descriptor);
      if(match && match.distance<=MATCH_THRESHOLD){
        const nip = match.best.nip;
        tampilkanPegawai(match.best);
        statusText.innerText = `👁️ ${match.best.nama} terdeteksi`;
        if(lastMatchedNip!==nip){ 
          lastMatchedNip=nip; 
          await prosesAbsensi(nip, match.best.nama, (100-match.distance*100).toFixed(1)); 
        }
      } else {
        statusText.innerText = "⚠️ Wajah tidak dikenal!";
        playFailOnce(); 
        lastMatchedNip = null; 
      }
    }
    requestAnimationFrame(detectionLoop);
  }


  function startQuagga(){
    Quagga.init({
      inputStream:{
        name:"Live",type:"LiveStream",target:video,
        constraints:{facingMode:facingMode,width:1280,height:720}
      },
      decoder:{readers:["code_128_reader","ean_reader","code_39_reader"]},
      locate:true
    },err=>{
      if(err){console.error(err);return;}
      Quagga.start();
      Quagga.onDetected(async data=>{
        const nip = data.codeResult.code.trim();
        if(lastMatchedNip!==nip){
          const found = pegawaiDescriptors.find(p=>p.nip===nip);
          if(found) tampilkanPegawai(found);
          lastMatchedNip=nip;
          await prosesAbsensi(nip, found?found.nama:nip, 100);
          statusText.innerText=`Barcode: ${nip}`;
        }
      });
    });
  }

  // --- Tombol ---
  toggleBtn.addEventListener('click', async()=>{
    detectionActive=false;
    Quagga.stop();
    facingMode = (facingMode==='user')?'environment':'user';
    await startCamera();
    startQuagga();
    detectionActive=true; detectionLoop();
  });

  torchBtn.addEventListener('click', toggleTorch);

  mulaiBtn.addEventListener('click', async()=>{
    keterangan = document.getElementById('keterangan').value.trim();
    if(!keterangan){ await showAlert('warning','Isi keterangan terlebih dahulu!'); return; }

    successSound = new Audio('{{ asset("sounds/zapsplat_multimedia_alert_notification_musical_soft_chimes_positive_003_61651.mp3") }}');
    failSound = new Audio('{{ asset("sounds/mixkit-wrong-long-buzzer-954.wav") }}');
    try {
      await Promise.all([
        successSound.play().then(()=>successSound.pause()).catch(()=>{}),
        failSound.play().then(()=>failSound.pause()).catch(()=>{}),
      ]);
      successSound.currentTime=0; failSound.currentTime=0;
    } catch(e){ console.warn("Audio unlock gagal",e); }

    formWrapper.style.display='none';
    kameraSection.classList.remove('d-none');
    await startCamera();

    const res = await fetch("{{ route('absensi.getDescriptors') }}");
    const data = await res.json();
    pegawaiDescriptors = data.pegawaiData.map(p=>({...p, descriptors:p.descriptors.map(d=>new Float32Array(d))}));

    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri('{{ asset("models/weights") }}'),
      faceapi.nets.faceLandmark68Net.loadFromUri('{{ asset("models/weights") }}'),
      faceapi.nets.faceRecognitionNet.loadFromUri('{{ asset("models/weights") }}')
    ]);

    startQuagga();
    detectionActive=true; detectionLoop();
  });

  window.addEventListener('beforeunload',()=>{
    detectionActive=false; Quagga.stop();
    if(stream) stream.getTracks().forEach(t=>t.stop());
  });
});

</script>

<style>
#pegawaiData {
  transition: opacity 0.5s ease-in-out;
}
</style>
@endsection
