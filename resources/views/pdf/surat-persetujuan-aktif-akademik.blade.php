@php
$user = $surat->user;
$data = $surat->data;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
@page {
    size: A4;
    margin: 3cm 3cm 2.5cm 4cm; /* BAWAH DIKECILKAN */
}

body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.6;
    color: #000;
}

p {
    margin: 0 0 10px 0;
    text-align: justify;
}

table {
    border-collapse: collapse;
}

/* HEADER */
.header img {
    height: 55px;
    margin-bottom: 18px;
}

/* JUDUL */
.judul {
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 18px;
}

/* DATA */
.data {
    margin-left: 30px;
    margin-bottom: 12px;
}

.data td {
    padding: 1px 4px;
    vertical-align: top;
}

/* TTD */
.ttd-table {
    width: 100%;
    margin-top: 30px;
}

.ttd-cell {
    width: 45%;
    vertical-align: top;
}

.ttd-cell p {
    margin: 0 0 5px 0;
}

.qr {
    text-align: center;
    margin: 8px 0;
}

.qr img {
    width: 90px; 
}

.nama {
    text-align: center;
    font-weight: bold;
}

/* FOOTER */
.footer {
    margin-top: 30px;
    text-align: center;
}

.footer img {
    width: 280px;
}
</style>
</head>

<body>

{{-- HEADER --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

{{-- JUDUL --}}
<div class="judul">
    SURAT PERSETUJUAN AKTIF AKADEMIK
</div>

{{-- ISI --}}
<p>Assalamu’alaikum Wr. Wb.</p>

<p>
    Berdasarkan surat permohonan aktif akademik,
    dengan ini Kepala Program Studi
    {{ namaProdi($user->prodi) }}
    Fakultas Teknologi Informasi Universitas YARSI
    menyatakan bahwa:
</p>

<table class="data">
    <tr><td width="140">Nama</td><td width="10">:</td><td>{{ $user->name }}</td></tr>
    <tr><td>NPM</td><td>:</td><td>{{ $user->npm }}</td></tr>
    <tr><td>Program Studi</td><td>:</td><td>{{ namaProdi($user->prodi) }}</td></tr>
    <tr>
        <td>Tanggal</td><td>:</td>
        <td>{{ \Carbon\Carbon::parse($data['tanggal_permohonan_disetujui'])->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<p>
    Ybs diminta untuk melakukan pembayaran paling lambat 1 minggu
    sebelum batas akhir cicilan pertama yaitu tanggal
    <strong>{{ \Carbon\Carbon::parse($data['batas_pembayaran'])->translatedFormat('d F Y') }}</strong>
    dengan nominal pembayaran
    <strong>Rp {{ number_format($data['nominal_pembayaran'],0,',','.') }}</strong>.
</p>

<p>
    Jika ybs tidak melakukan pembayaran sampai tanggal yang telah ditetapkan,
    maka permohonan aktif akademik dibatalkan.
</p>

<p>
    Atas perhatian dan kerjasamanya, kami mengucapkan terima kasih.
</p>

<p>Wassalamu’alaikum Wr. Wb.</p>

{{-- TTD --}}
<table class="ttd-table">
<tr>
    <td></td>
    <td class="ttd-cell">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}
        </p>

        <p><strong>Kepala Program Studi</strong></p>

        @if($surat->qr_token)
            <div class="qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ url('/surat/validasi/'.$surat->qr_token) }}">
            </div>
        @endif

        <div class="nama">
            {{ $surat->penandatangan }}
        </div>
    </td>
</tr>
</table>

{{-- FOOTER --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}">
</div>

</body>
</html>
