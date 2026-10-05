<style>
    /* ================= A4 PAGE ================= */
    .a4 {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        padding: 3cm 3cm 4cm 4cm;

        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.5;
        color: #000;

        box-shadow: 0 0 10px rgba(0,0,0,.15);
        position: relative;
    }

    .a4 p {
        margin: 0 0 12px 0;
        text-align: justify;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    /* ================= HEADER ================= */
    .header {
        margin-bottom: 25px;
    }

    .header img {
        height: 70px;
    }

    /* ================= JUDUL ================= */
    .judul {
        text-align: center;
        margin-bottom: 30px;
    }

    .judul .title {
        font-size: 16pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .judul .nomor {
        margin-top: 8px;
        font-weight: bold;
    }

    /* ================= IDENTITAS ================= */
    .indent {
        margin-left: 30px;
    }

    .indent td {
        padding: 3px 0;
        vertical-align: top;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        width: 45%;
        margin-left: auto;
        margin-top: 40px;
        text-align: left;
    }

    .signature p {
        margin: 0 0 6px 0;
    }

    /* ================= TEMBUSAN ================= */
    .tembusan {
        margin-top: 35px;
    }

    .tembusan p {
        margin: 0 0 4px 0;
        text-align: left;
    }

    /* ================= FOOTER ================= */
    .footer {
        position: absolute;
        bottom: 1.8cm;
        left: 0;
        width: 100%;
        text-align: center;
    }

    .footer img {
        width: 320px;
        display: block;
        margin: 0 auto;
    }
</style>

<div class="a4">

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <img src="{{ asset('img/logo.jpeg') }}" alt="Logo YARSI">
    </div>

    {{-- ================= JUDUL ================= --}}
    <div class="judul">
        <div class="title">SURAT TUGAS</div>
        <div class="nomor">No : {{ $surat->nomor_surat }}</div>
    </div>

    {{-- ================= PEMBUKA ================= --}}
    <p><strong>Assalamu’alaikum Wr. Wb.</strong></p>

    <p>
        Yang bertanda tangan di bawah ini :
    </p>

    {{-- ================= IDENTITAS PENUGAS ================= --}}
    <table class="indent">
        <tr>
            <td width="120">Nama</td>
            <td width="10">:</td>
            <td>{{ $surat->data['nama_penugas'] }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $surat->data['jabatan_penugas'] }}</td>
        </tr>
        <tr>
            <td>Instansi</td>
            <td>:</td>
            <td>{{ $surat->data['instansi_penugas'] }}</td>
        </tr>
    </table>

    <p style="margin-top:15px;">
        Dengan ini menugaskan kepada:
    </p>

    {{-- ================= IDENTITAS DITUGASKAN ================= --}}
    <table class="indent">
        <tr>
            <td width="120">Nama</td>
            <td width="10">:</td>
            <td>{{ $surat->data['nama_ditugaskan'] }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $surat->data['no_nip'] }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $surat->data['jabatan_ditugaskan'] }}</td>
        </tr>
        <tr>
            <td>Instansi</td>
            <td>:</td>
            <td>{{ $surat->data['instansi_ditugaskan'] }}</td>
        </tr>
    </table>

    {{-- ================= ISI TUGAS ================= --}}
    <p style="margin-top:15px;">
        Untuk membantu pelaksanaan kegiatan
        <strong>{{ $surat->data['nama_kegiatan'] }}</strong>
        ({{ $surat->data['sks'] }} SKS) yang dilaksanakan pada:
    </p>

    <table class="indent" style="margin-top:10px;">
        <tr>
            <td width="140">Hari / Tanggal</td>
            <td width="10">:</td>
            <td>
                {{ \Carbon\Carbon::parse($surat->data['tanggal'])->translatedFormat('l, d F Y') }}
            </td>
        </tr>
        <tr>
            <td>Pukul</td>
            <td>:</td>
            <td>{{ $surat->data['waktu'] }}</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $surat->data['tempat'] }}</td>
        </tr>
    </table>

    <p style="margin-top:20px;">
        Demikian surat tugas ini dibuat, agar dapat dilaksanakan dengan penuh
        tanggung jawab.
    </p>

    <p><strong>Wassalamu’alaikum Wr. Wb.</strong></p>

    {{-- ================= TTD ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p><strong>DEKAN</strong></p>
        <p><strong>FAKULTAS TEKNOLOGI INFORMASI</strong></p>
        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p><strong>{{ $surat->penandatangan ?? '—' }}</strong></p>
    </div>

    {{-- ================= TEMBUSAN ================= --}}
    <div class="tembusan">
        <p><strong>Tembusan :</strong></p>
        <p>1. Kalab & K</p>
        <p>2. Arsip</p>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
