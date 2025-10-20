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
        <h1>Detail Absensi {{ ucfirst($keterangan ?? '-') }}</h1>
      </div>
      <div class="col-sm-3 col-12">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></li>
          <li class="breadcrumb-item active">Detail Absensi</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <button class="btn btn-outline-success btn-lg btn-absensi"
              data-id="{{ $id_kegiatan ?? '' }}"
              data-keterangan="{{ $keterangan ?? '' }}">
          <i class="fas fa-plus"></i> Tambah Absensi
      </button>

      @if(!empty($absensi_models) && $absensi_models->isNotEmpty())
      <a href="{{ route('absensi.cetak', $absensi_models->first()->kegiatan_id) }}" 
         target="_blank" 
         class="btn btn-success btn-sm">
          <i class="fas fa-file-download"></i> Unduh Data Absensi (PDF)
      </a>
      @endif
    </div>

    {{-- Tabel Daftar Pegawai --}}
    <div class="card shadow">
      <div class="card-header">
        <h3 class="card-title">Daftar Pegawai - {{ $tanggal ?? '-' }}</h3>
      </div>
      <div class="card-body table-responsive">
        @php
          $pegawai = $pegawai ?? collect();
          $absensi_models = $absensi_models ?? collect();
          $absenOtomatis = $absensi_models->keyBy('nip');

          $dataTampil = $pegawai->map(function($p) use ($absenOtomatis) {
              if ($absenOtomatis->has($p->nip)) {
                  $absen = $absenOtomatis[$p->nip];
                  return [
                      'id' => $absen->id,
                      'nip' => $p->nip,
                      'nama' => $p->nama,
                      'status' => $absen->status ?? 'Hadir',
                      'tipe' => 'otomatis'
                  ];
              } else {
                  return [
                      'id' => null,
                      'nip' => $p->nip,
                      'nama' => $p->nama,
                      'status' => 'Tidak Hadir',
                      'tipe' => 'belum'
                  ];
              }
          })->sortBy(function ($item) {
              $status = strtolower(trim($item['status']));
              if (stripos($status, 'tidak') !== false) return 3;
              if (stripos($status, 'hadir') !== false) return 1;
              return 2;
          })->values();
      @endphp

        <table id="tabelAbsensi" class="table table-bordered table-striped text-center align-middle">
          <thead class="table-success text-center">
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama Pegawai</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          @forelse($dataTampil as $p)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $p['nip'] }}</td>
            <td>{{ $p['nama'] }}</td>
            <td>
              @php
                  $badgeClass = $p['tipe'] === 'otomatis' ? 'bg-success' : 'bg-danger';
              @endphp
              <span class="badge {{ $badgeClass }}">{{ $p['status'] }}</span>
            </td>
            <td class="text-center no-print">
              @if($p['id'])
                <div class="btn-group" role="group" style="gap: 4px;">
                  @if($isMaster)
                    <a href="{{ route('absensi.edit', $p['id']) }}" class="btn btn-outline-info btn-sm">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('absensi.destroyOne', $p['id']) }}" 
                          method="POST" 
                          class="form-hapus" 
                          style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn btn-outline-danger btn-sm btn-hapus">
                        <i class="fas fa-trash"></i> Hapus
                      </button>
                    </form>

                  @else
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="fas fa-lock"></i> Edit
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="fas fa-lock"></i> Hapus
                    </button>
                  @endif
                </div>
              @else
                <h2>-</h2>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">Tidak ada data pegawai</td>
          </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#tabelAbsensi').DataTable({
            responsive: true,
            pageLength: 25
        });
    }

    $(document).on('click', '.btn-absensi', function(e) {
        e.preventDefault();

        const kegiatanId = $(this).data('id');
        const keterangan = $(this).data('keterangan');

        if (!keterangan) {
            Swal.fire('Gagal', 'Keterangan kegiatan tidak ditemukan.', 'error');
            return;
        }

        Swal.fire({
            title: 'Pilih Metode Absensi',
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

    $(document).on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
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
            title: 'Gagal!',
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    @endif
});
</script>
@endsection
