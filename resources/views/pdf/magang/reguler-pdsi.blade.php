@php
    use Carbon\Carbon;
    $data = $surat->data;
    $mahasiswa = $data['mahasiswa'];
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        table.meta td {
            padding: 2px 4px;
        }
        .justify {
            text-align: justify;
        }
        .ttd {
            margin-top: 50px;
            text-align: center;
        }
        .lampiran-table {
            width: 100%;
            border-collapse: collapse;
        }
        .lampiran-table th,
        .lampiran-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}" height="60">
</div>

<table class="meta">
    <tr><td width="140">Rujukan kami</td><td>: {{ $surat->nomor_surat }}</td></tr>
    <tr><td>Rujukan anda</td><td>:</td></tr>
    <tr><td>Perihal</td><td>: <strong>Permohonan Izin Praktek Kerja Lapangan (PKL)</strong></td></tr>
</table>

<p class="judul">Mahasiswa FTI Universitas YARSI</p>

<p>
    Kepada Yth.<br>
    {{ $data['kepada_yth'] }}<br>
    {{ $data['nama_mitra'] }}<br>
    {{ $data['alamat_mitra'] }}
</p>

<p><em>Assalamu’alaikum Wr. Wb.</em></p>

<p class="justify">
    Salah satu program yang wajib dilaksanakan dalam menempuh Pendidikan di
    Universitas YARSI Fakultas Teknologi Informasi Program Studi
    <strong>Perpustakaan dan Sains Informasi</strong> adalah kegiatan magang
    Merdeka Belajar Kampus Merdeka (MBKM).
</p>

<p class="justify">
    Pelaksanaan magang dimulai
    {{ Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') }}
    sampai
    {{ Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') }}.
    Adapun peserta magang mahasiswa kami sebagai berikut (data terlampir).
</p>

<p>Adapun tujuan dari Magang adalah agar mahasiswa:</p>
<ul>
    <li>Manajemen koleksi, administrasi, dan organisasi</li>
    <li>Pengolahan koleksi tercetak dan non tercetak</li>
    <li>Layanan informasi dan perpustakaan</li>
    <li>Teknologi informasi</li>
    <li>Preservasi informasi dan konservasi media</li>
    <li>Pengelolaan kearsipan</li>
</ul>

<div class="ttd">
    <p>Jakarta, {{ $surat->tanggal_disetujui_final->translatedFormat('d F Y') }}</p>
    <p><strong>DEKAN FAKULTAS TEKNOLOGI INFORMASI</strong></p>
    <img src="{{ $qrUrl }}" width="90">
    <p><strong>Dr. Ummi Azizah Rachmawati, M.Kom.</strong></p>
</div>

<p>
    Tembusan:
    <br>• Ka. Prodi PdSI
    <br>• Koord Magang
    <br>• Arsip
</p>

<div class="page-break"></div>

<p><strong>Lampiran : {{ $surat->nomor_surat }}</strong></p>

<table class="lampiran-table">
    <tr>
        <th>No</th><th>NPM</th><th>Nama</th><th>No. HP</th>
    </tr>
    @foreach($mahasiswa as $i => $m)
    <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $m['npm'] }}</td>
        <td>{{ $m['nama'] }}</td>
        <td>{{ $m['no_kontak'] }}</td>
    </tr>
    @endforeach
</table>

</body>
</html>
