# Progres Kerja — Sprint 2 (Proyek SAKTI)

**Terakhir Diperbarui:** 18 November 2026
**Sprint Goal:** Mengembangkan fitur formulir dinamis dan interaktif (dropdown searchable master, date picker, time picker, tabel dinamis anggota kegiatan, filter indeks surat tugas), manajemen draf pengajuan mandiri (simpan, ubah, hapus permanen draf terproteksi anti-IDOR), serta penguatan keamanan autentikasi melalui mekanisme penguncian akun berjenjang (*progressive lockout*).

---

## Ringkasan Status

| ID Task | Nama Task | Status | Keterangan |
| :--- | :--- | :---: | :--- |
| **B-01 B** | Penguncian sementara saat gagal login berulang | **Selesai** | Lockout berjenjang: kelipatan 3x gagal menambah 1 menit kunci. Countdown timer aksesibel, proteksi login saat terkunci. Test lolos (8 tests, 25 assertions). |
| **B-07** | Penyaringan form surat tugas per indeks kegiatan | **Selesai** | Filter daftar formulir dosen berdasarkan Pendidikan, Penelitian, Pengabdian, Kemahasiswaan. Test lolos. |
| **B-08** | Input prodi, semester, dan dosen melalui dropdown | **Selesai** | Dropdown searchable teraksesibel (Tab/Enter/Esc), master prodi/semester/dosen, dan validasi `exists` + 422. Test lolos. |
| **B-09** | Pemilihan tanggal awal dan akhir kegiatan (date picker) | **Selesai** | Date picker aksesibel (keyboard + focus ring), format `YYYY-MM-DD`, validasi server tanggal selesai $\ge$ tanggal mulai, dan pesan error terverifikasi. Test lolos. |
| **B-10 A** | Pemilihan waktu mulai dan selesai kegiatan (time picker) | **Selesai** | Mask format 24 jam (`HH:mm`), popover visual aksesibel (WCAG AA), normalisasi pemisah titik, dan validasi jam selesai $>$ mulai pada hari yang sama. Test lolos (6 tests, 26 assertions & 11 JS unit tests). |
| **B-10 B** | Pengisian daftar anggota dan peran dalam kegiatan | **Selesai** | Tabel relasi `surat_personel`, endpoint bulk `/surat/{id}/personel`, komponen `<x-personel-list>` responsif (target >= 44x44px, focus ring >= 3:1), validasi baris kosong. Test lolos (7 feature tests + 7 JS unit tests). |
| **B-13 A** | Penyimpanan pengajuan surat sebagai draf | **Belum dikerjakan** | Status 'Draf' di DB, isolasi dari antrean TU, validasi wajib template. |
| **B-13 B** | Pembukaan dan pengubahan draf surat milik pengusul | **Selesai** | Halaman edit draf populate isian lama (termasuk template dengan form khusus), aksi kirim pengajuan ubah status jadi `menunggu`, proteksi 403 IDOR. 14 test lolos. |
| **B-14** | Penghapusan permanen draf pengajuan surat | **Selesai** | Modal konfirmasi focus trap & Escape (`modalHapusDraf`), endpoint `DELETE /surat/{id}/draf`, tombol hapus nonaktif pada surat diajukan, penolakan 422 non-draf. |
| **B-19** | Pengurutan antrean surat secara FIFO di dasbor TU | **Selesai** | Default query `ORDER BY created_at ASC, id ASC`, kolom `Waktu Masuk` (`DD/MM/YYYY HH:mm`), indikator `Prioritas FIFO`, isolasi draf & surat selesai. |
| **B-20** | Verifikasi surat oleh Admin TU beserta catatan revisi | **Selesai** | Endpoint `POST /admin/surat/{id}/verifikasi` (`Diverifikasi TU`) dan `POST /admin/surat/{id}/revisi` (`Perlu Revisi`), modal catatan revisi wajib isi. |
| **B-27** | Daftar riwayat seluruh pengajuan surat di akun pengusul | **Selesai** | Tabel responsif & kartu mobile riwayat pengusul (`Nomor Surat`, `Tanggal`, `Status`), badge kontras >= 4.5:1, empty state jelas, `/riwayat-surat` & `/api/riwayat-surat`. |
| **B-28** | Pratinjau (preview) dokumen surat dari daftar riwayat | **Selesai** | Endpoint `GET /surat/{id}/pratinjau`, modal Escape-closable (>= 14px, kontras 21:1), loading/error state, halaman 404 aman, & update README. |

---

## Detail Progres Task

