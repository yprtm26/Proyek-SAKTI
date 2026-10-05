<div
    style="
        width:210mm;
        min-height:297mm;
        margin:auto;
        background:#fff;
        padding:3cm 3cm 4.5cm 4cm;
        font-family:'Times New Roman', serif;
        font-size:12pt;
        line-height:1.7;
        position:relative;
    "
>

    {{-- ================= HEADER LOGO ================= --}}
    <div style="margin-bottom:30px;">
        <img src="{{ asset('img/logo.jpeg') }}" style="height:60px;">
    </div>

    {{-- ================= JUDUL ================= --}}
    <p style="font-weight:bold; margin-bottom:20px; text-align: center;">
        PERMOHONAN AKTIF AKADEMIK
    </p>

    <p>Assalamu’alaikum Wr. Wb.</p>

    <p>Yang bertanda tangan di bawah ini:</p>

    <table style="margin-left:30px; margin-bottom:16px;">
        <tr>
            <td width="140">Nama</td>
            <td width="10">:</td>
            <td>{{ $surat->user->name }}</td>
        </tr>
        <tr>
            <td>NPM</td>
            <td>:</td>
            <td>{{ $surat->user->npm }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ namaProdi($surat->user->prodi) }}</td>
        </tr>
    </table>

    <p>
        Mengajukan permohonan aktif akademik pada semester
        <strong>{{ ucfirst($surat->data['semester']) }}</strong>
        tahun akademik
        <strong>{{ $surat->data['tahun_akademik'] }}</strong>.
    </p>

    <p>
        Demikian permohonan ini saya sampaikan.
        Atas perhatian Bapak/Ibu, saya ucapkan terima kasih.
    </p>

    <p>Wassalamu’alaikum Wr. Wb.</p>

    {{-- ================= TTD (KANAN) ================= --}}
    <div style="margin-top:50px; text-align:right;">
        <p>
            Jakarta, {{ now()->translatedFormat('d F Y') }}
        </p>

        <p>Mahasiswa/i,</p>

        <div style="margin-top:10px;">
            @if($surat->qr_token)
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                    style="display:block; margin-left:auto; margin-bottom:10px;">
            @endif

            <strong>{{ $surat->user->name }}</strong>
        </div>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div
        style="
            position:absolute;
            bottom:1.8cm;
            left:0;
            width:100%;
            text-align:center;
        "
    >
        <img
            src="{{ asset('img/footer.jpg') }}"
            style="width:320px; display:block; margin:0 auto;"
            alt="Footer YARSI"
        >
    </div>

</div>
