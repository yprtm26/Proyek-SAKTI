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

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST"
          action="{{ url('/dosen/surat/ajukan/'.$jenisSurat->id) }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- IDENTITAS DOSEN (TERKUNCI) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Dosen (Pengusul)
                </label>
                <input type="text"
                       value="{{ Auth::user()->name }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
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

        {{-- ================= ANGGOTA (DINAMIS) ================= --}}
        @include('surat.partials.tabel-personel', ['defaultPeran' => 'Penulis ke-1'])

        {{-- ================= JUDUL PENELITIAN ================= --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Judul Penelitian
            </label>
            <input type="text"
                   name="judul"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        {{-- ================= NAMA JURNAL ================= --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Jurnal / Prosiding
            </label>
            <input type="text"
                   name="nama_jurnal"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        {{-- ================= LINK JURNAL ================= --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Link Jurnal (URL)
            </label>
            <input type="url"
                   name="link_jurnal"
                   placeholder="https://"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        {{-- ================= ACTION ================= --}}
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
