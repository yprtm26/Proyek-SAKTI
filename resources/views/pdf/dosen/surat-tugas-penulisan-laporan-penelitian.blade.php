<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 3cm 3cm 4cm 4cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #000;
        }

        /* ========== UTIL ========== */
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .justify { text-align: justify; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        p {
            margin: 0 0 10px 0;
        }

        /* ========== HEADER ========== */
        .header img {
            height: 65px;
        }

        /* ========== JUDUL ========== */
        .judul {
            text-align: center;
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .judul .title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }

        .judul .subtitle {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 6px;
            text-decoration: underline;
        }

        .judul .nomor {
            margin-top: 10px;
            font-weight: bold;
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

    .signature img {
        margin: 10px 0;
        width: 95px;
    }

        /* ========== TEMBUSAN ========== */
        .tembusan {
            margin-top: 35px;
            font-size: 11.5pt;
        }

        .tembusan p {
            margin: 0 0 4px 0;
        }

        /* ========== FOOTER (AMAN DOMPDF) ========== */
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

<div class="a4">

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <img src="{{ public_path('img/logo.jpeg') }}" alt="Logo YARSI">
    </div>

    {{-- ================= JUDUL ================= --}}
    <div class="judul">
        <div class="title">SURAT TUGAS</div>
        <div class="subtitle">PENULISAN LAPORAN PENELITIAN</div>
        <div class="nomor">No. : {{ $surat->nomor_surat }}</div>
    </div>

    {{-- ================= PEMBUKA ================= --}}
    <p class="bold">
        Assalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= ISI ================= --}}
    <p class="justify" style="text-indent:30px;">
        Dalam rangka menulis publikasi hasil penelitian/pemikiran
        {{ $surat->data['tujuan_penulisan'] }},
        bersama ini kami menugaskan:
    </p>

    {{-- ================= IDENTITAS DOSEN ================= --}}
    <table style="margin-top:15px;">
        <tr>
            <td class="bold" style="padding-left:60px;">
                {{ $surat->data['nama_dosen'] }}
            </td>
        </tr>
    </table>

    <p class="justify" style="margin-top:15px;">
        Untuk mempublikasikan laporannya di
        <strong>{{ $surat->data['tujuan_penulisan'] }}</strong>.
    </p>

    <p class="justify" style="margin-top:20px;">
        Demikian surat tugas ini kami berikan agar dapat dipergunakan sebagaimana mestinya.
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

        <p class="bold">Dekan Fakultas Teknologi Informasi</p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <p class="bold">{{ $surat->penandatangan ?? '—' }}</p>
    </div>

    {{-- ================= TEMBUSAN ================= --}}
    <div class="tembusan">
        <p class="bold">Tembusan :</p>
        <p>1. Kepala Prodi {{ namaProdi($surat->user->prodi) }}</p>
        <p>2. Arsip</p>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ public_path('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>


</body>
</html>