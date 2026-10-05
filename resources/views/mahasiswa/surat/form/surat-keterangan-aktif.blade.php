@extends('layouts.app')

@section('content')

    {{-- FORM --}}
    <main class="max-w-4xl mx-auto p-6">

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

            {{-- FORM START --}}
            <form method="POST" action="{{ url('/mahasiswa/surat/ajukan/'.$jenisSurat->id) }}">
                @csrf

                {{-- DATA OTOMATIS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Nama Mahasiswa
                        </label>
                        <input type="text"
                               value="{{ auth()->user()->name }}"
                               disabled
                               class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            NPM
                        </label>
                        <input type="text"
                               value="{{ auth()->user()->npm }}"
                               disabled
                               class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>

                </div>

                {{-- INPUT DINAMIS --}}
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">
                        Semester Aktif
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
                           placeholder="Contoh: 2025/2026"
                           required
                           class="w-full border rounded px-3 py-2">
                </div>

                {{-- SUBMIT --}}

                <div class="flex justify-end gap-3">
                <a href="/mahasiswa/dashboard" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg">Kembali</a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-600 transition">Ajukan Surat</button>
                </div>

            </form>
            {{-- FORM END --}}

        </div>

    </main>
@endsection
