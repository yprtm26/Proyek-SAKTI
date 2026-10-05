<style>
    .paper {
        width: 210mm;
        min-height: 297mm;
        margin: auto;
        background: white;
        padding: 30mm 25mm 30mm 30mm;
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.7;
        color: #000;
        position: relative; /* PENTING */
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
        bottom: 1cm;
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
                <img src="{{ asset('img/logo.jpeg') }}" width="160">
            </td>
        </tr>
    </table>

    {{-- ================= JUDUL SURAT ================= --}}
    <table style="margin-top:40px;">
        <tr>
            <td class="center bold underline" style="font-size:16pt;">
                SURAT TUGAS
            </td>
        </tr>
        <tr>
            <td class="center bold" style="padding-top:6px;">
                JURI LOMBA DEBAT
                <span style="text-transform: uppercase;">
                    {{ $surat->data['nama_lomba'] }}
                </span>
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
        {{ $surat->data['informasi_lomba'] }},
        <span class="bold">dengan ini kami mengizinkan dan menugaskan:</span>
    </p>

    {{-- ================= NAMA DOSEN ================= --}}
    <p class="center bold" style="margin-top:25px; font-size:13pt;">
        {{ $surat->data['nama_dosen'] }}
    </p>

    {{-- ================= PENUGASAN ================= --}}
    <p class="bold justify" style="margin-top:20px;">
        Untuk menjadi Juri kegiatan {{ $surat->data['nama_lomba'] }} pada:
    </p>

    <table style="margin-top:15px; margin-left:30px;">
        <tr>
            <td width="20%">Tanggal</td>
            <td width="3%">:</td>
            <td>{{ $surat->data['tanggal_lomba'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $surat->data['waktu_lomba'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $surat->data['tempat_lomba'] ?? '-' }}</td>
        </tr>
    </table>

    <p class="justify" style="margin-top:20px;">
        Demikian surat tugas ini kami diberikan, agar dapat dipergunakan
        sebagaimana mestinya.
    </p>

    <p class="bold" style="margin-top:15px;">
        Wassalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= TTD ================= --}}
    <table style="margin-top:35px;">
        <tr>
            <td width="60%"></td>
            <td class="left">
                <p>
                    Jakarta,
                    {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                        ?? now()->translatedFormat('d F Y') }}
                </p>
                <span class="bold">DEKAN</span><br>
                <span class="bold">FAKULTAS TEKNOLOGI INFORMASI</span>

                 @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p><strong>{{ $surat->penandatangan ?? '—' }}</strong></p>
            </td>
        </tr>
    </table>

    {{-- ================= TEMBUSAN ================= --}}
    <div class="tembusan">
        <p><strong>Tembusan :</strong></p>
        <p>1. Wakil Dekan II</p>
        <p>2. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
        <p>3. Arsip</p>
    </div>

        {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>



</div>
