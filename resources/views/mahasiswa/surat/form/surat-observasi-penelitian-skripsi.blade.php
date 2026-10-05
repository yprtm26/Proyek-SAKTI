@extends('layouts.app')

@section('content')


<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        {{ $jenisSurat->nama }}
    </h2>
    <p class="text-sm text-gray-500">
        Isi data berikut dengan benar
    </p>
</div>
<div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded shadow">

    <h2 class="text-xl font-semibold mb-6">
                Form Pengajuan Surat
            </h2>

    <form method="POST" action="{{ url('/mahasiswa/surat/ajukan/'.$jenisSurat->id) }}">
        @csrf

        {{-- PERIHAL --}}
        <div class="mb-4">
            <label class="block text-sm font-medium">Perihal</label>
            <input type="text" name="perihal"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Permohonan Izin Observasi dan Wawancara"
                   required>
        </div>

        {{-- KEPADA --}}
        <div class="mb-4">
            <label class="block text-sm font-medium">Kepada Yth.</label>
            <input type="text" name="kepada"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Corporate Secretary"
                   required>
        </div>

        {{-- INSTANSI --}}
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Instansi</label>
            <input type="text" name="instansi"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: PT TASPEN (Persero)"
                   required>
        </div>

        {{-- KOTA --}}
        <div class="mb-4">
            <label class="block text-sm font-medium">Kota Instansi</label>
            <input type="text" name="kota_instansi"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Jakarta"
                   required>
        </div>

        {{-- JENIS KEGIATAN --}}
        <div class="mb-4">
            <label class="block text-sm font-medium">Jenis Kegiatan</label>
            <input type="text" name="jenis_kegiatan"
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Observasi dan Wawancara"
                   required>
        </div>

        {{-- JUDUL SKRIPSI --}}
        <div class="mb-6">
            <label class="block text-sm font-medium">Judul Skripsi</label>
            <textarea name="judul_skripsi"
                      class="w-full border rounded px-3 py-2 mt-1"
                      rows="3"
                      placeholder="Contoh: Efektivitas KMS Terhadap Produktivitas Karyawan PT. Taspen (Persero)"
                      required></textarea>
        </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <a href="/mahasiswa/dashboard" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg">Kembali</a>
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition">Ajukan Surat</button>
                </div>

    </form>

</div>
@endsection
