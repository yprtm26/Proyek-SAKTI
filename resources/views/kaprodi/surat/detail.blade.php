<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Surat — Kaprodi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background:#f0f0f0;
            font-family: "Poppins", sans-serif;
        }
        .container {
            width: 900px;
            margin: 30px auto;
            background:white;
            padding:20px;
            border-radius:6px;
        }
        table {
            width:100%;
            border-collapse: collapse;
        }
        td {
            padding:6px 8px;
            vertical-align:top;
        }
        .badge {
            background:orange;
            color:white;
            padding:4px 10px;
            border-radius:4px;
            font-size:13px;
        }
        .preview {
            margin:20px 0;
            background:#eee;
            padding:30px;
            text-align:center;
            color:#555;
        }
        .actions {
            margin-top:20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-actions {
            display: flex;
            gap: 10px;
        }
        .right-actions {
            text-align: right;
        }
        .btn {
            padding:8px 14px;
            border-radius:4px;
            border:none;
            font-size:14px;
            cursor:pointer;
        }
        .btn-success {
            background:#27ae60;
            color:white;
        }
        .btn-danger {
            background:#c0392b;
            color:white;
        }
        .btn-back {
            background:#3498db;
            color:white;
            text-decoration:none;
            padding:8px 14px;
            border-radius:4px;
        }
    </style>
</head>
<body>

<div class="container">

    <h3>Informasi Pengajuan Surat</h3>

    <table>
        <tr>
            <td width="160">Nama</td>
            <td width="10">:</td>
            <td><strong>{{ $surat->user->name }}</strong></td>
        </tr>
        <tr>
            <td>NPM</td>
            <td>:</td>
            <td>{{ $surat->user->npm }}</td>
        </tr>
        <tr>
            <td>Jenis Surat</td>
            <td>:</td>
            <td>{{ $surat->jenisSurat->nama }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>:</td>
            <td>
                <span class="badge">
                    {{ statusMahasiswa($surat->status) }}
                </span>
            </td>
        </tr>
    </table>

    <hr>

    <h4>Data Pengajuan</h4>

    @foreach($surat->data as $key => $value)
        <p>{{ ucfirst(str_replace('_',' ',$key)) }} : {{ $value }}</p>
    @endforeach

    <hr>

    <div class="actions">
        <div class="left-actions">
        @if($surat->status === 'menunggu_kaprodi')
            <form method="POST" action="/kaprodi/surat/{{ $surat->id }}/setujui"
                onsubmit="return confirm('Setujui surat dan TERBITKAN nomor surat?')">
                @csrf
                <button class="btn btn-success">
                    Setujui
                </button>
            </form>

            <form method="POST" action="/kaprodi/surat/{{ $surat->id }}/tolak"
                onsubmit="return confirm('Yakin ingin MENOLAK surat ini?')">
                @csrf
                <button class="btn btn-danger">
                    Tolak
                </button>
            </form>
        @endif
        </div>

        <div class="right-actions">
        <a href="/kaprodi/dashboard" class="btn-back">
            Kembali
        </a>
    </div>
    </div>

</div>

</body>
</html>
