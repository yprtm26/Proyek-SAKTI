@php
    // =====================================================================
    // B-10 B — Komponen daftar anggota/personel kegiatan (dynamic rows)
    //
    // Parameter opsional:
    // - $field            : nama field (default: anggota)
    // - $identityKey      : kunci identitas per baris (instansi/npm)
    // - $identityLabel    : label kolom identitas
    // - $pengusulNama     : nama pengusul (baris pertama, read-only)
    // - $pengusulIdentitas: NIM/NIDN pengusul (baris pertama)
    // - $rows             : data awal (dipakai saat edit draf / old input)
    // =====================================================================
    $field = $field ?? 'anggota';
    $identityKey = $identityKey ?? 'instansi';
    $identityLabel = $identityLabel ?? 'Instansi / Unit';
    $pengusulNama = $pengusulNama ?? (auth()->user()->name ?? '');
    $pengusulIdentitas = $pengusulIdentitas ?? '';
    $minRows = $minRows ?? 1;

    $barisAwal = old($field, $rows ?? []);
    $barisAwal = is_array($barisAwal) ? array_values($barisAwal) : [];

    if (count($barisAwal) === 0) {
        $barisAwal = [['nama' => $pengusulNama]];
    }

    // Baris pertama selalu pengusul dan namanya dikunci dari akun login.
    $barisAwal[0]['nama'] = $pengusulNama;

    if ($identityKey === 'npm' && $pengusulIdentitas !== '') {
        $barisAwal[0][$identityKey] = $pengusulIdentitas;
    }

    $templateId = 'personel-template-' . $field;

    $inputClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2';
    $readonlyClass = 'w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-gray-700 cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2';
    $tombolHapusClass = 'inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border border-red-300 px-2 text-lg font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2';
@endphp

<div class="personel-editor"
     data-personel-field="{{ $field }}"
     data-personel-identity="{{ $identityKey }}"
     data-personel-min="{{ $minRows }}">

    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="w-12 border border-gray-300 px-2 py-2">No</th>
                    <th scope="col" class="border border-gray-300 px-2 py-2 text-left">Nama</th>
                    <th scope="col" class="border border-gray-300 px-2 py-2 text-left">Peran</th>
                    <th scope="col" class="border border-gray-300 px-2 py-2 text-left">{{ $identityLabel }}</th>
                    <th scope="col" class="w-16 border border-gray-300 px-2 py-2">
                        <span class="sr-only">Aksi</span>
                    </th>
                </tr>
            </thead>

            <tbody data-personel-body>
                @foreach($barisAwal as $i => $baris)
                    <tr data-personel-row>
                        <td data-personel-number class="border border-gray-300 px-2 py-2 text-center">
                            {{ $i + 1 }}
                        </td>

                        {{-- NAMA --}}
                        <td class="border border-gray-300 px-2 py-2">
                            @if($i === 0)
                                <input type="text"
                                       name="{{ $field }}[{{ $i }}][nama]"
                                       value="{{ $pengusulNama }}"
                                       readonly
                                       data-personel-nama
                                       aria-label="Nama pengusul"
                                       class="{{ $readonlyClass }}">
                            @else
                                <input type="text"
                                       name="{{ $field }}[{{ $i }}][nama]"
                                       value="{{ $baris['nama'] ?? '' }}"
                                       required
                                       data-personel-nama
                                       aria-label="Nama anggota baris {{ $i + 1 }}"
                                       class="{{ $inputClass }}">
                            @endif

                            @error($field . '.' . $i . '.nama')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- PERAN --}}
                        <td class="border border-gray-300 px-2 py-2">
                            <input type="text"
                                   name="{{ $field }}[{{ $i }}][peran]"
                                   value="{{ $baris['peran'] ?? ($baris['keterangan'] ?? '') }}"
                                   required
                                   data-personel-peran
                                   aria-label="Peran anggota baris {{ $i + 1 }}"
                                   placeholder="Contoh: Ketua / Anggota"
                                   class="{{ $inputClass }}">

                            @error($field . '.' . $i . '.peran')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- IDENTITAS (INSTANSI / NIM) --}}
                        <td class="border border-gray-300 px-2 py-2">
                            <input type="text"
                                   name="{{ $field }}[{{ $i }}][{{ $identityKey }}]"
                                   value="{{ $baris[$identityKey] ?? ($identityKey === 'npm' ? ($baris['npm'] ?? '') : '') }}"
                                   data-personel-identity
                                   aria-label="{{ $identityLabel }} anggota baris {{ $i + 1 }}"
                                   placeholder="{{ $identityKey === 'npm' ? 'NIM' : 'Contoh: Universitas YARSI' }}"
                                   @if($i === 0 && $identityKey === 'npm')
                                       readonly
                                   @endif
                                   @if($identityKey === 'npm' && $i > 0)
                                       required
                                   @endif
                                   class="{{ $i === 0 && $identityKey === 'npm' ? $readonlyClass : $inputClass }}">

                            @error($field . '.' . $i . '.' . $identityKey)
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        {{-- AKSI HAPUS --}}
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            @if($i > 0)
                                <button type="button"
                                        data-personel-remove
                                        aria-label="Hapus anggota baris {{ $i + 1 }}"
                                        class="{{ $tombolHapusClass }}">
                                    ✕
                                </button>
                            @else
                                <span class="text-xs text-gray-500">Pengusul</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button type="button"
            data-personel-add
            class="mt-3 inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
        + Tambah Anggota
    </button>

    <p class="mt-2 text-xs text-gray-500">
        Baris pertama adalah pengusul dan tidak dapat dihapus.
        Nama dan peran setiap anggota wajib diisi.
    </p>

    <p data-personel-error
       class="mt-2 hidden text-sm font-medium text-red-600"
       role="alert"
       aria-live="polite"></p>
