@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        Dashboard Disposisi
    </h2>

    {{-- Tombol Tambah hanya untuk Admin --}}
    <div class="flex gap-3">
        @if(Auth::user()->role === 'admin')
            <a href="{{ url('/admin/disposisi/create') }}" class="inline-flex items-center gap-2
                px-4 py-2 bg-green-500 text-white text-sm font-medium
                rounded-full hover:bg-green-600 transition">
                Tambah Disposisi
            </a>
        @endif
    </div>
</div>

{{-- TABEL DISPOSISI --}}
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">No Surat</th>
                <th class="px-4 py-3">Perihal</th>
                <th class="px-4 py-3">File</th>
                <th class="px-4 py-3">Pengirim</th>
                <th class="px-4 py-3">Penerima</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Prioritas</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            @forelse($disposisiList as $i => $disposisi)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">{{ $i + 1 }}</td>
                <td class="px-4 py-3">{{ $disposisi->surat->no_surat }}</td>
                <td class="px-4 py-3">{{ $disposisi->surat->perihal }}</td>
                <td class="px-4 py-3">
                    <a href="{{ asset('storage/'.$disposisi->surat->file_path) }}" target="_blank" class="text-blue-600 hover:underline">
                        Lihat File
                    </a>
                </td>
                <td class="px-4 py-3">{{ $disposisi->fromUser->name }}</td>
                <td class="px-4 py-3">{{ $disposisi->toUser->name }}</td>
                <td class="px-4 py-3">{{ $disposisi->created_at->format('d-m-Y H:i') }}</td>
                <td class="px-4 py-3">
                    @if($disposisi->status === 'terkirim')
                        <span class="px-2 py-1 rounded text-xs text-white bg-orange-500">Terkirim</span>
                    @elseif($disposisi->status === 'dibaca')
                        <span class="px-2 py-1 rounded text-xs text-white bg-blue-500">Dibaca</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs text-white bg-green-500">Selesai</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @php
                        $warna = [
                            'sangat_segera' => 'bg-red-100 text-red-700',
                            'segera' => 'bg-yellow-100 text-yellow-700',
                            'biasa' => 'bg-green-100 text-green-700',
                        ];
                    @endphp

                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        {{ $warna[$disposisi->prioritas] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ strtoupper(str_replace('_',' ', $disposisi->prioritas)) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ url('/disposisi/'.$disposisi->id) }}" class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200
                        px-3 py-1 rounded text-xs font-semibold">
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                    Belum ada disposisi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{-- Tombol Kembali ke Dashboard sesuai role --}}
        @php
            $role = Auth::user()->role;
            $dashboardLink = match($role) {
                'dosen'    => url('/dosen/dashboard'),
                'kaprodi'  => url('/kaprodi/dashboard'),
                'dekan'    => url('/dekan/dashboard'),
                default    => url('/'), // fallback
            };
        @endphp

        <a href="{{ $dashboardLink }}"
        class="border border-blue-500 text-blue-600 
                hover:bg-blue-50 px-3 py-1 rounded text-xs h-fit">
            Kembali ke Dashboard
        </a>
</div>
@endsection
