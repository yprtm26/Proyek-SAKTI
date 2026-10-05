@php
    $data = $surat->data;
    $mahasiswa = $data['mahasiswa'];
    $prodi = $surat->user->prodi;
@endphp

<div class="bg-white max-w-4xl mx-auto p-10 text-sm leading-relaxed shadow">

    {{-- ================= HEADER ================= --}}
    <div class="mb-6">
        <img src="{{ asset('img/logo.jpeg') }}" class="h-16">
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
            <td>: Permohonan Izin Magang</td>
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

        {{-- ================= ISI SURAT ================= --}}
        <div style="text-align: justify; text-justify: inter-word;">

            <p class="mb-4">
                Puji Syukur kepada Allah Subhanahu Wata’ala yang senantiasa melimpahkan Rahmat dan
                Ridho-Nya kepada kita semua dalam melakukan aktivitas sehari-hari. Aamiin.
            </p>

            <p class="mb-4">
                Sehubungan dengan ajuan permohonan dari mahasiswa yang akan melakukan
                magang mandiri ke pihak <strong>{{ $data['nama_mitra'] }}</strong>,
                dengan ini kami memohon kesediaan Bapak/Ibu untuk memberikan izin kepada
                mahasiswa yang datanya tersebut dibawah ini:
            </p>

            {{-- TABEL MAHASISWA --}}
            <table class="w-full border mb-4 text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1">No</th>
                        <th class="border px-2 py-1">NPM</th>
                        <th class="border px-2 py-1">Nama</th>
                        <th class="border px-2 py-1">Prodi</th>
                        <th class="border px-2 py-1">Semester</th>
                        <th class="border px-2 py-1">No. Kontak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mahasiswa as $i => $mhs)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i+1 }}</td>
                        <td class="border px-2 py-1">{{ $mhs['npm'] }}</td>
                        <td class="border px-2 py-1">{{ $mhs['nama'] }}</td>
                        <td class="border px-2 py-1">
                            {{ $prodi === 'TI' ? 'Teknik Informatika' : 'PdSI' }}
                        </td>
                        <td class="border px-2 py-1 text-center">{{ $mhs['semester'] }}</td>
                        <td class="border px-2 py-1">{{ $mhs['no_kontak'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="mb-4">
                Dengan ini kami memohon kepada Bapak/Ibu untuk dapat menerima mahasiswa kami tersebut
                untuk melaksanakan program <strong>Magang Mandiri</strong> yang akan dilaksanakan mulai tanggal
                <strong>{{ \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') }}</strong>
                sampai dengan
                <strong>{{ \Carbon\Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') }}</strong>
                pada instansi/lembaga/perusahaan Bapak/Ibu pimpin.
            </p>

            <p class="mb-6">
                Demikian surat pengantar ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan
                terima kasih.
            </p>

        </div>
        
        <p class="italic font-bold mb-10">Wassalaamu’alaikum Wr. Wb.</p>

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
            <p>Tembusan:</p>
            <ul class="list-disc ml-6">
                <li>Kepala Prodi {{ $prodi === 'TI' ? 'Teknik Informatika' : 'Perpustakaan dan Sains Informasi' }}</li>
                <li>Arsip</li>
            </ul>
        </div>

    </div>

    <div class="flex justify-center mt-10">
        <img src="{{ asset('img/footer.jpg') }}" class="w-80">
    </div>

</div>
