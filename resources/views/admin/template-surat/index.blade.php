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

    {{-- TAB NAVIGASI --}}
    <div class="flex border-b border-gray-200 mb-0">
        <button type="button"
                class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 transition-colors"
                data-tab="mahasiswa"
                id="tab-mahasiswa">
            Mahasiswa
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $templateMahasiswa->count() }}</span>
        </button>
        <button type="button"
                class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 transition-colors"
                data-tab="dosen"
                id="tab-dosen">
            Dosen
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">{{ $templateDosen->count() }}</span>
        </button>
    </div>

    {{-- PANEL MAHASISWA --}}
    <div class="tab-panel" id="panel-mahasiswa">
        @include('admin.template-surat._tabel', [
            'daftar'    => $templateMahasiswa,
            'kategori'  => 'mahasiswa',
        ])
    </div>

    {{-- PANEL DOSEN --}}
    <div class="tab-panel" id="panel-dosen" style="display:none;">
        @include('admin.template-surat._tabel', [
            'daftar'    => $templateDosen,
            'kategori'  => 'dosen',
        ])
    </div>

    <div class="mt-4">
        <a href="{{ url('/admin/dashboard') }}"
           class="text-sm text-gray-600 hover:text-gray-800">
            &larr; Kembali ke Dashboard
        </a>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');

        function aktivkan(tabId) {
            tabs.forEach(function (btn) {
                const aktif = btn.dataset.tab === tabId;
                btn.classList.toggle('border-blue-600', aktif);
                btn.classList.toggle('text-blue-600', aktif);
                btn.classList.toggle('border-transparent', !aktif);
                btn.classList.toggle('text-gray-500', !aktif);
            });
            panels.forEach(function (panel) {
                panel.style.display = panel.id === 'panel-' + tabId ? '' : 'none';
            });
        }

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                aktivkan(btn.dataset.tab);
            });
        });

        // Default tab: mahasiswa
        aktivkan('mahasiswa');
    });
</script>
@endsection
