@extends('layouts.app')

@section('content')
@php
$user = auth()->user();
@endphp

<main class="max-w-4xl mx-auto p-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">
            Isi data berikut dengan benar
        </p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-xl font-semibold mb-6">
            Form Pengajuan Surat
        </h2>

        <form method="POST" action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}">
            @csrf

            {{-- ================= DATA MAHASISWA ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        Nama Mahasiswa
                    </label>
                    <input type="text"
                           value="{{ $user->name }}"
                           readonly
                           class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        NPM
                    </label>
                    <input type="text"
                           value="{{ $user->npm }}"
                           readonly
                           class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        Program Studi
                    </label>
                    <input type="text"
                           value="{{ $user->prodi }}"
                           readonly
                           class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        Fakultas
                    </label>
                    <input type="text"
                           value="Fakultas Teknologi Informasi"
                           readonly
                           class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>

            </div>

            {{-- ================= AKADEMIK ================= --}}
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">
                    Informasi Akademik
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            IPK
                        </label>
                        <input name="ipk"
                               required
                               placeholder="Contoh: 3.75"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Jumlah SKS
                        </label>
                        <input name="sks"
                               type="number"
                               required
                               placeholder="Contoh: 120"
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- ================= TUJUAN ================= --}}
            <div class="mb-6">
                <label class="block text-sm text-gray-600 mb-1">
                    Tujuan Rekomendasi
                </label>
                <select name="tujuan"
                        required
                        class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Tujuan --</option>
                    <option value="magang">Magang</option>
                    <option value="pekerjaan">Melamar Pekerjaan</option>
                </select>
            </div>

            {{-- ================= PENGALAMAN ================= --}}
            <div class="mb-6">
                <label class="block text-sm text-gray-600 mb-1">
                    Pengalaman
                </label>
                <textarea name="pengalaman[]"
                          rows="4"
                          placeholder="Contoh:
1. Anggota Departemen ........ (Januari 2021-Desember 2021)
2. Asisten Perkuliahan ....... (Januari 2023 -Juli 2023)"
                          class="w-full border rounded px-3 py-2"></textarea>
            </div>

            {{-- ================= PROJECT ================= --}}
            <div class="mb-6">
                <label class="block text-sm text-gray-600 mb-1">
                    Project
                </label>
                <textarea name="project[]"
                          rows="4"
                          placeholder="Contoh:
1. FrontEnd Developer ..... Proyek Akhir Mata Kuliah ........ (November 2024 - Januari 2025)"
                          class="w-full border rounded px-3 py-2"></textarea>
            </div>

            {{-- ================= ACTION ================= --}}
            <div class="flex justify-end gap-3">
                <a href="/mahasiswa/dashboard"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg">
                    Kembali
                </a>

                <button type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                    Ajukan Surat
                </button>
            </div>

        </form>
    </div>

</main>
@endsection
