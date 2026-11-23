{{--
|--------------------------------------------------------------------------
| B-10 B: KOMPONEN TABEL DINAMIS ANGGOTA KEGIATAN & PERAN
|--------------------------------------------------------------------------
| Memenuhi kriteria aksesibilitas:
| 1. Tombol tambah & hapus memiliki target klik/sentuh minimal 44x44px.
| 2. Focus ring dengan rasio kontras >= 3:1 (focus:ring-2 focus:ring-offset-2).
| 3. Validasi blokir submit jika baris anggota memiliki kolom nama / peran kosong.
| 4. Terhubung ke relasi database SuratPersonel (nama, peran, instansi, urutan).
--}}

@php
    $defaultPeran = $defaultPeran ?? 'Ketua / Penanggung Jawab';
    $identityRequired = $identityRequired ?? false;
    $initialUser = auth()->user();
    $initialNama = $initialUser ? $initialUser->name : '';

    if ($initialUser && $initialUser->role === 'mahasiswa') {
        $initialIdentitas = $initialUser->npm ?? '';
    } elseif ($initialUser && filled($initialUser->nidn)) {
        $initialIdentitas = 'Universitas YARSI (' . $initialUser->nidn . ')';
    } else {
        $initialIdentitas = 'Universitas YARSI';
    }

    $barisLama = old('personel', null);

    if (is_array($barisLama) && count($barisLama) > 0) {
        $barisAwal = array_values($barisLama);
    } elseif (isset($surat)) {
        $barisAwal = collect(personelSurat($surat))->map(fn ($p) => [
            'nama' => $p['nama'] ?? '',
            'peran' => $p['peran'] ?? ($p['keterangan'] ?? ''),
            'instansi' => $p['instansi'] ?? ($p['npm'] ?? ($p['nidn'] ?? '')),
        ])->values()->all();
    } else {
        $barisAwal = [];
    }

    if (count($barisAwal) === 0) {
        $barisAwal = [[
            'nama' => $initialNama,
            'peran' => $defaultPeran,
            'instansi' => $initialIdentitas,
        ]];
    }

    // Baris pertama selalu pengusul.
    $barisAwal[0]['nama'] = $initialNama;
    $barisAwal[0]['peran'] = $barisAwal[0]['peran'] ?? $defaultPeran;
    $barisAwal[0]['instansi'] = $barisAwal[0]['instansi'] ?? $initialIdentitas;

    $uid = 'personel-' . str_replace('.', '', uniqid('', true));
@endphp

