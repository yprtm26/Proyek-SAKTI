<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pengajuan Surat</title>
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

        h3 {
            margin-bottom:15px;
        }

        table {
            border-collapse: collapse;
            width:100%;
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

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
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
            border:none;
            border-radius:4px;
            cursor:pointer;
            font-size:14px;
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
            margin-left:10px;
        }

        .preview {
            margin:20px 0;
            background:#eee;
            padding:30px;
            text-align:center;
            color:#555;
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
            <td>Diajukan pada</td>
            <td>:</td>
            <td>{{ $surat->created_at->format('d F Y, H:i') }}</td>
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

    @if(isset($surat->data['semester']))
        <p>Semester: {{ $surat->data['semester'] }}</p>
    @endif

    @if(isset($surat->data['tahun_akademik']))
        <p>Tahun Akademik: {{ $surat->data['tahun_akademik'] }}</p>
    @endif

    <hr>

    <div class="actions">
        <div class="left-actions">
            @if($surat->status === 'menunggu')

                <form method="POST" action="/admin/surat/{{ $surat->id }}/tolak"
                    onsubmit="return confirm('Yakin ingin MENOLAK surat ini?')"
                    style="display:inline;">
                    @csrf
                    
                    <label>Alasan Penolakan (opsional)</label><br>
                    <textarea name="alasan_tolak"
                            rows="3"
                            placeholder="Contoh: Data tidak lengkap / Judul tidak sesuai"
                            style="width:100%;"></textarea>

                    <br><br>
                    <button class="btn btn-danger">
                        Tolak
                    </button>
                </form>
            @endif
        </div>
        <div class="right-actions">
            <form method="POST" action="/admin/surat/{{ $surat->id }}/setujui"
                    onsubmit="return confirm('Yakin ingin MENYETUJUI surat ini?')"
                    style="display:inline;">
                    @csrf
                    <button class="btn btn-success">
                        Setujui
                    </button>
                </form>
            <a href="/admin/dashboard" class="btn-back">
                Kembali
            </a>
        </div>
    </div>

</div>

</body>
</html>
