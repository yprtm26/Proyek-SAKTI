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
    <form method="POST"
          action="{{ url('/dosen/surat/ajukan/'.$jenisSurat->id) }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- NAMA DOSEN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Dosen
            </label>
            <input type="text"
                   name="nama_dosen"
                   value="{{ old('nama_dosen', Auth::user()->name ?? '') }}"
                   readonly
                   class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                   required>
        </div>

        {{-- INFORMASI KEGIATAN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Informasi Kegiatan
            </label>
            <textarea name="informasi"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"
                      placeholder="Contoh: Seminar Nasional Teknologi Informasi yang diselenggarakan oleh ..."
                      required>{{ old('informasi') }}</textarea>
        </div>

        {{-- KEPERLUAN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Keperluan / Peran
            </label>
            <textarea name="keperluan"
                      rows="2"
                      class="w-full border rounded-lg px-3 py-2"
                      placeholder="Contoh: Sebagai narasumber pada kegiatan tersebut"
                      required>{{ old('keperluan') }}</textarea>
        </div>

        {{-- TANGGAL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Kegiatan
            </label>
            <input type="text"
                   name="tanggal"
                   value="{{ old('tanggal') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: 28 Oktober 2025"
                   required>
        </div>

        {{-- WAKTU --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Waktu Kegiatan
            </label>
            <input type="text"
                   name="waktu"
                   value="{{ old('waktu') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Pk. 08.00 WIB - selesai"
                   required>
        </div>

        {{-- TEMPAT --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tempat Kegiatan
            </label>
            <input type="text"
                   name="tempat"
                   value="{{ old('tempat') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Auditorium Universitas YARSI Jakarta"
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
