<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi {{ ucfirst($keterangan) }}</title>
    <style>
    body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
    .kop { text-align: center; border-bottom: 3px solid black; padding-bottom: 5px; margin-bottom: 15px; }
    .kop img { float: left; width: 80px; height: 90px; margin-right: 15px; }
    .kop h2, .kop h3, .kop p { margin: 0; line-height: 1.3;}
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid black; padding: 5px; text-align: center; }
    th { background-color: #ffffffff; }
  </style>
</head>
<body>
    <div class="kop">
        <img src="{{ public_path('master/images/logokumham.jpg') }}" alt="Logo">
        <h2>KEMENTERIAN HUKUM REPUBLIK INDONESIA</h2>
        <h3>KANTOR WILAYAH KEPULAUAN RIAU</h3>
        <p>Jalan Daeng Kamboja, Tanjungpinang 29125</p>
        <p>Telepon: +62811 7709 007 | Email: kanwilkepri@kemenkum.go.id</p>
    </div>

    @php
        use Carbon\Carbon;
        // Gunakan tanggal dari controller
        $tanggalAbsensi = $tanggal ?? Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y');
        // Tanggal cetak real-time
        $tanggalCetak = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y H:i');
    @endphp

    <h2 style="text-align:center;">Laporan Absensi {{ ucfirst($keterangan) }}</h2>
    <p style="text-align:center;">Tanggal Absensi: {{ $tanggalAbsensi }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Waktu Absen</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataTampil as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p['nip'] }}</td>
                    <td>{{ $p['nama'] }}</td>
                    <td>{{ $p['waktu_absen'] }}</td>
                    <td>{{ $p['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:30px; text-align:right;">
        <i>Dicetak pada: {{ $tanggalCetak }}</i>
    </p>
</body>
</html>
