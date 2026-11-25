@props([
    'name' => 'personel',
    'id' => 'personel-kegiatan',
    'label' => 'Daftar Anggota & Peran Kegiatan',
    'rows' => null,
    'minRows' => 1,
    'prefillUser' => true,
    'defaultPeran' => '',
    'required' => true,
])

@php
    $oldRows = old($name);
    $resolvedRows = is_array($oldRows)
        ? array_values($oldRows)
        : (is_array($rows) ? array_values($rows) : []);

    if (count($resolvedRows) === 0 && $minRows > 0) {
        if ($prefillUser && auth()->check()) {
            $user = auth()->user();
            $identitas = $user->nidn ?? $user->npm ?? '';
            $resolvedRows = [[
                'nama' => $user->name,
                'peran' => $defaultPeran,
                'instansi' => $identitas !== '' ? 'Universitas YARSI (' . $identitas . ')' : 'Universitas YARSI',
            ]];
        } else {
            $resolvedRows = [[
                'nama' => '',
                'peran' => $defaultPeran,
                'instansi' => '',
            ]];
        }
    }

    $totalRows = count($resolvedRows);
    $personelErrors = [];
    foreach ($errors->getMessages() as $errKey => $errMsgs) {
        if ($errKey === $name || str_starts_with($errKey, $name . '.')) {
            foreach ($errMsgs as $msg) {
                $personelErrors[] = $msg;
            }
        }
    }
    $personelErrors = array_values(array_unique($personelErrors));
@endphp

