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


        <input type="hidden" name="jenis_surat_id" value="{{ $jenisSurat->id }}">

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
                @if($field !== 'semester' && $field !== 'personel')
                    <label for="field-{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $label }}
                        @if(! $terkunci)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                @endif

                @if($terkunci)
                    <input type="text"
                           id="field-{{ $field }}"
                           name="{{ $field }}"
                           value="{{ $nilai }}"
                           readonly
                           class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
                @elseif($field === 'personel')
                    <x-personel-list
                        name="personel"
                        :id="'field-' . $field"
                        :label="$label"
                        :rows="old('personel', [])"
                        :prefill-user="true"
                        default-peran=""
                        required
                    />
                @elseif($field === 'semester')
                    @php
                        $semesterLama = old('semester')
                            ? \App\Models\Semester::query()->where('jenis', 'paruh')->find(old('semester'))
                            : null;
                    @endphp
                    <x-combobox
                        :name="$field"
                        :id="'field-' . $field"
                        :label="$label"
                        endpoint="{{ url('/api/master/semester') }}"
                        :params="['jenis' => 'paruh']"
                        :value="old('semester')"
                        :value-label="$semesterLama?->nama"
                        placeholder="-- Pilih Semester --"
                        search-placeholder="Cari semester..."
                        required
                    />
                @elseif($tipe === 'textarea')
                    <textarea id="field-{{ $field }}"
                              name="{{ $field }}"
                              rows="3"
                              required
                              class="w-full border rounded-lg px-3 py-2">{{ $nilai }}</textarea>
                @elseif($tipe === 'date')
                    <x-date-field
                        :name="$field"
                        :id="'field-' . $field"
                        :value="$nilai"
                        :min-field="$field === 'tanggal_selesai' ? 'tanggal_mulai' : ($field === 'tanggal_akhir' ? 'tanggal_awal' : null)"
                        :error-key="$field"
                        hint="Format YYYY-MM-DD."
                        required
                    />
                @elseif($tipe === 'time')
                    <x-time-field
                        :name="$field"
                        :id="'field-' . $field"
                        :value="$nilai"
                        :min-field="$field === 'waktu_selesai' ? 'waktu_mulai' : ($field === 'waktu_akhir' ? 'waktu_awal' : ($field === 'jam_selesai' ? 'jam_mulai' : null))"
                        :error-key="$field"
                        hint="Format 24 jam HH:mm (contoh: 08:30 atau 14:00)."
                        required
                    />
                @else
                    <input type="{{ $tipe }}"
                           id="field-{{ $field }}"
                           name="{{ $field }}"
                           value="{{ $nilai }}"
                           @if($tipe === 'number') step="any" @endif
                           required
                           class="w-full border rounded-lg px-3 py-2">
                @endif

                @if($tipe !== 'date' && $tipe !== 'time')
                    @error($field)
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">
                Template ini belum memiliki field isian.
            </p>
        @endforelse

        {{-- AKSI --}}
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t">
            <a href="{{ $kembaliUrl }}"
               class="min-h-[44px] px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium inline-flex items-center justify-center transition-colors">
                Kembali
            </a>

            {{-- B-13 A: TOMBOL SIMPAN DRAF --}}
            <button type="submit"
                    name="action"
                    value="draft"
                    formaction="{{ url('/surat/draf') }}"
                    formnovalidate
                    class="btn-simpan-draf min-h-[44px] px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Simpan Draf</span>
            </button>

            <button type="submit"
                    name="action"
                    value="submit"
                    class="min-h-[44px] px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 inline-flex items-center gap-2">
                <span>Ajukan Surat</span>
            </button>
        </div>
    </form>

</main>
@endsection
