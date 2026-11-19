# Progres B-09 — Pemilihan Tanggal Awal dan Akhir Kegiatan (Date Picker)

**Status:** Selesai (100% — Siap dipindahkan ke Review/Done)  
**Papan Trello:** Sprint 2 — Card B-09  
**Referensi:** `docs/progres-sprint-2.md`  
**Tanggal:** 18 November 2026

---

## 1. Ringkasan Fitur

B-09 menyediakan **date picker aksesibel** untuk memilih tanggal awal dan tanggal
akhir kegiatan. Nilai selalu dipertukarkan dalam format `YYYY-MM-DD`, baik ketika
pengguna memilih dari kalender visual maupun mengetik langsung ke input.

Perubahan utama:

- Input tanggal kini memiliki **tombol kalender** dan popover kalender yang dapat
  dinavigasi dengan keyboard.
- Seluruh input tanggal pada halaman aplikasi otomatis ditingkatkan
  (*progressive enhancement*), termasuk form pengajuan, form dinamis, dan filter
  tanggal lain. Jika JavaScript gagal dimuat, input `type="date"` bawaan browser
  tetap dapat dipakai.
- Server menolak tanggal selesai yang lebih awal daripada tanggal mulai dengan
  **HTTP 422** (JSON) atau redirect + pesan validasi (form biasa).
- Tanggal yang tidak memakai format `YYYY-MM-DD` juga ditolak di server.

---

## 2. Acceptance Criteria

| # | Kriteria | Status | Bukti |
|---|---|:---:|---|
| AC-1 | Pengusul dapat memilih tanggal mulai dan selesai lewat date picker interaktif atau input `YYYY-MM-DD` | ✅ | `x-date-field`, `public/js/datepicker.js`; form magang & form dinamis |
| AC-2 | Kalender dapat dibuka, dinavigasi dengan keyboard, dan ditutup dengan **Escape** tanpa kehilangan fokus | ✅ | `resolveDateKey`, pengujian `tests/js/datepicker.test.mjs` (23 test) |
| AC-3 | Sistem menolak tanggal selesai sebelum tanggal mulai | ✅ | `App\Support\DateRange`, `tests/Feature/DateRangeValidationTest.php` |
| AC-4 | Kontras teks dan focus ring memadai (WCAG AA) | ✅ | `public/css/datepicker.css`: teks `#111827`, aksen `#c2410c` + teks putih (rasio ±5.2:1), outline 3px |

---

## 3. Arsitektur

```text
app/Support/DateRange.php                        <- aturan + pesan validasi tanggal
routes/web.php                                   <- penerapan aturan pada form magang & form dinamis
resources/views/components/date-field.blade.php  <- komponen Blade reusable
public/js/datepicker.js                          <- logika murni + kalender DOM
public/css/datepicker.css                        <- kontras, focus ring, target sentuh 44px
resources/views/layouts/app.blade.php            <- memuat CSS & modul JS

resources/views/mahasiswa/surat/form/surat-permohonan-magang.blade.php
resources/views/surat/form-dinamis.blade.php

tests/Feature/DateRangeValidationTest.php
tests/js/datepicker.test.mjs
```

### Alur nilai tanggal

```text
ketikan pengguna / kalender
        │
        ▼
input[type="date"]  ── nilai selalu YYYY-MM-DD ──►  request HTTP
        │                                              │
        └── parseIsoDate() untuk tampilan kalender      ▼
                                                DateRange::rules()
                                                required + date_format:Y-m-d
                                                after_or_equal:tanggal_mulai
                                                        │
                                                        ▼
                                                simpan / 422 + pesan error
```

---

## 4. Komponen Date Picker

`resources/views/components/date-field.blade.php` menerima properti:

```blade
<x-date-field
    name="tanggal_selesai"
    id="tanggal-selesai"
    label="Tanggal Selesai"
    :value="old('tanggal_selesai')"
    :min-field="'tanggal_mulai'"
    :error-key="'tanggal_selesai'"
    hint="Tidak boleh lebih awal daripada tanggal mulai."
    required
/>
```

Atribut penting:

- `type="date"` tetap dipertahankan agar input teks `YYYY-MM-DD` dan date picker
  bawaan browser berfungsi sebagai fallback.
- `aria-haspopup="dialog"`, `aria-expanded`, dan `aria-controls` pada tombol
  kalender.
- `role="dialog"` pada popover, `role="grid"` pada tabel kalender,
  `role="gridcell"` pada setiap tanggal, serta `aria-selected` dan
  `aria-current="date"`.
- `aria-label` lengkap pada setiap tanggal (contoh: “Rabu, 18 November 2026”).
- `aria-live="polite"` untuk nama bulan dan pesan error rentang.
- `aria-invalid` dan `aria-describedby` dihubungkan ke pesan error server.

