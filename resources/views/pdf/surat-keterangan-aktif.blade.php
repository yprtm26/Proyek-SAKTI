<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6;
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

        .ttd-block {
            text-align: left;
            width: 45%;
        }

        .qr {
            margin-top: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td>
            <img src="{{ public_path('img/logo.jpeg') }}" width="200">
        </td>
    </tr>
</table>

{{-- ================= JUDUL ================= --}}
<table style="margin-top:40px;">
    <tr>
        <td class="center bold underline" style="font-size:16pt;">
            SURAT KETERANGAN AKTIF
        </td>
    </tr>
    <tr>
        <td class="center bold" style="padding-top:6px;">
            {{ $surat->nomor_surat }}
        </td>
    </tr>
</table>

{{-- ================= ISI ================= --}}
<p class="justify" style="margin-top:40px; text-indent:30px;">
    Yang bertanda tangan di bawah ini, Kepala Program Studi
    {{ namaProdi($surat->user->prodi) }}
    Program Sarjana Fakultas Teknologi Informasi Universitas YARSI menerangkan bahwa mahasiswa
    tersebut di bawah ini:
</p>

<table style="margin-top:20px;">
    <tr>
        <td width="220" class="bold">Nama</td>
        <td width="10">:</td>
        <td class="bold">{{ $surat->user->name }}</td>
    </tr>
    <tr>
        <td class="bold">Nomor Pokok Mahasiswa</td>
        <td>:</td>
        <td class="bold">{{ $surat->user->npm }}</td>
    </tr>
</table>

<p class="justify" style="margin-top:20px;">
    adalah benar sebagai mahasiswa Program Studi
    {{ namaProdi($surat->user->prodi) }}
    Program Sarjana Fakultas Teknologi Informasi Universitas YARSI, yang bersangkutan
    <strong>saat ini aktif di Semester {{ $surat->data['semester'] }}
    {{ $surat->data['tahun_akademik'] }}</strong>.
</p>

<p class="justify" style="margin-top:15px;">
    Demikian surat keterangan ini dibuat untuk dipergunakan untuk sebagaimana mestinya.
</p>

{{-- ================= TTD PEJABAT ================= --}}
<table style="margin-top:30px;">
    <tr>
        <td width="55%"></td>
        <td class="ttd-block" style="width:45%;">
            <div class="bold">
                Jakarta, {{ $surat->tanggal_disetujui_final->translatedFormat('d F Y') }}
            </div>
            <div class="bold" style="margin-top:5px;">
                Kepala Program Studi {{ namaProdi($surat->user->prodi) }}
            </div>
            <div class="bold">
                Program Sarjana
            </div>

            {{-- RUANG TANDA TANGAN --}}
            <div class="qr">
                <img src="{{ $qrUrl }}" width="100">
            </div>

            <div class="bold" style="margin-top:5px;">
                {{ namaPenandatangan($surat) }}
            </div>
        </td>
    </tr>
</table>

{{-- ================= TEMBUSAN ================= --}}
<table style="margin-top:40px;">
    <tr>
        <td>Tembusan :</td>
    </tr>
    <tr>
        <td>1. Arsip</td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<table style="margin-top:70px;">
    <tr>
        <td class="center">
            <img src="{{ public_path('img/footer.jpg') }}" width="350">
        </td>
    </tr>
</table>

</body>
</html>
