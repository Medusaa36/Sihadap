<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Barcode - {{ $pegawai->nama }}</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center; 
      align-items: center;      
      text-align: center;
    }

    .container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      border: 1px solid #ccc;
      padding: 10px 20px;
      border-radius: 10px;
    }

    .info {
      margin-bottom: 10px;
    }

    .info p {
      margin: 3px 0;
      font-size: 14px;
    }

    .barcode {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      max-width: 350px;
    }

    .barcode svg, .barcode img {
      width: 100%;
      height: auto;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="info">
      <p><strong>Nama:</strong> {{ $pegawai->nama }}</p>
      <p><strong>NIP:</strong> {{ $pegawai->nip }}</p>
    </div>

    <div class="barcode">
      {!! $barcode !!}
    </div>
  </div>
</body>
</html>
