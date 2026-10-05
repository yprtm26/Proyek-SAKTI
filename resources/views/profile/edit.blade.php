@extends('layouts.app')

@section('content')
<div class="px-8 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded shadow border">

        {{-- Header --}}
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-700">Edit Profil</h2>
        </div>

        {{-- Form --}}
        <form method="POST" action="/profile/update" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">
                    {{ $user->role === 'dosen' ? 'Nama Lengkap dan Gelar' : 'Nama Lengkap' }}
                </label>
                <input type="text"
                        name="name"
                        value="{{ old('name',$user->name) }}"
                        @if($user->role !== 'dosen') disabled @endif
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200 focus:outline-none">
            </div>

            {{-- Prodi --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">Program Studi</label>

                @if($user->role === 'dosen')
                    <select name="prodi"
                            class="w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200 focus:outline-none">

                        <option value="">-- Pilih Program Studi --</option>

                        <option value="TI"
                            {{ old('prodi', $user->prodi) == 'TI' ? 'selected' : '' }}>
                            Teknik Informatika
                        </option>

                        <option value="PdSI"
                            {{ old('prodi', $user->prodi) == 'PdSI' ? 'selected' : '' }}>
                            Perpustakaan dan Sains Informasi
                        </option>

                    </select>
                @else
                    <input type="text"
                        value="{{ $user->prodi }}"
                        disabled
                        class="w-full border rounded px-3 py-2 bg-gray-100">
                @endif
            </div>

            {{-- Telepon --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">No. Telepon</label>
                <input type="text"
                       name="telephonenumber"
                       value="{{ old('telephonenumber',$user->telephonenumber) }}"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200 focus:outline-none">
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email"
                       name="email"
                       value="{{ old('email',$user->email) }}"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-indigo-200 focus:outline-none">
            </div>

            @if($user->role === 'admin')
                <hr class="my-6">

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        Password Baru <span class="text-gray-400">(khusus admin)</span>
                    </label>
                    <input type="password"
                        name="password"
                        class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        Konfirmasi Password
                    </label>
                    <input type="password"
                        name="password_confirmation"
                        class="w-full border rounded px-3 py-2">
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Action --}}
            <div class="flex gap-3 pt-4">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded transition">
                    Simpan
                </button>

                <a href="/profile"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-5 py-2 rounded transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@endsection