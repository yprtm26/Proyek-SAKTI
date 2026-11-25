@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Pratinjau Dokumen Surat
            </h2>
            <p class="text-sm text-gray-700 mt-1">
                {{ $surat->jenisSurat?->nama ?? 'Dokumen Surat' }}
                @if($surat->nomor_surat)
                    ({{ $surat->nomor_surat }})
                @endif
            </p>
        </div>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : auth()->user()->dashboardUrl() }}"
           class="sakti-touch-target px-4 py-2 bg-white border border-gray-400 text-gray-900 hover:bg-gray-100 rounded-lg text-sm font-semibold">
            Kembali
        </a>
    </div>

    <div class="sakti-preview-viewport rounded-xl border border-gray-300">
        @include('surat.partials.pratinjau-content', ['surat' => $surat])
    </div>
</div>
@endsection
