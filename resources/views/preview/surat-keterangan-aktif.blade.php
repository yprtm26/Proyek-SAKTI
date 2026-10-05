@php
    $prodi = $surat->user->prodi;
@endphp

<style>
    /* ================= A4 PAGE ================= */
    .a4 {
        width: 210mm;
        min-height: 297mm;
        background: #fff;
        margin: 0 auto;
        padding: 3cm 3cm 4.5cm 4cm;

        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.5;

        box-shadow: 0 0 10px rgba(0,0,0,.15);
        position: relative;
    }

    .a4 p {
        text-align: justify;
        margin-bottom: 12px;
    }

    /* ================= HEADER ================= */
    .header {
        text-align: left;
        margin-bottom: 25px;
    }

    .header img {
        height: 70px;
    }

    /* ================= TITLE ================= */
    .title {
        text-align: center;
        margin: 25px 0;
    }

    .title h2 {
        margin: 0;
        font-size: 14pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .title .nomor {
        margin-top: 6px;
        font-size: 12pt;
    }

    /* ================= DATA TABLE ================= */
    .data-table {
        margin-left: 1cm;
        margin-bottom: 15px;
    }

    .data-table td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /* ================= SIGNATURE ================= */
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

    {{-- ================= TITLE & NOMOR ================= --}}
    <div class="title">
        <h2>SURAT KETERANGAN AKTIF</h2>
        <div class="nomor">
            {{ $surat->nomor_surat ?? '—' }}
        </div>
    </div>

    {{-- ================= ISI SURAT (TIDAK DIUBAH) ================= --}}
    <p>
        Yang bertanda tangan di bawah ini, Kepala Program Studi
        {{ namaProdi($surat->user->prodi) }}
        Fakultas Teknologi Informasi Universitas YARSI, menerangkan bahwa:
    </p>

    <table class="data-table">
        <tr>
            <td width="30%">Nama</td>
            <td width="5%">:</td>
            <td>{{ $surat->user->name }}</td>
        </tr>
        <tr>
            <td>Nomor Pokok Mahasiswa</td>
            <td>:</td>
            <td>{{ $surat->user->npm }}</td>
        </tr>
    </table>

    <p>
        Adalah benar sebagai mahasiswa Program Studi
        {{ namaProdi($surat->user->prodi) }},
        Program Sarjana Fakultas Teknologi Informasi Universitas YARSI,
        yang bersangkutan saat ini aktif pada Semester
        {{ $surat->data['semester'] ?? '-' }}
        {{ $surat->data['tahun_akademik'] ?? '-' }}.
    </p>

    <p>
        Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
    </p>

    {{-- ================= SIGNATURE ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p><strong>Kepala Program Studi {{ namaProdi($surat->user->prodi) }}</strong></p>
        <p><strong>Program Sarjana</strong></p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <strong>{{ $surat->penandatangan ?? '—' }}</strong>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
