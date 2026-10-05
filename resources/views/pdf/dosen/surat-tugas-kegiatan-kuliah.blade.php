<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .justify { text-align: justify; }
        table { width: 100%; }
        ul { margin-left: 25px; }
    </style>
</head>
<body>

{{-- HEADER --}}
<table>
    <tr>
        <td>
            <img src="{{ public_path('img/logo.jpeg') }}" width="180">
        </td>
    </tr>
</table>

{{-- JUDUL --}}
<p class="center bold underlined" style="font-size:16pt; margin-top:30px;">
    SURAT TUGAS
</p>
<p class="center bold">
    No : {{ $surat->nomor_surat }}
</p>

{{-- PEMBUKA --}}
<p class="bold" style="margin-top:30px;">
    Assalamu’alaikum Wr. Wb.
</p>

<p class="justify" style="margin-top: 20px;">
    Yang bertanda tangan di bawah ini :
</p>

{{-- IDENTITAS PENUGAS --}}
<table style="margin-top: 10px; margin-left:30px;">
    <tr>
        <td width="80">Nama</td>
        <td width="10">:</td>
        <td>{{ $surat->data['nama_penugas'] }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>{{ $surat->data['jabatan_penugas'] }}</td>
    </tr>
    <tr>
        <td>Instansi</td>
        <td>:</td>
        <td>{{ $surat->data['instansi_penugas'] }}</td>
    </tr>
</table>

<p class="justify" style="margin-top:15px;">
    Dengan ini menugaskan kepada:
</p>

{{-- IDENTITAS DITUGASKAN --}}
<table style="margin-left:30px;">
    <tr>
        <td width="80">Nama</td>
        <td width="10">:</td>
        <td>{{ $surat->data['nama_ditugaskan'] }}</td>
    </tr>
    <tr>
        <td>NIP</td>
        <td>:</td>
        <td>{{ $surat->data['no_nip'] }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>{{ $surat->data['jabatan_ditugaskan'] }}</td>
    </tr>
    <tr>
        <td>Instansi</td>
        <td>:</td>
        <td>{{ $surat->data['instansi_ditugaskan'] }}</td>
    </tr>
</table>

{{-- ISI TUGAS --}}
<p class="justify" style="margin-top:15px;">
    Untuk membantu pelaksanaan kegiatan
    <strong>{{ $surat->data['nama_kegiatan'] }}</strong>
    ({{ $surat->data['sks'] }} SKS) yang dilaksanakan pada:
</p>

<table style="margin-top:20px; margin-left:30px;">
    <tr>
        <td width="80">Hari / Tanggal</td>
        <td width="10">:</td>
        <td>
            {{ \Carbon\Carbon::parse($surat->data['tanggal'])->translatedFormat('l, d F Y') }}
        </td>
    </tr>
    <tr>
        <td>Pukul</td>
        <td>:</td>
        <td>{{ $surat->data['waktu'] }}</td>
    </tr>
    <tr>
        <td>Tempat</td>
        <td>:</td>
        <td>{{ $surat->data['tempat'] }}</td>
    </tr>
</table>


<p class="justify" style="margin-top:20px;">
    Demikian surat tugas ini dibuat, agar dapat dilaksanakan dengan penuh tanggung jawab.
</p>

<p class="bold" style="margin-top:15px;">
    Wassalamu’alaikum Wr. Wb.
</p>

{{-- TTD --}}
<table>
    <tr>
        <td width="50%"></td>
        <td>
            Jakarta,
            {{ \Carbon\Carbon::parse($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}<br>
            <strong>DEKAN</strong><br>
            <strong>FAKULTAS TEKNOLOGI INFORMASI</strong><br><br>

            @if(isset($qrUrl))
                <img src="{{ $qrUrl }}" width="70"><br>
            @endif

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
        <td>1. Kalab & K</td>
    </tr>
    <tr>
        <td>2. Arsip</td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<table style="margin-top:80px;">
    <tr>
        <td class="center">
            <img src="{{ public_path('img/footer.jpg') }}" width="350">
        </td>
    </tr>
</table>

</body>
</html>
