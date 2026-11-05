@extends('layouts.app')

@section('content')
<main class="max-w-5xl mx-auto">

    {{-- JUDUL + TOMBOL TAMBAH --}}
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">
                Kelola Template Surat
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Daftar template surat yang tersedia untuk pengajuan mahasiswa dan dosen.
            </p>
        </div>

        <a href="{{ route('admin.template-surat.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Template
        </a>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- TABEL --}}
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Nama Template</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-center">Target</th>
                    <th class="px-4 py-3 text-center">Alur</th>
                    <th class="px-4 py-3 text-center">Field</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center w-40">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
            @forelse($jenisSuratList as $jenisSurat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $jenisSurat->nama }}</div>
                        <div class="text-xs text-gray-400">{{ $jenisSurat->slug }}</div>
                    </td>

                    <td class="px-4 py-3 text-gray-600">
                        {{ $jenisSurat->kode ?: '-' }}
                    </td>

                    <td class="px-4 py-3 text-center capitalize text-gray-600">
                        {{ $jenisSurat->target_user ?: 'mahasiswa' }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-wrap justify-center gap-1">
                            @foreach(($jenisSurat->alur ?? []) as $tahap)
                                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium capitalize">
                                    {{ $tahap }}
                                </span>
                            @endforeach
                        </div>
                    </td>

                    <td class="px-4 py-3 text-center text-gray-600">
                        {{ is_array($jenisSurat->fields) ? count($jenisSurat->fields) : 0 }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        @if($jenisSurat->aktif)
                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                Aktif
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                Nonaktif
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.template-surat.edit', $jenisSurat) }}"
                               class="px-3 py-1.5 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 text-sm font-medium">
                                Edit
                            </a>

                            <form action="{{ route('admin.template-surat.destroy', $jenisSurat) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                        Belum ada template surat.
                        <a href="{{ route('admin.template-surat.create') }}" class="text-blue-600 hover:underline">
                            Buat template pertama
                        </a>.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ url('/admin/dashboard') }}"
           class="text-sm text-gray-600 hover:text-gray-800">
            &larr; Kembali ke Dashboard
        </a>
    </div>

</main>
@endsection
