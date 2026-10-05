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
        .header {
            margin-bottom: 20px;
        }
        .header img {
            width: 120px;
        }
        .judul {
            font-weight: bold;
            margin-top: 10px;
        }
        table.meta td {
            vertical-align: top;
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
            margin-top: 20px;
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

{{-- ================= HALAMAN 1 ================= --}}

<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

<table class="meta">
    <tr>
        <td width="140">Rujukan kami</td>
        <td>: {{ $surat->nomor_surat }}</td>
    </tr>
    <tr>
        <td>Rujukan anda</td>
        <td>:</td>
    </tr>
    <tr>
        <td>Perihal</td>
        <td>: <strong>Permohonan Izin Praktek Kerja Lapangan (PKL)</strong></td>
    </tr>
</table>

<p class="judul">Mahasiswa FTI Universitas YARSI</p>

<p>
    Kepada Yth.<br>
    {{ $data['kepada_yth'] }}<br>
    {{ $data['nama_mitra'] }}<br>
    Alamat: {{ $data['alamat_mitra'] }}
</p>

<p><em>Assalamu’alaikum Wr. Wb.</em></p>

<p class="justify">
    Salah satu program yang wajib dilaksanakan dalam menempuh Pendidikan di
    Universitas YARSI Fakultas Teknologi Informasi Program Studi
    <strong>Teknik Informatika</strong> adalah kegiatan Kerja Praktek (magang)
    yang dilaksanakan oleh mahasiswa.
</p>

<p class="justify">
    Berdasarkan hal tersebut, kami bermaksud mengajukan permohonan kesediaan
    {{ $data['nama_mitra'] }} untuk dapat menerima mahasiswa kami melakukan
    Kerja Praktek (magang) di bidang Teknologi Informasi.
    Pelaksanaan magang dimulai pada tanggal
    {{ Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') }}
    sampai dengan
    {{ Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') }}.
    Adapun peserta magang mahasiswa kami sebagai berikut (data terlampir).
</p>

<p>Tujuan dari kegiatan magang ini adalah agar mahasiswa kami:</p>
<ol type="a">
    <li>Mengenal dan merasakan dunia kerja</li>
    <li>Menerapkan pengetahuan teoritis ke dalam dunia kerja</li>
    <li>Membekali mahasiswa dengan pengalaman praktik dalam bidang IT</li>
</ol>

<p class="justify">
    Demikianlah permohonan ini kami sampaikan, atas perhatian dan kerjasamanya
    kami ucapkan terima kasih.
</p>

<p><em>Wassalaamu’alaikum Wr. Wb.</em></p>

<div class="ttd">
    <p>Jakarta, {{ $surat->tanggal_disetujui_final->translatedFormat('d F Y') }}</p>
    <p><strong>DEKAN FAKULTAS TEKNOLOGI INFORMASI</strong></p>

    <img src="{{ $qrUrl }}" width="90">

    <p><strong>Dr. Ummi Azizah Rachmawati, M.Kom.</strong></p>
</div>

<p>
    Tembusan:
    <br>• Ka. Prodi TI
    <br>• Koord Magang
    <br>• Arsip
</p>

<div class="page-break"></div>

{{-- ================= HALAMAN 2 (LAMPIRAN) ================= --}}

<p><strong>Lampiran : {{ $surat->nomor_surat }}</strong></p>

<table class="lampiran-table">
    <thead>
        <tr>
            <th>No</th>
            <th>NPM</th>
            <th>Nama</th>
            <th>No. HP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mahasiswa as $i => $m)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $m['npm'] }}</td>
            <td>{{ $m['nama'] }}</td>
            <td>{{ $m['no_kontak'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
