@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">Detail Disposisi</h2>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        {{-- No Surat --}}
        <div>
            <p class="text-gray-500 mb-1">No Surat</p>
            <p class="font-medium text-gray-700">{{ $disposisi->surat->no_surat }}</p>
        </div>

        {{-- Perihal --}}
        <div>
            <p class="text-gray-500 mb-1">Perihal</p>
            <p class="font-medium text-gray-700">{{ $disposisi->surat->perihal }}</p>
        </div>

        {{-- Nama Pengirim --}}
        <div>
            <p class="text-gray-500 mb-1">Nama Pengirim</p>
            <p class="font-medium text-gray-700">
                {{ $disposisi->surat->nama_pengirim ?? '-' }}
            </p>
        </div>

        {{-- Tanggal Terima Surat --}}
        <div>
            <p class="text-gray-500 mb-1">Tanggal Terima Surat</p>
            <p class="font-medium text-gray-700">
                {{ $disposisi->surat->tgl_terima_surat?->format('d-m-Y') ?? '-' }}
            </p>
        </div>

        {{-- Tanggal Surat --}}
        <div>
            <p class="text-gray-500 mb-1">Tanggal Surat</p>
            <p class="font-medium text-gray-700">
                {{ $disposisi->surat->tgl_surat?->format('d-m-Y') ?? '-' }}
            </p>
        </div>

        {{-- Status --}}
        <div>
            <p class="text-gray-500 mb-1">Status</p>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                {{ $disposisi->status === 'selesai' ? 'bg-green-100 text-green-700' :
                   ($disposisi->status === 'dibaca' ? 'bg-blue-100 text-blue-700' :
                    'bg-orange-100 text-orange-700') }}">
                {{ ucfirst($disposisi->status) }}
            </span>
        </div>

        {{-- Prioritas --}}
        <div>
            <p class="text-gray-500 mb-1">Prioritas</p>
            @php
                $warna = [
                    'sangat_segera' => 'bg-red-100 text-red-700',
                    'segera' => 'bg-yellow-100 text-yellow-700',
                    'biasa' => 'bg-green-100 text-green-700',
                ];
            @endphp
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                {{ $warna[$disposisi->prioritas] ?? 'bg-gray-100 text-gray-700' }}">
                {{ strtoupper(str_replace('_',' ', $disposisi->prioritas)) }}
            </span>
        </div>

        {{-- Jumlah Pembayaran (opsional) --}}
        <div>
            <p class="text-gray-500 mb-1">Jumlah Pembayaran</p>
            <p class="font-medium text-gray-700">
                {{ $disposisi->surat->jumlah_pembayaran ? 'Rp '.number_format($disposisi->surat->jumlah_pembayaran,0,',','.') : '-' }}
            </p>
        </div>

        {{-- Dari --}}
        <div>
            <p class="text-gray-500 mb-1">Dari</p>
            <p class="font-medium text-gray-700">{{ $disposisi->fromUser->name }} ({{ $disposisi->fromUser->role }})</p>
        </div>

        {{-- Ke --}}
        <div>
            <p class="text-gray-500 mb-1">Ke</p>
            <p class="font-medium text-gray-700">{{ $disposisi->toUser->name }} ({{ $disposisi->toUser->role }})</p>
        </div>

        {{-- Tanggal Disposisi --}}
        <div>
            <p class="text-gray-500 mb-1">Tanggal Disposisi</p>
            <p class="font-medium text-gray-700">{{ $disposisi->created_at->format('d-m-Y H:i') }}</p>
        </div>

        {{-- Catatan --}}
        @if($disposisi->catatan)
        <div class="md:col-span-2">
            <p class="text-gray-500 mb-1">Keterangan</p>
            <p class="font-medium text-gray-700">{{ $disposisi->catatan }}</p>
        </div>
        @endif

        {{-- Pilihan Email / Cetak --}}
        <div class="md:col-span-2">
            <p class="text-gray-500 mb-1">Pilihan</p>
            <p class="font-medium text-gray-700">
                {{ $disposisi->surat->pilihan_kirim ?? '-' }}
            </p>
        </div>

        <div class="md:col-span-2"
            x-data="{ openFile: false }"
            x-cloak>

            <p class="text-gray-500 mb-1">File Surat</p>

            <div class="flex flex-wrap items-center gap-3">

                {{-- DOWNLOAD FILE SURAT --}}
                <a href="{{ asset('storage/' . $disposisi->surat->file_path) }}"
                download
                class="bg-blue-600 text-white px-4 py-2 rounded">
                    Download File
                </a>

                {{-- DOWNLOAD FILE TAMBAHAN (JIKA ADA) --}}
                @if($disposisi->file_tambahan)
                    <a href="{{ asset('storage/'.$disposisi->file_tambahan) }}"
                    download
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                        Download Surat Tambahan
                    </a>
                @endif

                {{-- TOMBOL TAMBAH FILE (ADMIN & JIKA BELUM ADA FILE TAMBAHAN) --}}
                @if(
                     Auth::user()->role === 'admin' &&
                    $disposisi->to_user_id === auth()->id() &&
                    !$disposisi->file_tambahan &&
                    $disposisi->status !== 'selesai'
                )
                    <button type="button"
                            @click="openFile = true"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        + Tambah File
                    </button>
                @endif
            </div>

            {{-- MODAL UPLOAD FILE --}}
            <div x-show="openFile"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
                style="display:none">

                <div @click.away="openFile = false"
                    class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">

                    <h3 class="text-lg font-semibold mb-4">
                        Tambah File Disposisi
                    </h3>

                    <form method="POST"
                        action="{{ url('/disposisi/'.$disposisi->id.'/upload-file') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="block mb-2 text-gray-600">
                                Pilih File
                            </label>
                            <input type="file"
                                name="file_tambahan"
                                required
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                Upload
                            </button>
                            <button type="button"
                                    @click="openFile = false"
                                    class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
                                Batal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- Tombol Aksi --}}
