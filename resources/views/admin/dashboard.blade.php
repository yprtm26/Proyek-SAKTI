@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
    <h2 class="text-xl font-bold text-gray-900">
        Antrean Pengajuan Surat - Mahasiswa
    </h2>

    <div class="flex flex-wrap gap-2">
        <a href="{{ auth()->user()->role === 'admin' ? url('/admin/disposisi') : url('/disposisi') }}"
           class="sakti-touch-target inline-flex items-center gap-2 px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-800 transition">
            Dashboard Disposisi
        </a>

        <a href="{{ url('/admin/surat') }}"
           class="sakti-touch-target inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100 transition">
            Log Surat
        </a>

        <a href="{{ url('/admin/template-surat') }}"
           class="sakti-touch-target inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100 transition">
            Kelola Template
        </a>
    </div>
</div>

{{-- ================= FILTER & SEARCH ================= --}}
<form method="GET"
      action="/admin/dashboard"
      class="bg-white border border-gray-300 rounded-xl p-4 mb-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

        {{-- SEARCH --}}
        <div class="lg:col-span-3">
            <label for="adminSearchQ" class="block text-xs font-semibold text-gray-800 mb-1">Pencarian</label>
            <input type="text"
                   id="adminSearchQ"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Cari nama / NPM / jenis surat"
                   class="w-full min-h-[44px] px-3 py-2 border border-gray-400 rounded-lg text-sm text-gray-900 bg-white">
        </div>

        {{-- PRODI --}}
        <div class="lg:col-span-2">
            <label for="adminFilterProdi" class="block text-xs font-semibold text-gray-800 mb-1">Prodi</label>
            <select id="adminFilterProdi" name="prodi"
                    class="w-full min-h-[44px] border border-gray-400 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white">
                <option value="">Semua Prodi</option>
                <option value="PdSI" {{ request('prodi')=='PdSI'?'selected':'' }}>PdSI</option>
                <option value="TI" {{ request('prodi')=='TI'?'selected':'' }}>TI</option>
            </select>
        </div>

        {{-- JENIS --}}
        <div class="lg:col-span-2">
            <label for="adminFilterJenis" class="block text-xs font-semibold text-gray-800 mb-1">Jenis Surat</label>
            <select id="adminFilterJenis" name="jenis"
                    class="w-full min-h-[44px] border border-gray-400 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white">
                <option value="">Semua Jenis</option>
                @foreach($jenisSurat as $j)
                    <option value="{{ $j->id }}"
                        {{ request('jenis')==$j->id?'selected':'' }}>
                        {{ $j->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SORT (Default FIFO: asc) --}}
        <div class="lg:col-span-2">
            <label for="adminFilterSort" class="block text-xs font-semibold text-gray-800 mb-1">Urutan Antrean</label>
            <select id="adminFilterSort" name="sort"
                    onchange="this.form.submit()"
                    class="w-full min-h-[44px] border border-gray-400 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white">
                <option value="asc" {{ ($sort ?? request('sort', 'asc')) === 'asc' ? 'selected' : '' }}>
                    Terlama
                </option>
                <option value="desc" {{ ($sort ?? request('sort')) === 'desc' ? 'selected' : '' }}>
                    Terbaru
                </option>
            </select>
        </div>

        {{-- RESET & APPLY --}}
        <div class="lg:col-span-1">
            <a href="/admin/dashboard"
               class="sakti-touch-target w-full px-3 py-2 border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100">
                Reset
            </a>
        </div>

        <div class="lg:col-span-1">
            <button type="submit"
                    class="sakti-touch-target w-full px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg">
                Terapkan
            </button>
        </div>

        {{-- SWITCH --}}
        <div class="lg:col-span-1">
            <a href="/admin/dashboard-dosen"
               class="sakti-touch-target w-full px-3 py-2 bg-orange-50 border border-orange-400 text-orange-900 text-xs font-bold rounded-lg hover:bg-orange-100 text-center">
                Surat Dosen
            </a>
        </div>

    </div>
</form>

@include('admin.partials.antrean-fifo-table', [
    'suratPengajuan' => $suratPengajuan,
    'roleTarget' => 'mahasiswa',
    'identityLabel' => 'NPM / NIDN / NIP',
    'emptyMessage' => 'Belum ada pengajuan surat aktif dari mahasiswa.',
    'sort' => $sort ?? 'asc',
])

@endsection
