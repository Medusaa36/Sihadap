@extends('layout.absensi')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6 col-12">
        <h1>Edit Absensi - {{ ucfirst($absensi->keterangan) }}</h1>
      </div>
      <div class="col-sm-6 col-12">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></li>
          <li class="breadcrumb-item"><a href="{{ route('absensi.detailKegiatan', $absensi->id) }}">Detail Absensi</a></li>
          <li class="breadcrumb-item active">Edit Absensi</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid">
  <form method="POST" action="{{ route('absensi.update', $absensi->id) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="keterangan" value="{{ $absensi->keterangan }}">

    <div class="card shadow">
      <div class="card-body">

        <div class="form-group">
          <label for="nip">Pegawai (NIP)</label>
          <select name="nip" id="nip" class="form-control select2" required>
            <option value="">-- Pilih Pegawai --</option>
            @foreach($pegawai as $p)
              <option value="{{ $p->nip }}" {{ $p->nip == $absensi->nip ? 'selected' : '' }}>
                {{ $p->nip }} - {{ $p->nama }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="status">Status Absensi</label>
          <select name="status" id="status" class="form-control" required>
            <option value="">-- Pilih Status --</option>
            <option value="Sakit" {{ $absensi->status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="Izin" {{ $absensi->status == 'Izin' ? 'selected' : '' }}>Izin</option>
            <option value="Cuti" {{ $absensi->status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
            <option value="Dinas Luar" {{ $absensi->status == 'Dinas Luar' ? 'selected' : '' }}>Dinas Luar</option>
            <option value="Lainnya" {{ !in_array($absensi->status, ['Sakit','Izin','Cuti','Dinas Luar']) ? 'selected' : '' }}>
              Lainnya
            </option>
          </select>
        </div>

        <div id="keterangan-lainnya" class="form-group"
          style="display: {{ !in_array($absensi->status, ['Sakit','Izin','Cuti','Dinas Luar']) ? 'block' : 'none' }};">
          <label for="keterangan_manual">Keterangan Lainnya</label>
          <input type="text" name="keterangan_manual" id="keterangan_manual"
                 class="form-control"
                 value="{{ !in_array($absensi->status, ['Sakit','Izin','Cuti','Dinas Luar']) ? $absensi->status : '' }}"
                 placeholder="Isi keterangan lain jika diperlukan">
        </div>

        <div class="form-group">
          <label for="waktu_absen">Waktu Absen</label>
          <input type="datetime-local" name="waktu_absen" id="waktu_absen" 
                 class="form-control"
                 value="{{ \Carbon\Carbon::parse($absensi->waktu_absen)->format('Y-m-d\TH:i') }}"
                 required>
        </div>

        <div class="d-flex justify-content-between">
          <a href="{{ route('absensi.detailKegiatan', $absensi->id) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Simpan Perubahan
          </button>
        </div>

      </div>
    </div>
  </form>
</div>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', () => {
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
document.addEventListener('DOMContentLoaded', () => {
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: "{{ session('error') }}",
    showConfirmButton: true
  });
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
  const statusSelect = document.getElementById('status');
  const keteranganLainnya = document.getElementById('keterangan-lainnya');
  const keteranganManual = document.getElementById('keterangan_manual');

  statusSelect.addEventListener('change', () => {
    if (statusSelect.value === 'Lainnya') {
      keteranganLainnya.style.display = 'block';
      keteranganManual.required = true;
    } else {
      keteranganLainnya.style.display = 'none';
      keteranganManual.required = false;
      keteranganManual.value = '';
    }
  });
});

</script>
@endsection
