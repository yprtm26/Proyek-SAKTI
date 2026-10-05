@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        Pengajuan Surat — MAHASISWA
    </h2>

    <div class="flex gap-3">

        {{-- TOMBOL KE DASHBOARD DISPOSISI --}}
        <div class="flex gap-3">
            <a href="{{ auth()->user()->role === 'admin' ? url('/admin/disposisi') : url('/disposisi') }}"
            class="inline-flex items-center gap-2
                    px-4 py-2
                    bg-green-500 text-white text-sm font-medium
                    rounded-full
                    hover:bg-green-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 0v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8m18 0L12 13 3 8"/>
                </svg>
                Dashboard Disposisi
            </a>
        </div>

            <a href="{{ url('/admin/surat') }}"
            class="inline-flex items-center gap-2
                    px-4 py-2
                    bg-white border border-gray-300
                    text-gray-700 text-sm font-medium
                    rounded-full
                    hover:bg-gray-100 transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-gray-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12.75V6.75a3 3 0 013-3h4.28
                        a1.5 1.5 0 011.06.44l1.72 1.72
                        a1.5 1.5 0 001.06.44H18.75
                        a3 3 0 013 3v3.75" />
                <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12.75A3.75 3.75 0 016 9h15.75
                        a3.75 3.75 0 013.75 3.75v4.5
                        A3.75 3.75 0 0118.75 21H6
                        a3.75 3.75 0 01-3.75-3.75v-4.5z" />
                </svg>

                Log Surat
            </a>
    </div>

</div>

{{-- ================= FILTER & SEARCH ================= --}}
{{-- ================= FILTER (1 BARIS) ================= --}}
<form method="GET"
      class="bg-gray-50 border rounded-lg p-3 mb-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

        {{-- SEARCH --}}
        <div class="lg:col-span-3">
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
                       placeholder="Cari nama / NPM / jenis surat"
                       class="w-full pl-10 pr-3 py-2
                              border rounded-lg
                              focus:ring-2 focus:ring-blue-500
                              focus:outline-none">
            </div>
        </div>

        {{-- PRODI --}}
        <div class="lg:col-span-1">
            <select name="prodi"
                    class="w-full border rounded-lg px-3 py-2">
                <option value="">Prodi</option>
                <option value="PdSI" {{ request('prodi')=='PdSI'?'selected':'' }}>PdSI</option>
                <option value="TI" {{ request('prodi')=='TI'?'selected':'' }}>TI</option>
            </select>
        </div>

        {{-- JENIS --}}
        <div class="lg:col-span-2">
            <select name="jenis"
                    class="w-full border rounded-lg px-3 py-2">
                <option value="">Jenis Surat</option>
                @foreach($jenisSurat as $j)
                    <option value="{{ $j->id }}"
                        {{ request('jenis')==$j->id?'selected':'' }}>
                        {{ $j->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SORT --}}
        <div class="lg:col-span-2">
            <select name="sort"
                    class="w-full border rounded-lg px-3 py-2">
                <option value="desc" {{ request('sort')=='desc'?'selected':'' }}>
                    Terbaru
                </option>
                <option value="asc" {{ request('sort')=='asc'?'selected':'' }}>
                    Terlama
                </option>
            </select>
        </div>

        {{-- RESET --}}
        <div class="lg:col-span-1">
            <a href="/admin/dashboard"
               class="inline-flex justify-center w-full
                      px-4 py-2
                      border border-gray-300
                      text-gray-700 text-sm font-medium
                      rounded-lg
                      hover:bg-gray-100">
                Reset
            </a>
        </div>

        {{-- APPLY --}}
        <div class="lg:col-span-1">
            <button type="submit"
                    class="inline-flex justify-center w-full
                           px-4 py-2
                           bg-blue-600 hover:bg-blue-700
                           text-white text-sm font-medium
                           rounded-lg">
                Terapkan
            </button>
        </div>

        {{-- SWITCH --}}
        <div class="lg:col-span-2">
            <a href="{{ '/admin/dashboard-dosen' }}"
               class="ml-auto pl-4 border-l
                      inline-flex justify-center w-full items-center gap-2
                      px-4 py-2
                      bg-blue-50
                      border-blue-300
                      text-blue-700 text-sm font-medium
                      rounded-full
                      hover:bg-blue-100 transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4 text-blue-600"
                     fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 9.75L12 3l9 6.75V21
                             a.75.75 0 01-.75.75H3.75
                             A.75.75 0 013 21V9.75z" />
                </svg>

                Surat Dosen
            </a>
        </div>

    </div>
</form>


{{-- ================= TABLE ================= --}}
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">NPM</th>
                <th class="px-4 py-3">Jenis Surat</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y">
        @forelse($suratPengajuan as $i => $surat)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">{{ $i + 1 }}</td>
                <td class="px-4 py-3">{{ $surat->user->name }}</td>
                <td class="px-4 py-3">{{ $surat->user->npm }}</td>
                <td class="px-4 py-3">{{ $surat->jenisSurat->nama }}</td>
                <td class="px-4 py-3">
                    {{ $surat->created_at->format('d-m-Y H:i') }}
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs text-white bg-orange-500">
                        {{ statusMahasiswa($surat->status) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="/admin/surat/{{ $surat->id }}"
                       class="inline-block bg-blue-100 text-blue-700 hover:bg-blue-200 
                            px-3 py-1 rounded text-xs font-semibold">
                        Detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                    Belum ada pengajuan surat
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
