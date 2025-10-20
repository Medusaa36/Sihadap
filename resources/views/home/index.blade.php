@extends('layout.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Dashboard</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-lg-6 col-12">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ $totalPegawai }}</h3>
            <h6>Total Pegawai</h6>
          </div>
          <div class="icon"><i class="fas fa-users"></i></div>
        </div>
      </div>

      <div class="col-lg-6 col-12">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3 id="jam">Memuat...</h3>
            <h6 id="tanggal">Memuat...</h6>
          </div>
          <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
      </div>
    </div>

    <div class="card card-success">
      <div class="card-header" style="background:#11375c">
        <h3 class="card-title">Grafik Kehadiran Pegawai</h3>
      </div>
      <div class="card-body" style="max-height: 550px; overflow-y: auto;">
        @if($grafik->isEmpty())
          <p class="text-center text-muted mt-3">Belum ada data absensi yang tersedia.</p>
        @else
          <div class="row">
            @foreach($grafik as $g)
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm border-light">
                  <div class="card-header bg-light text-center">
                    <strong>{{ $g['keterangan'] }}</strong><br>
                    <small>{{ \Carbon\Carbon::parse($g['tanggal'])->translatedFormat('d F Y') }}</small>
                  </div>
                  <div class="card-body">
                    <canvas id="chart-{{ $loop->index }}" height="160"></canvas>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const grafikData = @json($grafik);

  grafikData.forEach((item, index) => {
    const ctx = document.getElementById(`chart-${index}`).getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Hadir', 'Tidak Hadir', 'Lainnya'],
        datasets: [{
          label: 'Jumlah Pegawai',
          data: [item.hadir, item.tidak_hadir, item.lainnya],
          backgroundColor: ['#28a745', '#dc3545', '#ffc107']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  });
});

function updateAbsensiTime() {
  const now = new Date();
  const optionsTanggal = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' };
  const tanggalStr = new Intl.DateTimeFormat('id-ID', optionsTanggal).format(now);
  const optionsJam = { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta', hour12: false };
  const jamStr = new Intl.DateTimeFormat('id-ID', optionsJam).format(now);
  document.getElementById('tanggal').innerText = tanggalStr;
  document.getElementById('jam').innerText = jamStr;
}
updateAbsensiTime();
setInterval(updateAbsensiTime, 1000);
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('password_success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('password_success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    @endif
});
</script>

<style>
.card-body canvas {
  display: block;
  margin: auto;
  max-height: 150px;
}
.card-header {
  font-size: 0.95rem;
  line-height: 1.2;
}
</style>
@endpush
