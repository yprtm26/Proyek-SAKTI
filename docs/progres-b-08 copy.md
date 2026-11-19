# Progres B-08 — Input Prodi, Semester, dan Dosen melalui Dropdown

**Status:** Selesai (100% — Siap dipindahkan ke Review/Done)  
**Papan Trello:** Sprint 2 — Card B-08  
**Referensi:** `docs/progres-sprint-2.md`  
**Tanggal:** 18 November 2026

---

## 1. Ringkasan Fitur

B-08 menyediakan **satu komponen combobox aksesibel** yang dipakai ulang untuk memilih:

- **Program studi** — master tabel `prodi` (TI, PdSI),
- **Semester** — master tabel `semester` dengan dua kategori: `paruh` (Ganjil/Genap) dan `tingkat` (Semester 1–8),
- **Dosen** — data pengguna aktif dengan `role = 'dosen'` pada tabel `users` (tanpa menduplikasi data dosen).

Dropdown dapat dicari, dioperasikan penuh dengan keyboard, dan ID yang dikirim selalu divalidasi ke database. ID yang tidak terdaftar ditolak dengan **HTTP 422** pada permintaan JSON/API.

---

## 2. Acceptance Criteria

| # | Kriteria | Status | Bukti |
|---|---|:---:|---|
| AC-1 | Dropdown prodi, semester, dan dosen menampilkan data master valid dari database dan dapat dicari (*searchable*) | ✅ | Endpoint `/api/master/prodi`, `/api/master/semester`, `/api/master/dosen`; filter `q`; test `MasterDataDropdownTest` |
| AC-2 | Dropdown dapat dibuka, dipilih, dan ditutup menggunakan **Tab, Enter, Escape** | ✅ | `public/js/combobox.js` + `tests/js/combobox.test.mjs` (9 test) |
| AC-3 | Server menolak submit dan merespons **422** bila ID prodi/semester/dosen tidak ada di database | ✅ | `App\Support\MasterData` (`Rule::exists`), `/api/master/validasi`, feature test 422 |

---

## 3. Arsitektur

```text
database/migrations/2026_11_18_000001_create_prodi_table.php
database/migrations/2026_11_18_000002_create_semester_table.php
app/Models/Prodi.php
app/Models/Semester.php
app/Support/MasterData.php          <- aturan exists + query option
database/seeders/ProdiSeeder.php
database/seeders/SemesterSeeder.php

routes/web.php
  GET  /api/master/prodi
  GET  /api/master/semester?jenis=paruh|tingkat
  GET  /api/master/dosen
  GET|POST /api/master/validasi

resources/views/components/combobox.blade.php   <- komponen Blade reusable
public/js/combobox.js                           <- logika keyboard + DOM
resources/views/layouts/app.blade.php            <- memuat modul combobox

tests/Feature/MasterDataDropdownTest.php
tests/js/combobox.test.mjs
```

### Master data

| Tabel | Kolom penting | Isi seeder |
|---|---|---|
| `prodi` | `kode`, `nama`, `jenjang`, `aktif` | `TI` Teknik Informatika, `PdSI` Perpustakaan dan Sains Informasi |
| `semester` | `kode`, `nama`, `jenis` (`paruh`/`tingkat`), `urutan`, `aktif` | Ganjil, Genap, Semester 1–8 |

Data dosen memakai `users` dengan `role = 'dosen'`; label dropdown = `name`, deskripsi = NIDN dan prodi.

---

## 4. Komponen Dropdown

`resources/views/components/combobox.blade.php` menerima properti:

```blade
<x-combobox
    name="semester"
    id="semester-aktif"
    label="Semester Aktif"
    endpoint="{{ url('/api/master/semester') }}"
    :params="['jenis' => 'paruh']"
    :value="old('semester')"
    :value-label="$semesterLama?->nama"
    placeholder="-- Pilih Semester --"
    search-placeholder="Cari semester..."
    required
/>
```

Perilaku keyboard:

| Tombol | Saat tertutup | Saat terbuka |
|---|---|---|
| **Tab** | Membuka dropdown dan memindahkan fokus ke kolom cari | Menutup dropdown dan melanjutkan fokus ke elemen berikutnya |
| **Enter** | Membuka dropdown | Memilih opsi aktif |
| **Escape** | — | Menutup dropdown dan mengembalikan fokus ke tombol pemicu |
| Arrow Down/Up | Membuka dropdown | Memindahkan opsi aktif (dengan *wrap-around*) |
| Home/End | — | Ke opsi pertama/terakhir |
| Spasi | Membuka dropdown | Memilih opsi aktif (hanya dari tombol) |

Atribut ARIA yang dipakai: `role="combobox"`, `aria-haspopup="listbox"`, `aria-expanded`, `aria-controls`, `role="listbox"`, `role="option"`, `aria-selected`, `aria-activedescendant`, dan `role="searchbox"`. Fokus memakai `focus-visible:ring-2 ring-orange-600` agar kontras outline memadai.

---

## 5. Validasi Server

`App\Support\MasterData` menjadi sumber aturan tunggal:

```php
MasterData::prodiRules(true);              // exists:prodi,id + aktif
MasterData::semesterRules(true, 'paruh');  // exists:semester,id + aktif + jenis
MasterData::dosenRules(true);              // exists:users,id + role=dosen
```

- Permintaan **JSON/AJAX** yang gagal validasi mendapat **422** beserta `errors`.
- Pengiriman form biasa tetap mendapat redirect + pesan error Laravel (perilaku standar browser), namun tidak ada data yang disimpan.
- Endpoint `/api/master/validasi` dapat dipakai untuk memeriksa ID sebelum submit dan selalu mengembalikan 422 untuk ID yang tidak valid.

