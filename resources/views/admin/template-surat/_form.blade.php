@php
    // Form ini dipakai oleh halaman create & edit.
    // $jenisSurat bernilai null pada halaman create.
    $jenisSurat = $jenisSurat ?? null;

    $alurTerpilih = old('alur', $jenisSurat->alur ?? ['admin']);
    $fieldTerpilih = old('fields', $jenisSurat->fields ?? ['']);
@endphp

{{-- ERROR --}}
@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FORM --}}
<form method="POST"
      action="{{ $action }}"
      class="bg-white shadow rounded-lg p-6 space-y-5">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    {{-- NAMA TEMPLATE --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Nama Template <span class="text-red-500">*</span>
        </label>
        <input type="text"
               name="nama"
               value="{{ old('nama', $jenisSurat->nama ?? '') }}"
               placeholder="Contoh: Surat Keterangan Aktif"
               class="w-full border rounded-lg px-3 py-2"
               required>
        @error('nama')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- KODE KLASIFIKASI --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Kode Klasifikasi <span class="text-gray-400">(opsional)</span>
        </label>
        <input type="text"
               name="kode"
               value="{{ old('kode', $jenisSurat->kode ?? '') }}"
               placeholder="Contoh: SKet PP.30.02"
               class="w-full border rounded-lg px-3 py-2">
        @error('kode')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- TARGET PENGGUNA --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Target Pengguna <span class="text-red-500">*</span>
            </label>
            <select name="target_user"
                    class="w-full border rounded-lg px-3 py-2"
                    required>
                <option value="mahasiswa" {{ old('target_user', $jenisSurat->target_user ?? 'mahasiswa') === 'mahasiswa' ? 'selected' : '' }}>
                    Mahasiswa
                </option>
                <option value="dosen" {{ old('target_user', $jenisSurat->target_user ?? 'mahasiswa') === 'dosen' ? 'selected' : '' }}>
                    Dosen
                </option>
            </select>
            @error('target_user')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Status Template
            </label>
            {{-- Nilai 0 dikirim saat checkbox tidak dicentang, agar status bisa dinonaktifkan. --}}
            <input type="hidden" name="aktif" value="0">
            <label class="inline-flex items-center gap-2 mt-2 cursor-pointer">
                <input type="checkbox"
                       name="aktif"
                       value="1"
                       {{ old('aktif', $jenisSurat->aktif ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Aktif (langsung tampil di daftar pengajuan)</span>
            </label>
            @error('aktif')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ALUR PERSETUJUAN --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Alur Persetujuan <span class="text-red-500">*</span>
        </label>
        <div class="flex flex-wrap gap-5">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                       name="alur[]"
                       value="admin"
                       {{ in_array('admin', $alurTerpilih) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Admin TU</span>
            </label>

            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                       name="alur[]"
                       value="kaprodi"
                       {{ in_array('kaprodi', $alurTerpilih) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Kaprodi</span>
            </label>

            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                       name="alur[]"
                       value="dekan"
                       {{ in_array('dekan', $alurTerpilih) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Dekan</span>
            </label>
        </div>
        @error('alur')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- FIELD DINAMIS --}}
    <div>
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">
                Field Dinamis <span class="text-red-500">*</span>
            </label>

            <button type="button"
                    id="tambahField"
                    class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4"
                     fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Field
            </button>
        </div>

        <p class="text-xs text-gray-500 mb-3">
            Setiap field akan menjadi kolom isian wajib pada form pengajuan surat.
            Gunakan huruf kecil dan garis bawah, contoh: <span class="font-medium">tahun_akademik</span>.
        </p>

        <div id="daftarField" class="space-y-2">
            @foreach($fieldTerpilih as $field)
                <div class="field-row flex gap-2 items-center">
                    <input type="text"
                           name="fields[]"
                           value="{{ $field }}"
                           placeholder="Contoh: tahun_akademik"
                           pattern="[a-z0-9_]+"
                           title="Hanya huruf kecil, angka, dan garis bawah"
                           class="w-full border rounded-lg px-3 py-2"
                           required>

                    <button type="button"
                            class="hapusField px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium whitespace-nowrap">
                        Hapus
                    </button>
                </div>
            @endforeach
        </div>

        @error('fields')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        @error('fields.*')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- AKSI --}}
    <div class="flex justify-end gap-3 pt-4">
        <button type="submit"
                class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
            {{ $submitLabel ?? 'Simpan Template' }}
        </button>

        <a href="{{ route('admin.template-surat.index') }}"
           class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
            Batal
        </a>
    </div>

</form>

{{-- TEMPLATE BARIS FIELD (untuk JS) --}}
<template id="fieldRowTemplate">
    <div class="field-row flex gap-2 items-center">
        <input type="text"
               name="fields[]"
               placeholder="Contoh: tahun_akademik"
               pattern="[a-z0-9_]+"
               title="Hanya huruf kecil, angka, dan garis bawah"
               class="w-full border rounded-lg px-3 py-2"
               required>

        <button type="button"
                class="hapusField px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium whitespace-nowrap">
            Hapus
        </button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const daftarField = document.getElementById('daftarField');
        const tambahField = document.getElementById('tambahField');
        const fieldRowTemplate = document.getElementById('fieldRowTemplate');

        function pasangTombolHapus(row) {
            row.querySelector('.hapusField').addEventListener('click', function () {
                if (daftarField.querySelectorAll('.field-row').length > 1) {
                    row.remove();
                } else {
                    // Sisakan satu baris kosong agar form tidak kehilangan input.
                    row.querySelector('input[name="fields[]"]').value = '';
                }
            });
        }

        daftarField.querySelectorAll('.field-row').forEach(pasangTombolHapus);

        tambahField.addEventListener('click', function () {
            const row = fieldRowTemplate.content.firstElementChild.cloneNode(true);
            pasangTombolHapus(row);
            daftarField.appendChild(row);
            row.querySelector('input[name="fields[]"]').focus();
        });
    });
</script>