<div class="flex flex-wrap gap-3 mt-4">

    {{-- Tombol Terima --}}
    @if(Auth::id() === $disposisi->to_user_id
        && $disposisi->status === 'terkirim'
        && Auth::user()->role !== 'admin')
        <form method="POST" action="{{ url('/disposisi/'.$disposisi->id.'/terima') }}">
            @csrf
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm transition">
                Terima Disposisi
            </button>
        </form>
    @endif

    {{-- Tombol Kirim ke User Lain --}}
@if(Auth::id() === $disposisi->to_user_id
    && $disposisi->status === 'terkirim'
    && Auth::user()->role !== 'admin')
    <div x-data="{ open: {{ $errors->has('to_user_name') ? 'true' : 'false' }} }" class="relative">
        <button type="button" x-on:click="open = true"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm transition">
            Disposisi
        </button>

        {{-- Modal --}}
        <div x-show="open" x-transition.opacity style="display: none"
             class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
            <div @click.away="open = false" class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">

                <form method="POST" action="{{ url('/disposisi/'.$disposisi->id.'/lempar') }}">
                    @csrf

                    {{-- Kirim ke --}}
                    <div>
                        <label class="block mb-1 font-medium">Kirim ke</label>
                        <select name="to_user_id"
                                class="border px-3 py-2 w-full rounded"
                                required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div class="mt-4">
                        <label class="block mb-2 text-gray-600">Catatan</label>
                        <textarea name="catatan" class="w-full border rounded px-3 py-2 mb-4">{{ old('catatan') }}</textarea>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="flex justify-end gap-2">
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Kirim
                        </button>
                        <button type="button" x-on:click="open = false"
                            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
                            Batal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endif

    {{-- Tombol Kirim ke Admin --}}
    @if(
        Auth::id() === $disposisi->to_user_id &&
        $disposisi->status === 'terkirim' &&
        Auth::user()->role !== 'admin'
    )
        <div x-data="{ openAdmin: false }">
            <button type="button"
                @click="openAdmin = true"
                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded shadow-sm transition">
                Kirim ke Admin
            </button>

            {{-- Modal Kirim ke Admin --}}
            <div x-show="openAdmin" x-transition.opacity style="display:none"
                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">

                <div @click.away="openAdmin = false"
                    class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">

                    <form method="POST" action="{{ url('/disposisi/'.$disposisi->id.'/kirim-admin') }}">
                        @csrf

                        {{-- Catatan --}}
                        <div>
                            <label class="block mb-2 font-medium">Catatan</label>
                            <textarea name="catatan"
                                    class="w-full border rounded px-3 py-2"
                                    rows="3"
                                    placeholder="Tulis catatan untuk admin..."></textarea>
                        </div>

                        {{-- Tombol --}}
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Kirim
                            </button>
                            <button type="button"
                                @click="openAdmin = false"
                                class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
                                Batal
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Tombol Selesai (Admin setelah menambahkan file) --}}
    @if(
        Auth::user()->role === 'admin' &&
        $disposisi->file_tambahan &&
        $disposisi->status !== 'selesai'
    )
        <form method="POST" action="{{ url('/admin/disposisi/'.$disposisi->id.'/selesai') }}">
            @csrf
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm transition">
                Selesai
            </button>
        </form>
    @endif

    {{-- spacer --}}
    <div class="flex-grow"></div>

    {{-- Tombol kembali (kanan) --}}
    @if(Auth::user()->role === 'admin')
        <a href="{{ url('/admin/disposisi') }}"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm transition">
            Kembali ke Dashboard Disposisi
        </a>
    @else
        <a href="{{ url('/disposisi') }}"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm transition">
            Kembali ke Dashboard Disposisi
        </a>
    @endif
</div>

<script src="//unpkg.com/alpinejs" defer></script>

@endsection
