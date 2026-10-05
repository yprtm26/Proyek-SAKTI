@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-6">

    <h2 class="text-2xl font-semibold mb-4">
        Manajemen Akun Sistem
    </h2>

    {{-- tombol tambah --}}
    <a href="/administrator/tambah-akun"
       class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">
        + Tambah Akun
    </a>

    {{-- notifikasi --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================= ADMIN ================= --}}
    <h3 class="text-xl font-semibold mt-6 mb-2">Akun Admin</h3>
    @include('administrator.partials.tabel-akun', [
        'users' => $users->where('role', 'admin')
    ])

    {{-- ================= KAPRODI ================= --}}
    <h3 class="text-xl font-semibold mt-6 mb-2">Akun Kaprodi</h3>
    @include('administrator.partials.tabel-akun', [
        'users' => $users->where('role', 'kaprodi')
    ])

    {{-- ================= DEKAN ================= --}}
    <h3 class="text-xl font-semibold mt-6 mb-2">Akun Dekan</h3>
    @include('administrator.partials.tabel-akun', [
        'users' => $users->where('role', 'dekan')
    ])

</div>
@endsection
