<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Surat - Dekan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f0f0f0;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
        }

        .container {
            width: 900px;
            margin: 30px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .badge {
            background: orange;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-danger {
            background: #c0392b;
            color: white;
        }

        .btn-back {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Detail Pengajuan Surat</h2>

    <p><strong>Nama:</strong> {{ $surat->user->name }}</p>
    <p><strong>NPM:</strong> {{ $surat->user->npm }}</p>
    <p><strong>Program Studi:</strong> {{ $surat->user->prodi }}</p>
    <p><strong>Jenis Surat:</strong> {{ $surat->jenisSurat->nama }}</p>
    <p><strong>Status:</strong>
        <span class="badge">
            {{ statusMahasiswa($surat->status) }}
        </span>
    </p>

    <hr>

    <h4>Data Pengajuan</h4>
    @foreach($surat->data as $key => $value)
        <p>{{ ucfirst(str_replace('_',' ', $key)) }} : {{ $value }}</p>
    @endforeach

    <hr>

    @if($surat->status === 'menunggu_dekan')
        <form method="POST"
              action="/dekan/surat/{{ $surat->id }}/setujui"
              onsubmit="return confirm('Yakin ingin MENYETUJUI surat ini?')"
              style="display:inline;">
            @csrf
            <button class="btn btn-success">
                Setujui
            </button>
        </form>

        <form method="POST"
              action="/dekan/surat/{{ $surat->id }}/tolak"
              onsubmit="return confirm('Yakin ingin MENOLAK surat ini?')"
              style="display:inline;">
            @csrf
            <button class="btn btn-danger">
                Tolak
            </button>
        </form>
    @endif

    <br><br>
    <a href="/dekan/dashboard" class="btn-back">Kembali</a>

</div>

</body>
</html>
