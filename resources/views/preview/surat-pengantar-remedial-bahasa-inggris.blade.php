@php
function labelMataKuliah($kode) {
    return match ($kode) {
        'BI1' => 'Bahasa Inggris I',
        'BI2' => 'Bahasa Inggris II',
        'BI3' => 'Bahasa Inggris III',
        'BI4' => 'Bahasa Inggris IV',
        'BI5' => 'Bahasa Inggris V',
        'BI6' => 'Bahasa Inggris VI',
        default => $kode,
    };
}
@endphp

<style>
    /* ================= A4 PREVIEW ================= */
    .a4 {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 3cm 3cm 4cm 4cm;

        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #000;

        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,.15);
        position: relative;
    }

    .a4 p {
        text-align: justify;
        margin-bottom: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11pt;
    }

    /* ================= HEADER ================= */
    .header {
        margin-bottom: 20px;
    }

    .header img {
        height: 65px;
    }

    /* ================= META ================= */
    .meta {
        margin-bottom: 20px;
    }

    .meta td {
        padding: 2px 4px;
        vertical-align: top;
    }

    /* ================= TABLE ================= */
    .data-table {
        margin: 10px 0 15px 0;
    }

    .data-table th,
    .data-table td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }

    .data-table th {
        font-weight: bold;
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

    {{-- ================= META SURAT ================= --}}
    <div class="meta">
        <table>
            <tr>
                <td width="25%">Rujukan Kami</td>
                <td width="5%">:</td>
                <td>{{ $surat->nomor_surat }}</td>
            </tr>
            <tr>
                <td>Rujukan Anda</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Remedial Semester Antara</strong></td>
            </tr>
        </table>
    </div>

    {{-- ================= ISI (TIDAK DIUBAH) ================= --}}
    <p><strong>
        Kepada Yth:<br>
        Manager Optima Language Centre<br>
        Di Jakarta
    </strong></p>

    <p>Assalamu’alaikum warahmatullahi wabarakatuh</p>

    <p>
        Sehubungan dengan akan dilaksanakan semester antara mata kuliah Bahasa Inggris,
        kami dari Program Studi <strong>{{ namaProdi($surat->user->prodi) }}</strong>,
        Fakultas Teknologi Informasi Universitas YARSI,
        bermaksud mengirimkan mahasiswa kami untuk mengikuti Program Remedial tersebut.
        Adapun daftar mahasiswa adalah sebagai berikut:
    </p>

    {{-- ================= TABEL MAHASISWA ================= --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">No</th>
                <th>Nama</th>
                <th width="20%">NPM</th>
                <th>Mata Kuliah</th>
            </tr>
        </thead>
        <tbody>
        @foreach($surat->data['mahasiswa'] as $i => $mhs)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $mhs['nama'] }}</td>
                <td>{{ $mhs['npm'] }}</td>
                <td>
                    {{ collect($mhs['mata_kuliah'] ?? [])
                        ->map(fn($k) => labelMataKuliah($k))
                        ->implode(', ') ?: '-' }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p>
        Kami berharap program remedial ini dapat membantu mahasiswa kami dalam
        meningkatkan kemampuan Bahasa Inggris mereka sehingga dapat mencapai hasil
        yang lebih baik di semester mendatang.
    </p>

    <p>
        Demikianlah, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
    </p>

    <p>Wassalamu’alaikum warahmatullahi wabarakatuh</p>

    {{-- ================= TTD ================= --}}
    <div class="signature">
        <p>
            Kepala Program Studi {{ namaProdi($surat->user->prodi) }}
        </p>

        @if($surat->qr_token)
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                alt="QR Validasi">
        @endif

        <strong>{{ $surat->penandatangan ?? '—' }}</strong>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <img src="{{ asset('img/footer.jpg') }}" alt="Footer YARSI">
    </div>

</div>
