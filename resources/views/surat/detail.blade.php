@extends('layouts.app')

@section('content')

@php
    $user = auth()->user();
@endphp

{{-- ================= HEADER ================= --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-700">
            Detail Surat
        </h2>
        <p class="text-sm text-gray-500">
            {{ $surat->jenisSurat->nama }}
        </p>
    </div>

    {{-- PENTING: tombol kembali SELALU ADA (read-only / aksi) --}}
    <a href="{{ url()->previous() }}"
       class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">
        Kembali
    </a>
</div>

{{-- ================= INFO SURAT ================= --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Informasi Surat
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        <div>
            <p class="text-gray-500">Status</p>
            <span class="inline-block mt-1 px-3 py-1 text-xs rounded
                {{ $surat->status === 'disetujui_final' ? 'bg-green-100 text-green-700' :
                   ($surat->status === 'ditolak' ? 'bg-red-100 text-red-700' :
                    'bg-orange-100 text-orange-700') }}">
                {{ statusMahasiswa($surat->status) }}
            </span>
        </div>

        @if($surat->nomor_surat)
        <div>
            <p class="text-gray-500">Nomor Surat</p>
            <p class="font-medium">{{ $surat->nomor_surat }}</p>
        </div>
        @endif

        <div>
            <p class="text-gray-500">Tanggal Pengajuan</p>
            <p class="font-medium">
                {{ $surat->created_at->translatedFormat('d M Y H:i') }}
            </p>
        </div>

        {{-- hanya muncul jika DISETUJUI --}}
        @if($surat->status === 'disetujui_final')
        <div>
            <p class="text-gray-500">Tanggal Persetujuan</p>
            <p class="font-medium">
                {{ optional($surat->tanggal_disetujui_final)->translatedFormat('d M Y H:i') }}
            </p>
        </div>
        @endif

        {{-- hanya muncul jika DITOLAK --}}
        @if($surat->status === 'ditolak' && $surat->alasan_tolak)
        <div class="md:col-span-2">
            <p class="text-gray-500">Alasan Penolakan</p>
            <p class="text-red-600 font-medium">
                {{ $surat->alasan_tolak }}
            </p>
        </div>
        @endif
    </div>
</div>

{{-- ================= INFO ================= --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Informasi Pengaju 
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Nama</p>
            <p class="font-medium">{{ $surat->user->name }}</p>
        </div>

        <div>
            @if($surat->user->role === 'mahasiswa')
                <p class="text-gray-500">NPM</p>
                <p class="font-medium">{{ $surat->user->npm }}</p>
            @elseif($surat->user->role === 'dosen')
                <p class="text-gray-500">NIK</p>
                <p class="font-medium">{{ $surat->user->nidn ?? '-' }}</p>
            @endif
        </div>

        <div>
            @if($surat->user->role === 'mahasiswa')
            <p class="text-gray-500">Program Studi</p>
            <p class="font-medium">
                {{ namaProdi($surat->user->prodi) }}
            </p>
            @endif
        </div>
    </div>
</div>

{{-- ================= LAMPIRAN ================= --}}
@if($surat->lampiran->count())
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Lampiran Dokumen
    </h3>

    <ul class="space-y-2 text-sm">
        @foreach($surat->lampiran as $file)
            <li class="flex items-center justify-between">
                <span>{{ $file->nama_file_asli }}</span>

                <a href="{{ asset('storage/'.$file->path_file) }}"
                   target="_blank"
                   class="text-blue-600 hover:underline">
                    Lihat
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endif

{{-- ================= PREVIEW ================= --}}
<div class="-mx-6 bg-gray-100 py-10">
    <h3 class="font-semibold text-gray-700 mb-4 px-6">
        Preview Surat
    </h3>

    {{-- SURAT MAGANG --}}
    @if($surat->jenisSurat->slug === 'surat-permohonan-magang')
        @include(
            'preview.magang.' . $surat->data['tipe_magang'],
            ['surat' => $surat]
        )

    {{-- SURAT DOSEN --}}
    @elseif(
        $surat->user->role === 'dosen' &&
        view()->exists('preview.dosen.' . $surat->jenisSurat->slug)
    )
        @include('preview.dosen.' . $surat->jenisSurat->slug, ['surat' => $surat])    

    {{-- DEFAULT SURAT LAIN --}}
    @elseif(view()->exists('preview.' . $surat->jenisSurat->slug))
        @include('preview.' . $surat->jenisSurat->slug)

    @else
        <p class="text-gray-500 italic text-sm px-6">
            Preview surat belum tersedia
        </p>
    @endif
</div>

{{-- ================= AKSI ================= --}}
{{-- PENTING: hanya role + status yang VALID --}}
@if(
    ($user->role === 'admin'   && $surat->status === 'menunggu') ||
    ($user->role === 'kaprodi' && $surat->status === 'menunggu_kaprodi') ||
    ($user->role === 'dekan'   && $surat->status === 'menunggu_dekan')
)
<div class="flex justify-end gap-3">

    <button onclick="openRejectModal()"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
        Tolak
    </button>

    <button onclick="openApproveModal()"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        Setujui
    </button>
</div>
@endif

{{-- ================= FINALISASI ADMIN (DOSEN) ================= --}}
@if(
    $user->role === 'admin' &&
    $surat->status === 'menunggu_admin_final' &&
    $surat->user->role === 'dosen'
)
<div class="flex justify-end gap-3 mt-4">

    {{-- tombol edit nomor surat (optional, jika mau pakai modal nanti) --}}
    <button onclick="openFinalisasiModal()"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
        Finalisasi Surat
    </button>

</div>
@endif


{{-- ================= DOWNLOAD PDF (ADMIN SAJA) ================= --}}
@if(
    $surat->status === 'disetujui_final' &&
    auth()->user()->role === 'admin'
)

    {{-- SURAT MAHASISWA --}}
    @if($surat->user->role === 'mahasiswa')
        <a href="/mahasiswa/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

    {{-- SURAT DOSEN --}}
    @if($surat->user->role === 'dosen')
        <a href="/dosen/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

@endif

{{-- ================= DOWNLOAD PDF Admin, Dekan, Kaprodi =================
@if($surat->status === 'disetujui_final')

    @if($surat->user->role === 'mahasiswa')
        <a href="/mahasiswa/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

    @if($surat->user->role === 'dosen')
        <a href="/dosen/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

@endif --}}

{{-- ================= MODAL SETUJUI ================= --}}
<div id="approveModal"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            Konfirmasi Persetujuan
        </h3>

        <p class="text-sm text-gray-600 mb-4">
            Apakah Anda yakin ingin menyetujui surat ini?
        </p>

        <form method="POST"
              action="/{{ $user->role }}/surat/{{ $surat->id }}/setujui">
            @csrf
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="closeApproveModal()"
                        class="px-4 py-2 bg-gray-200 rounded">
                    Batal
                </button>
                <button class="px-4 py-2 bg-green-600 text-white rounded">
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL TOLAK ================= --}}
<div id="rejectModal"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            Konfirmasi Penolakan
        </h3>

        <form method="POST"
              action="/{{ $user->role }}/surat/{{ $surat->id }}/tolak">
            @csrf

            {{-- ADMIN SAJA YANG ADA ALASAN --}}
            @if($user->role === 'admin')
                <label class="text-sm text-gray-600">
                    Alasan Penolakan (opsional)
                </label>
                <textarea name="alasan_tolak"
                    class="w-full border rounded px-3 py-2 mt-2"
                    rows="3"></textarea>
            @endif

            <div class="flex justify-end gap-3 mt-4">
                <button type="button"
                        onclick="closeRejectModal()"
                        class="px-4 py-2 bg-gray-200 rounded">
                    Batal
                </button>
                <button class="px-4 py-2 bg-red-600 text-white rounded">
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ================= MODAL FINALISASI ================= --}}
<div id="finalisasiModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-6 relative">

        {{-- Tombol tutup --}}
        <button onclick="closeFinalisasiModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>

        <h3 class="text-lg font-semibold mb-4">Finalisasi Surat</h3>

        {{-- ALERT ERROR --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="/admin/surat/{{ $surat->id }}/finalisasi" method="POST">
            @csrf

            {{-- Tanggal Tanda Tangan --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Persetujuan Tanda Tangan
                </label>
                <input type="date"
                       name="tanggal_ttd"
                       value="{{ old('tanggal_ttd', now()->translatedFormat('Y-m-d')) }}"
                       class="w-full border rounded px-3 py-2"
                       required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
                <input type="text"
                name="nomor_surat"
                value="{{ $surat->nomor_surat ?? generateNomorSurat($surat) }}"
                class="w-full border rounded px-3 py-2 mt-2 mb-4">
                </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeFinalisasiModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">
                    Batal
                </button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Finalisasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApproveModal() {
        document.getElementById('approveModal').classList.remove('hidden');
        document.getElementById('approveModal').classList.add('flex');
    }
    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

        function openFinalisasiModal() {
        document.getElementById('finalisasiModal').classList.remove('hidden');
        document.getElementById('finalisasiModal').classList.add('flex');
    }

    function closeFinalisasiModal() {
        document.getElementById('finalisasiModal').classList.add('hidden');
    }
</script>

@endsection
