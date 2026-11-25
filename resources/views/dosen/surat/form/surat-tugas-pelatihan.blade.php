@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto p-6">
    
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">
            Isi data berikut dengan benar
        </p>
    </div>

    {{-- FORM --}}
    <form method="POST" action="{{ url('/dosen/surat/ajukan/'.$jenisSurat->id) }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- IDENTITAS DOSEN (TERKUNCI) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Dosen
                </label>
                <input type="text"
                       name="nama_dosen"
                       value="{{ old('nama_dosen', Auth::user()->name ?? '') }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    NIDN
                </label>
                <input type="text"
                       value="{{ Auth::user()->nidn ?? '-' }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Program Studi
                </label>
                <input type="text"
                       value="{{ namaProdi(Auth::user()->prodi) }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>
        </div>

         {{-- NAMA PELATIHAN --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelatihan</label>
            <input type="text" name="nama_pelatihan"
                   class="w-full border rounded px-3 py-2 mt-1"
                   required>
        </div>

        {{-- TANGGAL PELATIHAN --}}
        <div class="mb-4">
            <x-date-field
                name="tanggal_pelatihan"
                label="Tanggal Pelatihan"
                :value="old('tanggal_pelatihan')"
                required
            />
        </div>

        {{-- WAKTU PELATIHAN --}}
        <div class="mb-4">
            <x-time-field
                name="waktu_pelatihan"
                label="Waktu Pelatihan"
                :value="old('waktu_pelatihan')"
                required
            />
        </div>

        {{-- ACTION --}}
        <div class="flex flex-wrap justify-end gap-3 pt-4">
            <a href="{{ url('/dosen/dashboard') }}"
               class="sakti-touch-target px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-900 font-medium">
                Batal
            </a>

            <x-tombol-simpan-draf :jenis-surat="$jenisSurat" />

            <button type="submit"
                    class="sakti-touch-target px-5 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white font-semibold">
                Ajukan Surat
            </button>
        </div>

    </form>

</main>
@endsection
