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

        {{-- NAMA MATA KULIAH --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Mata Kuliah
            </label>
            <input type="text"
                   name="nama_matkul"
                   value="{{ old('nama_matkul') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Contoh: Pemrograman Berorientasi Objek"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Program Studi
            </label>
            <input type="text"
                   name="prodi"
                   value="{{ old('prodi') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">
                        Semester Aktif Matkul
                    </label>
                    <select name="semester"
                            required
                            class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Semester --</option>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">
                        Tahun Akademik
                    </label>
                    <input type="text"
                           name="tahun_akademik"
                           placeholder="Contoh: 2024/2025"
                           required
                           class="w-full border rounded px-3 py-2">
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
