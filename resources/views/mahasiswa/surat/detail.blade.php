@extends('layouts.app')

@section('content')

{{-- ================= HEADER ================= --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        Detail Surat
    </h2>
    <p class="text-sm text-gray-500">
        {{ $surat->jenisSurat->nama }}
    </p>
</div>

{{-- ================= INFO SURAT ================= --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Informasi Surat
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Status</p>
            <span class="inline-block mt-1 px-2 py-1 text-xs rounded
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
                {{ $surat->created_at->format('d M Y H:i') }}
            </p>
        </div>

        @if($surat->status === 'disetujui_final' && $surat->tanggal_disetujui_final)
        <div>
            <p class="text-gray-500">Tanggal Persetujuan</p>
            <p class="font-medium">
                {{ $surat->tanggal_disetujui_final->format('d M Y H:i') }}
            </p>
        </div>
        @endif

        @if($surat->alasan_tolak)
        <div class="md:col-span-2">
            <p class="text-gray-500">Alasan Penolakan</p>
            <p class="text-red-600 font-medium">
                {{ $surat->alasan_tolak }}
            </p>
        </div>
        @endif
    </div>
</div>

{{-- ================= INFO MAHASISWA ================= --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Informasi Mahasiswa
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Nama</p>
            <p class="font-medium">{{ auth()->user()->name }}</p>
        </div>

        <div>
            <p class="text-gray-500">NPM</p>
            <p class="font-medium">{{ auth()->user()->npm }}</p>
        </div>

        <div>
            <p class="text-gray-500">Program Studi</p>
            <p class="font-medium">{{ namaProdi(auth()->user()->prodi) }}</p>
        </div>

        {{-- ================= no hp =================
        <div>
            <p class="text-gray-500">No. HP</p>
            <p class="font-medium">
                {{ auth()->user()->telephone_number ?? '-' }}
            </p>
        </div>--}}

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

{{-- Untuk Permohonan ke Persetujuan --}}
@if($surat->children->count())
<div class="bg-green-50 border border-green-200 rounded p-4 mt-6">
    <h4 class="font-semibold text-green-700 mb-2">
        Surat Terkait
    </h4>

    @foreach($surat->children as $child)
        <a href="/mahasiswa/surat/{{ $child->id }}"
           class="text-green-700 underline text-sm block">
            {{ $child->jenisSurat->nama }}
        </a>
    @endforeach
</div>
@endif


{{-- ================= PREVIEW SURAT ================= --}}
<div class="-mx-6 bg-gray-100 py-10">
    <h3 class="font-semibold text-gray-700 mb-4 px-6">
        Preview Surat
    </h3>
    
    @if($surat->jenisSurat->slug === 'surat-permohonan-magang')
        @include(
            'preview.magang.' . $surat->data['tipe_magang'],
            ['surat' => $surat]
        )

    {{-- DEFAULT SURAT LAIN --}}
    @elseif(view()->exists('preview.' . $surat->jenisSurat->slug))
        @include('preview.' . $surat->jenisSurat->slug)

    @else
        <p class="text-gray-500 italic text-sm px-6">
            Preview surat belum tersedia
        </p>
    @endif
</div>

{{-- ================= AKSI MAHASISWA ================= --}}
<div class="flex flex-wrap gap-3">

    @if($surat->status === 'menunggu')
        <form method="POST" action="/mahasiswa/surat/{{ $surat->id }}/batal">
            @csrf
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Batalkan Pengajuan
            </button>
        </form>
    @endif

    @if($surat->status === 'disetujui_final')
        <a href="/mahasiswa/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

    <a href="/mahasiswa/surat"
       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
        Kembali
    </a>
</div>

@endsection
