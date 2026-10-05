@php
$user = $surat->user;
$data = $surat->data;
@endphp

<style>
    .a4 {
        width: 210mm;
        min-height: 297mm;
        margin: auto;
        background: #fff;
        padding: 3cm 3cm 4cm 4cm;

        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.7;
        color: #000;

        position: relative;
    }

    .a4 p {
        margin: 0 0 12px 0;
        text-align: justify;
    }

    table {
        border-collapse: collapse;
    }

    /* HEADER */
    .header {
        margin-bottom: 30px;
    }

    .header img {
        height: 60px;
    }

    /* JUDUL */
    .judul {
        font-weight: bold;
        margin-bottom: 25px;
        text-transform: uppercase;
        text-align: center;
        width: 100%;
    }

    /* DATA */
    .data {
        margin-left: 30px;
        margin-bottom: 16px;
    }

    .data td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /*  SIGNATURE */
    .signature {
        width: 45%;
        margin-left: auto;
        margin-top: 45px;
        text-align: left;
    }

    .signature p {
        margin: 0 0 6px 0;
        text-align: left;
    }

    .signature img {
        margin: 12px 0;
        width: 100px;
    }

    /* FOOTER */
    .footer {
        position: absolute;
        bottom: 0.6cm;
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

    {{-- HEADER --}}
    <div class="header">
        <img src="{{ asset('img/logo.jpeg') }}" alt="Logo YARSI">
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
        <tr>
            <td width="140">Nama</td><td width="10">:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>NPM</td><td>:</td>
            <td>{{ $user->npm }}</td>
        </tr>
        <tr>
            <td>Program Studi</td><td>:</td>
            <td>{{ namaProdi($user->prodi) }}</td>
        </tr>
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
    {{-- ================= SIGNATURE ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p><strong>Kepala Program Studi</strong></p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <strong>{{ $surat->penandatangan ?? '—' }}</strong>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
