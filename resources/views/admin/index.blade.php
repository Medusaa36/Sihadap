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
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-9 col-12">
        <h1>Daftar Data Admin</h1>
      </div>
      <div class="col-sm-3 col-12">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Admin</li>
        </ol>
      </div>
    </div>

    @if($isMaster)
      <a href="{{ route('admin.create') }}" class="btn btn-app bg-teal">
          <i class="fas fa-plus"></i> Tambah Admin
      </a>
    @endif
  </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">Data Admin Kantor Wilayah Kementerian Hukum Kepulauan Riau</h2>
        </div>

        <div class="card-body table-responsive p-0" style="max-height: 650px; overflow-y: auto;">
            <table id="admin-table" class="table table-bordered table-hover text-nowrap">
                <thead class="table-success text-center">
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>NAMA PEGAWAI</th>
                        <th>JENIS KELAMIN</th>
                        <th>TIPE ADMIN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admin_models as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $item->nip }}</td>
                            <td class="text-center">{{ $item->pegawai->nama ?? '-' }}</td>
                            <td class="text-center">{{ $item->pegawai->jenis_kelamin ?? '-' }}</td>
                            <td class="text-center">{{ ucfirst($item->tipe_admin) ?? '-' }}</td>

                            {{-- Kolom aksi --}}
                            <td class="text-center">
                                @if($isMaster)
                                    <a href="{{ route('admin.edit', $item->id) }}" class="btn btn-outline-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('admin.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-delete" 
                                                data-nama="{{ $item->pegawai->nama ?? $item->nip }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-lock"></i> Tidak Diizinkan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

@if($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        html: `{!! implode("<br>", $errors->all()) !!}`
    });
</script>
@endif

<script>
$(document).ready(function(){
    $('.btn-delete').click(function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Yakin?',
            text: "Hapus data admin: " + nama + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $('#admin-table').DataTable({
        paging: true,
        pageLength: 10,
        info: false,
        ordering: true,
        language: {
            search: "Cari:",
            zeroRecords: "Tidak ada data yang ditemukan",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            paginate: {
                previous: "<",
                next: ">"
            }
        }
    });
});
</script>
@endpush
@endsection
