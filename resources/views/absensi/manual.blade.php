@extends('layout.absensi')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6 col-12">
        <h1>Absensi Manual - {{ ucfirst($keterangan) }}</h1>
      </div>
      <div class="col-sm-6 col-12">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></li>
          <li class="breadcrumb-item active">Absensi Manual</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid">

  <form method="POST" action="{{ route('absensi.manual.simpan') }}">
    @csrf
    <input type="hidden" name="keterangan" value="{{ $keterangan }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <input type="hidden" name="kegiatan_id" value="{{ $kegiatan_id ?? '' }}">

    <div class="card shadow">
      <div class="card-body">
        <div class="form-group">
          <label for="nama">Nama Pegawai</label>
          <select name="nama" id="nama" class="form-control" required>
            <option value="" disabled selected>Pilih Nama</option>
            @foreach($belumAbsen as $p)
              <option value="{{ $p->nama }}" data-nip="{{ $p->nip }}">{{ $p->nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="nip">NIP</label>
          <select name="nip" id="nip" class="form-control" required>
            <option value="" disabled selected>Pilih NIP</option>
            @foreach($belumAbsen as $p)
              <option value="{{ $p->nip }}" data-nama="{{ $p->nama }}">{{ $p->nip }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="status">Status Absensi</label>
          <select name="status" id="status" class="form-control" required>
            <option value="" disabled selected>Pilih Status</option>
            <option value="Sakit">Sakit</option>
            <option value="Izin">Izin</option>
            <option value="Cuti">Cuti</option>
            <option value="Dinas Luar">Dinas Luar</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div class="form-group" id="keterangan-lainnya" style="display: none;">
          <label for="keterangan_manual">Keterangan Lainnya</label>
          <input type="text" name="keterangan_manual" id="keterangan_manual" class="form-control" placeholder="Isi keterangan lain jika diperlukan">
        </div>

        <button type="submit" class="btn btn-success">
          <i class="fa fa-save"></i> Simpan Absensi
        </button>
      </div>
    </div>
  </form>

  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-info text-white text-center">
      <h5 class="mb-0">Daftar Pegawai yang Belum Absen</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0 text-center">
          <thead class="bg-light">
            <tr>
              <th>NIP</th>
              <th>Nama</th>
            </tr>
          </thead>
          <tbody>
            @forelse($belumAbsen as $index => $pegawai)
              <tr>
                <td>{{ $pegawai->nip }}</td>
                <td>{{ $pegawai->nama }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-muted py-3">
                  Semua pegawai telah melakukan absensi untuk kegiatan ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      showConfirmButton: false,
      timer: 2000
    });
  });
</script>
@endif

@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: "{{ session('error') }}",
      showConfirmButton: true,
    });
  });
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
  const namaSelect = document.getElementById('nama');
  const nipSelect = document.getElementById('nip');
  const statusSelect = document.getElementById('status');
  const keteranganLainnya = document.getElementById('keterangan-lainnya');
  const keteranganManual = document.getElementById('keterangan_manual');
  const form = document.querySelector('form');

  namaSelect.addEventListener('change', () => {
    const selectedOption = namaSelect.selectedOptions[0];
    if (selectedOption) nipSelect.value = selectedOption.dataset.nip;
  });

  nipSelect.addEventListener('change', () => {
    const selectedOption = nipSelect.selectedOptions[0];
    if (selectedOption) namaSelect.value = selectedOption.dataset.nama;
  });

  statusSelect.addEventListener('change', () => {
    if (statusSelect.value === 'Lainnya') {
      keteranganLainnya.style.display = 'block';
      keteranganManual.required = true;
    } else {
      keteranganLainnya.style.display = 'none';
      keteranganManual.required = false;
    }
  });

  form.addEventListener('submit', () => {
    if (statusSelect.value === 'Lainnya' && keteranganManual.value.trim() !== '') {
      statusSelect.value = keteranganManual.value.trim();
    }
  });
});
</script>
@endsection
