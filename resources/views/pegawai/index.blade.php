@extends('layout.pegawai')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Daftar Data Pegawai</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Pegawai</li>
        </ol>
      </div>
    </div>
    <a href="{{ route('pegawai.create') }}" class="btn btn-app bg-teal">
      <i class="fas fa-plus"></i> Tambah Pegawai
    </a>
  </div>
</section>

<section class="content">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2 class="card-title">
        Data Pegawai Kantor Wilayah Kementerian Hukum Kepulauan Riau
      </h2>
    </div>

    <div class="card-footer text-right no-print">
      <a href="{{ route('pegawai.print') }}" class="btn btn-success">
        <i class="fas fa-file-download"></i> Unduh Data Pegawai (PDF)
      </a>
    </div>

    <div class="card-body table-responsive p-0">
      <table id="pegawai-table" class="table table-bordered table-hover text-nowrap">
        <thead class="table-success text-center">
          <tr>
            <th>No</th>
            <th>NIP</th>
            <th>Nama Pegawai</th>
            <th>Jenis Kelamin</th>
            <th>Data Wajah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pegawai_models as $item)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $item->nip }}</td>
            <td class="text-center">{{ $item->nama }}</td>
            <td class="text-center">{{ $item->jenis_kelamin }}</td>

            <td class="text-center">
              @if(!empty($item->verifikasi_wajah))
                <i class="fas fa-check-circle text-success"></i>
                <form action="{{ route('pegawai.hapus-wajah', $item->nip) }}" method="POST" class="d-inline form-hapus-wajah">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-outline-warning btn-sm btn-hapus-wajah">
                    <i class="fas fa-user-slash"></i> Hapus Data Wajah
                  </button>
                </form>
              @else
                <i class="fas fa-times-circle text-danger"></i> Belum Ada Data Wajah
              @endif
            </td>

            <td class="text-center no-print">
              <a href="{{ route('pegawai.data-wajah', $item->nip) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-smile"></i> Data Wajah
              </a>

              <a href="{{ route('pegawai.barcode', $item->nip) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-barcode"></i> Barcode
              </a>

              <a href="{{ route('pegawai.edit', $item->nip) }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-edit"></i> Data
              </a>

              <form action="{{ route('pegawai.destroy', $item->nip) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-sm btn-delete">
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

@if(session('success'))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Sukses!',
    text: '{{ session("success") }}',
    timer: 3000,
    showConfirmButton: false,
  });
</script>
@endpush
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Konfirmasi hapus pegawai
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data pegawai akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // Konfirmasi hapus wajah
    document.querySelectorAll('.btn-hapus-wajah').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Hapus Data Wajah?',
                text: "Data verifikasi wajah akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // DataTables
    $('#pegawai-table').DataTable({
        paging: true,
        pageLength: 25,
        info: false,
        ordering: true,
        language: {
            search: "Cari:",
            zeroRecords: "Tidak ada data yang ditemukan",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            paginate: { previous: "<", next: ">" }
        }
    });
});
</script>
@endpush
@endsection