<section id="{{ $id }}"
         class="sakti-personel-section my-4 space-y-4"
         aria-labelledby="{{ $id }}-heading"
         data-sakti-personel
         data-field-name="{{ $name }}"
         data-min-rows="{{ $minRows }}"
         data-next-index="{{ $totalRows }}">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 id="{{ $id }}-heading" class="text-base font-semibold text-gray-900">
                {{ $label }}
                @if($required)
                    <span class="text-red-700" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib)</span>
                @endif
            </h3>
            <p class="text-xs text-gray-700 mt-1">
                Isi nama dan peran setiap anggota kegiatan. Kolom Nama dan Peran wajib diisi pada setiap baris.
            </p>
        </div>

        <button type="button"
                id="btn-tambah-personel"
                data-personel-add
                aria-label="Tambah baris anggota kegiatan"
                class="sakti-personel-btn-add inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Anggota</span>
        </button>
    </div>

    {{-- Live region untuk pembaca layar saat baris ditambah atau dihapus --}}
    <p data-personel-status class="sr-only" role="status" aria-live="polite"></p>

    {{-- Error validasi klien & server --}}
    <div id="alert-personel-kosong"
         data-personel-error
         role="alert"
         aria-live="assertive"
         class="{{ count($personelErrors) ? '' : 'hidden' }} p-3 bg-red-50 border border-red-700 text-red-800 text-sm rounded-lg flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <div data-personel-error-text>
            @if(count($personelErrors))
                {{ implode(' ', $personelErrors) }}
            @endif
        </div>
    </div>

    {{-- Header kolom untuk layar desktop --}}
    <div class="hidden md:grid md:grid-cols-12 gap-3 px-4 py-2 bg-gray-200 border border-gray-300 rounded-lg text-xs font-semibold text-gray-900">
        <div class="md:col-span-1 text-center">No</div>
        <div class="md:col-span-4">Nama Lengkap <span class="text-red-700" aria-hidden="true">*</span></div>
        <div class="md:col-span-3">Peran dalam Kegiatan <span class="text-red-700" aria-hidden="true">*</span></div>
        <div class="md:col-span-3">Instansi / NIM / NIDN</div>
        <div class="md:col-span-1 text-center">Aksi</div>
    </div>

    {{-- Daftar baris anggota --}}
    <div data-personel-rows class="space-y-3">
        @foreach($resolvedRows as $idx => $row)
            @php
                $no = $idx + 1;
                $namaVal = is_array($row) ? ($row['nama'] ?? '') : '';
                $peranVal = is_array($row) ? ($row['peran'] ?? '') : '';
                $instansiVal = is_array($row) ? ($row['instansi'] ?? '') : '';
                $namaErr = $errors->first($name . '.' . $idx . '.nama');
                $peranErr = $errors->first($name . '.' . $idx . '.peran');
                $cannotRemove = $totalRows <= $minRows;
            @endphp
            <div class="sakti-personel-row p-3 md:px-4 md:py-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-start"
                 data-personel-row
                 data-row-index="{{ $idx }}">
                <div class="md:col-span-1 flex items-center justify-between md:justify-center md:pt-2.5">
                    <span class="text-xs font-semibold text-gray-700 md:hidden">Anggota</span>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-900 text-xs font-semibold"
                          data-personel-number>{{ $no }}</span>
                </div>

                <div class="md:col-span-4">
                    <label for="{{ $name }}-{{ $idx }}-nama" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                        Nama Lengkap Anggota {{ $no }} <span class="text-red-700" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="{{ $name }}-{{ $idx }}-nama"
                           name="{{ $name }}[{{ $idx }}][nama]"
                           value="{{ $namaVal }}"
                           data-personel-nama
                           aria-required="true"
                           aria-invalid="{{ $namaErr ? 'true' : 'false' }}"
                           placeholder="Nama lengkap anggota"
                           class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
                    <p data-personel-field-error="nama"
                       class="{{ $namaErr ? '' : 'hidden' }} text-xs font-medium text-red-700 mt-1"
                       role="alert">{{ $namaErr }}</p>
                </div>

                <div class="md:col-span-3">
                    <label for="{{ $name }}-{{ $idx }}-peran" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                        Peran dalam Kegiatan Anggota {{ $no }} <span class="text-red-700" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="{{ $name }}-{{ $idx }}-peran"
                           name="{{ $name }}[{{ $idx }}][peran]"
                           value="{{ $peranVal }}"
                           data-personel-peran
                           aria-required="true"
                           aria-invalid="{{ $peranErr ? 'true' : 'false' }}"
                           placeholder="Contoh: Ketua / Anggota / Narasumber"
                           class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
                    <p data-personel-field-error="peran"
                       class="{{ $peranErr ? '' : 'hidden' }} text-xs font-medium text-red-700 mt-1"
                       role="alert">{{ $peranErr }}</p>
                </div>

                <div class="md:col-span-3">
                    <label for="{{ $name }}-{{ $idx }}-instansi" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                        Instansi, NIM, atau NIDN Anggota {{ $no }}
                    </label>
                    <input type="text"
                           id="{{ $name }}-{{ $idx }}-instansi"
                           name="{{ $name }}[{{ $idx }}][instansi]"
                           value="{{ $instansiVal }}"
                           data-personel-instansi
                           placeholder="Instansi / NIM / NIDN"
                           class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
                </div>

                <div class="md:col-span-1 flex justify-end md:justify-center">
                    <button type="button"
                            data-personel-remove
                            aria-label="Hapus anggota baris {{ $no }}"
                            @if($cannotRemove)
                                disabled
                                aria-disabled="true"
                                title="Minimal satu anggota kegiatan diperlukan"
                            @else
                                title="Hapus baris anggota ini"
                            @endif
                            class="sakti-personel-btn-remove inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="md:sr-only">Hapus</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty state (muncul bila minRows = 0 dan seluruh baris dihapus) --}}
    <div data-personel-empty
         class="{{ $totalRows === 0 ? '' : 'hidden' }} bg-white border border-dashed border-gray-400 rounded-lg p-6 text-center space-y-3">
        <p class="text-sm font-medium text-gray-800">
            Belum ada anggota kegiatan yang ditambahkan.
        </p>
        <p class="text-xs text-gray-700">
            Tambahkan minimal satu anggota beserta perannya agar tanggung jawab tercatat pada surat.
        </p>
        <button type="button"
                data-personel-empty-add
                class="sakti-personel-btn-add inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold">
            Tambah Anggota Pertama
        </button>
    </div>
</section>
