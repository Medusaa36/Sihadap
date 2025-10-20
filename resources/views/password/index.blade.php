@extends('layout.password')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Ganti Password</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
                    <li class="breadcrumb-item active">Ganti Password</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Ganti Password</h3>
        </div>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" class="form-control" value="{{ $admin->nama }}" disabled>
                </div>

                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" class="form-control" value="{{ $admin->nip }}" disabled>
                </div>
 s
                <div class="form-group">
                    <label>Password Lama</label>
                    <div class="input-group">
                        <input type="password" name="password_lama" id="password_lama" class="form-control" required>
                        <span class="input-group-text" onclick="togglePassword('password_lama', this)" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password_baru" id="password_baru" class="form-control" required minlength="8">
                        <span class="input-group-text" onclick="togglePassword('password_baru', this)" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <small class="text-muted">Minimal 8 karakter</small>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" required minlength="8">
                        <span class="input-group-text" onclick="togglePassword('konfirmasi_password', this)" style="cursor:pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
    function togglePassword(id, el) {
        const input = document.getElementById(id);
        const icon = el.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

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

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonText: 'Tutup'
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'Tutup'
            });
        @endif
    });
</script>
@endsection
