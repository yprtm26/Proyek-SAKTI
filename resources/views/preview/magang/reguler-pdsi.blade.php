@php
    $data = $surat->data;
    $mahasiswa = $data['mahasiswa'];
@endphp

<div class="bg-white mx-auto max-w-4xl shadow p-10 text-sm leading-relaxed shadow">

    {{-- ================= HALAMAN 1 ================= --}}

    <div class="flex items-center mb-6">
        <img src="{{ asset('img/logo.jpeg') }}" class="h-14 mr-4">
        
    </div>

    {{-- ================= NOMOR SURAT ================= --}}
    <table class="mb-4 font-bold">
        <tr>
            <td class="w-40 align-top">Rujukan kami</td>
            <td>: {{ $surat->nomor_surat }}</td>
        </tr>
        <tr>
            <td>Rujukan anda</td>
            <td>:</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>: Permohonan Izin Praktek Kerja Lapangan (PKL)</td>
        </tr>
        <tr>
            <td></td>
            <td>&nbsp;&nbsp;Mahasiswa FTI Universitas YARSI</td>
        </tr>
    </table>

    <div class="ml-[10rem]">
        
        {{-- KEPADA YTH --}}
        <div class="mb-4 font-bold">
            <p>
                Kepada Yth.<br>
                {{ $data['kepada_yth'] }}<br>
                {{ $data['nama_mitra'] }}<br>
                Alamat : {{ $data['alamat_mitra'] }}
            </p>
        </div>

        <p class="italic font-bold mb-4">Assalamu’alaikum Wr. Wb.</p>

        <div style="text-align: justify; text-justify: inter-word;">
                <p class="mb-4">
                    Salah satu program yang wajib dilaksanakan dalam menempuh Pendidikan di
                    <strong>Universitas YARSI Fakultas Teknologi Informasi Program Studi Perpustakaan
                    dan Sains Informasi</strong> adalah kegiatan magang yang dilaksanakan oleh mahasiswa.
                </p>

                <p class="mb-4">
                    Berdasarkan hal tersebut, kami bermaksud mengajukan permohonan kesediaan
                    <strong>{{ $data['nama_mitra'] }}</strong> dapat menerima mahasiswa kami, untuk melakukan
                    magang. Sekiranya Bapak/Ibu berkenan,
                    untuk tanggal pelaksanaan magang bagi mahasiswa kami yaitu mulai tanggal
                    <strong>{{ \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') }}</strong>
                    sampai dengan
                    <strong>{{ \Carbon\Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') }}.</strong>
                    Adapun peserta magang mahasiswa kami sebagai berikut (data terlampir).
                </p>

                <p class="mb-2">Adapun tujuan dari Magang adalah agar mahasiswa:</p>

                <ol class="list-[lower-alpha] ml-6 mb-4">
                    <li class="mb-2">
                        Mengenal dan merasakan dunia kerja agar mereka dapat
                        mempersiapkan diri apabila nanti memasuki dunia kerja.
                    </li>
                    <li class="mb-2">
                        Menerapkan pengetahuan teoritis yang diperoleh dari perkuliahan ke
                        dalam praktik di perpustakaan atau Pusat Dokumentasi dan Informasi.
                    </li>
                    <li>
                        Membekali mahasiswa dengan pengalaman praktik dalam bidang:
                        <ul class="list-disc ml-6 mt-2">
                            <li>Manajemen koleksi, administrasi, dan organisasi</li>
                            <li>Pengolahan koleksi tercetak dan non tercetak</li>
                            <li>Layanan informasi dan perpustakaan</li>
                            <li>Teknologi informasi untuk pengolahan koleksi dan informasi</li>
                            <li>Preservasi informasi dan konservasi media</li>
                            <li>Pengelolaan kearsipan</li>
                        </ul>
                    </li>
                </ol>

                <p class="mb-6">
                    Berkaitan dengan hal tersebut di atas, mohon kiranya Bapak/Ibu dapat memberikan
                    bimbingan dan penilaian kepada mahasiswa kami tersebut. Demikianlah permohonan
                    ini kami sampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.
                </p>
            </div>

        <p class="italic font-bold bm-10">Wassalaamu’alaikum Wr. Wb.</p>

    {{-- ================= TANDA TANGAN ================= --}}
        <div class="flex justify-end mt-10">
            <div style="text-align:right;">

                <p>
                    Jakarta,
                    {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                </p>

                <p class="font-bold mt-1">
                    DEKAN FAKULTAS TEKNOLOGI INFORMASI
                </p>

                <div style="margin-top:10px;">
                    @if($surat->qr_token)
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/surat/validasi/'.$surat->qr_token) }}"
                            style="display:block; margin-left:auto; margin-bottom:10px;">
                    @endif

                    <strong>{{ $surat->penandatangan }}</strong>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <p>Tembusan :</p>
            <ul class="list-disc ml-6">
                <li>Ka. Prodi PdSI</li>
                <li>Koord Magang</li>
                <li>Arsip</li>
            </ul>
        </div>
    </div>
</div>

{{-- ================= PAGE BREAK ================= --}}
<div style="page-break-after: always;"></div>

{{-- ================= HALAMAN 2 (LAMPIRAN) ================= --}}

<div class="bg-white mx-auto max-w-4xl shadow p-10 text-sm">

    <h3 class="font-bold mb-4">
        Lampiran : {{ $surat->nomor_surat }}
    </h3>

    <table class="w-full border border-collapse">
        <thead>
            <tr>
                <th class="border px-2 py-1">No</th>
                <th class="border px-2 py-1">NPM</th>
                <th class="border px-2 py-1">Nama</th>
                <th class="border px-2 py-1">No. HP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mahasiswa as $i => $m)
            <tr>
                <td class="border px-2 py-1 text-center">{{ $i+1 }}</td>
                <td class="border px-2 py-1">{{ $m['npm'] }}</td>
                <td class="border px-2 py-1">{{ $m['nama'] }}</td>
                <td class="border px-2 py-1">{{ $m['no_kontak'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-center mt-10">
        <img src="{{ asset('img/footer.jpg') }}" class="w-80">
    </div>

</div>
