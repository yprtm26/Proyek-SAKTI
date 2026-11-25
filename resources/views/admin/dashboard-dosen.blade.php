@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
    <h2 class="text-xl font-bold text-gray-900">
        Antrean Pengajuan Surat - Dosen
    </h2>

    <div class="flex flex-wrap gap-2">
        <a href="{{ url('/admin/template-surat') }}"
           class="sakti-touch-target inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100 transition">
            Kelola Template
        </a>

        <a href="{{ url('/admin/surat') }}"
           class="sakti-touch-target inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100 transition">
            Log Surat
        </a>
    </div>
</div>

{{-- ================= FILTER & SEARCH ================= --}}
<form method="GET"
      action="/admin/dashboard-dosen"
      class="bg-white border border-gray-300 rounded-xl p-4 mb-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

        {{-- SEARCH --}}
        <div class="lg:col-span-3">
            <label for="adminDosenSearchQ" class="block text-xs font-semibold text-gray-800 mb-1">Pencarian</label>
            <input type="text"
                   id="adminDosenSearchQ"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Cari nama / NIK / jenis surat"
                   class="w-full min-h-[44px] px-3 py-2 border border-gray-400 rounded-lg text-sm text-gray-900 bg-white">
        </div>

        {{-- JENIS --}}
        <div class="lg:col-span-3">
            <label for="adminDosenFilterJenis" class="block text-xs font-semibold text-gray-800 mb-1">Jenis Surat</label>
            <select id="adminDosenFilterJenis" name="jenis"
                    class="w-full min-h-[44px] border border-gray-400 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white">
                <option value="">Semua Jenis Surat</option>
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
            <label for="adminDosenFilterSort" class="block text-xs font-semibold text-gray-800 mb-1">Urutan Antrean</label>
            <select id="adminDosenFilterSort" name="sort"
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

        {{-- RESET --}}
        <div class="lg:col-span-1">
            <a href="/admin/dashboard-dosen"
               class="sakti-touch-target w-full px-3 py-2 border border-gray-400 text-gray-900 text-sm font-semibold rounded-lg hover:bg-gray-100">
                Reset
            </a>
        </div>

        {{-- APPLY --}}
        <div class="lg:col-span-1">
            <button type="submit"
                    class="sakti-touch-target w-full px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg">
                Terapkan
            </button>
        </div>

        {{-- SWITCH --}}
        <div class="lg:col-span-2">
            <a href="/admin/dashboard"
               class="sakti-touch-target w-full px-3 py-2 bg-orange-50 border border-orange-400 text-orange-900 text-sm font-bold rounded-lg hover:bg-orange-100 text-center">
                Surat Mahasiswa
            </a>
        </div>

    </div>
</form>

@include('admin.partials.antrean-fifo-table', [
    'suratPengajuan' => $suratPengajuan,
    'roleTarget' => 'dosen',
    'identityLabel' => 'NIDN / NIK',
    'emptyMessage' => 'Tidak ada pengajuan surat aktif dari dosen.',
    'sort' => $sort ?? 'asc',
])

@endsection