Selain memvalidasi, server **mengisi label** dari master agar dokumen lama tetap terbaca:

```php
$validated['semester_id'] = $semester->id;
$validated['semester']    = $semester->nama; // "Ganjil", "Semester 3", dst.
$validated['prodi_id']    = $prodi->id;
$validated['prodi']       = $prodi->kode;    // TI / PdSI
```

---

## 6. Titik Integrasi Form

| Form | Field | Master |
|---|---|---|
| Surat Keterangan Aktif | `semester` | semester `paruh` |
| Surat Permohonan Aktif Akademik | `semester` | semester `paruh` |
| Surat Permohonan Magang (pengaju + baris tambahan) | `mahasiswa[i][semester]` | semester `tingkat` |
| Surat Tugas Pengembangan Modul | `prodi_id`, `semester` | prodi + semester `paruh` |
| Surat Tugas Pengembangan RPS | `prodi_id`, `semester` | prodi + semester `paruh` |
| Surat Tugas Kegiatan Kuliah | `dosen_id` | dosen (`users.role = dosen`) |
| Form dinamis (`form-dinamis.blade.php`) | field `semester` | semester `paruh` |

Catatan: pada form identitas mahasiswa, prodi tetap dikunci ke akun pengusul sesuai aturan B-02. Prodi yang dapat dipilih berada pada form dosen yang memang menentukan prodi mata kuliah/kegiatan.

---

## 7. Bukti Pengujian

### Feature test

```text
php artisan test --filter=MasterDataDropdownTest

PASS  Tests\Feature\MasterDataDropdownTest
 ✓ api prodi hanya mengembalikan master aktif dan dapat dicari
 ✓ api semester dapat disaring paruh dan tingkat
 ✓ api dosen hanya mengembalikan pengguna berperan dosen
 ✓ api master mewajibkan autentikasi
 ✓ api validasi mengembalikan 422 untuk id master yang tidak ada
 ✓ submit dengan semester tidak valid ditolak 422 dan tidak menyimpan surat
 ✓ submit semester valid menyimpan id dan label master
 ✓ submit magang dengan semester tingkat tidak valid ditolak 422
 ✓ submit dosen dengan prodi atau semester tidak valid ditolak 422
 ✓ submit dosen dengan master valid menyimpan label dan mengunci identitas
 ✓ submit dosen penugas tidak valid ditolak 422
 ✓ submit dosen penugas valid menyimpan nama dari master
 ✓ form mahasiswa menampilkan dropdown semester termasuk baris magang
 ✓ form dinamis memakai combobox semester
 ✓ form menampilkan combobox aksesibel untuk prodi semester dan dosen

Tests: 15 passed (102 assertions)
```

### Unit test JavaScript (keyboard navigation)

```text
node --test tests/js

✔ normalizeText menghapus spasi tepi, huruf besar, dan aksen
✔ filterOptions mencari berdasarkan label dan kode tanpa membedakan huruf besar
✔ filterOptions mencocokkan deskripsi dan mengembalikan kosong bila tidak ada
✔ clampIndex dan moveIndex menjaga indeks tetap di dalam rentang
✔ Enter membuka dropdown saat tertutup dan memilih opsi saat terbuka
✔ Escape menutup dropdown dan mengembalikan fokus ke pemicu
✔ Tab membuka dropdown saat tertutup dan menutupnya saat terbuka
✔ tombol panah membuka dropdown atau memindahkan opsi aktif
✔ spasi hanya membuka dropdown dari tombol, bukan saat mengetik pencarian

tests 9
pass 9
fail 0
```

### Regresi

```text
php artisan test --filter=PemilihanTemplateAktifTest   -> 19 passed
php artisan test --filter=PengajuanTerikatKeAkunTest   ->  5 passed
php artisan test --filter=HakAksesRoleTest             ->  9 passed
```

### Skenario uji manual (browser)

| Langkah | Hasil yang diharapkan |
|---|---|
| Tab sampai fokus di tombol dropdown | Tombol menampilkan focus ring oranye; `aria-expanded="false"` |
| Tekan **Tab** saat tombol fokus | Dropdown terbuka, fokus pindah ke kolom pencarian, `aria-expanded="true"` |
| Ketik `ganjil` / `teknik` | Daftar tersaring tanpa membedakan huruf besar/kecil |
| Tekan **ArrowDown** lalu **Enter** | Opsi aktif terpilih, nilai tersimpan di input tersembunyi, popup tertutup |
| Tekan **Tab** saat popup terbuka | Popup tertutup dan fokus lanjut ke elemen form berikutnya |
| Tekan **Escape** saat popup terbuka | Popup tertutup dan fokus kembali ke tombol pemicu |
| Kirim `POST` JSON dengan `semester_id`/`prodi_id`/`dosen_id` palsu | Respons **422** dengan `errors` pada field terkait |

---

## 8. Cara Menjalankan

```bash
php artisan migrate
php artisan db:seed --class=ProdiSeeder
php artisan db:seed --class=SemesterSeeder

# atau sekaligus saat pengembangan
php artisan migrate --seed
```

Tidak ada proses build frontend: `public/js/combobox.js` adalah ES module statis yang dimuat langsung dari layout.

---

## 9. Catatan

- `public/storage` symlink yang belum ada tidak memengaruhi B-08 karena tidak ada berkas lampiran baru.
- Pencarian dilakukan di sisi klien setelah data master dimuat; endpoint tetap mendukung `?q=` untuk pencarian di server.
- `LoginLockoutTest` tidak dijalankan ulang pada verifikasi B-08 karena memanggil LDAP nyata dan lambat; perubahan B-08 tidak menyentuh autentikasi.
