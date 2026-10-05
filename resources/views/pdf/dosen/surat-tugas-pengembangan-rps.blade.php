<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
    @page {
        size: A4;
        margin: 2cm 2cm 2cm 2cm; /* bawah lebih besar utk footer */
    }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.5;
        color: #000;
    }

    p {
        margin: 0 0 12px 0;
        text-align: justify;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .center { text-align: center; }
    .bold { font-weight: bold; }
    .underline { text-decoration: underline; }

    /* ================= HEADER ================= */
    .header img {
        height: 70px;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        width: 45%;
        margin-left: auto;
        margin-top: 30px;
        text-align: left;
    }

    .signature p {
        margin: 0 0 6px 0;
    }

    .signature img {
        width: 65px; /* QR diperkecil */
        margin: 8px 0;
        display: block;
    }

    /* ================= FOOTER ================= */
    .footer {
        margin-top: 35px;
        text-align: center;
    }

    .footer img {
        width: 300px;
        display: block;
        margin: 0 auto;
    }
</style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}" alt="Logo YARSI">
</div>

{{-- ================= JUDUL ================= --}}
<table style="margin-top:10px;">
    <tr>
        <td class="center bold underline" style="font-size:16pt;">
            SURAT TUGAS
        </td>
    </tr>
    <tr>
        <td class="center bold underline" style="padding-top:6px;">
            MENGEMBANGKAN DESAIN PEMBELAJARAN<br>
            MATA KULIAH
            <span style="text-transform: uppercase;">
                {{ $surat->data['nama_matkul'] }}
            </span>
        </td>
    </tr>
    <tr>
        <td class="center bold" style="padding-top:10px;">
            No. : {{ $surat->nomor_surat }}
        </td>
    </tr>
</table>

{{-- ================= PEMBUKA ================= --}}
<p class="bold" style="margin-top:10px;">
    Assalamu’alaikum Wr. Wb.
</p>

{{-- ================= ISI ================= --}}
<p>
    Dalam rangka peningkatan kualitas pembelajaran dan persiapan pelaksanaan
    kegiatan Proses Belajar Mengajar (PBM) pada semester
    <strong>{{ $surat->data['semester'] }}</strong>
    TA.
    <strong>{{ $surat->data['tahun_akademik'] }}</strong>
    bagi mahasiswa Prodi
    <strong>{{ $surat->data['prodi'] }}</strong>
    Fakultas Teknologi Informasi Universitas YARSI,
    dengan ini kami menugaskan dosen:
</p>

<table style="margin-top:10px;">
    <tr>
        <td class="center bold" style="font-size:13pt;">
            {{ $surat->data['nama_dosen'] }}
        </td>
    </tr>
</table>

<p style="margin-top:10px;">
    Untuk membuat dan mengembangkan
    <strong>Rencana Pembelajaran Semester (RPS)</strong>
    mata kuliah
    <strong>"{{ $surat->data['nama_matkul'] }}"</strong>
    sesuai dengan kebutuhan.
</p>

<p>
    Demikian surat tugas ini kami berikan agar dapat dipergunakan sebagaimana mestinya.
</p>

<p class="bold">
    Wassalamu’alaikum Wr. Wb.
</p>

{{-- ================= TTD ================= --}}
<div class="signature">
    <p>
        Jakarta,
        {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
            ?? now()->translatedFormat('d F Y') }}
    </p>

    <p class="bold">
        DEKAN<br>
        FAKULTAS TEKNOLOGI INFORMASI
    </p>

    @if($surat->qr_token)
        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
            alt="QR Validasi">
    @endif

    <p class="bold">
        {{ $surat->penandatangan ?? 'Dr. Ummi Azizah Rachmawati, M.Kom.' }}
    </p>
</div>

{{-- ================= TEMBUSAN ================= --}}
<table style="margin-top:30px;">
    <tr>
        <td class="bold">Tembusan :</td>
    </tr>
    <tr>
        <td>1. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</td>
    </tr>
    <tr>
        <td>2. Arsip</td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
</div>

</body>
</html>
