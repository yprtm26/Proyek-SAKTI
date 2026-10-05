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
    margin: 2.5cm 2.5cm 2.5cm 3cm;
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

/* ================= HEADER ================= */
.header img {
    height: 65px;
    margin-bottom: 15px;
}

/* ================= META ================= */
.meta td {
    padding: 1px 4px;
    vertical-align: top;
    font-size: 11pt;
}

/* ================= TABEL ANGGOTA ================= */
.anggota-table {
    margin: 10px 0 12px 0;
}

.anggota-table th,
.anggota-table td {
    border: 1px solid #000;
    padding: 4px 6px;
    font-size: 11pt;
}

.anggota-table th {
    text-align: center;
    font-weight: bold;
}

/* ================= SIGNATURE ================= */
.signature {
    width: 48%;
    margin-left: auto;
    margin-top: 18px;
}

.signature .jabatan {
    line-height: 1.4;
}

.signature .qr img {
    width: 85px;
}

.signature .nama {
    margin-top: 4px;
    font-weight: bold;
}

/* ================= FOOTER ================= */
.footer {
    position: absolute;
    bottom: 0.8cm;
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

{{-- ================= HEADER ================= --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

{{-- ================= META SURAT ================= --}}
<div class="meta">
<table>
<tr>
    <td width="25%">Rujukan Kami</td>
    <td width="5%">:</td>
    <td>{{ $surat->nomor_surat ?? '—' }}</td>
</tr>
<tr>
    <td>Rujukan Anda</td>
    <td>:</td>
    <td></td>
</tr>
<tr>
    <td>Perihal</td>
    <td>:</td>
    <td><strong>{{ $surat->data['perihal'] }}</strong></td>
</tr>
</table>
</div>

<br>

{{-- ================= TUJUAN ================= --}}
<p>
<strong>
Kepada Yth :<br>
{{ $surat->data['kepada_yth'] }}<br>
{{ $surat->data['nama_instansi'] }}<br>
Di {{ $surat->data['kota_instansi'] }}
</strong>
</p>

<p>
    Assalaamu’alaikum warahmatullahi wabarakatuh
</p>

{{-- ================= ISI ================= --}}
<p>
Diberitahukan dengan hormat bahwa mahasiswa kami :
</p>

{{-- ================= TABEL ANGGOTA ================= --}}
<table class="anggota-table">
<thead>
<tr>
    <th width="8%">No</th>
    <th>Nama</th>
    <th width="30%">NPM</th>
</tr>
</thead>
<tbody>
@foreach($surat->data['anggota_tim'] as $i => $anggota)
<tr>
    <td style="text-align:center;">{{ $i + 1 }}</td>
    <td>{{ $anggota['nama'] }}</td>
    <td>{{ $anggota['npm'] }}</td>
</tr>
@endforeach
</tbody>
</table>

<p>
Sehubungan dengan tugas <strong>Mata Kuliah {{ $surat->data['mata_kuliah'] }}</strong>,
kami mohon kesediaan Bapak/Ibu untuk dapat mengijinkan mahasiswa yang bersangkutan
<strong>untuk melakukan {{ $surat->data['jenis_kegiatan'] }}</strong>,
yang Bapak/Ibu pimpin.
</p>

<p>
Demikianlah permohonan ini kami sampaikan, atas bantuan dan kerjasamanya
kami ucapkan terima kasih.
</p>

<p>
    Wassalaamu’alaikum warahmatullahi wabarakatuh
</p>

{{-- ================= SIGNATURE ================= --}}
<div class="signature">
<p>
Jakarta,
{{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
    ?? now()->translatedFormat('d F Y') }}
</p>

<div class="jabatan">
<strong>DEKAN</strong><br>
<strong>FAKULTAS TEKNOLOGI INFORMASI</strong>
</div>

@if($surat->qr_token)
<div class="qr">
    <img src="{{ $qrUrl }}">
</div>
@endif

<div class="nama">
{{ $surat->penandatangan ?? '—' }}
</div>
</div>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}">
</div>

</body>
</html>
