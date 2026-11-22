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

<div class="bg-gray-50 border border-gray-200 rounded-xl p-5 my-6" id="wrapper-tabel-personel">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                onclick="tambahBarisPersonel()"
                aria-label="Tambah baris anggota kegiatan baru"
                class="min-h-[44px] min-w-[44px] px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg inline-flex items-center justify-center gap-2 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Anggota</span>
        </button>
    </div>

    {{-- ALERT VALIDASI KLIEN JIKA ADA BARIS KOSONG --}}
    <div id="alert-personel-kosong" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Harap lengkapi kolom <strong>Nama</strong> dan <strong>Peran</strong> pada seluruh baris anggota sebelum mengajukan surat.</span>
    </div>

    {{-- TABEL RESPONSIVE ANGGOTA --}}
    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th scope="col" class="w-12 px-3 py-3 text-center">No</th>
                    <th scope="col" class="px-3 py-3 text-left">Nama Lengkap <span class="text-red-500">*</span></th>
                    <th scope="col" class="px-3 py-3 text-left">Peran dalam Kegiatan <span class="text-red-500">*</span></th>
                    <th scope="col" class="px-3 py-3 text-left">Instansi / NIM / NIDN</th>
                    <th scope="col" class="w-20 px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody-personel" class="divide-y divide-gray-100">
                {{-- BARIS 1 (DEFAULT: PENGUSUL SEBAGAI KETUA / ANGGOTA UTAMA) --}}
                @php
                    $initialUser = auth()->user();
                    $initialNama = $initialUser ? $initialUser->name : '';
                    $initialIdentitas = $initialUser ? ($initialUser->nidn ?? $initialUser->npm ?? '') : '';
                @endphp
                <tr class="baris-personel bg-white hover:bg-gray-50 transition-colors" data-index="0">
                    <td class="nomor-urut px-3 py-3 text-center font-medium text-gray-500">1</td>
                    <td class="px-3 py-2">
                        <label for="personel-0-nama" class="sr-only">Nama Lengkap Anggota 1</label>
                        <input type="text"
                               id="personel-0-nama"
                               name="personel[0][nama]"
                               value="{{ old('personel.0.nama', $initialNama) }}"
                               required
                               placeholder="Nama anggota"
                               class="input-personel-nama w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="px-3 py-2">
                        <label for="personel-0-peran" class="sr-only">Peran Anggota 1</label>
                        <input type="text"
                               id="personel-0-peran"
                               name="personel[0][peran]"
                               value="{{ old('personel.0.peran', 'Ketua / Penanggung Jawab') }}"
                               required
                               placeholder="Contoh: Ketua Tim / Narasumber"
                               class="input-personel-peran w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="px-3 py-2">
                        <label for="personel-0-instansi" class="sr-only">Instansi atau NIM Anggota 1</label>
                        <input type="text"
                               id="personel-0-instansi"
                               name="personel[0][instansi]"
                               value="{{ old('personel.0.instansi', $initialIdentitas ? 'Universitas YARSI (' . $initialIdentitas . ')' : 'Universitas YARSI') }}"
                               placeholder="Instansi / Unit / NIM"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="px-3 py-2 text-center text-xs text-gray-400">
                        <span class="inline-block py-1 px-2 bg-gray-100 rounded text-gray-500 font-medium">Utama</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- SCRIPT INTERAKTIF DINAMIS (B-10 B) --}}
