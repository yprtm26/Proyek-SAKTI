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

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-3 text-sm text-gray-500 font-medium">
                Data dosen yang menugaskan
            </span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        {{-- PENUGAS --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Penugas
            </label>
            <input type="text" name="nama_penugas"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jabatan Penugas
            </label>
            <input type="text" name="jabatan_penugas"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Dekan Fakultas Teknologi Informasi"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Instansi Penugas
            </label>
            <input type="text" name="instansi_penugas"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Universitas YARSI"
                   required>
        </div>

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-3 text-sm text-gray-500 font-medium">
                Data dosen yang ditugaskan
            </span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        {{-- DITUGASKAN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Dosen yang Ditugaskan
            </label>
            <input type="text" name="nama_ditugaskan"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                NIP / NIDN
            </label>
            <input type="text" name="no_nip"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jabatan Dosen
            </label>
            <input type="text" name="jabatan_ditugaskan"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Dosen Tetap"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Instansi Dosen
            </label>
            <input type="text" name="instansi_ditugaskan"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Fakultas Teknologi Informasi Universitas YARSI"
                   required>
        </div>

        <hr>

        {{-- KEGIATAN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Kegiatan Kuliah
            </label>
            <input type="text" name="nama_kegiatan"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Perkuliahan Mata Kuliah ..."
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jumlah SKS
            </label>
            <input type="number" name="sks"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: 3"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Pelaksanaan
            </label>
            <input type="date" name="tanggal"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Waktu Pelaksanaan
            </label>
            <input type="text" name="waktu"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Pk. 08.00 – selesai"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tempat Pelaksanaan
            </label>
            <input type="text" name="tempat"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Ruang / Gedung / Kampus"
                   required>
        </div>

        {{-- ACTION --}}
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold">
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