</div>

{{-- Template baris baru (di-clone oleh JavaScript) --}}
<template id="{{ $templateId }}">
    <tr data-personel-row>
        <td data-personel-number class="border border-gray-300 px-2 py-2 text-center"></td>

        <td class="border border-gray-300 px-2 py-2">
            <input type="text"
                   name="{{ $field }}[__INDEX__][nama]"
                   required
                   data-personel-nama
                   aria-label="Nama anggota baru"
                   class="{{ $inputClass }}">
        </td>

        <td class="border border-gray-300 px-2 py-2">
            <input type="text"
                   name="{{ $field }}[__INDEX__][peran]"
                   required
                   data-personel-peran
                   aria-label="Peran anggota baru"
                   placeholder="Contoh: Ketua / Anggota"
                   class="{{ $inputClass }}">
        </td>

        <td class="border border-gray-300 px-2 py-2">
            <input type="text"
                   name="{{ $field }}[__INDEX__][{{ $identityKey }}]"
                   data-personel-identity
                   aria-label="{{ $identityLabel }} anggota baru"
                   placeholder="{{ $identityKey === 'npm' ? 'NIM' : 'Contoh: Universitas YARSI' }}"
                   @if($identityKey === 'npm')
                       required
                   @endif
                   class="{{ $inputClass }}">
        </td>

        <td class="border border-gray-300 px-2 py-2 text-center">
            <button type="button"
                    data-personel-remove
                    aria-label="Hapus anggota baru"
                    class="{{ $tombolHapusClass }}">
                ✕
            </button>
        </td>
    </tr>
</template>

@once
<script>
    (function () {
        if (window.__personelEditorReady) {
            return;
        }
        window.__personelEditorReady = true;

        function renumber(editor) {
            const rows = Array.from(editor.querySelectorAll('[data-personel-row]'));

            rows.forEach(function (row, index) {
                const nomor = row.querySelector('[data-personel-number]');
                if (nomor) {
                    nomor.textContent = index + 1;
                }

                const nama = row.querySelector('[data-personel-nama]');
                if (nama) {
                    nama.setAttribute(
                        'aria-label',
                        index === 0 ? 'Nama pengusul' : 'Nama anggota baris ' + (index + 1)
                    );
                }

                const peran = row.querySelector('[data-personel-peran]');
                if (peran) {
                    peran.setAttribute('aria-label', 'Peran anggota baris ' + (index + 1));
                }

                const identitas = row.querySelector('[data-personel-identity]');
                if (identitas) {
                    identitas.setAttribute('aria-label', 'Identitas anggota baris ' + (index + 1));
                }

                const hapus = row.querySelector('[data-personel-remove]');
                if (hapus) {
                    hapus.setAttribute('aria-label', 'Hapus anggota baris ' + (index + 1));
                }
            });
        }

        function fokusTanpaScroll(element) {
            if (!element) {
                return;
            }

            try {
                element.focus({ preventScroll: true });
                element.scrollIntoView({ block: 'center', behavior: 'smooth' });
            } catch (error) {
                element.focus();
            }
        }

        document.addEventListener('click', function (event) {
            const tambah = event.target.closest('[data-personel-add]');

            if (tambah) {
                const editor = tambah.closest('.personel-editor');
                const template = document.getElementById(
                    'personel-template-' + editor.dataset.personelField
                );
                const body = editor.querySelector('[data-personel-body]');

                if (!template || !body) {
                    return;
                }

                const index = body.querySelectorAll('[data-personel-row]').length;
                const html = template.innerHTML.replace(/__INDEX__/g, index);
                const holder = document.createElement('tbody');
                holder.innerHTML = html.trim();

                const row = holder.firstElementChild;
                if (!row) {
                    return;
                }

                body.appendChild(row);
                renumber(editor);
                fokusTanpaScroll(row.querySelector('input'));

                return;
            }

            const hapus = event.target.closest('[data-personel-remove]');

            if (hapus) {
                const editor = hapus.closest('.personel-editor');
                const row = hapus.closest('[data-personel-row]');

                if (!editor || !row) {
                    return;
                }

                const tombolTambah = editor.querySelector('[data-personel-add]');
                row.remove();
                renumber(editor);
                fokusTanpaScroll(tombolTambah);
            }
        });

        // Blokir submit jika ada baris yang nama atau perannya kosong.
        document.addEventListener('submit', function (event) {
            const form = event.target;

            if (!form || form.tagName !== 'FORM') {
                return;
            }

            form.querySelectorAll('.personel-editor').forEach(function (editor) {
                const kotakError = editor.querySelector('[data-personel-error]');
                let pertamaTidakValid = null;

                editor.querySelectorAll('[data-personel-row]').forEach(function (row) {
                    ['nama', 'peran'].forEach(function (kunci) {
                        const input = row.querySelector('[data-personel-' + kunci + ']');

                        if (!input) {
                            return;
                        }

                        const kosong = input.value.trim() === '';
                        input.setAttribute('aria-invalid', kosong ? 'true' : 'false');

                        if (kosong && !pertamaTidakValid) {
                            pertamaTidakValid = input;
                        }
                    });
                });

                if (pertamaTidakValid) {
                    event.preventDefault();

                    if (kotakError) {
                        kotakError.textContent =
                            'Nama dan peran setiap anggota wajib diisi sebelum surat diajukan.';
                        kotakError.classList.remove('hidden');
                    }

                    fokusTanpaScroll(pertamaTidakValid);
                } else if (kotakError) {
                    kotakError.textContent = '';
                    kotakError.classList.add('hidden');
                }
            });
        }, true);
    })();
</script>
@endonce