<script>
    let barisIndexCounter = 1;

    function tambahBarisPersonel() {
        const tbody = document.getElementById('tbody-personel');
        const alertBox = document.getElementById('alert-personel-kosong');
        if (alertBox) alertBox.classList.add('hidden');

        const currentRowsCount = tbody.querySelectorAll('tr.baris-personel').length;
        const newNo = currentRowsCount + 1;
        const currentIndex = barisIndexCounter++;

        const tr = document.createElement('tr');
        tr.className = 'baris-personel bg-white hover:bg-gray-50 transition-colors';
        tr.setAttribute('data-index', currentIndex);

        tr.innerHTML = `
            <td class="nomor-urut px-3 py-3 text-center font-medium text-gray-500">${newNo}</td>
            <td class="px-3 py-2">
                <label for="personel-${currentIndex}-nama" class="sr-only">Nama Lengkap Anggota ${newNo}</label>
                <input type="text"
                       id="personel-${currentIndex}-nama"
                       name="personel[${currentIndex}][nama]"
                       required
                       placeholder="Nama anggota"
                       class="input-personel-nama w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </td>
            <td class="px-3 py-2">
                <label for="personel-${currentIndex}-peran" class="sr-only">Peran Anggota ${newNo}</label>
                <input type="text"
                       id="personel-${currentIndex}-peran"
                       name="personel[${currentIndex}][peran]"
                       required
                       placeholder="Contoh: Anggota / Asisten"
                       class="input-personel-peran w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </td>
            <td class="px-3 py-2">
                <label for="personel-${currentIndex}-instansi" class="sr-only">Instansi atau NIM Anggota ${newNo}</label>
                <input type="text"
                       id="personel-${currentIndex}-instansi"
                       name="personel[${currentIndex}][instansi]"
                       placeholder="Instansi / NIM / NIDN"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </td>
            <td class="px-3 py-2 text-center">
                {{-- TOMBOL HAPUS BARIS (TARGET KLIK >= 44x44px, FOCUS RING KONTRAS >= 3:1) --}}
                <button type="button"
                        onclick="hapusBarisPersonel(this)"
                        aria-label="Hapus baris anggota ${newNo}"
                        title="Hapus anggota ini"
                        class="btn-hapus-personel min-h-[44px] min-w-[44px] p-2.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Aksesibilitas: Pindahkan fokus otomatis ke input Nama pada baris baru
        const inputNamaBaru = tr.querySelector('.input-personel-nama');
        if (inputNamaBaru) {
            inputNamaBaru.focus();
        }
    }

    function hapusBarisPersonel(buttonEl) {
        const row = buttonEl.closest('tr.baris-personel');
        if (row) {
            row.remove();
            renumberPersonelRows();
        }
    }

    function renumberPersonelRows() {
        const rows = document.querySelectorAll('#tbody-personel tr.baris-personel');
        rows.forEach((row, idx) => {
            const noCell = row.querySelector('.nomor-urut');
            if (noCell) noCell.textContent = idx + 1;

            const btnHapus = row.querySelector('.btn-hapus-personel');
            if (btnHapus) {
                btnHapus.setAttribute('aria-label', `Hapus baris anggota ${idx + 1}`);
            }
        });
    }

    // Validasi Form Submit: Blokir jika ada baris dengan Nama atau Peran kosong
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('#wrapper-tabel-personel')?.closest('form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('#tbody-personel tr.baris-personel');
            let hasEmptyField = false;
            let firstInvalidInput = null;

            rows.forEach((row) => {
                const namaInput = row.querySelector('.input-personel-nama');
                const peranInput = row.querySelector('.input-personel-peran');

                const namaVal = namaInput ? namaInput.value.trim() : '';
                const peranVal = peranInput ? peranInput.value.trim() : '';

                if (namaVal === '' || peranVal === '') {
                    hasEmptyField = true;
                    if (!firstInvalidInput) {
                        firstInvalidInput = namaVal === '' ? namaInput : peranInput;
                    }
                    if (namaInput && namaVal === '') namaInput.classList.add('border-red-500', 'bg-red-50');
                    if (peranInput && peranVal === '') peranInput.classList.add('border-red-500', 'bg-red-50');
                } else {
                    if (namaInput) namaInput.classList.remove('border-red-500', 'bg-red-50');
                    if (peranInput) peranInput.classList.remove('border-red-500', 'bg-red-50');
                }
            });

            if (hasEmptyField) {
                e.preventDefault();
                e.stopPropagation();

                const alertBox = document.getElementById('alert-personel-kosong');
                if (alertBox) {
                    alertBox.classList.remove('hidden');
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                }
            }
        });
    });
</script>
