@extends('layouts.app')

@section('content')
@php
    $dosenLama = old('dosen_id')
        ? \App\Models\User::query()->where('role', 'dosen')->find(old('dosen_id'))
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

        {{-- B-13 A: template wajib dikirim agar simpan draf bisa divalidasi --}}
        <input type="hidden" name="jenis_surat_id" value="{{ $jenisSurat->id }}">

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-3 text-sm text-gray-500 font-medium">
                Data dosen yang menugaskan
            </span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        {{-- PENUGAS --}}
        <div>
            <x-combobox
                name="dosen_id"
                id="dosen-penugas"
                label="Nama Penugas"
                endpoint="{{ url('/api/master/dosen') }}"
                :value="old('dosen_id')"
                :value-label="$dosenLama?->name"
                placeholder="-- Pilih Dosen Penugas --"
                search-placeholder="Cari nama atau NIDN dosen..."
                hint="Tab untuk membuka, Enter untuk memilih, Escape untuk menutup."
                required
            />
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
                   value="{{ old('nama_ditugaskan', Auth::user()->name ?? '') }}"
                   readonly
                   class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    NIP / NIDN
                </label>
                <input type="text" name="no_nip"
                       value="{{ old('no_nip', Auth::user()->nidn ?? '') }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed"
                       required>
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
            <x-date-field
                name="tanggal"
                label="Tanggal Pelaksanaan"
                :value="old('tanggal')"
                required
            />
        </div>

        <div>
            <x-time-field
                name="waktu"
                label="Waktu Pelaksanaan"
                :value="old('waktu')"
                required
            />
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
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t">
            <a href="{{ url('/dosen/dashboard') }}"
               class="min-h-[44px] px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium inline-flex items-center justify-center transition-colors">
                Batal
            </a>

            {{-- B-13 A: TOMBOL SIMPAN DRAF --}}
            <button type="submit"
                    name="action"
                    value="draft"
                    formaction="{{ url('/surat/draf') }}"
                    formnovalidate
                    class="btn-simpan-draf min-h-[44px] px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Simpan Draf</span>
            </button>

            <button type="submit"
                    name="action"
                    value="submit"
                    class="min-h-[44px] px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 inline-flex items-center gap-2">
                <span>Ajukan Surat</span>
            </button>
        </div>

    </form>

</main>
@endsection
