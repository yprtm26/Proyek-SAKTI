@php
    $user = $surat->user;
    $kaprodi = \App\Models\User::where('role','kaprodi')
        ->where('prodi',$user->prodi)
        ->first();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
@page {
    size: A4;
    margin: 2.5cm 3cm 3.2cm 4cm;
}

body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.45;
    color: #000;
}

p {
    margin: 0 0 8px 0;
    text-align: justify;
}

table {
    width: 100%;
    border-collapse: collapse;
}

/* HEADER */
.header img {
    height: 65px;
    margin-bottom: 18px;
}

/* JUDUL */
.title {
    text-align: center;
    margin-bottom: 16px;
}

.title h2 {
    margin: 0;
    font-size: 14pt;
    font-weight: bold;
    text-decoration: underline;
}

.title .nomor {
    margin-top: 4px;
}

/* DATA */
.data {
    margin-left: 1cm;
    margin-bottom: 10px;
}

.data td {
    padding: 1px 4px;
    vertical-align: top;
}

/* SIGNATURE */
.signature {
    width: 48%;
    margin-left: auto;
    margin-top: 18px;
}

.signature .qr {
    text-align: center;
    margin: 8px 0;
}

.signature .qr img {
    width: 90px;
}

.signature .nama {
    text-align: center;
    font-weight: bold;
    margin-top: 4px;
}

/* PAGE BREAK */
.page-break {
    page-break-after: always;
}

/* FOOTER */
.footer {
    position: fixed;
    bottom: -0.6cm; /* DITURUNKAN */
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

{{-- ================= HALAMAN 1 ================= --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

<div class="title">
    <h2>SURAT REKOMENDASI</h2>
    <div class="nomor">{{ $surat->nomor_surat ?? '—' }}</div>
</div>

<p>Yang bertanda tangan di bawah ini:</p>

<table class="data">
    <tr><td width="160">Nama</td><td width="10">:</td><td>{{ $kaprodi->name }}</td></tr>
    <tr><td>Jabatan</td><td>:</td><td>Kepala Program Studi {{ namaProdi($user->prodi) }}</td></tr>
    <tr><td>NIK</td><td>:</td><td>{{ $kaprodi->nidn }}</td></tr>
    <tr><td>E-mail</td><td>:</td><td>{{ $kaprodi->emailyarsi }}</td></tr>
    <tr><td>No. Telp</td><td>:</td><td>{{ $kaprodi->telephonenumber }}</td></tr>
</table>

<p>Menyetujui Nama mahasiswa berikut</p>

<table class="data">
    <tr><td width="160">Nama</td><td width="10">:</td><td>{{ $user->name }}</td></tr>
    <tr><td>NPM</td><td>:</td><td>{{ $user->npm }}</td></tr>
    <tr><td>Program Studi</td><td>:</td><td>{{ namaProdi($user->prodi) }}</td></tr>
    <tr><td>Fakultas</td><td>:</td><td>Fakultas Teknologi Informasi</td></tr>
    <tr><td>IPK</td><td>:</td><td>{{ $surat->data['ipk'] }}</td></tr>
    <tr><td>Jumlah SKS ditempuh</td><td>:</td><td>{{ $surat->data['sks'] }} SKS</td></tr>
</table>

<p>Untuk <strong>{{ strtoupper($surat->data['tujuan']) }}</strong> dengan pertimbangan sebagai berikut: (terlampir)</p>

<div class="signature">
    <p>
        Jakarta,
        {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}
    </p>

    <p>Kepala Program Studi {{ namaProdi($user->prodi) }}</p>

    <div class="qr">
        <img src="{{ $qrUrl }}">
    </div>

    <div class="nama">{{ $kaprodi->name }}</div>
</div>

<div class="page-break"></div>

{{-- ================= HALAMAN 2 ================= --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

<p><strong>Lampiran: {{ $surat->nomor_surat }}</strong></p>

<p>- Mengingat yang bersangkutan aktif dalam kegiatan organisasi dan sosial:</p>
<ul style="margin-left:1.2cm;">
@foreach($surat->data['pengalaman'] as $p)
    <li>{{ $p }}</li>
@endforeach
</ul>

<p>- Mengingat yang bersangkutan memiliki pengalaman di bidangnya:</p>
<ul style="margin-left:1.2cm;">
@foreach($surat->data['project'] as $pr)
    <li>{{ $pr }}</li>
@endforeach
</ul>

<p>
    Demikian surat rekomendasi ini diberikan sebagai pelengkap dokumen data diri
    untuk {{ $surat->data['tujuan'] }}. Atas perhatian Bapak/Ibu saya ucapkan terima kasih. 
</p>


{{-- FOOTER --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}">
</div>

</body>
</html>
