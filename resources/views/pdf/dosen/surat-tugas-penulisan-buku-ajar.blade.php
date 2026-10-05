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
        line-height: 1.6;
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
        height: 65px;
    }

    /* ================= JUDUL ================= */
    .judul {
        text-align: center;
        margin-top: 25px;
        margin-bottom: 30px;
    }

    .judul .title {
        font-size: 16pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .judul .subtitle {
        font-size: 14pt;
        font-weight: bold;
        margin-top: 6px;
        text-decoration: underline;
    }

    .judul .nomor {
        margin-top: 10px;
        font-weight: bold;
    }

    /* ================= ANGGOTA ================= */
    ul {
        margin: 12px 0 12px 45px;
    }

    ul li {
        margin-bottom: 4px;
        font-weight: bold;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        width: 45%;
        margin-left: auto;
        margin-top: 10px;
    }

    .signature p {
        margin: 0 0 6px 0;
    }

    .signature img {
        width: 70px;
        margin: 8px 0;
        display: block;
    }

    /* ================= TEMBUSAN ================= */
    .tembusan {
        margin-top: 10px;
        font-size: 11.5pt;
    }

    .tembusan p {
        margin: 0 0 4px 0;
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
<div class="judul">
    <div class="title">SURAT TUGAS</div>
    <div class="subtitle">
        PENULISAN BUKU AJAR
        <span style="text-transform: uppercase;">
            {{ $surat->data['prodi'] }}
        </span>
    </div>
    <div class="nomor">
        No. : {{ $surat->nomor_surat }}
    </div>
</div>

{{-- ================= PEMBUKA ================= --}}
<p class="bold">
    Assalamu’alaikum Wr. Wb.
</p>

<p style="text-indent:30px;">
    Dalam rangka pemenuhan salah satu dharma di bidang penelitian dosen
    Fakultas Teknologi Informasi Universitas YARSI, dengan ini kami
    <strong>mengizinkan dan menugaskan:</strong>
</p>

{{-- ================= DAFTAR ANGGOTA ================= --}}
<ul>
    @foreach($surat->data['anggota'] ?? [] as $a)
        <li>{{ $a['nama'] ?? '-' }}</li>
    @endforeach
</ul>

{{-- ================= ISI ================= --}}
<p style="text-indent:30px;">
    Untuk menulis buku ajar Program Studi
    <strong>{{ $surat->data['prodi'] ?? '-' }}</strong>
    dan mendaftarkan Surat Pencatatan Ciptaan Buku dengan judul
    <strong>"{{ $surat->data['judul'] ?? '-' }}"</strong>
    ke {{ $surat->data['tempat'] ?? '-' }}
    pada
    {{
        !empty($surat->data['tanggal'])
            ? \Carbon\Carbon::parse($surat->data['tanggal'])->translatedFormat('d F Y')
            : '-'
    }}.
</p>

<p style="text-indent:30px;">
    Demikian surat tugas ini diberikan agar pihak-pihak yang terkait
    dapat membantu seperlunya.
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

    <p class="bold">DEKAN</p>
    <p class="bold">FAKULTAS TEKNOLOGI INFORMASI</p>

    @if($surat->qr_token)
        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
            alt="QR Validasi">
    @endif

    <p class="bold">{{ $surat->penandatangan ?? '—' }}</p>
</div>

{{-- ================= TEMBUSAN ================= --}}
<div class="tembusan">
    <p class="bold">Tembusan :</p>
    <p>1. Wakil Rektor I</p>
    <p>2. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
    <p>3. Arsip</p>
</div>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
</div>

</body>
</html>
