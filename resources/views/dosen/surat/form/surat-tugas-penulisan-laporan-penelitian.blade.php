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

        {{-- INFORMASI PENELITIAN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Informasi Penelitian
            </label>

            <textarea name="informasi_penelitian"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"
                      placeholder= "Nama Penelitian"
                      required>{{ old('informasi_penelitian') }}</textarea>
        </div>

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

         {{-- TUJUAN PENULISAN & PUBLIKASI --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tujuan Penulisan dan Publikasi Artikel Ilmiah
            </label>

            <textarea name="tujuan_penulisan"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"
                      placeholder= "Contoh: Di Unit Pelaksana Teknis Perpustakaan Universitas YARSI."
                      required>{{ old('tujuan_penulisan') }}</textarea>
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