### Perilaku keyboard

| Tombol | Aksi |
|---|---|
| **Enter / Spasi** pada tombol kalender | Membuka kalender |
| **Arrow Left/Right** | Geser fokus satu hari |
| **Arrow Up/Down** | Geser fokus satu minggu |
| **Home / End** | Awal / akhir minggu (Senin–Minggu) |
| **Page Up / Page Down** | Bulan sebelumnya / berikutnya |
| **Shift + Page Up / Page Down** | Tahun sebelumnya / berikutnya |
| **Enter / Spasi** pada tanggal | Memilih tanggal, menutup kalender, fokus kembali ke input |
| **Escape** | Menutup kalender dan mengembalikan fokus ke tombol pemicu |
| **Tab** | Menutup kalender dan melanjutkan fokus ke elemen berikutnya |

### Kontras & focus ring

- Teks utama `#111827` di atas putih (kontras ±16:1).
- Tanggal terpilih: latar `#c2410c`, teks putih (kontras ±5.2:1).
- Hari nonaktif: `#6b7280` di atas `#f3f4f6` dengan garis coret.
- Focus ring: `outline: 3px solid #c2410c; outline-offset: 2px` pada tanggal,
  tombol navigasi, tombol footer, dan input.
- Target sentuh tanggal dan tombol minimal `2.75rem` (44px).
- Mendukung `forced-colors` (mode kontras tinggi Windows).

---

## 5. Validasi Server

`App\Support\DateRange` menjadi sumber aturan tunggal:

```php
DateRange::rules(['tanggal_mulai', 'tanggal_selesai']);
// tanggal_mulai   => required|date_format:Y-m-d
// tanggal_selesai => required|date_format:Y-m-d|after_or_equal:tanggal_mulai

DateRange::messages(['tanggal_mulai', 'tanggal_selesai']);
// tanggal_selesai.after_or_equal:
// "Tanggal selesai tidak boleh lebih awal daripada tanggal mulai."
```

Aturan diterapkan pada:

- `POST /mahasiswa/surat/ajukan/{id}` untuk **Surat Permohonan Magang**.
- `POST /mahasiswa/surat/ajukan/{id}` untuk form dinamis (field tanggal apa pun).
- `POST /dosen/surat/ajukan/{id}` untuk form dinamis dosen.

Field tanggal dikenali dari nama `tanggal`, `tanggal_*`, atau `*_tanggal`.
Pasangan rentang yang didukung: `tanggal_mulai`–`tanggal_selesai` dan
`tanggal_awal`–`tanggal_akhir`.

Catatan perilaku:

- Tanggal selesai **sama dengan** tanggal mulai diterima (`>=`).
- Permintaan JSON/AJAX yang gagal mendapat **422** beserta `errors`.
- Form biasa mendapat redirect + pesan error; tidak ada data yang disimpan.
- Browser juga memblokir submit lebih awal melalui `setCustomValidity()` dan
  atribut `min` yang disinkronkan dari tanggal mulai.

---

## 6. Titik Integrasi Form

| Form | Field | Perlakuan |
|---|---|---|
| Surat Permohonan Magang | `tanggal_mulai`, `tanggal_selesai` | Komponen `x-date-field`; tanggal selesai memakai `min-field="tanggal_mulai"` |
| Form dinamis mahasiswa/dosen | semua field bertipe tanggal | `x-date-field` otomatis untuk `tipeFieldSurat() === 'date'` |
| Form dosen dengan input tanggal tetap | `tanggal`, `tanggal_pelatihan`, dll. | Ditingkatkan otomatis oleh `initAll()` |
| Filter tanggal admin/kaprodi/dekan | `tanggal` | Ditingkatkan otomatis tanpa mengubah nilai atau query |

---

## 7. Bukti Pengujian

### Feature test

```text
php artisan test --filter=DateRangeValidationTest

PASS  Tests\Feature\DateRangeValidationTest
 ✓ magang menolak tanggal selesai sebelum tanggal mulai
 ✓ magang menerima tanggal selesai sama dengan tanggal mulai
 ✓ tanggal harus berformat yyyy mm dd
 ✓ form dinamis menolak rentang tanggal terbalik
 ✓ form magang menampilkan date picker aksesibel
 ✓ form dinamis merender date picker untuk field tanggal

Tests: 6 passed (32 assertions)
```

Skenario utama yang diverifikasi:

