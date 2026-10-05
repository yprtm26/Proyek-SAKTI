@extends('layouts.app')

@section('content')
<h2 class="text-xl font-semibold mb-4">Buat Disposisi Baru</h2>

@if($errors->any())
<div class="mb-4 text-red-600">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ url('/admin/disposisi') }}"
      method="POST"
      enctype="multipart/form-data"
      class="space-y-4 bg-white p-6 rounded shadow">

    @csrf

    {{-- Nomor Surat --}}
    <div>
        <label class="block mb-1 font-medium">Nomor Surat</label>
        <input type="text"
            name="no_surat"
            class="border px-3 py-2 w-full rounded"
            value="{{ old('no_surat') }}"
            placeholder="Contoh: 123/UNIV/II/2026"
            required>
    </div>

    {{-- Perihal --}}
    <div>
        <label class="block mb-1 font-medium">Perihal</label>
        <input type="text"
               name="perihal"
               class="border px-3 py-2 w-full rounded"
               value="{{ old('perihal') }}"
               required>
    </div>

    {{-- Nama Pengirim --}}
    <div>
        <label class="block mb-1 font-medium">Nama Pengirim</label>
        <input type="text"
               name="nama_pengirim"
               class="border px-3 py-2 w-full rounded"
               value="{{ old('nama_pengirim') }}"
               required>
    </div>

    {{-- Tanggal Terima Surat --}}
    <div>
        <label class="block mb-1 font-medium">Tanggal Terima Surat</label>
        <input type="date"
               name="tgl_terima_surat"
               class="border px-3 py-2 w-full rounded"
               value="{{ old('tgl_terima_surat') }}"
               required>
    </div>

    {{-- Tanggal di Surat --}}
    <div>
        <label class="block mb-1 font-medium">Tanggal di Surat</label>
        <input type="date"
               name="tgl_surat"
               class="border px-3 py-2 w-full rounded"
               value="{{ old('tgl_surat') }}"
               required>
    </div>


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

    {{-- Pilihan Email / Cetak --}}
    <div>
        <label class="block mb-1 font-medium">Kirim via</label>
        <select name="pilihan_kirim"
                class="border px-3 py-2 w-full rounded"
                required>
            <option value="">-- Pilih --</option>
            <option value="email">Email</option>
            <option value="cetak">Cetak</option>
        </select>
    </div>

    {{-- Jumlah Pembayaran (opsional) --}}
    <div>
        <label class="block mb-1 font-medium">Jumlah Pembayaran *(opsional)</label>
        <input type="number"
               name="jumlah_pembayaran"
               class="border px-3 py-2 w-full rounded"
               value="{{ old('jumlah_pembayaran') }}">
    </div>

    {{-- Catatan --}}
    <div>
        <label class="block mb-1 font-medium">Keterangan *(opsional)</label>
        <textarea name="catatan"
                  class="border px-3 py-2 w-full rounded"
                  rows="4">{{ old('catatan') }}</textarea>
    </div>

    {{-- File --}}
    <div>
        <label class="block mb-1 font-medium">File Surat</label>
        <input type="file"
               name="file"
               class="border px-3 py-2 w-full rounded"
               required>
    </div>

    {{-- Prioritas --}}
    <div class="mb-4">
        <label class="block font-medium mb-1">Prioritas Disposisi</label>
        <select name="prioritas" required class="w-full border rounded px-3 py-2">
            <option value="">-- Pilih Prioritas --</option>
            <option value="sangat_segera">Sangat Segera</option>
            <option value="segera">Segera</option>
            <option value="biasa">Biasa</option>
        </select>
    </div>

    {{-- Tombol --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Kirim Disposisi
        </button>

        <a href="{{ url('/admin/disposisi') }}"
           class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
            Batal
        </a>
    </div>

</form>
@endsection
