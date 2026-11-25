@extends('layouts.app')

@section('content')
    @include('surat.partials.riwayat-pengusul', [
        'role' => 'mahasiswa',
        'dashboardUrl' => '/mahasiswa/dashboard',
        'riwayatUrl' => '/mahasiswa/surat',
        'baseSuratUrl' => '/mahasiswa/surat',
        'suratList' => $suratList,
        'totalSemuaSurat' => $totalSemuaSurat ?? $suratList->count(),
    ])
@endsection
