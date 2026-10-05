<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 2cm 2cm 2cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* ===== UTIL ===== */
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .justify { text-align: justify; }

        p {
            margin: 0 0 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ===== HEADER ===== */
        .header img {
            height: 65px;
        }

        /* ===== JUDUL ===== */
        .judul {
            text-align: center;
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .judul .title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .judul .subtitle {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 6px;
        }

        .judul .nomor {
            margin-top: 10px;
            font-weight: bold;
        }

        /* ===== SIGNATURE ===== */
        .signature {
            width: 45%;
            margin-left: auto;
            margin-top: 35px;
        }

        .signature p {
            margin: 0 0 6px 0;
        }

        .signature img {
            width: 95px;
            margin: 10px 0;
        }

        /* ===== TEMBUSAN ===== */
        .tembusan {
            margin-top: 35px;
            font-size: 11.5pt;
        }

        .tembusan p {
            margin: 0 0 4px 0;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 60px;
            text-align: center;
        }

        .footer img {
            width: 320px;
        }
    </style>
</head>

<body>

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <img src="{{ public_path('img/logo.jpeg') }}" alt="Logo YARSI">
    </div>

    {{-- ================= JUDUL ================= --}}
    <div class="judul">
        <div class="title">SURAT TUGAS</div>
        <div class="subtitle">
            JURI LOMBA DEBAT {{ strtoupper($surat->data['nama_lomba']) }}
        </div>
        <div class="nomor">No. : {{ $surat->nomor_surat }}</div>
    </div>

    {{-- ================= PEMBUKA ================= --}}
    <p class="bold">
        Assalamu’alaikum Wr. Wb.
    </p>

    <p class="justify" style="text-indent:30px;">
        {{ $surat->data['informasi_lomba'] }},
        <span class="bold">dengan ini kami mengizinkan dan menugaskan:</span>
    </p>

    {{-- ================= NAMA DOSEN ================= --}}
    <p class="center bold" style="margin:20px 0; font-size:13pt;">
        {{ $surat->data['nama_dosen'] }}
    </p>

    {{-- ================= PENUGASAN ================= --}}
    <p class="bold justify">
        Untuk menjadi Juri kegiatan {{ $surat->data['nama_lomba'] }} pada:
    </p>

    <table style="margin-top:15px; margin-left:30px;">
        <tr>
            <td width="20%">Tanggal</td>
            <td width="3%">:</td>
            <td>{{ $surat->data['tanggal_lomba'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $surat->data['waktu_lomba'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $surat->data['tempat_lomba'] ?? '-' }}</td>
        </tr>
    </table>

    <p class="justify" style="margin-top:20px;">
        Demikian surat tugas ini kami diberikan, agar dapat dipergunakan
        sebagaimana mestinya.
    </p>

    <p class="bold">
        Wassalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= TTD + QR ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p class="bold">
            DEKAN<br>
            FAKULTAS TEKNOLOGI INFORMASI
        </p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p class="bold">
            {{ $surat->penandatangan ?? 'Dr. Ummi Azizah Rachmawati, M.Kom.' }}
        </p>
    </div>

    {{-- ================= TEMBUSAN ================= --}}
    <div class="tembusan">
        <p class="bold">Tembusan :</p>
        <p>1. Wakil Dekan II</p>
        <p>2. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
        <p>3. Arsip</p>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</body>
</html>
