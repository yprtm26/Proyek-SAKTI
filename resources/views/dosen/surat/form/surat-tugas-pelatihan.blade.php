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

        {{-- NAMA DOSEN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Dosen
            </label>
            <input type="text"
                   name="nama_dosen"
                   value="{{ old('nama_dosen', Auth::user()->name ?? '') }}" readonly
                   class="w-full border rounded-lg px-3 py-2 focus:ring focus:border-green-400"
                   required>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelatihan</label>
            <input type="text" name="tanggal_pelatihan"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: 12–13 Maret 2026"
                   required>
        </div>

        {{-- WAKTU PELATIHAN --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pelatihan</label>
            <input type="text" name="waktu_pelatihan"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: 08.00 s/d 16.00 WIB"
                   required>
        </div>

        {{-- ACTION --}}
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold">
                Ajukan Surat
            </button>

            <a href="{{ url('/dosen/dashboard') }}"
               class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                Batal
            </a>

        </div>

    </form>

</main>
@endsection
