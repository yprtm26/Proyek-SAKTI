@extends('layouts.app')

@section('content')
    @include('surat.partials.riwayat-pengusul', [
        'role' => 'dosen',
        'dashboardUrl' => '/dosen/dashboard',
        'riwayatUrl' => '/dosen/surat',
        'baseSuratUrl' => '/dosen/surat',
        'suratList' => $suratList,
        'totalSemuaSurat' => $totalSemuaSurat ?? $suratList->count(),
    ])
@endsection
