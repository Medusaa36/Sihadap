@extends('layout.absensi')

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
        <h1>Absensi</h1>
      </div>
      <div class="col-sm-3 col-12">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Absensi</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="mb-3">
      <a href="{{ route('absensi.kamera') }}" class="btn btn-success">
        <i class="fa fa-camera"></i> Mulai Absensi
      </a>
    </div>

    <div class="mb-3">
      <h6>
        Tanggal & Jam:
        <span id="current-datetime" class="text-primary fw-bold"></span>
      </h6>
    </div>

    <div class="card shadow">
      <div class="card-header">
        <h3 class="card-title">Daftar Kegiatan</h3>
      </div>

      <div class="card-body table-responsive">
        <table id="absensi-table" class="table table-bordered table-striped text-center align-middle">
          <thead class="table-success text-center">
            <tr>
              <th>No</th>
              <th>Kegiatan</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kegiatan as $data)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $data->keterangan ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('l, d F Y') }}</td>
                <td class="text-center">
                  <a href="{{ route('absensi.detailKegiatan', $data->kegiatan_id) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-eye"></i> Lihat
                  </a>

                  <button class="btn btn-outline-success btn-sm btn-absensi"
                          data-id="{{ $data->kegiatan_id }}"
                          data-keterangan="{{ $data->keterangan }}">
                      <i class="fas fa-plus"></i> Absensi
                  </button>

                  <a href="{{ route('absensi.cetak', $data->kegiatan_id) }}" 
                    target="_blank" 
                    class="btn btn-outline-primary btn-sm {{ $data->kegiatan_id ? '' : 'disabled' }}">
                    <i class="fas fa-print"></i> Cetak
                  </a>

                  @if($isMaster)
                    <form action="{{ route('absensi.destroyKegiatan', $data->kegiatan_id) }}" 
                          method="POST" 
                          class="form-hapus d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn btn-outline-danger btn-sm btn-hapus">
                        <i class="fas fa-trash"></i> Hapus Semua
                      </button>
                    </form>
                  @else
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                      <i class="fas fa-lock"></i> Hapus Semua
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">Belum ada kegiatan</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const datetimeSpan = document.getElementById('current-datetime');

  function updateDateTime() {
    const now = new Date();
    const options = {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
      timeZone: 'Asia/Jakarta'
    };
    datetimeSpan.textContent = new Intl.DateTimeFormat('id-ID', options).format(now);
  }

  updateDateTime();
  setInterval(updateDateTime, 1000);

  $('#absensi-table').DataTable({
    paging: true,
    pageLength: 10,
    info: false,
    ordering: true,
    language: {
      search: "Cari:",
      zeroRecords: "Tidak ada data yang ditemukan",
      lengthMenu: "Tampilkan _MENU_ data per halaman",
      paginate: { previous: "<", next: ">" }
    }
  });

  $(document).on('click', '.btn-absensi', function(e) {
    e.preventDefault();

    const kegiatanId = $(this).data('id');
    const keterangan = $(this).data('keterangan');

    Swal.fire({
      title: 'Pilih Metode Absensi',
      text: keterangan ? `Untuk kegiatan: "${keterangan}"` : '',
      icon: 'question',
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonText: 'Manual',
      denyButtonText: 'Otomatis (Kamera)',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        const url = kegiatanId 
          ? `/absensi/manual/${kegiatanId}` 
          : `/absensi/manual?keterangan=${encodeURIComponent(keterangan)}`;
        window.location.href = url;
      } else if (result.isDenied) {
        const query = kegiatanId 
          ? `id=${kegiatanId}&keterangan=${encodeURIComponent(keterangan)}`
          : `keterangan=${encodeURIComponent(keterangan)}`;
        window.location.href = `/absensi/kamera-aksi?${query}`;
      }
    });
  });

  $(document).on('click', '.btn-hapus', function(e){
    e.preventDefault();
    const form = $(this).closest('form');
    Swal.fire({
      title: 'Yakin?',
      text: "Absensi ini akan dihapus!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if(result.isConfirmed){
        form.submit();
      }
    });
  });

  @if(session('success'))
      Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: '{{ session('success') }}',
          showConfirmButton: false,
          timer: 2000
      });
  @endif

  @if(session('error'))
      Swal.fire({
          icon: 'error',
          title: 'Perhatian!',
          text: '{{ session('error') }}',
          showConfirmButton: false,
          timer: 2000
      });
  @endif

  @if(session('warning'))
      Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: '{{ session('warning') }}',
          showConfirmButton: false,
          timer: 2000
      });
  @endif
});
</script>
@endsection
