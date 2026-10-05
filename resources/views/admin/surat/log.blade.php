@extends('layouts.app') {{-- HEADER --}}

@section('content')

<div class="bg-white rounded shadow p-6">

    {{-- ================= HEADER + KEMBALI ================= --}}
        <div class="mb-6 flex justify-between items-start">
            
            <div>
                <h2 class="text-2xl font-semibold text-gray-700">
                    Log — Surat
                </h2>
            </div>

            {{-- Tombol Kembali --}}
            <a href="/admin/dashboard"
            class="border border-blue-500 text-blue-600 
                    hover:bg-blue-50 px-3 py-1 rounded text-xs h-fit">
                Kembali ke Dashboard
            </a>
        </div>
    {{-- ================= FILTER ================= --}}
    <form method="GET" action="/admin/surat"
      class="bg-gray-50 border rounded-lg p-4 mb-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">

        {{-- SEARCH --}}
        <div class="lg:col-span-4">
            <label class="text-sm font-medium mb-1 block text-gray-700">
                Pencarian
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>

                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Nama / NPM / NIK"
                       class="w-full pl-10 pr-3 py-2
                              border rounded-lg
                              focus:ring-2 focus:ring-blue-500
                              focus:outline-none">
            </div>
        </div>

        {{-- STATUS --}}
        <div class="lg:col-span-2">
            <label class="text-sm font-medium mb-1 block">Status</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2">
                <option value="">Semua</option>
                <option value="menunggu" {{ request('status')=='menunggu'?'selected':'' }}>Menunggu</option>
                <option value="disetujui_final" {{ request('status')=='disetujui_final'?'selected':'' }}>Disetujui</option>
                <option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option>
            </select>
        </div>

        {{-- PRODI --}}
        <div class="lg:col-span-2">
            <label class="text-sm font-medium mb-1 block">Prodi</label>
            <select name="prodi" class="w-full border rounded-lg px-3 py-2">
                <option value="">Semua</option>
                <option value="TI" {{ request('prodi')=='TI'?'selected':'' }}>TI</option>
                <option value="PdSI" {{ request('prodi')=='PdSI'?'selected':'' }}>PdSI</option>
            </select>
        </div>

        {{-- TANGGAL --}}
        <div class="lg:col-span-2">
            <label class="text-sm font-medium mb-1 block">Tanggal</label>
            <input type="date" name="tanggal"
                   value="{{ request('tanggal') }}"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        {{-- SORT --}}
        <div class="lg:col-span-2">
            <label class="text-sm font-medium mb-1 block">Urutan</label>
            <select name="sort" class="w-full border rounded px-3 py-2">
                <option value="desc" {{ request('sort')=='desc'?'selected':'' }}>Terbaru</option>
                <option value="asc" {{ request('sort')=='asc'?'selected':'' }}>Terlama</option>
            </select>
        </div>

        {{-- JENIS SURAT --}}
        <div class="lg:col-span-4">
            <label class="text-sm font-medium mb-1 block">Jenis Surat</label>
            <select name="jenis" class="w-full border rounded-lg px-3 py-2">
                <option value="">Semua</option>
                @foreach($jenisSurat as $j)
                    <option value="{{ $j->id }}" {{ request('jenis')==$j->id?'selected':'' }}>
                        {{ $j->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- USER / ROLE --}}
        <div class="lg:col-span-2">
            <label class="text-sm font-medium mb-1 block">Pengaju</label>
            <select name="role" class="w-full border rounded-lg px-3 py-2">
                <option value="">Mahasiswa Dosen</option>
                <option value="mahasiswa" {{ request('role')=='mahasiswa'?'selected':'' }}>
                    Mahasiswa
                </option>
                <option value="dosen" {{ request('role')=='dosen'?'selected':'' }}>
                    Dosen
                </option>
            </select>
        </div>

        {{-- ACTION --}}
        <div class="lg:col-span-12 flex items-center gap-2 pt-2 border-t">

            {{-- RESET --}}
            <a href="/admin/surat"
               class="inline-flex items-center gap-2
                      px-4 py-2
                      border border-gray-300
                      text-gray-700 text-sm font-medium
                      rounded-lg hover:bg-gray-100">
                Reset
            </a>

            {{-- RIGHT --}}
            <div class="ml-auto flex gap-2">

                <button type="submit"
                        class="inline-flex items-center gap-2
                               px-4 py-2
                               bg-blue-600 hover:bg-blue-700
                               text-white text-sm font-medium
                               rounded-lg">
                    Terapkan
                </button>

                <a href="/admin/surat/export/excel?{{ http_build_query(request()->query()) }}"
                   class="inline-flex items-center gap-2
                          px-4 py-2
                          bg-teal-600 hover:bg-teal-700
                          text-white text-sm font-medium
                          rounded-lg">
                    Export Excel
                </a>

            </div>
        </div>

    </div>
</form>


    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-3 py-2">No</th>
                    <th class="border px-2 py-2 w-[110px] text-center">Nomor Surat</th>
                    <th class="border px-3 py-2 min-w-[195px] text-center">Nama</th>
                    <th class="border px-2 py-2 w-[110px] text-center">NPM / NIK</th>
                    <th class="border px-3 py-2 min-w-[160px]">Jenis</th>
                    <th class="border px-3 py-2 min-w-[180px]">Status</th>
                    <th class="border px-3 py-2">Tgl Pengajuan</th>
                    <th class="border px-3 py-2">Tgl Disetujui</th>
                    <th class="border px-3 py-2">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($suratList as $i => $surat)
                <tr>
                    <td class="border-t px-3 py-2">{{ $i+1 }}</td>
                    <td class="border-t px-2 py-2 w-[110px] text-center text-sm">{{ $surat->nomor_surat ?? '-' }}</td>
                    <td class="border-t px-3 py-2 min-w-[195px] text-center">{{ $surat->user->name }}</td>
                    <td class="border-t px-2 py-2 w-[110px] text-center text-sm">{{ $surat->user->npm ?? $surat->user->nidn }}</td>
                    <td class="border-t px-3 py-2 min-w-[160px]">{{ $surat->jenisSurat->nama }}</td>
                    <td class="border-t px-3 py-2 min-w-[180px]">
                        @php
                            $status = $surat->status;
                            $color = match($status) {
                                'ditolak' => 'bg-red-600',
                                'disetujui_final' => 'bg-green-600',
                                default => 'bg-orange-500'
                            };
                        @endphp
                        <span class="border-t px-3 py-1 rounded {{ $color }} text-white text-xs font-semibold">
                            {{ 
                                $surat->user->role === 'dosen'
                                    ? statusDosen($status)
                                    : statusMahasiswa($status)
                            }}
                        </span>
                    </td>
                    {{-- ================= TABLE =================
                    <td class="border px-3 py-2">
                        {{ optional($surat->tanggal_disetujui_final ?? $surat->created_at)->format('d-m-Y H:i') }}
                    </td> --}}

                    <td class="border-t px-3 py-2">
                        {{ $surat->created_at->format('d-m-Y H:i') }}
                    </td>

                    <td class="border-t px-3 py-2">
                        {{ $surat->tanggal_disetujui_final
                            ? $surat->tanggal_disetujui_final->format('d-m-Y H:i')
                            : '-' }}
                    </td>

                    <td class="border-t px-3 py-2 text-center">
                        <a href="/admin/surat/{{ $surat->id }}"
                           class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200 
                                px-3 py-1 rounded text-xs font-semibold">
                        Detail
                    </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
