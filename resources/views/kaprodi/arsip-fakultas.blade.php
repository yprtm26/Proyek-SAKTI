@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto p-6">

    <div class="mb-6 flex justify-between items-start">
    
    <div>
        <h2 class="text-2xl font-semibold text-gray-700">
            Arsip Surat Fakultas
        </h2>
        <p class="text-sm text-gray-500">
            Surat Fakultas: Tembusan Kaprodi (read-only)
        </p>
    </div>

    {{-- Tombol Kembali --}}
    <a href="/kaprodi/dashboard"
       class="border border-blue-500 text-blue-600 
              hover:bg-blue-50 px-3 py-1 rounded text-xs h-fit">
        Kembali ke Dashboard
    </a>
</div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-3 py-2">No</th>
                    <th class="border px-3 py-2">Nomor Surat</th>
                    <th class="border px-3 py-2">Jenis Surat</th>
                    <th class="border px-3 py-2">Mahasiswa</th>
                    <th class="border px-3 py-2">Tanggal Disetujui</th>
                    <th class="border px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratList as $i => $surat)
                <tr>
                    <td class="border px-3 py-2 text-center">{{ $i+1 }}</td>
                    <td class="border px-3 py-2">{{ $surat->nomor_surat }}</td>
                    <td class="border px-3 py-2">{{ $surat->jenisSurat->nama }}</td>
                    <td class="border px-3 py-2">
                        {{ $surat->user->name }}<br>
                        <span class="text-xs text-gray-500">
                            {{ $surat->user->npm ?? $surat->user->nidn }}
                        </span>
                    </td>
                    <td class="border px-3 py-2">
                        {{ optional($surat->tanggal_disetujui_final)->format('d-m-Y H:i') }}
                    </td>
                    <td class="border px-3 py-2 text-center">
                        <a href="/kaprodi/arsip-fakultas/{{ $surat->id }}"
                        class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200 
                                px-3 py-1 rounded text-xs font-semibold">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6"
                        class="border px-3 py-4 text-center text-gray-500">
                        Belum ada arsip surat fakultas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</main>
@endsection
