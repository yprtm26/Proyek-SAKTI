<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 2cm 2cm 2cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .justify { text-align: justify; }

        p {
            margin: 0 0 12px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ================= HEADER ================= */
        .header img {
            height: 70px;
        }

        /* ================= SIGNATURE ================= */
        .signature {
            width: 45%;
            margin-left: auto;
            margin-top: 35px;
        }

        .signature p {
            margin: 0 0 6px 0;
        }

        /* ⬇️ QR DIPERKECIL */
        .signature img {
            width: 75px;     /* <<< UKURAN QR */
            margin: 8px 0;
            display: block;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 60px;
            text-align: center;
        }

        .footer img {
            width: 320px;
        }
    </style>
</head>
<body>

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <img src="{{ public_path('img/logo.jpeg') }}" alt="Logo YARSI">
    </div>

    {{-- ================= JUDUL ================= --}}
    <table style="margin-top:35px;">
        <tr>
            <td class="center bold underline" style="font-size:16pt;">
                SURAT TUGAS
            </td>
        </tr>
        <tr>
            <td class="center bold" style="padding-top:10px;">
                No : {{ $surat->nomor_surat }}
            </td>
        </tr>
    </table>

    {{-- ================= PEMBUKA ================= --}}
    <p class="justify bold" style="margin-top:40px; text-indent:30px;">
        Assalamu’alaikum Wr. Wb.
    </p>

    <p class="justify" style="text-indent:30px;">
        Sehubungan dengan {{ $surat->data['informasi'] }},
        dengan ini Dekan Fakultas Teknologi Informasi
        Universitas YARSI menugaskan kepada dosen yang namanya tercantum di bawah ini :
    </p>

    {{-- ================= IDENTITAS DOSEN ================= --}}
    <table style="margin-top:20px;">
        <tr>
            <td class="center bold" style="font-size:13pt;">
                {{ $surat->data['nama_dosen'] }}
            </td>
        </tr>
    </table>

    {{-- ================= ISI TUGAS ================= --}}
    <table style="margin-top:20px; margin-left:30px;">
        <tr>
            <td width="120">Keperluan</td>
            <td width="10">:</td>
            <td>{{ $surat->data['keperluan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $surat->data['tanggal'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $surat->data['waktu'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $surat->data['tempat'] ?? '-' }}</td>
        </tr>
    </table>

    <p class="justify" style="margin-top:20px;">
        Demikian surat tugas ini kami diberikan, agar dapat dipergunakan sebagaimana mestinya.
    </p>

    <p class="justify bold">
        Wassalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= TTD + QR ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p class="bold">
            DEKAN FAKULTAS TEKNOLOGI INFORMASI
        </p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p class="bold">
            {{ $surat->penandatangan ?? '—' }}
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
        <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</body>
</html>
