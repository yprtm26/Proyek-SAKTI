@php
    $user = $surat->user;
    $kaprodi = \App\Models\User::where('role','kaprodi')
        ->where('prodi',$user->prodi)
        ->first();
@endphp

<style>
    .a4 {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        padding: 3cm 3cm 4.5cm 4cm;

        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #000;

        position: relative;
        box-shadow: 0 0 10px rgba(0,0,0,.15);
    }

    .a4 p {
        margin: 0 0 12px 0;
        text-align: justify;
    }

    table {
        border-collapse: collapse;
    }

    /* ================= HEADER ================= */
    .header {
        margin-bottom: 25px;
    }

    .header img {
        height: 70px;
    }

    /* ================= JUDUL ================= */
    .title {
        text-align: center;
        margin-bottom: 20px;
    }

    .title h2 {
        margin: 0;
        font-size: 14pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .title .nomor {
        margin-top: 6px;
        font-size: 12pt;
    }

    /* ================= DATA ================= */
    .data {
        margin-left: 1cm;
        margin-bottom: 15px;
    }

    .data td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        width: 50%;
        margin-left: auto;
        margin-top: 45px;
        text-align: left;
    }

    .signature .qr {
        margin: 15px 0;
    }

    .signature .nama {
        margin-top: 5px;
        font-weight: bold;
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
    <div class="title">
        <h2>SURAT REKOMENDASI</h2>
        <div class="nomor">
            {{ $surat->nomor_surat ?? '—' }}
        </div>
    </div>

    {{-- ================= ISI ================= --}}
    <p>
        Yang bertanda tangan di bawah ini:
    </p>

    {{-- ================= DATA KAPRODI ================= --}}
    <table class="data">
        <tr>
            <td width="160">Nama</td>
            <td width="10">:</td>
            <td>{{ $kaprodi->name }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Kepala Program Studi {{ namaProdi($user->prodi) }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td>{{ $kaprodi->nidn }}</td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td>:</td>
            <td>{{ $kaprodi->emailyarsi }}</td>
        </tr>
        <tr>
            <td>No. Telp</td>
            <td>:</td>
            <td>{{ $kaprodi->telephonenumber }}</td>
        </tr>
    </table>

    <p>
        Dengan ini memberikan <strong>rekomendasi</strong> kepada mahasiswa berikut:
    </p>

    {{-- ================= DATA MAHASISWA ================= --}}
    <table class="data">
        <tr>
            <td width="160">Nama</td>
            <td width="10">:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>NPM</td>
            <td>:</td>
            <td>{{ $user->npm }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ namaProdi($user->prodi) }}</td>
        </tr>
        <tr>
            <td>Fakultas</td>
            <td>:</td>
            <td>Fakultas Teknologi Informasi</td>
        </tr>
        <tr>
            <td>IPK</td>
            <td>:</td>
            <td>{{ $surat->data['ipk'] }}</td>
        </tr>
        <tr>
            <td>Jumlah SKS ditempuh</td>
            <td>:</td>
            <td>{{ $surat->data['sks'] }} SKS</td>
        </tr>
    </table>

    <p>
        Untuk keperluan <strong>{{ strtoupper($surat->data['tujuan']) }}</strong>,
        dengan pertimbangan sebagai berikut:
    </p>

    {{-- ================= PENGALAMAN ================= --}}
    <p>- Mengingat yang bersangkutan aktif dalam kegiatan organisasi dan kegiatan sosial di kampus selama menjadi mahasiswa:</p>
    @if(!empty($surat->data['pengalaman']))
        <ul style="margin-left:1.2cm; margin-bottom:12px;">
            @foreach($surat->data['pengalaman'] as $p)
                <li>{{ $p }}</li>
            @endforeach
        </ul>
    @endif

    {{-- ================= PROJECT ================= --}}
    <p>- Mengingat yang bersangkutan sudah memiliki beberapa pengalaman dalam bidangnya</p>
    @if(!empty($surat->data['project']))
        <ul style="margin-left:1.2cm; margin-bottom:12px;">
            @foreach($surat->data['project'] as $pr)
                <li>{{ $pr }}</li>
            @endforeach
        </ul>
    @endif

    <p>
         Demikian surat rekomendasi ini diberikan sebagai pelengkap dokumen data diri untuk
         {{ ($surat->data['tujuan']) }}. Atas perhatian Bapak/Ibu saya ucapkan terima kasih.
    </p>

    <p>
        Wassalamu’alaikum Wr. Wb.
    </p>

    {{-- ================= TTD ================= --}}
    <div class="signature">
        <p>
            Jakarta,
            {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y')
                ?? now()->translatedFormat('d F Y') }}
        </p>

        <p>
            Kepala Program Studi {{ namaProdi($user->prodi) }}
        </p>

        @if($surat->qr_token)
            <div class="qr">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                    alt="QR Validasi">
            </div>
        @endif

        <div class="nama">
            {{ $kaprodi->name }}
        </div>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
