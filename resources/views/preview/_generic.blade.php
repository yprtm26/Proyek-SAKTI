@php
    $user = $surat->user;
    $data = (array) ($surat->data ?? []);
    $personelList = \App\Support\SuratPersonelSync::readFromSurat($surat);
    $statusLabel = $user?->role === 'dosen'
        ? statusDosen($surat->status)
        : statusMahasiswa($surat->status);
@endphp

<article class="a4 border border-gray-300 mx-auto p-6 sm:p-10 bg-white text-black" style="color: #000000; background-color: #ffffff; font-size: 15px; line-height: 1.65;">
    {{-- Kop Surat Resmi --}}
    <div class="flex items-center justify-between border-b-2 border-black pb-4 mb-6 gap-4">
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/logo.jpeg') }}"
                 alt="Logo Universitas YARSI"
                 width="64"
                 height="64"
                 class="h-16 w-auto object-contain">
            <div>
                <p class="font-bold uppercase tracking-wide" style="font-size: 15px; color: #000000;">
                    Universitas YARSI
                </p>
                <p class="font-semibold" style="font-size: 14px; color: #000000;">
                    Fakultas Teknologi Informasi
                </p>
                <p style="font-size: 14px; color: #000000;">
                    Jl. Letjen Suprapto Kav. 13, Cempaka Putih, Jakarta Pusat
                </p>
            </div>
        </div>
    </div>

    {{-- Judul Dokumen & Nomor Surat --}}
    <div class="text-center mb-6">
        <h2 class="font-bold uppercase underline" style="font-size: 18px; color: #000000;">
            {{ $surat->jenisSurat?->nama ?? 'Dokumen Pengajuan Surat' }}
        </h2>
        <p class="mt-1" style="font-size: 14px; color: #000000;">
            Nomor: {{ $surat->nomor_surat ?? 'Belum diterbitkan' }}
        </p>
    </div>

    {{-- Identitas Pengusul --}}
    <p class="mb-3" style="font-size: 15px; color: #000000;">
        Berikut adalah rincian resmi pengajuan surat dari sivitas akademika Fakultas Teknologi Informasi:
    </p>

    <table class="w-full border-collapse mb-6" style="font-size: 14px; color: #000000;">
        <tbody>
            <tr>
                <td class="py-1.5 pr-3 font-semibold align-top w-48" style="color: #000000;">Nama Pengusul</td>
                <td class="py-1.5 pr-2 align-top w-4" style="color: #000000;">:</td>
                <td class="py-1.5 align-top" style="color: #000000;">{{ $user?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="py-1.5 pr-3 font-semibold align-top" style="color: #000000;">
                    {{ $user?->role === 'mahasiswa' ? 'NPM' : ($user?->role === 'dosen' ? 'NIDN' : 'Identitas') }}
                </td>
                <td class="py-1.5 pr-2 align-top" style="color: #000000;">:</td>
                <td class="py-1.5 align-top" style="color: #000000;">
                    {{ $user?->npm ?? $user?->nidn ?? $user?->serial_number ?? '-' }}
                </td>
            </tr>
            @if($user?->prodi)
                <tr>
                    <td class="py-1.5 pr-3 font-semibold align-top" style="color: #000000;">Program Studi</td>
                    <td class="py-1.5 pr-2 align-top" style="color: #000000;">:</td>
                    <td class="py-1.5 align-top" style="color: #000000;">{{ namaProdi($user->prodi) }}</td>
                </tr>
            @endif
            <tr>
                <td class="py-1.5 pr-3 font-semibold align-top" style="color: #000000;">Status Pengajuan</td>
                <td class="py-1.5 pr-2 align-top" style="color: #000000;">:</td>
                <td class="py-1.5 align-top" style="color: #000000;">{{ $statusLabel }}</td>
            </tr>
            <tr>
                <td class="py-1.5 pr-3 font-semibold align-top" style="color: #000000;">Tanggal Pengajuan</td>
                <td class="py-1.5 pr-2 align-top" style="color: #000000;">:</td>
                <td class="py-1.5 align-top" style="color: #000000;">{{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Data Isian Surat --}}
    @php
        $excludedKeys = ['personel', 'prodi_id', 'semester_id'];
        $displayData = collect($data)->reject(fn ($v, $k) => in_array($k, $excludedKeys, true) || $v === null || $v === '');
    @endphp

    @if($displayData->isNotEmpty())
        <h3 class="font-bold border-b border-black pb-1 mb-3" style="font-size: 15px; color: #000000;">
            Rincian Isian Surat
        </h3>
        <table class="w-full border-collapse mb-6" style="font-size: 14px; color: #000000;">
            <tbody>
                @foreach($displayData as $key => $val)
                    <tr>
                        <td class="py-1.5 pr-3 font-semibold align-top w-48" style="color: #000000;">
                            {{ labelFieldSurat((string) $key) }}
                        </td>
                        <td class="py-1.5 pr-2 align-top w-4" style="color: #000000;">:</td>
                        <td class="py-1.5 align-top" style="color: #000000;">
                            @if(is_array($val))
                                {{ collect($val)->map(fn ($item) => is_array($item) ? implode(', ', array_filter($item, 'is_scalar')) : (string) $item)->implode('; ') }}
                            @else
                                {{ (string) $val }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Daftar Anggota Kegiatan jika ada --}}
    @if(count($personelList) > 0)
        <h3 class="font-bold border-b border-black pb-1 mb-3" style="font-size: 15px; color: #000000;">
            Daftar Anggota dan Peran Kegiatan
        </h3>
        <table class="w-full border border-black border-collapse mb-6" style="font-size: 14px; color: #000000;">
            <thead>
                <tr>
                    <th class="border border-black px-2 py-1.5 text-center" style="color: #000000;">No</th>
                    <th class="border border-black px-2 py-1.5 text-left" style="color: #000000;">Nama</th>
                    <th class="border border-black px-2 py-1.5 text-left" style="color: #000000;">Peran</th>
                    <th class="border border-black px-2 py-1.5 text-left" style="color: #000000;">Instansi / NIM / NIDN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personelList as $idx => $anggota)
                    <tr>
                        <td class="border border-black px-2 py-1.5 text-center" style="color: #000000;">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5" style="color: #000000;">{{ $anggota['nama'] !== '' ? $anggota['nama'] : '-' }}</td>
                        <td class="border border-black px-2 py-1.5" style="color: #000000;">{{ $anggota['peran'] !== '' ? $anggota['peran'] : '-' }}</td>
                        <td class="border border-black px-2 py-1.5" style="color: #000000;">{{ $anggota['instansi'] !== '' ? $anggota['instansi'] : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Blok Tanda Tangan --}}
    <div class="mt-8 flex justify-end">
        <div class="w-64 text-left" style="font-size: 14px; color: #000000;">
            <p style="color: #000000;">
                Jakarta, {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d F Y') ?? $surat->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
            </p>
            <p class="mt-12 font-bold" style="color: #000000;">
                {{ $surat->penandatangan ?? 'Menunggu Pengesahan Pejabat Berwenang' }}
            </p>
        </div>
    </div>
</article>
