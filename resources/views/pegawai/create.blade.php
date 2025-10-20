@extends('layout.pegawai')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Data Pegawai</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
          <li class="breadcrumb-item active">Tambah Pegawai</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid">
  <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Tambah Data Pegawai</h3></div>

    <form id="pegawaiForm" action="{{ route('pegawai.store') }}" method="POST">
      @csrf
      <div class="card-body">
        <div class="form-group">
          <label>NAMA</label>
          <input type="text" class="form-control" name="nama" id="nama" required>
        </div>

        <div class="form-group">
          <label>NIP</label>
          <input type="number" class="form-control" name="nip" id="nip" required>
        </div>

        <div class="form-group">
          <label>JENIS KELAMIN</label>
          <select class="custom-select" id="jk" name="jenis_kelamin" required>
            <option value="" disabled selected>Pilih Jenis Kelamin</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div> 
        <button type="submit" class="btn btn-success mt-3" id="simpanBtn">Simpan</button>
      </div>
    </form>
  </div>
</div>
<style>
#video {
  transform: scaleX(-1);
}

#overlay {
  transform: scaleX(-1);
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

@endsection
