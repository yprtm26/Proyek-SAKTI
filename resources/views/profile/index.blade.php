@extends('layouts.app')

@section('content')

@php
    $dashboardUrl = match($user->role) {
        'admin' => '/admin/dashboard',
        'dosen' => '/dosen/dashboard',
        'kaprodi' => '/kaprodi/dashboard',
        'dekan' => '/dekan/dashboard',
        default => '/mahasiswa/dashboard',
    };
@endphp

<div class="max-w-3xl mx-auto mt-6">

    <div class="bg-white rounded-lg shadow border">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-700">Informasi Profil</h2>

            <a href="{{ route('profile.edit') }}"
               class="text-gray-400 hover:text-gray-600 transition"
               title="Edit Profil">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </a>
        </div>

        {{-- Content --}}
        <div class="px-6 py-5 space-y-6 text-sm text-gray-800">

            <div>
                <p class="text-gray-500">Nama Lengkap</p>
                <p class="font-medium">{{ $user->name }}</p>
            </div>

            <div>
                <p class="text-gray-500">Program Studi</p>
                <p class="font-medium">{{ $user->prodi ?? '-' }}</p>
            </div>

            @if($user->role === 'mahasiswa')
            <div>
                <p class="text-gray-500">NPM</p>
                <p class="font-medium">{{ $user->npm }}</p>
            </div>
            @endif

            <div>
                <p class="text-gray-500">Kontak</p>
                <p class="font-medium">{{ $user->telephonenumber ?? '-' }}</p>
            </div>

            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium">{{ $user->email }}</p>
            </div>

            <div>
                <p class="text-gray-500">Email Yarsi</p>
                <p class="font-medium">{{ $user->emailyarsi ?? '-' }}</p>
            </div>


        </div>
    </div>

    <div class="flex justify-end pt-4 ">
        <a href="{{ $dashboardUrl }}"
        class="px-4 py-2 rounded-lg bg-gray-300 hover:bg-gray-400 text-gray-700">
            Kembali
        </a>
    </div>
</div>


@endsection