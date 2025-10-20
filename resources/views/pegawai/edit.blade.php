@extends('layout.pegawai')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Edit Data Pegawai</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
          <li class="breadcrumb-item active">Edit Pegawai</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid">
  <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Edit Data Pegawai</h3></div>

    <form id="pegawaiForm" action="{{ route('pegawai.update', $pegawai_models->nip) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="form-group">
          <label>Nama</label>
          <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai_models->nama) }}" required>
        </div>

        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai_models->nip) }}" readonly>
        </div>

        <div class="form-group">
          <label>Jenis Kelamin</label>
          <select class="custom-select" id="jk" name="jenis_kelamin" required>
            <option value="" disabled>Pilih Jenis Kelamin</option>
            <option value="Laki-laki" {{ old('jenis_kelamin', $pegawai_models->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-Laki</option>
            <option value="Perempuan" {{ old('jenis_kelamin', $pegawai_models->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
          </select>
        </div>
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
@endsection
