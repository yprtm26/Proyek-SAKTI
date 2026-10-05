@php
    $prodi = $surat->user->prodi;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
@page {
    size: A4;
    margin: 2m 2cm 2cm 2cm; /* DIPERKECIL */
}

body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.45;
    color: #000;
}

p {
    text-align: justify;
    margin: 0 0 10px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

/* HEADER */
.header img {
    height: 65px;
    margin-bottom: 15px;
}

/* META */
.meta td {
    padding: 1px 4px;
    vertical-align: top;
}

/* DATA */
.data-table {
    margin-left: 1cm;
    margin-bottom: 10px;
}

.data-table td {
    padding: 1px 4px;
}

/* SIGNATURE */
.signature {
    width: 48%;
    margin-left: auto;
    margin-top: 20px; /* DIPERKECIL */
}

.signature .jabatan {
    line-height: 1.4;
}

.signature .qr img {
    width: 85px; /* DIPERKECIL */
}

.signature .nama {
    margin-top: 4px;
    font-weight: bold;
}

/* FOOTER */
.footer {
    position: absolute;
    bottom: 0.5cm;
    left: 0;
    right: 0;
    text-align: center;
}

.footer img {
    width: 300px;
}
</style>
</head>

<body>

<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

<div class="meta">
<table>
<tr>
    <td width="25%">Rujukan Kami</td><td width="5%">:</td>
    <td>{{ $surat->nomor_surat }}</td>
</tr>
<tr>
    <td>Rujukan Anda</td><td>:</td><td></td>
</tr>
<tr>
    <td>Perihal</td><td>:</td>
    <td>{{ $surat->data['perihal'] }}</td>
</tr>
</table>
</div>
<br>
<p><strong>Kepada Yth :<br>
{{ $surat->data['kepada'] }}<br>
{{ $surat->data['instansi'] }}<br>
{{ $surat->data['kota_instansi'] }}</strong></p>

<p>Assalamu’alaikum warahmatullahi wabarakatuh</p>

<p>
Dalam rangka penyelesaian studi mahasiswa kami di Program Studi
{{ namaProdi($prodi) }} Program Sarjana (S1)
Fakultas Teknologi Informasi Universitas YARSI,
diperlukan penelitian guna menyelesaikan penulisan skripsinya.
</p>

<p>
Untuk itu, bersama ini kami mohon Bapak/Ibu berkenan mengizinkan mahasiswa
tersebut untuk melakukan
<strong>{{ $surat->data['jenis_kegiatan'] }}</strong>
terkait dengan penelitian skripsinya yaitu :
</p>

<table class="data-table">
<tr><td width="30%">Nama</td><td width="5%">:</td><td>{{ $surat->user->name }}</td></tr>
<tr><td>NPM</td><td>:</td><td>{{ $surat->user->npm }}</td></tr>
<tr><td>Judul Skripsi</td><td>:</td><td>{{ $surat->data['judul_skripsi'] }}</td></tr>
</table>

<p>
Demikianlah permohonan ini kami sampaikan, atas perhatian dan perkenan
Bapak/Ibu kami ucapkan terima kasih.
</p>

<p>Wassalaamu’alaikum warahmatullahi wabarakatuh</p>

<div class="signature">
<p>Jakarta, {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}</p>

<div class="jabatan">
<strong>DEKAN</strong><br>
<strong>FAKULTAS TEKNOLOGI INFORMASI</strong>
</div>

@if($surat->qr_token)
<div class="qr">
    <img src="{{ $qrUrl }}">
</div>
@endif

<div class="nama">{{ $surat->penandatangan }}</div>
</div>

<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}">
</div>

</body>
</html>