### 1. [B-01 B] Penguncian sementara saat gagal login berulang
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Skema database counter login gagal dan timestamp `locked_until` (Backend) (`database/migrations/2026_11_14_143031_add_login_lockout_to_users_table.php`)
  - [x] 2. Penguncian akun selama 1 menit setelah 5x gagal (Backend) (`app/Http/Controllers/AuthController.php`)
  - [x] 3. UI pesan peringatan kunci akun dengan countdown timer teraksesibel (Front-end) (`resources/views/auth/login.blade.php`)
  - [x] 4. Uji coba skenario gagal, countdown, dan proteksi login saat terkunci (Pengujian) (`tests/Feature/LoginLockoutTest.php`)
* **Aturan Lockout:**
  - Gagal 1x–4x: Menampilkan sisa percobaan sebelum kunci.
  - Gagal ke-5: Akun dikunci 1 menit (60 detik).
  - Percobaan selama masa kunci ditolak, tanpa menambah counter.
  - Counter di-reset setelah login berhasil.
* **Bukti Pengujian:**
  ```text
  php artisan test --filter=LoginLockoutTest
  PASS  Tests\Feature\LoginLockoutTest
   ✓ gagal 1x sampai 4x counter naik tapi belum dikunci
   ✓ gagal 5x kunci 1 menit
   ✓ percobaan ke-6 ditolak selama masa kunci
  ✓ password benar ditolak saat terkunci
  ✓ login berhasil setelah kunci habis dan counter reset
  ✓ sisa percobaan tampil saat belum kunci
  ✓ username tidak dikenal pesan generik
  Tests: 8 passed (25 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-01b.md`

---

### 2. [B-07] Penyaringan form surat tugas per indeks kegiatan
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Rancang UI filter daftar form surat tugas sesuai indeks kegiatan (Josua Rajak) (UI/UX) (`resources/views/dosen/dashboard.blade.php`)
  - [x] 2. Query filter form berdasarkan indeks kegiatan (Yuga Pratama Andika Syaputra) (Backend) (`database/migrations/2026_11_14_150000_add_indeks_kegiatan_to_jenis_surat_table.php`, `routes/web.php`)
  - [x] 3. Dynamic field filter dan uji validasi indeks kosong (Arman Dava Wicaksono) (Front-end & Pengujian) (`tests/Feature/PenyaringanSuratTugasIndeksTest.php`)
* **Bukti Pengujian:**
  ```text
  php artisan test --filter=PenyaringanSuratTugasIndeksTest
  PASS  Tests\Feature\PenyaringanSuratTugasIndeksTest
  ✓ dashboard menyaring daftar formulir dengan indeks yang dipilih
  ✓ indeks tidak valid menampilkan daftar kosong
  Tests: 2 passed
  ```
* **Dokumentasi Terkait:** `docs/progres-b-07.md`

---

### 3. [B-08] Input prodi, semester, dan dosen melalui dropdown
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Desain komponen accessible dropdown untuk prodi, semester, dosen (UI/UX) (`resources/views/components/combobox.blade.php`, `public/js/combobox.js`)
  - [x] 2. Tabel database master prodi/dosen dan API dropdown list (Backend) (`database/migrations/2026_11_18_000001_create_prodi_table.php`, `database/migrations/2026_11_18_000002_create_semester_table.php`, `app/Support/MasterData.php`, `routes/web.php`)
  - [x] 3. Integrasi custom dropdown searchable dengan keyboard navigation (Front-end) (`public/js/combobox.js`, form mahasiswa & dosen)
  - [x] 4. Uji fungsi keyboard navigation (Tab/Enter/Esc) & validasi API dropdown (Pengujian) (`tests/js/combobox.test.mjs`, `tests/Feature/MasterDataDropdownTest.php`)
* **Bukti Pengujian:**
  ```text
  php artisan test --filter=MasterDataDropdownTest
  PASS  Tests\Feature\MasterDataDropdownTest
  Tests: 15 passed (102 assertions)

  node --test tests/js
  tests 9
  pass 9
  fail 0
  ```
* **Dokumentasi Terkait:** `docs/progres-b-08.md`

---

### 4. [B-09] Pemilihan tanggal awal dan akhir kegiatan (date picker)
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Rancang date picker ramah aksesibilitas (kontras & focus ring) (UI/UX) (`resources/views/components/date-field.blade.php`, `public/css/datepicker.css`, `public/js/datepicker.js`)
  - [x] 2. Validasi rentang tanggal (tanggal selesai $\ge$ mulai) di server (Backend) (`app/Support/DateRange.php`, `routes/web.php`)
  - [x] 3. Pasang date picker di form pengajuan dan parse input teks `YYYY-MM-DD` (Front-end) (`resources/views/mahasiswa/surat/form/surat-permohonan-magang.blade.php`, `resources/views/surat/form-dinamis.blade.php`, `public/js/datepicker.js`)
  - [x] 4. Uji skenario tanggal selesai < tanggal mulai dan verifikasi pesan error (Pengujian) (`tests/Feature/DateRangeValidationTest.php`, `tests/js/datepicker.test.mjs`)
