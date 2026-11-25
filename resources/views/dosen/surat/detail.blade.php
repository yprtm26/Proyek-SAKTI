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
            <p class="text-gray-600">Status</p>
            @php $badge = statusBadgeMeta((string) $surat->status); @endphp
            <span class="inline-block mt-1 {{ $badge['class'] }}"
                  style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }};">
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
            <p class="text-gray-600">Alasan Penolakan</p>
            <p class="text-red-700 font-medium">
                {{ $surat->alasan_tolak }}
            </p>
        </div>
        @endif

        @if($surat->catatan_revisi)
        <div class="md:col-span-2">
            <p class="text-gray-600">Catatan Revisi dari Admin TU</p>
            <p class="text-amber-900 bg-amber-50 border border-amber-300 rounded px-3 py-2 font-medium mt-1">
                {{ $surat->catatan_revisi }}
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

{{-- ================= DAFTAR ANGGOTA KEGIATAN ================= --}}
@include('surat.partials.detail-personel', ['surat' => $surat])

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
            <button type="submit" class="sakti-touch-target bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Batalkan Pengajuan
            </button>
        </form>
    @endif

    {{-- B-13 B & B-14: Tombol edit & hapus jika draf --}}
    @if($surat->status === 'draf')
        <a href="/dosen/surat/{{ $surat->id }}/edit-draf"
           class="sakti-touch-target bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            Edit Draf
        </a>
        <button type="button"
                data-delete-draft-trigger
                data-delete-url="/surat/{{ $surat->id }}/draf"
                data-surat-name="{{ $surat->jenisSurat->nama }}"
                class="sakti-touch-target bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            Hapus Draf
        </button>
    @endif

    @if($surat->status === 'disetujui_final')
        <a href="/dosen/surat/{{ $surat->id }}/pdf"
           class="sakti-touch-target bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            Download PDF
        </a>
    @endif

    <a href="/dosen/surat"
       class="sakti-touch-target bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold">
        Kembali
    </a>
</div>

<x-modal-hapus-draf />

@endsection
