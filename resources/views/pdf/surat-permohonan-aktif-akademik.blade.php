<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
@page {
    size: A4;
    margin: 3cm 3cm 3cm 4cm;
}

body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.7;
    color: #000;
}

p {
    margin: 0 0 12px 0;
    text-align: justify;
}

table {
    border-collapse: collapse;
}

/* ================= FOOTER ================= */
.footer {
    position: absolute;
    bottom: 0.3cm; /* diturunkan lagi */
    left: 0;
    right: 0;
    text-align: center;
}

.footer img {
    width: 320px;
}
</style>
</head>

<body>

{{-- ================= HEADER LOGO ================= --}}
<div style="margin-bottom:30px;">
    <img src="{{ public_path('img/logo.jpeg') }}" style="height:60px;">
</div>

{{-- ================= JUDUL ================= --}}
<p style="font-weight:bold; margin-bottom:20px; text-align: center;">
    PERMOHONAN AKTIF AKADEMIK
</p>

<p>Assalamu’alaikum Wr. Wb.</p>

<p>Yang bertanda tangan di bawah ini:</p>

<table style="margin-left:30px; margin-bottom:16px;">
    <tr>
        <td width="140">Nama</td>
        <td width="10">:</td>
        <td>{{ $surat->user->name }}</td>
    </tr>
    <tr>
        <td>NPM</td>
        <td>:</td>
        <td>{{ $surat->user->npm }}</td>
    </tr>
    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ namaProdi($surat->user->prodi) }}</td>
    </tr>
</table>

<p>
    Mengajukan permohonan aktif akademik pada semester
    <strong>{{ ucfirst($surat->data['semester']) }}</strong>
    tahun akademik
    <strong>{{ $surat->data['tahun_akademik'] }}</strong>.
</p>

<p>
    Demikian permohonan ini saya sampaikan.
    Atas perhatian Bapak/Ibu, saya ucapkan terima kasih.
</p>

<p>Wassalamu’alaikum Wr. Wb.</p>

{{-- ================= TTD ================= --}}
<table width="45%" style="margin-left:auto; margin-top:45px; text-align:center;">
    <tr>
        <td>
            Jakarta, {{ now()->translatedFormat('d F Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding-top:6px;">
            Mahasiswa/i,
        </td>
    </tr>

    @if($surat->qr_token)
    <tr>
        <td style="padding-top:10px;">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                style="width:120px;"
            >
        </td>
    </tr>
    @endif

    <tr>
        <td style="padding-top:6px; font-weight:bold;">
            {{ $surat->user->name }}
        </td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
</div>

</body>
</html>
