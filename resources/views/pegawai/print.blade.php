<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Pegawai</title>
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
  <p>Jalan Daeng Kamboja Tanjungpinang 29125</p>
  <p>Telepon: +62811 7709 007 | Email: kanwilkepri@kemenkum.go.id</p>
</div>

<p style="text-align:right;">Dicetak Pada: {{ $tanggal }}</p>
<h4 style="text-align:center; text-decoration:underline;">DAFTAR DATA PEGAWAI</h4>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>NIP</th>
      <th>Nama Pegawai</th>
      <th>Jenis Kelamin</th>
    </tr>
  </thead>
  <tbody>
    @foreach($pegawai as $key => $item)
    <tr>
      <td>{{ $key + 1 }}</td>
      <td>{{ $item->nip }}</td>
      <td>{{ $item->nama }}</td>
      <td>{{ $item->jenis_kelamin }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
</body>
</html>
