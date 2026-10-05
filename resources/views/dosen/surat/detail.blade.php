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
                {{ statusDosen($surat->status) }}
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

{{-- ================= INFO DOSEN ================= --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">
        Informasi Dosen
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Nama</p>
            <p class="font-medium">{{ auth()->user()->name }}</p>
        </div>

        <div>
            <p class="text-gray-500">NIK</p>
            <p class="font-medium">{{ auth()->user()->nidn }}</p>
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

{{-- ================= PREVIEW SURAT ================= --}}
<div class="-mx-6 bg-gray-100 py-10">
    <h3 class="font-semibold text-gray-700 mb-4 px-6">
        Preview Surat
    </h3>
    
    @if(view()->exists('preview.dosen.' . $surat->jenisSurat->slug))
        @include('preview.dosen.' . $surat->jenisSurat->slug)
    @else
        <p class="text-gray-500 italic text-sm">
            Preview surat belum tersedia
        </p>
    @endif
</div>


{{-- ================= AKSI DOSEN ================= --}}
<div class="flex flex-wrap gap-3">

    @if($surat->status === 'menunggu')
        <form method="POST" action="/dosen/surat/{{ $surat->id }}/batal">
            @csrf
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Batalkan Pengajuan
            </button>
        </form>
    @endif

    @if($surat->status === 'disetujui_final')
        <a href="/dosen/surat/{{ $surat->id }}/pdf"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    @endif

    <a href="/dosen/surat"
       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
        Kembali
    </a>
</div>

@endsection