<div class="bg-gray-50 border border-gray-200 rounded-xl p-5 my-6 personel-wrapper"
     id="{{ $uid }}"
     data-identity-required="{{ $identityRequired ? '1' : '0' }}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Daftar Personel / Anggota Kegiatan
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Tambahkan susunan anggota tim atau kepanitiaan. Kolom Nama dan Peran wajib diisi untuk setiap baris.
            </p>
        </div>

        {{-- TOMBOL TAMBAH ANGGOTA (TARGET KLIK >= 44x44px, FOCUS RING KONTRAS >= 3:1) --}}
        <button type="button"
                id="btn-tambah-personel"
                data-personel-add
                aria-label="Tambah baris anggota kegiatan baru"
                class="min-h-[44px] min-w-[44px] px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg inline-flex items-center justify-center gap-2 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Anggota</span>
        </button>
    </div>

    {{-- ALERT VALIDASI KLIEN JIKA ADA BARIS KOSONG --}}
    <div data-personel-alert class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2" role="alert" aria-live="polite">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span data-personel-alert-text>Harap lengkapi kolom <strong>Nama</strong> dan <strong>Peran</strong> pada seluruh baris anggota sebelum mengajukan surat.</span>
    </div>

    {{-- TABEL RESPONSIVE ANGGOTA --}}
    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th scope="col" class="w-12 px-3 py-3 text-center">No</th>
                    <th scope="col" class="px-3 py-3 text-left">Nama Lengkap <span class="text-red-500">*</span></th>
                    <th scope="col" class="px-3 py-3 text-left">Peran dalam Kegiatan <span class="text-red-500">*</span></th>
                    <th scope="col" class="px-3 py-3 text-left">Instansi / NIM / NIDN @if($identityRequired)<span class="text-red-500">*</span>@endif</th>
                    <th scope="col" class="w-20 px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-personel" data-personel-body class="divide-y divide-gray-100">
                @foreach($barisAwal as $i => $baris)
                    <tr class="baris-personel bg-white hover:bg-gray-50 transition-colors">
                        <td class="nomor-urut px-3 py-3 text-center font-medium text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">
                            <label for="{{ $uid }}-{{ $i }}-nama" class="sr-only">Nama Lengkap Anggota {{ $i + 1 }}</label>
                            <input type="text"
                                   id="{{ $uid }}-{{ $i }}-nama"
                                   name="personel[{{ $i }}][nama]"
                                   value="{{ $baris['nama'] ?? '' }}"
                                   required
                                   placeholder="Nama anggota"
                                   class="input-personel-nama w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @if(isset($errors) && $errors->has('personel.'.$i.'.nama'))
                                <p class="text-red-600 text-xs mt-1">{{ $errors->first('personel.'.$i.'.nama') }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <label for="{{ $uid }}-{{ $i }}-peran" class="sr-only">Peran Anggota {{ $i + 1 }}</label>
                            <input type="text"
                                   id="{{ $uid }}-{{ $i }}-peran"
                                   name="personel[{{ $i }}][peran]"
                                   value="{{ $baris['peran'] ?? '' }}"
                                   required
                                   placeholder="Contoh: Ketua Tim / Narasumber"
                                   class="input-personel-peran w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @if(isset($errors) && $errors->has('personel.'.$i.'.peran'))
                                <p class="text-red-600 text-xs mt-1">{{ $errors->first('personel.'.$i.'.peran') }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <label for="{{ $uid }}-{{ $i }}-instansi" class="sr-only">Instansi atau NIM Anggota {{ $i + 1 }}</label>
                            <input type="text"
                                   id="{{ $uid }}-{{ $i }}-instansi"
                                   name="personel[{{ $i }}][instansi]"
                                   value="{{ $baris['instansi'] ?? '' }}"
                                   @if($identityRequired) required @endif
                                   placeholder="Instansi / Unit / NIM"
                                   class="input-personel-instansi w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @if(isset($errors) && $errors->has('personel.'.$i.'.instansi'))
                                <p class="text-red-600 text-xs mt-1">{{ $errors->first('personel.'.$i.'.instansi') }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center text-xs text-gray-400">
                            @if($i === 0)
                                <span class="inline-block py-1 px-2 bg-gray-100 rounded text-gray-500 font-medium">Utama</span>
                            @else
                                <button type="button"
                                        data-personel-remove
                                        aria-label="Hapus baris anggota {{ $i + 1 }}"
                                        title="Hapus anggota ini"
                                        class="btn-hapus-personel min-h-[44px] min-w-[44px] p-2.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
{{-- SCRIPT INTERAKTIF DINAMIS (B-10 B) --}}
<script>
    (function () {
        if (window.__personelTableReady) {
            return;
        }
        window.__personelTableReady = true;

        function renumber(wrapper) {
            const rows = wrapper.querySelectorAll('tr.baris-personel');
            rows.forEach(function (row, idx) {
                const no = row.querySelector('.nomor-urut');
                if (no) no.textContent = idx + 1;

                const nama = row.querySelector('.input-personel-nama');
                const peran = row.querySelector('.input-personel-peran');
                const instansi = row.querySelector('.input-personel-instansi');
                const hapus = row.querySelector('[data-personel-remove]');

                if (nama) {
                    nama.name = `personel[${idx}][nama]`;
                    nama.id = `${wrapper.id}-${idx}-nama`;
                    const label = row.querySelector(`label[for$="-nama"]`);
                    if (label) { label.setAttribute('for', nama.id); label.textContent = `Nama Lengkap Anggota ${idx + 1}`; }
                }
                if (peran) {
                    peran.name = `personel[${idx}][peran]`;
                    peran.id = `${wrapper.id}-${idx}-peran`;
                    const label = row.querySelector(`label[for$="-peran"]`);
                    if (label) { label.setAttribute('for', peran.id); label.textContent = `Peran Anggota ${idx + 1}`; }
                }
                if (instansi) {
                    instansi.name = `personel[${idx}][instansi]`;
                    instansi.id = `${wrapper.id}-${idx}-instansi`;
                    const label = row.querySelector(`label[for$="-instansi"]`);
                    if (label) { label.setAttribute('for', instansi.id); label.textContent = `Instansi atau NIM Anggota ${idx + 1}`; }
                }
                if (hapus) {
                    hapus.setAttribute('aria-label', `Hapus baris anggota ${idx + 1}`);
                }
            });
        }

        function addRow(wrapper) {
            const tbody = wrapper.querySelector('[data-personel-body]');
            const alertBox = wrapper.querySelector('[data-personel-alert]');
            if (alertBox) alertBox.classList.add('hidden');

            const index = tbody.querySelectorAll('tr.baris-personel').length;
            const identityRequired = wrapper.dataset.identityRequired === '1';
            const tr = document.createElement('tr');
            tr.className = 'baris-personel bg-white hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="nomor-urut px-3 py-3 text-center font-medium text-gray-500">${index + 1}</td>
                <td class="px-3 py-2">
                    <label for="${wrapper.id}-${index}-nama" class="sr-only">Nama Lengkap Anggota ${index + 1}</label>
                    <input type="text" id="${wrapper.id}-${index}-nama" name="personel[${index}][nama]" required placeholder="Nama anggota"
                           class="input-personel-nama w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </td>
                <td class="px-3 py-2">
                    <label for="${wrapper.id}-${index}-peran" class="sr-only">Peran Anggota ${index + 1}</label>
                    <input type="text" id="${wrapper.id}-${index}-peran" name="personel[${index}][peran]" required placeholder="Contoh: Anggota / Asisten"
                           class="input-personel-peran w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </td>
                <td class="px-3 py-2">
                    <label for="${wrapper.id}-${index}-instansi" class="sr-only">Instansi atau NIM Anggota ${index + 1}</label>
                    <input type="text" id="${wrapper.id}-${index}-instansi" name="personel[${index}][instansi]" ${identityRequired ? 'required' : ''} placeholder="Instansi / NIM / NIDN"
                           class="input-personel-instansi w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" data-personel-remove aria-label="Hapus baris anggota ${index + 1}" title="Hapus anggota ini"
                            class="btn-hapus-personel min-h-[44px] min-w-[44px] p-2.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            const input = tr.querySelector('.input-personel-nama');
            if (input) input.focus();
        }

        // Kompatibilitas global untuk test/markup lama.
        window.tambahBarisPersonel = function (btn) {
            const wrapper = btn ? btn.closest('.personel-wrapper') : document.querySelector('.personel-wrapper');
            if (wrapper) addRow(wrapper);
        };
        window.hapusBarisPersonel = function (btn) {
            const wrapper = btn ? btn.closest('.personel-wrapper') : document.querySelector('.personel-wrapper');
            const row = btn ? btn.closest('tr.baris-personel') : null;
            if (wrapper && row) {
                row.remove();
                renumber(wrapper);
            }
        };

        document.addEventListener('click', function (e) {
            const add = e.target.closest('[data-personel-add]');
            if (add) {
                const wrapper = add.closest('.personel-wrapper');
                if (wrapper) addRow(wrapper);
                return;
            }

            const remove = e.target.closest('[data-personel-remove]');
            if (remove) {
                const wrapper = remove.closest('.personel-wrapper');
                const row = remove.closest('tr.baris-personel');
                if (wrapper && row) {
                    row.remove();
                    renumber(wrapper);
                }
            }
        });

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            form.querySelectorAll('.personel-wrapper').forEach(function (wrapper) {
                const identityRequired = wrapper.dataset.identityRequired === '1';
                const alertBox = wrapper.querySelector('[data-personel-alert]');
                const alertText = wrapper.querySelector('[data-personel-alert-text]');
                let hasEmpty = false;
                let firstInvalid = null;

                wrapper.querySelectorAll('tr.baris-personel').forEach(function (row) {
                    const nama = row.querySelector('.input-personel-nama');
                    const peran = row.querySelector('.input-personel-peran');
                    const instansi = row.querySelector('.input-personel-instansi');
                    const checks = [
                        [nama, nama && nama.value.trim() === ''],
                        [peran, peran && peran.value.trim() === ''],
                        [instansi, identityRequired && instansi && instansi.value.trim() === ''],
                    ];

                    checks.forEach(function ([input, invalid]) {
                        if (!input) return;
                        input.classList.toggle('border-red-500', invalid);
                        input.classList.toggle('bg-red-50', invalid);
                        if (invalid) {
                            hasEmpty = true;
                            if (!firstInvalid) firstInvalid = input;
                        }
                    });
                });

                if (hasEmpty) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (alertBox) {
                        if (alertText) {
                            alertText.textContent = identityRequired
                                ? 'Harap lengkapi kolom Nama, Peran, dan Instansi/NIM pada seluruh baris anggota sebelum mengajukan surat.'
                                : 'Harap lengkapi kolom Nama dan Peran pada seluruh baris anggota sebelum mengajukan surat.';
                        }
                        alertBox.classList.remove('hidden');
                        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (firstInvalid) firstInvalid.focus();
                } else if (alertBox) {
                    alertBox.classList.add('hidden');
                }
            });
        }, true);
    })();
</script>
@endonce
