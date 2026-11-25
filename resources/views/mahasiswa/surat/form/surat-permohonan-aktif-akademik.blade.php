@extends('layouts.app')

@section('content')
@php
    $semesterLama = old('semester')
        ? \App\Models\Semester::query()->where('jenis', 'paruh')->find(old('semester'))
        : null;
@endphp

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
            
            <form method="POST"
                action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}"
                enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

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

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Program Studi
                        </label>
                        <input type="text"
                               value="{{ namaProdi(auth()->user()->prodi) }}"
                               disabled
                               class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>

                </div>

                <div class="mb-4">
                    <x-combobox
                        name="semester"
                        id="semester-aktif-akademik"
                        label="Pilih Semester Aktif"
                        endpoint="{{ url('/api/master/semester') }}"
                        :params="['jenis' => 'paruh']"
                        :value="old('semester')"
                        :value-label="$semesterLama?->nama"
                        placeholder="-- Pilih Semester --"
                        search-placeholder="Cari semester..."
                        hint="Tab untuk membuka, Enter untuk memilih, Escape untuk menutup."
                        required
                    />
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

                {{--  LAMPIRAN INFORMASI STATUS KEUANGAN --}}
                <section>
                    <h3 class="font-semibold text-gray-700 mb-3">
                        Informasi Status Keuangan
                    </h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Maksimal Tanggal Bayar Tunggakan
                    </p>

                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <input type="date"
                            name="batas_pembayaran"
                            required
                            class="border rounded-lg px-4 py-2">

                        <input type="number"
                            name="nominal_pembayaran"
                            required
                            placeholder="Nominal (Rp) | Contoh: 2000000"
                            class="border rounded-lg px-4 py-2">
                    </div>

                    <p class="text-sm text-gray-500 mb-3">
                        Upload bukti status keuangan mahasiswa dari SISAKAD (PDF / JPG / PNG)
                    </p>

                    <input type="file"
                        name="lampiran"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                        class="block w-full border rounded-lg px-3 py-2">

                    {{-- INPUT HIDDEN --}}
                    <input type="hidden" name="label_lampiran" value="Status Keuangan">

                    <p class="text-xs text-gray-400 mt-2">
                        Maksimal 2 MB
                    </p>
                </section>
                

                <div class="flex flex-wrap justify-end gap-3">
                <a href="/mahasiswa/dashboard" class="sakti-touch-target bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-medium">Kembali</a>
                <x-tombol-simpan-draf :jenis-surat="$jenisSurat" />
                <button type="submit" class="sakti-touch-target bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded-lg font-semibold transition">Ajukan Surat</button>
                </div>

            </form>

        </div>

    </main>
@endsection
