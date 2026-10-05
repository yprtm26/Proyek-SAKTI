<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Dokumen - Sistem Pengajuan Surat</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-500 via-orange-400 to-orange-600">

<div class="bg-white w-full max-w-2xl rounded-xl shadow-lg p-8">

    <!-- LOGO KAMPUS -->
    <div class="flex justify-center mb-4">
        <img src="{{ asset('img/logo.jpeg') }}"
             alt="Logo Universitas YARSI"
             class="w-24">
    </div>

    <!-- STATUS -->
    <div class="text-center mb-6">
        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png"
             alt="Valid"
             class="w-20 mx-auto mb-3">
        <h2 class="text-2xl font-bold text-green-600">
            DOKUMEN VALID
        </h2>
        <p class="text-gray-600 text-sm">
            Dokumen ini telah ditandatangani secara sah
        </p>
    </div>

    @php
        $rolePengaju = $surat->user->role;
    @endphp

    <!-- DATA -->
    <div class="border-t pt-4">
        <table class="w-full text-sm text-gray-700">
            <tbody>

            {{-- ============ MAHASISWA ============ --}}
            @if($rolePengaju === 'mahasiswa')
                <tr>
                    <td class="font-bold w-52 py-1">Nama Mahasiswa</td>
                    <td class="py-1">: {{ $surat->user->name }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">NPM</td>
                    <td class="py-1">: {{ $surat->user->npm }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Program Studi</td>
                    <td class="py-1">: {{ namaProdi($surat->user->prodi) }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Jenis Surat</td>
                    <td class="py-1">: {{ $surat->jenisSurat->nama }}</td>
                </tr>

            {{-- ============ DOSEN ============ --}}
            @elseif($rolePengaju === 'dosen')
                <tr>
                    <td class="font-bold py-1">Nama Dosen</td>
                    <td class="py-1">: {{ $surat->data['nama_dosen'] ?? $surat->user->name }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">NIK</td>
                    <td class="py-1">: {{ $surat->data['nidn'] ?? $surat->user->nidn }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Fakultas</td>
                    <td class="py-1">: Fakultas Teknologi Informasi</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Jenis Surat</td>
                    <td class="py-1">: {{ $surat->jenisSurat->nama }}</td>
                </tr>
            @endif

            {{-- ===== TTD QR MAHASISWA ===== --}}
            @if($surat->jenisSurat->ttd_qr_type === 'mahasiswa')
                <tr>
                    <td class="font-bold py-1">Ditandatangani Oleh</td>
                    <td class="py-1">: {{ $surat->user->name }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Tanggal Pengajuan</td>
                    <td class="py-1">: {{ $surat->created_at->translatedFormat('d F Y, H:i:s') }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Disetujui Oleh</td>
                    <td class="py-1">: {{ $surat->penandatangan ?? '-' }}</td>
                </tr>

                @if($surat->tanggal_disetujui_final)
                <tr>
                    <td class="font-bold py-1">Tanggal Disetujui</td>
                    <td class="py-1">: {{ $surat->tanggal_disetujui_final->translatedFormat('d F Y, H:i:s') }}</td>
                </tr>
                @endif

            {{-- ===== TTD QR ADMIN / KAPRODI / DEKAN ===== --}}
            @else

                @if($surat->nomor_surat)
                <tr>
                    <td class="font-bold py-1">Nomor Surat</td>
                    <td class="py-1">: {{ $surat->nomor_surat }}</td>
                </tr>
                @endif

                <tr>
                    <td class="font-bold py-1">Ditandatangani Oleh</td>
                    <td class="py-1">: {{ $surat->penandatangan }}</td>
                </tr>

                @if($surat->tanggal_disetujui_final)
                <tr>
                    <td class="font-bold py-1">Tanggal Disetujui</td>
                    <td class="py-1">: {{ $surat->tanggal_disetujui_final->translatedFormat('d F Y, H:i:s') }}</td>
                </tr>
                @endif

            @endif
            </tbody>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="text-center text-xs text-gray-500 mt-6 border-t pt-4">
        Sistem Pengajuan Surat – Fakultas Teknologi Informasi<br>
        Universitas YARSI
    </div>

</div>
</body>
</html>
