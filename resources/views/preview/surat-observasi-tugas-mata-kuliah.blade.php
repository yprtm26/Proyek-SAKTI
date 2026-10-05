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

    table {
        width: 100%;
        border-collapse: collapse;
    }

    /* ================= HEADER ================= */
    .header {
        margin-bottom: 20px;
    }

    .header img {
        height: 70px;
    }

    /* ================= META SURAT ================= */
    .meta {
        margin-bottom: 20px;
    }

    .meta td {
        padding: 2px 4px;
        vertical-align: top;
        font-size: 11pt;
    }

    /* ================= TABLE ANGGOTA ================= */
    .anggota-table {
        margin: 10px 0 15px 0;
    }

    .anggota-table th,
    .anggota-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 11pt;
    }

    .anggota-table th {
        text-align: center;
        font-weight: bold;
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

    {{-- ================= META SURAT ================= --}}
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
                <td><strong>{{ $surat->data['perihal'] }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- ================= ISI (TIDAK DIUBAH) ================= --}}
    <p><strong>
        Kepada Yth:<br>
        {{ $surat->data['kepada_yth'] }}<br>
        {{ $surat->data['nama_instansi'] }}<br>
        Di {{ $surat->data['kota_instansi'] }}
    </strong></p>

    <p>
        Assalaamu’alaikum warahmatullahi wabarakatuh
    </p>
    
    <p>
        Diberitahukan dengan hormat bahwa mahasiswa kami:
    </p>

    {{-- ================= TABEL ANGGOTA ================= --}}
    <table class="anggota-table">
        <thead>
            <tr>
                <th width="8%">No</th>
                <th>Nama</th>
                <th width="30%">NPM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($surat->data['anggota_tim'] as $i => $anggota)
            <tr>
                <td style="text-align:center;">{{ $i+1 }}</td>
                <td>{{ $anggota['nama'] }}</td>
                <td>{{ $anggota['npm'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Sehubungan dengan tugas<strong> Mata Kuliah
        {{ $surat->data['mata_kuliah'] }}</strong>, Kami mohon kesediaan Bapak/Ibu untuk dapat
        mengijinkan mahasiswa yang bersangkutan <strong>untuk melakukan
        {{ $surat->data['jenis_kegiatan'] }}</strong>,
        yang Bapak/Ibu Pimpin.
    </p>

    <p>
        Demikianlah permohonan ini kami sampaikan, atas bantuan dan kerjasamanya kami ucapkan terima kasih.
    </p>

    <p>
        Wassalaamu’alaikum warahmatullahi wabarakatuh
    </p>

    {{-- ================= TTD ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p><strong>Dekan Fakultas Teknologi Informasi</strong></p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p><strong>{{ $surat->penandatangan ?? '—' }}</strong></p>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
