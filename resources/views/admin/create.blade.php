@extends('layout.admin')

@section('content')

@php
    use App\Models\AdminModel;
    $currentAdmin = null;
    if (session()->has('admin_id')) {
        $currentAdmin = AdminModel::find(session('admin_id'));
    }
    $isMaster = $currentAdmin && strtolower(trim($currentAdmin->tipe_admin)) === 'admin master';
@endphp

<section class="content-header">
  <h1>Tambah Data Admin</h1>
</section>

<div class="container-fluid">
    @if($isMaster)
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Admin</h3>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" 
                               value="{{ old('nama') }}" required>
                    </div>

                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip" id="nip" class="form-control" 
                               value="{{ old('nip') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan Password">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="togglePasswordBtn" onclick="togglePassword()" tabindex="-1" title="Tampilkan / Sembunyikan Password">
                                    <i id="togglePasswordIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tipe Admin</label>
                        <select name="tipe_admin" class="form-control" required>
                            <option value="" disabled selected>Pilih Tipe</option>
                            <option value="Admin Master" {{ old('tipe_admin')=='Admin Master'?'selected':'' }}>Admin Master</option>
                            <option value="Admin" {{ old('tipe_admin')=='Admin'?'selected':'' }}>Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-info">Simpan</button>
                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>

    @else
        <div class="alert alert-danger mt-4">
            <h4><i class="fas fa-exclamation-triangle"></i> Akses Ditolak!</h4>
            <p>Anda tidak memiliki izin untuk menambahkan data admin. Hanya <strong>Admin Master</strong> yang dapat melakukan aksi ini.</p>
            <a href="{{ route('admin.index') }}" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    @endif
</div>

<script>
$(function(){
    $("#nama").autocomplete({
        source: "{{ route('pegawai.searchByName') }}",
        select: function(event, ui){
            $("#nama").val(ui.item.nama);
            $("#nip").val(ui.item.nip);
        }
    });
    $("#nip").autocomplete({
        source: "{{ route('pegawai.searchByNip') }}",
        select: function(event, ui){
            $("#nip").val(ui.item.nip);
            $("#nama").val(ui.item.nama);
        }
    });
});

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
</script>

@endsection
