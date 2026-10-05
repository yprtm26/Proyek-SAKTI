@php
    $data = $surat->data;
    $mahasiswa = $data['mahasiswa'];
    $prodi = $surat->user->prodi;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4;
            margin: 2cm 2cm 2cm 2cm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.4;
            padding-top: 30px;
        }

        p {
            margin: 0 0 10px 0;
        }

        .header {
            position: fixed;
            top: -55px;     /* naik ke atas */
            left: -25px;    /* geser ke pojok kiri */
        }

        .header img {
            height: 70px;
        }

        .nomor-surat {
            margin-top: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .nomor-surat td {
            vertical-align: top;
            padding: 2px 0;
        }

        .content {
            margin-left: 10px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            margin-bottom: 12px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 4px;
        }

        table.data th {
            text-align: center;
            font-weight: bold;
        }

        .ttd {
            margin-top: 30px;
            text-align: right;
        }

        .tembusan {
            margin-top: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <img src="{{ public_path('img/logo.jpeg') }}">
</div>

{{-- NOMOR SURAT --}}
<table class="nomor-surat">
    <tr>
        <td width="140">Rujukan kami</td>
        <td>: {{ $surat->nomor_surat }}</td>
    </tr>
    <tr>
        <td>Rujukan anda</td>
        <td>:</td>
    </tr>
    <tr>
        <td>Perihal</td>
        <td>: Permohonan Izin Magang</td>
    </tr>
</table>

<div class="content">

    <p><strong>
        Kepada Yth.<br>
        {{ $data['kepada_yth'] }}<br>
        {{ $data['nama_mitra'] }}<br>
        Alamat : {{ $data['alamat_mitra'] }}
    </strong></p>

    <p><em><strong>Assalamu’alaikum Wr. Wb.</strong></em></p>

    <p style="text-align: justify;">
        Puji Syukur kepada Allah Subhanahu Wata’ala yang senantiasa melimpahkan Rahmat dan
        Ridho-Nya kepada kita semua dalam melakukan aktivitas sehari-hari. Aamiin.
    </p>

    <p style="text-align: justify;">
        Sehubungan dengan ajuan permohonan dari mahasiswa yang akan melakukan
        magang mandiri ke pihak <strong>{{ $data['nama_mitra'] }}</strong>,
        dengan ini kami memohon kesediaan Bapak/Ibu untuk memberikan izin kepada
        mahasiswa yang datanya tersebut dibawah ini:
    </p>

    {{-- TABEL MAHASISWA --}}
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>NPM</th>
                <th>Nama</th>
                <th>Prodi</th>
                <th>Semester</th>
                <th>No. Kontak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mahasiswa as $i => $mhs)
            <tr>
                <td align="center">{{ $i+1 }}</td>
                <td>{{ $mhs['npm'] }}</td>
                <td>{{ $mhs['nama'] }}</td>
                <td>{{ namaProdi($surat->user->prodi) }}</td>
                <td align="center">{{ $mhs['semester'] }}</td>
                <td>{{ $mhs['no_kontak'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: justify;">
        Dengan ini kami memohon kepada Bapak/Ibu untuk dapat menerima mahasiswa kami tersebut
        untuk melaksanakan program <strong>Magang Mandiri</strong> yang akan dilaksanakan mulai tanggal
        <strong>{{ \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') }}</strong>
        sampai dengan
        <strong>{{ \Carbon\Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') }}</strong>
        pada instansi/lembaga/perusahaan Bapak/Ibu pimpin.
    </p>

    <p style="text-align: justify;">
        Demikian surat pengantar ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan
        terima kasih.
    </p>

    <p><em><strong>Wassalaamu’alaikum Wr. Wb.</strong></em></p>

    {{-- TTD --}}
    <div class="ttd">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
        </p>

        <p><strong>DEKAN FAKULTAS TEKNOLOGI INFORMASI</strong></p>

        @if($surat->qr_token)
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ url('/surat/validasi/'.$surat->qr_token) }}">
        @endif

        <p><strong>{{ $surat->penandatangan }}</strong></p>
    </div>

    {{-- TEMBUSAN --}}
    <div class="tembusan">
        <p>Tembusan:</p>
        <ul>
            <li>Kepala Prodi {{ namaProdi($surat->user->prodi) }}</li>
            <li>Arsip</li>
        </ul>
    </div>

</div>

{{-- FOOTER --}}
<div class="footer">
    <img src="{{ public_path('img/footer.jpg') }}" width="300">
</div>

</body>
</html>