* **Bukti Pengujian:**
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

  node --test tests/js
  tests 23
  pass 23
  fail 0
  ```
* **Dokumentasi Terkait:** `docs/progres-b-09.md`

---

### 5. [B-10 A] Pemilihan waktu mulai dan selesai kegiatan (time picker)
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Validasi format waktu 24 jam dan urutan waktu di backend (Backend) (`app/Support/TimeRange.php`, `routes/web.php`)
  - [x] 2. Bangun UI input time picker dengan keyboard mask 24 jam (Front-end) (`resources/views/components/time-field.blade.php`, `public/js/timepicker.js`, `public/css/timepicker.css`)
  - [x] 3. Uji input time picker pada hari yang sama dan validasi urutan jam (Pengujian) (`tests/Feature/TimeRangeValidationTest.php`, `tests/js/timepicker.test.mjs`)
* **Bukti Pengujian:**
  ```text
  php artisan test --filter=TimeRangeValidationTest
  PASS  Tests\Feature\TimeRangeValidationTest
  ✓ form menolak format waktu tidak valid
  ✓ form menolak waktu selesai lebih awal pada hari yang sama
  ✓ form menolak waktu selesai sama dengan waktu mulai pada hari yang sama
  ✓ form menerima waktu selesai lebih akhir pada hari yang sama
  ✓ form menerima waktu lintas hari jika tanggal selesai berbeda
  ✓ form dinamis merender komponen time picker untuk field waktu
  Tests: 6 passed (26 assertions)

  node --test tests/js/timepicker.test.mjs
  tests 11
  pass 11
  fail 0
  ```
* **Dokumentasi Terkait:** `docs/progres-b-10a.md`

---

### 6. [B-10 B] Pengisian daftar anggota dan peran dalam kegiatan
* **Status:** Selesai (100% - Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Rancang antarmuka tabel dinamis tambah/hapus anggota kegiatan (UI/UX) (`resources/views/components/personel-list.blade.php`, `public/css/personel-list.css`)
  - [x] 2. Tabel relasi personel surat dan endpoint simpan bulk anggota (Backend) (`database/migrations/2026_11_25_000001_create_surat_personel_table.php`, `app/Models/SuratPersonel.php`, `app/Support/SuratPersonelSync.php`, `routes/web.php`)
  - [x] 3. Komponen dynamic input row anggota di form pengajuan (Front-end) (`public/js/personel-list.js`, `resources/views/surat/form-dinamis.blade.php`, `resources/views/surat/edit-draf.blade.php`, `resources/views/dosen/surat/form/surat-tugas-kegiatan-kuliah.blade.php`)
  - [x] 4. Uji validasi baris anggota kosong dan keteroperasian keyboard (Pengujian) (`tests/Feature/PersonelKegiatanB10BTest.php`, `tests/js/personel-list.test.mjs`)

---

### 7. [B-13 A] Penyimpanan pengajuan surat sebagai draf
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Skema status 'Draf' pada tabel pengajuan dan isolasi query TU (Backend)
  - [x] 2. Tombol aksi 'Simpan Draf' dan penanganan status di form pengajuan (Front-end)
  - [x] 3. Endpoint API simpan draf pengajuan parsial (Backend)
  - [x] 4. Uji simpan draf dan verifikasi draf tidak masuk ke antrean TU (Pengujian)

---

### 8. [B-13 B] Pembukaan dan pengubahan draf surat milik pengusul
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Endpoint get detail draf surat dengan guard kepemilikan akun (Backend)
  - [x] 2. Halaman edit draf dan populate data lama ke dalam form (Front-end)
  - [x] 3. Aksi 'Kirim Pengajuan' dari draf untuk update status ke 'menunggu' (Backend)
  - [x] 4. Uji proteksi akses lintas akun (IDOR 403) pada endpoint dan UI edit draf (Pengujian)
* **Bukti Pengujian:**
  ```text
  php artisan test --filter=EditDrafSuratB13BTest
  PASS  Tests\Feature\EditDrafSuratB13BTest
  ✓ mahasiswa dapat membuka halaman edit draf miliknya
  ✓ dosen dapat membuka halaman edit draf miliknya
  ✓ mahasiswa lain tidak dapat membuka draf milik orang lain
  ✓ dosen lain tidak dapat membuka draf milik dosen lain
  ✓ endpoint post update draf mengembalikan 403 jika bukan pemilik
  ✓ mahasiswa dapat memperbarui isian draf tanpa mengirim pengajuan
  ✓ mahasiswa dapat mengirim pengajuan dari draf sehingga status menjadi menunggu
  ✓ dosen dapat mengirim pengajuan dari draf sehingga status menjadi menunggu
  ✓ kirim pengajuan dari draf gagal jika field wajib kosong
  ✓ simpan draf ulang boleh dengan isian parsial tanpa error
  ✓ surat non draf tidak dapat diakses via endpoint edit draf
  ✓ surat yang dikirim dari draf muncul di antrean admin
  ✓ edit draf template dengan form khusus memunculkan isian lama
  ✓ kirim draf dari form khusus memperbarui surat yang sama
  Tests: 14 passed (46 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-13b.md`

---

### 9. [B-14] Penghapusan permanen draf pengajuan surat
* **Status:** Selesai (100%)
* **Checklist:**
  - [x] 1. Modal dialog konfirmasi hapus draf dengan focus trap (`resources/views/components/modal-hapus-draf.blade.php`, `public/js/sakti-modal.js`)
  - [x] 2. Endpoint DELETE draf dengan validasi status wajib 'Draf' (`routes/web.php`)
  - [x] 3. Integrasi tombol hapus draf di UI dan uji penolakan surat 'Diajukan' (`tests/Feature/HapusDrafB14Test.php`, `tests/js/sakti-modal.test.mjs`)

---

### 10. [B-19] Pengurutan antrean surat secara FIFO di dasbor TU
* **Status:** Selesai (100%)
* **Checklist:**
  - [x] 1. Modifikasi query antrean TU: `ORDER BY created_at ASC` (FIFO) (`app/Models/SuratPengajuan.php`, `routes/web.php`)
  - [x] 2. Tampilkan indikator waktu submit (`DD/MM/YYYY HH:mm`) dan tanda prioritas FIFO pada tabel TU (`resources/views/admin/partials/antrean-fifo-table.blade.php`)
  - [x] 3. Uji urutan antrean FIFO saat pengajuan masuk berurutan (`tests/Feature/AntreanFifoTuB19Test.php`)

---

### 11. [B-20] Verifikasi surat oleh Admin TU beserta catatan revisi
* **Status:** Selesai (100%)
* **Checklist:**
  - [x] 1. Desain modal catatan revisi dan tombol aksi verifikasi TU (`resources/views/components/modal-revisi-tu.blade.php`)
  - [x] 2. Endpoint persetujuan verifikasi surat oleh Admin TU (`POST /admin/surat/{id}/verifikasi`)
  - [x] 3. Endpoint pengembalian surat dengan validasi wajib catatan revisi (`POST /admin/surat/{id}/revisi`)
  - [x] 4. Form interaksi verifikasi dan modal revisi di dasbor TU (`resources/views/admin/partials/antrean-fifo-table.blade.php`, `resources/views/surat/detail.blade.php`)
  - [x] 5. Uji alur verifikasi lolos, pengembalian revisi, dan validasi revisi kosong (`tests/Feature/VerifikasiRevisiTuB20Test.php`)

---

### 12. [B-27] Daftar riwayat seluruh pengajuan surat di akun pengusul
* **Status:** Selesai (100%)
* **Checklist:**
  - [x] 1. Desain tabel responsif riwayat pengajuan dengan badge kontras tinggi (`public/css/sakti-ui.css`, `app/Helpers/StatusHelper.php`)
  - [x] 2. Endpoint daftar riwayat pengajuan khusus akun yang login (`/riwayat-surat`, `/api/riwayat-surat`, `/mahasiswa/surat`, `/dosen/surat`)
  - [x] 3. Bangun halaman riwayat pengajuan pengusul dengan empty state (`resources/views/surat/partials/riwayat-pengusul.blade.php`)
  - [x] 4. Uji tampilan riwayat pengusul dan cek kontras warna teks badge status (`tests/Feature/RiwayatPengusulB27Test.php`)

---

### 13. [B-28] Pratinjau (preview) dokumen surat dari daftar riwayat
* **Status:** Selesai (100%)
* **Checklist:**
  - [x] 1. Desain modal pratinjau surat resmi dengan rasio tipografi standar (`resources/views/components/modal-pratinjau-surat.blade.php`, `public/css/sakti-ui.css`)
  - [x] 2. Endpoint render data surat ke tampilan pratinjau HTML terformat (`GET /surat/{id}/pratinjau`, `app/Support/SuratPreview.php`, `resources/views/preview/_generic.blade.php`)
  - [x] 3. Uji pratinjau surat, review integrasi akhir Sprint 2, & update README (`tests/Feature/PratinjauSuratB28Test.php`, `README.md`)

---

## Panduan Update Dokumen Ini
1. Saat sedang mengerjakan task, ubah kolom **Status** di tabel ringkasan menjadi `Sedang Berjalan`.
2. Centang checkbox `[x]` pada item checklist yang sudah selesai dikerjakan.
3. Cantumkan nama file dan perintah test unit/feature yang dijalankan sebagai bukti verifikasi.
4. Jika task sudah selesai 100%, ubah status menjadi `Selesai`.
