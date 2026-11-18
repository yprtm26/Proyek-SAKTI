@extends('layouts.app')

@section('content')
@php
    $prodiAkun = \App\Models\Prodi::query()->where('kode', Auth::user()->prodi)->first();
    $prodiLama = old('prodi_id', $prodiAkun?->id);
    $labelProdiLama = old('prodi_id')
        ? \App\Models\Prodi::query()->find(old('prodi_id'))?->nama
        : $prodiAkun?->nama;
    $semesterLama = old('semester')
        ? \App\Models\Semester::query()->where('jenis', 'paruh')->find(old('semester'))
        : null;
@endphp
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
            <x-combobox
                name="prodi_id"
                id="prodi-pengembangan-modul"
                label="Program Studi"
                endpoint="{{ url('/api/master/prodi') }}"
                :value="$prodiLama"
                :value-label="$labelProdiLama"
                placeholder="-- Pilih Program Studi --"
                search-placeholder="Cari program studi..."
                hint="Tab untuk membuka, Enter untuk memilih, Escape untuk menutup."
                required
            />
        </div>

        <div class="mb-4">
            <x-combobox
                name="semester"
                id="semester-pengembangan-modul"
                label="Semester Aktif Matkul"
                endpoint="{{ url('/api/master/semester') }}"
                :params="['jenis' => 'paruh']"
                :value="old('semester')"
                :value-label="$semesterLama?->nama"
                placeholder="-- Pilih Semester --"
                search-placeholder="Cari semester..."
                required
            />
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
