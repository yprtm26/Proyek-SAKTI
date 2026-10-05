<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td>
            <img src="{{ public_path('img/logo.jpeg') }}" width="180">
        </td>
    </tr>
</table>

{{-- ================= JUDUL ================= --}}
<table style="margin-top:35px;">
    <tr>
        <td class="center bold underline" style="font-size:16pt;">
            SURAT TUGAS
        </td>
    </tr>
    <tr>
        <td class="center bold" style="padding-top:6px;">
            PENULISAN DAN PUBLIKASI ARTIKEL ILMIAH
        </td>
    </tr>
    <tr>
        <td class="center bold" style="padding-top:10px;">
            Nomor : {{ $surat->nomor_surat }}
        </td>
    </tr>
</table>

{{-- ================= PEMBUKA ================= --}}
<p class="justify bold" style="margin-top:40px; text-indent:30px;">
    Assalamu’alaikum Wr. Wb.
</p>

<p class="justify" style="margin-top:15px; text-indent:30px;">
    Dalam rangka pemenuhan luaran hasil penelitian dosen Fakultas Teknologi Informasi
    Universitas YARSI, dengan ini <strong style="font-weight: bold">kami mengizinkan dan menugaskan dosen:</strong>
</p>

{{-- ================= IDENTITAS DOSEN ================= --}}
<table style="margin-top:20px;">
    <tr>
        <td class="center bold">{{ $surat->data['nama_dosen'] }}</td>
    </tr>
</table>

{{-- ================= ISI TUGAS ================= --}}
<p class="justify" style="margin-top:10px;">
        {{ $surat->data['tujuan_penulisan'] }} dengan judul: <strong style="font-weight: bold">{{ $surat->data['judul_artikel'] }}</strong>
</p>

<p class="justify" style="margin-top:20px;">
    Demikian surat tugas ini kami berikan agar dapat dipergunakan sebagaimana mestinya.
</p>

<p class="justify bold" style="margin-top:15px;">
    Wassalamu’alaikum Wr. Wb.
</p>

{{-- ================= TTD DEKAN (QR) ================= --}}
<table>
    <tr>
        <td width="60%"></td>
        <td>
            Jakarta, {{ \Carbon\Carbon::parse($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}<br>
            <strong>DEKAN</strong><br>
            <strong>FAKULTAS TEKNOLOGI INFORMASI</strong>
            <br>
            <img src="{{ $qrUrl }}" width="90"><br>
            <strong>{{ $surat->penandatangan }}</strong>
        </td>
    </tr>
</table>

{{-- ================= TEMBUSAN ================= --}}
<table style="margin-top:40px;">
    <tr>
        <td>Tembusan :</td>
    </tr>
    <tr>
        <td>1. Kepala Prodi TI</td>
    </tr>
    <tr>
        <td>2. Arsip</td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<table style="margin-top:10px;">
    <tr>
        <td class="center">
            <img src="{{ public_path('img/footer.jpg') }}" width="350">
        </td>
    </tr>
</table>

</body>
</html>
