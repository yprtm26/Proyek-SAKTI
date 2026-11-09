@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();

    // Field identitas yang nilainya dikunci dari akun pengusul.
    $fieldTerkunci = $targetUser === 'dosen'
        ? [
            'nama_dosen' => $user->name,
            'no_nip' => $user->nidn,
            'prodi' => $user->prodi,
        ]
        : [
            'nama' => $user->name,
            'npm' => $user->npm,
            'prodi' => $user->prodi,
        ];

    $fields = $jenisSurat->fields ?? [];
@endphp

<main class="max-w-3xl mx-auto p-6">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">
            Isi data berikut dengan benar.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- IDENTITAS PENGUSUL (TERKUNCI) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b pb-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $targetUser === 'dosen' ? 'Nama Dosen' : 'Nama Mahasiswa' }}
                </label>
                <input type="text"
                       value="{{ $user->name }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $targetUser === 'dosen' ? 'NIDN' : 'NPM' }}
                </label>
                <input type="text"
                       value="{{ $targetUser === 'dosen' ? ($user->nidn ?? '-') : $user->npm }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Program Studi
                </label>
                <input type="text"
                       value="{{ namaProdi($user->prodi) }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>
        </div>

        {{-- FIELD DINAMIS DARI TEMPLATE --}}
        @forelse($fields as $field)
            @php
                $label = labelFieldSurat($field);
                $tipe = tipeFieldSurat($field);
                $terkunci = array_key_exists($field, $fieldTerkunci);
                $nilai = $terkunci ? $fieldTerkunci[$field] : old($field, '');
            @endphp

            <div>
                <label for="field-{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $label }}
                    @if(! $terkunci)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($terkunci)
                    <input type="text"
                           id="field-{{ $field }}"
                           name="{{ $field }}"
                           value="{{ $nilai }}"
                           readonly
                           class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
                @elseif($tipe === 'textarea')
                    <textarea id="field-{{ $field }}"
                              name="{{ $field }}"
                              rows="3"
                              required
                              class="w-full border rounded-lg px-3 py-2">{{ $nilai }}</textarea>
                @else
                    <input type="{{ $tipe }}"
                           id="field-{{ $field }}"
                           name="{{ $field }}"
                           value="{{ $nilai }}"
                           @if($tipe === 'number') step="any" @endif
                           required
                           class="w-full border rounded-lg px-3 py-2">
                @endif

                @error($field)
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @empty
            <p class="text-sm text-gray-500">
                Template ini belum memiliki field isian.
            </p>
        @endforelse

        {{-- AKSI --}}
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ $kembaliUrl }}"
               class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                Kembali
            </a>

            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold">
                Ajukan Surat
            </button>
        </div>
    </form>

</main>
@endsection
