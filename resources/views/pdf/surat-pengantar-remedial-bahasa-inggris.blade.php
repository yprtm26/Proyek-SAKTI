@php
function labelMataKuliah($kode) {
    return match ($kode) {
        'BI1' => 'Bahasa Inggris I',
        'BI2' => 'Bahasa Inggris II',
        'BI3' => 'Bahasa Inggris III',
        'BI4' => 'Bahasa Inggris IV',
        'BI5' => 'Bahasa Inggris V',
        'BI6' => 'Bahasa Inggris VI',
        default => $kode,
    };
}
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
    font-size: 11pt;
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

/* ================= TABLE ================= */
.data-table {
    margin: 10px 0 14px 0;
}

.data-table th,
.data-table td {
    border: 1px solid #000;
    padding: 5px 6px;
    text-align: center;
}

.data-table th {
    font-weight: bold;
}

/* ================= SIGNATURE ================= */
.signature {
    width: 48%;
    margin-left: auto;
    margin-top: 20px;
}

.signature p {
    margin: 0 0 6px 0;
}

.signature img {
    width: 85px;
    margin: 8px 0;
}

.signature .nama {
    font-weight: bold;
}

/* ================= FOOTER ================= */
.footer {
    position: absolute;
    bottom: 0cm;
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
    <td>{{ $surat->nomor_surat }}</td>
</tr>
<tr>
    <td>Rujukan Anda</td>
    <td>:</td>
    <td></td>
</tr>
<tr>
    <td>Perihal</td>
    <td>:</td>
    <td><strong>Remedial Semester Antara</strong></td>
</tr>
</table>
</div>

<br>

{{-- ================= ISI (TIDAK DIUBAH) ================= --}}
<p><strong>
Kepada Yth:<br>
Manager Optima Language Centre<br>
Di Jakarta
</strong></p>

<p>Assalamu’alaikum warahmatullahi wabarakatuh</p>

<p>
Sehubungan dengan akan dilaksanakan semester antara mata kuliah Bahasa Inggris,
kami dari Program Studi <strong>{{ namaProdi($surat->user->prodi) }}</strong>,
Fakultas Teknologi Informasi Universitas YARSI,
bermaksud mengirimkan mahasiswa kami untuk mengikuti Program Remedial tersebut.
Adapun daftar mahasiswa adalah sebagai berikut:
</p>

{{-- ================= TABEL MAHASISWA ================= --}}
<table class="data-table">
<thead>
<tr>
    <th width="8%">No</th>
    <th>Nama</th>
    <th width="20%">NPM</th>
    <th>Mata Kuliah</th>
</tr>
</thead>
<tbody>
@foreach($surat->data['mahasiswa'] as $i => $mhs)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $mhs['nama'] }}</td>
    <td>{{ $mhs['npm'] }}</td>
    <td>
        {{ collect($mhs['mata_kuliah'] ?? [])
            ->map(fn($k) => labelMataKuliah($k))
            ->implode(', ') ?: '-' }}
    </td>
</tr>
@endforeach
</tbody>
</table>

<p>
Kami berharap program remedial ini dapat membantu mahasiswa kami dalam
meningkatkan kemampuan Bahasa Inggris mereka sehingga dapat mencapai hasil
yang lebih baik di semester mendatang.
</p>

<p>
Demikianlah, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
</p>

<p>Wassalamu’alaikum warahmatullahi wabarakatuh</p>

{{-- ================= TTD ================= --}}
<div class="signature">
<p>
Jakarta,
{{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') }}
</p>

<p>
Kepala Program Studi {{ namaProdi($surat->user->prodi) }}
</p>

@if($surat->qr_token)
    <img src="{{ $qrUrl }}">
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
