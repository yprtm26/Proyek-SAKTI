@extends('layouts.app')

@section('content')

{{-- ================= HEADER ================= --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        {{ $jenisSurat->nama }}
    </h2>
    <p class="text-sm text-gray-500">
        Isi data berikut dengan benar
    </p>
</div>

@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}"
      class="space-y-6">
    @csrf

    {{-- ================= FORM UTAMA ================= --}}
    <div class="bg-white rounded shadow p-6 space-y-4">

        {{-- DATA OTOMATIS PENGUSUL --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-2 border-b">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Mahasiswa</label>
                <input type="text" value="{{ auth()->user()->name }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">NPM</label>
                <input type="text" value="{{ auth()->user()->npm }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Program Studi</label>
                <input type="text" value="{{ namaProdi(auth()->user()->prodi) }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100">
            </div>
        </div>

        {{-- PERIHAL --}}
        <div>
            <label class="block text-sm text-gray-600">Perihal</label>
            <input type="text" name="perihal" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Permohonan Izin Observasi Layanan Rujukan Perpustakaan">
        </div>

        {{-- KEPADA --}}
        <div>
            <label class="block text-sm text-gray-600">Kepada Yth.</label>
            <input type="text" name="kepada_yth" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Kepala Perpustakaan">
        </div>

        {{-- INSTANSI --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600">Nama Instansi</label>
                <input type="text" name="nama_instansi" required
                       class="w-full border rounded px-3 py-2 mt-1"
                       placeholder="Contoh: Perpustakaan Nasional Republik Indonesia">
            </div>

            <div>
                <label class="block text-sm text-gray-600">Kota Instansi</label>
                <input type="text" name="kota_instansi" required
                       class="w-full border rounded px-3 py-2 mt-1"
                       placeholder="Contoh: Jakarta">
            </div>
        </div>

        {{-- MATA KULIAH --}}
        <div>
            <label class="block text-sm text-gray-600">Mata Kuliah</label>
            <input type="text" name="mata_kuliah" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Jasa Rujukan Informasi">
        </div>

        {{-- JENIS KEGIATAN --}}
        <div>
            <label class="block text-sm text-gray-600">Jenis Kegiatan</label>
            <input type="text" name="jenis_kegiatan" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Observasi Layanan Rujukan di Perpustakaan">
        </div>
    </div>

    {{-- ================= ANGGOTA TIM (DINAMIS) ================= --}}
    @include('surat.partials.tabel-personel', ['defaultPeran' => 'Pemohon', 'identityRequired' => true])

    {{-- ================= BUTTON ================= --}}
    <div class="flex justify-end gap-3">
        <a href="/mahasiswa/dashboard"
           class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
            Batal
        </a>
        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
            Ajukan Surat
        </button>
    </div>
</form>

@endsection
