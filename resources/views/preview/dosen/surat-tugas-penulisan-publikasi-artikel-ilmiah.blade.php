<style>
    .paper {
        width: 210mm;
        min-height: 297mm;
        margin: auto;
        background: white;
        padding: 3cm 3cm 4cm 4cm;

        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #000;

        box-shadow: 0 0 10px rgba(0,0,0,.15);
        position: relative;
    }

    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    .underline { text-decoration: underline; }
    .justify { text-align: justify; }

    table {
        width: 100%;
        border-collapse: collapse;
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

<div class="paper">

    {{-- ================= HEADER ================= --}}
    <table>
        <tr>
            <td>
                <img src="{{ asset('img/logo.jpeg') }}" width="180">
            </td>
        </tr>
    </table>

    {{-- ================= JUDUL ================= --}}
    <table style="margin-top:35px;">
        <tr>
            <td class="center bold underline" style="font-size:16pt;">
                SURAT TUGAS
            </td>
        </tr>
        <tr>
            <td class="center bold" style="padding-top:6px;">
                PENULISAN DAN PUBLIKASI ARTIKEL ILMIAH
            </td>
        </tr>
        <tr>
            <td class="center bold" style="padding-top:10px;">
                No : {{ $surat->nomor_surat }}
            </td>
        </tr>
    </table>

    {{-- ================= PEMBUKA ================= --}}
    <p class="bold" style="margin-top:40px;">
        Assalamu’alaikum Wr. Wb.
    </p>

    <p class="justify" style="margin-top:15px; text-indent:30px;">
        Dalam rangka pemenuhan luaran hasil penelitian dosen Fakultas Teknologi
        Informasi Universitas YARSI, dengan ini kami mengizinkan dan menugaskan dosen:
    </p>

    {{-- ================= IDENTITAS DOSEN ================= --}}
    <table style="margin-top:20px;">
        <tr>
            <td class="center bold" style="font-size:15pt;">
                {{ $surat->data['nama_dosen'] }}
            </td>
        </tr>
    </table>

    {{-- ================= ISI TUGAS ================= --}}
    <p class="justify" style="margin-top:20px; text-indent:30px;">
        Untuk {{ $surat->data['tujuan_penulisan'] }} dengan judul:
        <strong>
            "{{ $surat->data['judul_artikel'] }}"
        </strong>
    </p>

    <p class="justify" style="margin-top:20px; text-indent:30px;">
        Demikian surat tugas ini kami berikan agar dapat dipergunakan sebagaimana mestinya.
    </p>

    <p class="bold" style="margin-top:15px;">
        Wassalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= TTD + QR ================= --}}
<div style="width:45%; margin-left:auto; margin-top:40px; text-align:left;">
    <p>
        Jakarta,
        {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
            ?? now()->translatedFormat('d F Y') }}
    </p>

    <p class="bold" style="margin-top:5px;">
        DEKAN<br>
        FAKULTAS TEKNOLOGI INFORMASI
    </p>

    @if($surat->qr_token)
        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
            style="margin:10px 0;">
    @endif

    <p class="bold">
        {{ $surat->penandatangan ?? 'Dr. Ummi Azizah Rachmawati, M.Kom.' }}
    </p>
</div>


    {{-- ================= TEMBUSAN ================= --}}
    <table style="margin-top:40px;">
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
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
