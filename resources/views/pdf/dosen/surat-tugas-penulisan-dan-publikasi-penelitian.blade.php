<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 3cm 3cm 4cm 4cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #000;
        }

        /* ========== UTIL ========== */
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .justify { text-align: justify; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        p {
            margin: 0 0 10px 0;
        }

        /* ========== HEADER ========== */
        .header img {
            height: 65px;
        }

        /* ========== JUDUL ========== */
        .judul {
            text-align: center;
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .judul .title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }

        .judul .subtitle {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 6px;
            text-decoration: underline;
        }

        .judul .nomor {
            margin-top: 10px;
            font-weight: bold;
        }

        /* ========== TABEL ANGGOTA ========== */
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11.5pt;
        }

        th {
            text-align: center;
            font-weight: bold;
        }

        td:first-child {
            text-align: center;
            width: 6%;
        }

        /* ================= SIGNATURE ================= */
    .signature {
        width: 45%;
        margin-left: auto;
        margin-top: 30px;
    }

    .signature p {
        margin: 0 0 6px 0;
    }

    .signature img {
        margin: 10px 0;
        width: 95px;
    }

        /* ========== TEMBUSAN ========== */
        .tembusan {
            margin-top: 35px;
            font-size: 11.5pt;
        }

        .tembusan p {
            margin: 0 0 4px 0;
        }

        /* ========== FOOTER (AMAN DOMPDF) ========== */
        .footer {
            margin-top: 60px;
            text-align: center;
        }

        .footer img {
            width: 320px;
            display: block;
            margin: 0 auto;
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
    <div class="subtitle">PENULISAN DAN PUBLIKASI PENELITIAN</div>
    <div class="nomor">No. : {{ $surat->nomor_surat }}</div>
</div>

{{-- ================= PEMBUKA ================= --}}
<p class="bold">Assalamu’alaikum Wr. Wb.</p>

<p class="justify" style="text-indent:30px;">
    Dalam rangka pemenuhan luaran hasil penelitian dosen FTI Universitas YARSI,
    dengan ini <strong>kami mengizinkan dan menugaskan :</strong>
</p>

{{-- ================= TABEL ANGGOTA ================= --}}
<table style="margin-top:15px;">
    <thead>
        <tr>
            <th width="6%">No</th>
            <th width="54%">Nama</th>
            <th width="40%">Sebagai</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($surat->data['anggota'] ?? []) as $i => $a)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $a['nama'] }}</td>
            <td>{{ $a['keterangan'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= ISI ================= --}}
<p class="justify" style="margin-top:20px;">
    Untuk menulis, mempublikasikan penelitian dengan judul:
    <strong>"{{ $surat->data['judul'] ?? '-' }}"</strong>
    pada Jurnal {{ $surat->data['nama_jurnal'] ?? '' }} di
    <span style="text-decoration: underline;">
        {{ $surat->data['link_jurnal'] }}
    </span>.
</p>

<p class="justify" style="margin-top:15px; text-indent:30px;">
    Demikianlah surat tugas ini kami diberikan, agar dapat dipergunakan
    sebagaimana mestinya.
</p>

<p class="bold">Wassalamu’alaikum Wr. Wb.</p>

{{-- ================= TTD ================= --}}
<div class="signature">
    <p>
        Jakarta,
        {{ \Carbon\Carbon::parse($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}
    </p>

    <p><strong>DEKAN</strong></p>
    <p><strong>FAKULTAS TEKNOLOGI INFORMASI</strong></p>

    <img src="{{ $qrUrl }}" alt="QR Validasi">

    <p><strong>{{ $surat->penandatangan }}</strong></p>
</div>

{{-- ================= TEMBUSAN ================= --}}
<div class="tembusan">
    <p class="bold">Tembusan :</p>
    <p>1. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
    <p>2. Arsip</p>
</div>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
</div>

</body>
</html>