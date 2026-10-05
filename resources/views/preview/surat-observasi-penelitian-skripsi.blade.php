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
        color: #000;

        box-shadow: 0 0 10px rgba(0,0,0,.15);
        position: relative;
    }

    .a4 p {
        text-align: justify;
        margin-bottom: 12px;
    }

    /* ================= HEADER ================= */
    .header { margin-bottom: 20px; }
    .header img { height: 70px; }

    .meta { margin-bottom: 20px; }
    .meta table td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /* ================= DATA MAHASISWA ================= */
    .data-table {
        margin-left: 1cm;
        margin-top: 10px;
        margin-bottom: 15px;
    }

    .data-table td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        width: 50%;
        margin-left: auto;
        margin-top: 45px;
        text-align: left;
    }

    .signature .jabatan {
        line-height: 1.6;
        white-space: nowrap;
    }

    .signature .qr {
        margin: 15px 0;
    }

    .signature .nama {
        margin-top: 5px;
        font-weight: bold;
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

    <div class="meta">
        <table>
            <tr>
                <td width="25%">Rujukan Kami</td>
                <td width="5%">:</td>
                <td>{{ $surat->nomor_surat ?? '—' }}</td>
            </tr>
            <tr>
                <td>Rujukan Anda</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td>{{ $surat->data['perihal'] }}</td>
            </tr>
        </table>
    </div>

    {{-- ================= TUJUAN ================= --}}
    <p>
        <strong>Kepada Yth :<br>
        {{ $surat->data['kepada'] }}<br>
        {{ $surat->data['instansi'] }}<br>
        {{ $surat->data['kota_instansi'] }}</strong>
    </p>

    <p>Assalamu’alaikum warahmatullahi wabarakatuh</p>

    {{-- ================= ISI SURAT ================= --}}
    <p>
        Dalam rangka penyelesaian studi mahasiswa kami di Program Studi
        {{ namaProdi($prodi) }} Program Sarjana (S1)
        Fakultas Teknologi Informasi Universitas YARSI,
        diperlukan penelitian guna menyelesaikan penulisan skripsinya.
    </p>

    <p>
        Untuk itu, bersama ini kami mohon Bapak/Ibu berkenan mengizinkan mahasiswa
        tersebut untuk melakukan
        <strong>{{ $surat->data['jenis_kegiatan'] }}</strong>
        terkait dengan penelitian skripsinya yaitu :
    </p>

    {{-- ================= DATA MAHASISWA ================= --}}
    <table class="data-table">
        <tr>
            <td width="30%">Nama</td>
            <td width="5%">:</td>
            <td>{{ $surat->user->name }}</td>
        </tr>
        <tr>
            <td>NPM</td>
            <td>:</td>
            <td>{{ $surat->user->npm }}</td>
        </tr>
        <tr>
            <td>Judul Skripsi</td>
            <td>:</td>
            <td>{{ $surat->data['judul_skripsi'] }}</td>
        </tr>
    </table>

    <p>
        Demikianlah permohonan ini kami sampaikan, atas perhatian dan perkenan
        Bapak/Ibu kami ucapkan terima kasih.
    </p>

    <p>
        Wassalaamu’alaikum warahmatullahi wabarakatuh
    </p>

    {{-- ================= SIGNATURE ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <div class="jabatan">
            <strong>DEKAN</strong><br>
            <strong>FAKULTAS TEKNOLOGI INFORMASI</strong>
        </div>

        @if($surat->qr_token)
            <div class="qr">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                    alt="QR Validasi">
            </div>
        @endif

        <div class="nama">
            {{ $surat->penandatangan }}
        </div>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