| Skenario | Hasil |
|---|---|
| `tanggal_mulai = 2026-03-10`, `tanggal_selesai = 2026-03-09` | **422**, error pada `tanggal_selesai`, pesan “Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.” |
| `tanggal_mulai = tanggal_selesai = 2026-03-10` | Diterima dan tersimpan |
| `tanggal_mulai = 05/01/2026` | **422**, pesan format `YYYY-MM-DD` |
| Form dinamis dosen dengan rentang terbalik | **422**, error pada `tanggal_selesai` |
| Form magang dirender | `type="date"`, `data-sakti-datepicker`, `aria-haspopup="dialog"`, `data-min-field` tersedia |

### Unit test JavaScript

```text
node --test tests/js

✔ parseIsoDate hanya menerima teks YYYY-MM-DD yang benar
✔ toIsoDate dan todayIsoDate menghasilkan format dua digit
✔ compareIsoDates membandingkan tanggal dan menolak format salah
✔ validateDateRange menolak tanggal selesai sebelum tanggal mulai
✔ validateDateRange menerima tanggal selesai sama dengan tanggal mulai
✔ validateDateRange membedakan input kosong dan format salah
✔ daysInMonth dan addMonths menangani akhir bulan serta tahun kabisat
✔ navigasi minggu dimulai hari Senin
✔ buildMonthMatrix membentuk 6 minggu dengan Senin sebagai kolom pertama
✔ label bulan dan tanggal panjang memakai bahasa Indonesia
✔ tombol panah memindahkan fokus harian dan mingguan
✔ PageUp dan PageDown berpindah bulan, Shift berpindah tahun
✔ Enter dan Space memilih, Escape menutup tanpa kehilangan fokus
✔ Tab menutup kalender dan membiarkan fokus berpindah

tests 23
pass 23
fail 0
```

### Regresi

```text
php artisan test --filter=MasterDataDropdownTest        -> 15 passed (102 assertions)
php artisan test --filter=PemilihanTemplateAktifTest    -> 19 passed (109 assertions)
php artisan test --filter=PengajuanTerikatKeAkunTest    ->  5 passed (20 assertions)
php artisan test --filter=HakAksesRoleTest              ->  9 passed (34 assertions)
php artisan test --filter=PenyaringanSuratTugasIndeksTest -> 2 passed (6 assertions)
```

### Skenario uji manual (browser)

| Langkah | Hasil yang diharapkan |
|---|---|
| Tekan **Tab** sampai fokus di input tanggal | Input menampilkan focus ring oranye 3px |
| Tekan tombol kalender atau **Arrow Down** pada input | Kalender terbuka, fokus berada pada tanggal terpilih/hari ini |
| Tekan **Arrow Right/Left/Up/Down** | Fokus berpindah hari/minggu tanpa keluar dari kalender |
| Tekan **Page Down** / **Shift + Page Down** | Tampilan pindah bulan / tahun |
| Tekan **Enter** pada tanggal | Nilai input terisi `YYYY-MM-DD`, kalender tertutup, fokus kembali ke input |
| Tekan **Escape** | Kalender tertutup, fokus kembali ke tombol pemicu (tidak hilang) |
| Ketik `2026-11-18` lalu submit | Nilai tersimpan sebagai `2026-11-18` |
| Isi tanggal selesai lebih awal dari tanggal mulai | Pesan error tampil; submit dicegah browser dan server menolak dengan 422 bila diteruskan |
| Isi tanggal selesai sama dengan tanggal mulai | Form dapat dikirim |

---

## 8. Cara Menjalankan

Tidak ada migrasi baru dan tidak ada proses build frontend. Aset dimuat langsung
dari folder `public`:

```bash
php artisan serve
```

Halaman yang dapat dipakai untuk verifikasi:

- `/mahasiswa/surat/ajukan/{id}` untuk template **Surat Permohonan Magang**.
- `/mahasiswa/surat/ajukan/{id}` atau `/dosen/surat/ajukan/{id}` untuk template
  dinamis yang memiliki field tanggal.

Menjalankan pengujian:

```bash
php artisan test --filter=DateRangeValidationTest
node --test tests/js
```

---

## 9. Catatan

- Date picker bersifat *progressive enhancement*: input `type="date"` tetap ada,
  sehingga aplikasi masih dapat digunakan jika JavaScript gagal dimuat.
- Validasi browser (`min` + `setCustomValidity`) hanya membantu pengalaman
  pengguna; keputusan akhir tetap berada di server melalui `DateRange`.
- Semua tanggal disimpan sebagai teks `YYYY-MM-DD` di kolom JSON `data`, sehingga
  dokumen PDF/preview yang sudah ada tidak memerlukan perubahan.
- `LoginLockoutTest` tidak dijalankan ulang karena memanggil LDAP nyata dan lambat;
  perubahan B-09 tidak menyentuh autentikasi.
