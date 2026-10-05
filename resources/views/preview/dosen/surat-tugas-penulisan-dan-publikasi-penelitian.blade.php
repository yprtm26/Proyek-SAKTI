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
        margin-bottom: 20px;
    }

    .header img {
        height: 70px;
    }

    /* ================= JUDUL ================= */
    .judul {
        text-align: center;
        margin-bottom: 25px;
    }

    .judul .title {
        font-size: 16pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .judul .subtitle {
        font-size: 14pt;
        font-weight: bold;
        margin-top: 4px;
    }

    .judul .nomor {
        margin-top: 10px;
        font-weight: bold;
    }

    /* ================= TABLE ANGGOTA ================= */
    .anggota-table {
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .anggota-table th,
    .anggota-table td {
        border: 1px solid #000;
        padding: 6px 8px;
        font-size: 11.5pt;
    }

    .anggota-table th {
        text-align: center;
        font-weight: bold;
    }

    .anggota-table td:first-child {
        text-align: center;
        width: 6%;
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

    /* ================= TEMBUSAN ================= */
    .tembusan {
        margin-top: 30px;
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
        <div class="subtitle">PENULISAN DAN PUBLIKASI PENELITIAN</div>
        <div class="nomor">No. : {{ $surat->nomor_surat }}</div>
    </div>

{{-- ================= PEMBUKA ================= --}}
<p class="bold" style="margin-top:40px;">
    Assalamu’alaikum Wr. Wb.
</p>

<p class="justify" style="margin-top:15px; text-indent:30px;">
    Dalam rangka pemenuhan luaran hasil penelitian dosen FTI Universitas YARSI, dengan ini <strong>kami mengizinkan dan menugaskan :</strong>
</p>

{{-- ================= TABEL ANGGOTA ================= --}}
    <table class="anggota-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($surat->data['anggota'] ?? []) as $i => $a)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $a['nama'] }}</td>
                <td>{{ $a['keterangan'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

{{-- ================= ISI ================= --}}
<p class="justify" style="margin-top:20px; text-indent:30px;">
    Untuk menulis, mempublikasikan penelitian dengan judul:
    <strong>
        "{{ $surat->data['judul'] ?? '-' }}"
    </strong> pada Jurnal {{ $surat->data['nama_jurnal'] ?? '' }} di <a href="{{ $surat->data['link_jurnal'] }}"
       target="_blank"
       style="color: blue; text-decoration: underline;">
        {{ $surat->data['link_jurnal'] }}
    </a>.
</p>

<p class="justify" style="margin-top:20px; text-indent:30px;">
    Demikianlah surat tugas ini kami diberikan, agar dapat dipergunakan
sebagaimana mestinya.
</p>

<p class="bold" style="margin-top:15px;">
    Wassalamu’alaikum Wr. Wb.
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


    {{-- ================= TEMBUSAN ================= --}}
    <div class="tembusan">
        <p><strong>Tembusan :</strong></p>
        <p>1. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
        <p>2. Arsip</p>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>