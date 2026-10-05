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
            line-height: 1.5;
            color: #000;
        }

        p {
            margin: 0 0 12px 0;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }

        /* ================= HEADER ================= */
        .header img {
            height: 70px;
        }

        /* ================= SIGNATURE ================= */
        .signature {
            width: 45%;
            margin-left: auto;
            margin-top: 30px;
        }

        .signature p {
            margin: 0 0 6px 0;
        }

        /* QR DIPERKECIL */
        .signature img {
            width: 70px;
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
            display: block;
            margin: 0 auto;
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
    <p class="bold" style="margin-top:40px;">
        Assalamu’alaikum Wr. Wb.
    </p>

    <p>
        Dalam rangka mengikuti kegiatan pengembangan diri untuk meningkatkan
        kompetensi, bersama ini kami menugaskan:
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
    <p style="margin-top:20px;">
        Untuk mengikuti pelatihan
        <strong>{{ $surat->data['nama_pelatihan'] }}</strong>
        pada
        <strong>{{ $surat->data['tanggal_pelatihan'] }}</strong>
        jam
        <strong>{{ $surat->data['waktu_pelatihan'] }}</strong>.
    </p>

    <p style="margin-top:20px;">
        Demikian surat tugas ini diberikan untuk dipergunakan dan dilaksanakan
        sebaik-baiknya.
    </p>

    <p class="bold">
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
