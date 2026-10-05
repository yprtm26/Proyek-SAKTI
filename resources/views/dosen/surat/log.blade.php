@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">

    <div class="mb-6 flex justify-between items-start">
        
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-1">
                Log Pengajuan Surat
            </h2>

            <p class="text-sm text-gray-500 mt-0">
                Daftar seluruh surat yang pernah diajukan beserta statusnya.
            </p>
        </div>

        {{-- Tombol Kembali --}}
        <a href="/dosen/dashboard"
        class="border border-blue-500 text-blue-600 
                hover:bg-blue-50 px-3 py-1 rounded text-xs h-fit">
            Kembali ke Dashboard
        </a>
    </div>

    {{-- ================= FILTER STATUS ================= --}}
    <div class="mt-4 mb-4 flex items-center gap-2 text-sm">

        <span class="text-gray-500">Tampilkan:</span>

        <a href="/dosen/surat"
        class="px-3 py-1 rounded-full border
                {{ request('status')=='' 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 text-gray-600 hover:bg-gray-100' }}">
            Semua
        </a>

        <a href="/dosen/surat?status=menunggu"
        class="px-3 py-1 rounded-full border
                {{ request('status')=='menunggu' 
                        ? 'bg-orange-500 text-white border-orange-500' 
                        : 'border-gray-300 text-gray-600 hover:bg-gray-100' }}">
            Menunggu
        </a>

        <a href="/dosen/surat?status=disetujui_final"
        class="px-3 py-1 rounded-full border
                {{ request('status')=='disetujui_final' 
                        ? 'bg-green-600 text-white border-green-600' 
                        : 'border-gray-300 text-gray-600 hover:bg-gray-100' }}">
            Disetujui
        </a>

        <a href="/dosen/surat?status=ditolak"
        class="px-3 py-1 rounded-full border
                {{ request('status')=='ditolak' 
                        ? 'bg-red-600 text-white border-red-600' 
                        : 'border-gray-300 text-gray-600 hover:bg-gray-100' }}">
            Ditolak
        </a>

    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Jenis Surat</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratList as $i => $surat)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $i+1 }}</td>
                        <td class="px-4 py-3">
                            {{ $surat->jenisSurat->nama }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $surat->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-white text-xs
                                {{ $surat->status === 'disetujui_final' ? 'bg-green-500' :
                                   ($surat->status === 'ditolak' ? 'bg-red-500' : 'bg-orange-500') }}">
                                {{ statusDosen($surat->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/dosen/surat/{{ $surat->id }}"
                               class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200 
                                px-3 py-1 rounded text-xs font-semibold">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">
                            Belum ada pengajuan surat
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
