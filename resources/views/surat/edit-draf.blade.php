@extends('layouts.app')

@section('content')
@php
    /**
     * B-13 B: Halaman Edit Draf Surat
     * Semua isian dari draf sebelumnya di-populate ke form sehingga pengusul
     * dapat melengkapi atau mengoreksi data sebelum mengirim pengajuan final.
     *
     * Variabel yang masuk dari route:
     *   $jenisSurat  - instance JenisSurat
     *   $draf        - instance SuratPengajuan (status = 'draf')
     *   $targetUser  - 'dosen' | 'mahasiswa'
     *   $action      - URL POST untuk update-draf
     *   $kembaliUrl  - URL kembali
     */
    $user = auth()->user();

    // Field identitas yang nilai-nya dikunci dari akun pengusul.
    $fieldTerkunci = $targetUser === 'dosen'
        ? [
            'nama_dosen'      => $user->name,
            'nama_ditugaskan' => $user->name,
            'no_nip'          => $user->nidn,
            'prodi'           => $user->prodi,
        ]
        : [
            'nama'  => $user->name,
            'npm'   => $user->npm,
            'prodi' => $user->prodi,
        ];

    $fields   = $jenisSurat->fields ?? [];
    $dataLama = (array) ($draf->data ?? []);
@endphp

<main class="max-w-3xl mx-auto p-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Anda sedang mengedit <strong>draf</strong> yang disimpan pada {{ $draf->updated_at->translatedFormat('d F Y, H:i') }}.
            Klik "Kirim Pengajuan" untuk mengirimkan ke Admin TU.</span>
        </div>
        <h2 class="text-xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">Lengkapi atau perbaiki isian di bawah ini.</p>
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

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ $action }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- Template tersimpan agar update-draf tidak perlu input jenis_surat_id lagi --}}
        <input type="hidden" name="jenis_surat_id" value="{{ $jenisSurat->id }}">

        {{-- IDENTITAS PENGUSUL (TERKUNCI - DIBACA DARI AKUN LOGIN) --}}
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

        {{-- FIELD DINAMIS DARI TEMPLATE -- nilai lama dari draf diisi ke field --}}
        @forelse($fields as $field)
            @php
                $label    = labelFieldSurat($field);
                $tipe     = tipeFieldSurat($field);
                $terkunci = array_key_exists($field, $fieldTerkunci);
                // Gunakan old() (flash saat validasi gagal), lalu data lama draf, lalu default ''.
                $nilai    = $terkunci ? $fieldTerkunci[$field] : old($field, $dataLama[$field] ?? '');
            @endphp

            <div>
                @if($field !== 'semester')
                    <label for="edit-{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $label }}
                        @if(! $terkunci)<span class="text-red-500">*</span>@endif
                    </label>
                @endif

                @if($terkunci)
                    <input type="text"
                           id="edit-{{ $field }}"
                           name="{{ $field }}"
                           value="{{ $nilai }}"
                           readonly
                           class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
                @elseif($field === 'semester')
                    @php
                        // drafts dari B-13 A menyimpan ID semester di `semester`
                        // (belum ada `semester_id`), sedangkan surat final memakai
                        // `semester_id`. Keduanya harus terbaca saat edit.
                        $semLama = $dataLama['semester_id'] ?? $dataLama['semester'] ?? null;
                        $semId   = old('semester', is_numeric($semLama) ? $semLama : null);
                        $semNama = is_numeric($semLama) ? null : ($dataLama['semester'] ?? null);
                        $semObj  = $semId ? \App\Models\Semester::query()->find($semId) : null;
                    @endphp
                    <x-combobox
                        :name="$field"
                        :id="'edit-' . $field"
                        :label="$label"
                        endpoint="{{ url('/api/master/semester') }}"
                        :params="['jenis' => 'paruh']"
                        :value="$semObj?->id ?? $semId"
                        :value-label="$semObj?->nama ?? $semNama"
                        placeholder="-- Pilih Semester --"
                        search-placeholder="Cari semester..."
                        required
                    />
                @elseif($tipe === 'textarea')
                    <textarea id="edit-{{ $field }}"
                              name="{{ $field }}"
                              rows="3"
                              required
                              class="w-full border rounded-lg px-3 py-2">{{ $nilai }}</textarea>
                @elseif($tipe === 'date')
                    <x-date-field
                        :name="$field"
                        :id="'edit-' . $field"
                        :value="$nilai"
                        :min-field="$field === 'tanggal_selesai' ? 'tanggal_mulai' : ($field === 'tanggal_akhir' ? 'tanggal_awal' : null)"
                        :error-key="$field"
                        hint="Format YYYY-MM-DD."
                        required
                    />
                @elseif($tipe === 'time')
                    <x-time-field
                        :name="$field"
                        :id="'edit-' . $field"
                        :value="$nilai"
                        :min-field="$field === 'waktu_selesai' ? 'waktu_mulai' : ($field === 'waktu_akhir' ? 'waktu_awal' : ($field === 'jam_selesai' ? 'jam_mulai' : null))"
                        :error-key="$field"
                        hint="Format 24 jam HH:mm (contoh: 08:30 atau 14:00)."
                        required
                    />
                @else
                    <input type="{{ $tipe }}"
                           id="edit-{{ $field }}"
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
            <p class="text-sm text-gray-500">Template ini belum memiliki field isian.</p>
        @endforelse

        {{-- B-13 B: pertahankan data lama yang bukan field template (mis. anggota,
             personel, referensi master) agar tidak hilang saat draf disimpan ulang. --}}
        @foreach($dataLama as $kunci => $nilaiLama)
            @if(! in_array($kunci, (array) $fields, true)
                && ! array_key_exists($kunci, $fieldTerkunci)
                && $kunci !== 'jenis_surat_id')
                <input type="hidden"
                       name="{{ $kunci }}"
                       value="{{ is_array($nilaiLama) ? json_encode($nilaiLama) : $nilaiLama }}">
            @endif
        @endforeach

        {{-- AKSI --}}
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t">
            <a href="{{ $kembaliUrl }}"
               class="min-h-[44px] px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium inline-flex items-center justify-center transition-colors">
                Kembali
            </a>

            {{-- Simpan Draf: perbarui draf tanpa validasi ketat --}}
            <button type="submit"
                    name="action"
                    value="draft"
                    formaction="{{ $action }}"
                    formnovalidate
                    class="btn-simpan-draf min-h-[44px] px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Simpan Draf</span>
            </button>

            {{-- Kirim Pengajuan: validasi penuh, ubah status ke 'menunggu' --}}
            <button type="submit"
                    name="action"
                    value="submit"
                    class="min-h-[44px] px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <span>Kirim Pengajuan</span>
            </button>
        </div>
    </form>

</main>
@endsection
