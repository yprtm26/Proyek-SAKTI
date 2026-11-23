# Progres Kerja — Sprint 2 (Proyek SAKTI)

**Terakhir Diperbarui:** 14 November 2026  
**Sprint Goal:** Mengembangkan fitur formulir dinamis dan interaktif (dropdown searchable master, date picker, time picker, tabel dinamis anggota kegiatan, filter indeks surat tugas), manajemen draf pengajuan mandiri (simpan, ubah, hapus permanen draf terproteksi anti-IDOR), serta penguatan keamanan autentikasi melalui mekanisme penguncian akun berjenjang (*progressive lockout*).

---

## Ringkasan Status

| ID Task | Nama Task | Status | Keterangan |
| :--- | :--- | :---: | :--- |
| **B-01 B** | Penguncian sementara saat gagal login berulang | **Selesai** | Lockout berjenjang: kelipatan 3x gagal menambah 1 menit kunci. Countdown timer aksesibel, proteksi login saat terkunci. Test lolos (8 tests, 25 assertions). |
| **B-07** | Penyaringan form surat tugas per indeks kegiatan | **Selesai** | Filter daftar formulir dosen berdasarkan Pendidikan, Penelitian, Pengabdian, Kemahasiswaan. Test lolos. |
| **B-08** | Input prodi, semester, dan dosen melalui dropdown | **Belum dikerjakan** | Dropdown searchable teraksesibel (Tab/Enter/Esc) & validasi 422 di server. |
| **B-09** | Pemilihan tanggal awal dan akhir kegiatan (date picker) | **Belum dikerjakan** | Date picker visual & format `YYYY-MM-DD`, validasi tanggal selesai $\ge$ tanggal mulai. |
| **B-10 A** | Pemilihan waktu mulai dan selesai kegiatan (time picker) | **Belum dikerjakan** | Mask format 24 jam (`HH:mm`), validasi jam selesai $>$ mulai pada hari yang sama. |
| **B-10 B** | Pengisian daftar anggota dan peran dalam kegiatan | **Selesai** | Dynamic input row anggota, target klik $\ge 44 \times 44\text{px}$, validasi baris kosong, bulk save ke `surat_personel`. Test lolos (5 tests, 12 assertions). |
| **B-13 A** | Penyimpanan pengajuan surat sebagai draf | **Belum dikerjakan** | Status 'Draf' di DB, isolasi dari antrean TU, validasi wajib template. |
| **B-13 B** | Pembukaan dan pengubahan draf surat milik pengusul | **Belum dikerjakan** | Halaman edit draf, aksi kirim pengajuan, proteksi 403 IDOR lintas akun. |
| **B-14** | Penghapusan permanen draf pengajuan surat | **Belum dikerjakan** | Fitur hapus draf mandiri oleh pengusul. |
| **B-19** | Pengurutan antrean surat secara FIFO di dasbor TU | **Belum dikerjakan** | Antrean urut kronologis pengajuan masuk. |
| **B-20** | Verifikasi surat oleh Admin TU beserta catatan revisi | **Belum dikerjakan** | Alur verifikasi surat dan feedback catatan revisi. |
| **B-27** | Daftar riwayat seluruh pengajuan surat di akun pengusul | **Belum dikerjakan** | Tabel riwayat pengajuan personal pengusul. |
| **B-28** | Pratinjau (preview) dokumen surat dari daftar riwayat | **Belum dikerjakan** | Modal / tab pratinjau dokumen surat PDF. |

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
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [ ] 1. Desain komponen accessible dropdown untuk prodi, semester, dosen (UI/UX)
  - [ ] 2. Tabel database master prodi/dosen dan API dropdown list (Backend)
  - [ ] 3. Integrasi custom dropdown searchable dengan keyboard navigation (Front-end)
  - [ ] 4. Uji fungsi keyboard navigation (Tab/Enter/Esc) & validasi API dropdown (Pengujian)

---

### 4. [B-09] Pemilihan tanggal awal dan akhir kegiatan (date picker)
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [ ] 1. Rancang date picker ramah aksesibilitas (kontras & focus ring) (UI/UX)
  - [ ] 2. Validasi rentang tanggal (tanggal selesai $\ge$ mulai) di server (Backend)
  - [ ] 3. Pasang date picker di form pengajuan dan parse input teks `YYYY-MM-DD` (Front-end)
  - [ ] 4. Uji skenario tanggal selesai < tanggal mulai dan verifikasi pesan error (Pengujian)

---

### 5. [B-10 A] Pemilihan waktu mulai dan selesai kegiatan (time picker)
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [ ] 1. Validasi format waktu 24 jam dan urutan waktu di backend (Backend)
  - [ ] 2. Bangun UI input time picker dengan keyboard mask 24 jam (Front-end)
  - [ ] 3. Uji input time picker pada hari yang sama dan validasi urutan jam (Pengujian)

---

### 6. [B-10 B] Pengisian daftar anggota dan peran dalam kegiatan
* **Status:** Selesai (100% — Siap dipindahkan ke Review/Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Rancang antarmuka tabel dinamis tambah/hapus anggota kegiatan (Josua Rajak) (UI/UX) (`resources/views/surat/partials/tabel-personel.blade.php`)
  - [x] 2. Tabel relasi personel surat dan endpoint simpan bulk anggota (Yuga Pratama Andika Syaputra) (Backend) (`database/migrations/2026_11_23_000001_create_surat_personel_table.php`, `app/Models/SuratPersonel.php`, `routes/web.php`)
  - [x] 3. Komponen dynamic input row anggota di form pengajuan (Josua Rajak) (Front-end) (`resources/views/surat/partials/tabel-personel.blade.php`, `resources/views/surat/form-dinamis.blade.php`, form khusus dosen HKI/buku ajar/penelitian, form observasi mahasiswa)
  - [x] 4. Uji validasi baris anggota kosong dan keteroperasian keyboard (Arman Dava Wicaksono) (Pengujian) (`tests/Feature/PengisianAnggotaKegiatanB10BTest.php`, `tests/Feature/PengajuanTerikatKeAkunTest.php`)
* **Bukti Pengujian:**
  ```text
  php artisan test tests/Feature/PengisianAnggotaKegiatanB10BTest.php tests/Feature/PengajuanTerikatKeAkunTest.php
  PASS  Tests\Feature\PengisianAnggotaKegiatanB10BTest
  ✓ pengusul dapat mengajukan surat dengan daftar anggota secara bulk
  ✓ sistem memblokir submit jika ada baris anggota yang namanya kosong
  ✓ sistem memblokir submit jika ada baris anggota yang perannya kosong
  ✓ komponen view tabel personel memenuhi standar aksesibilitas
  ✓ form khusus memakai komponen personel bersama
  ✓ preview dosen menampilkan personel dari tabel relasi
  ✓ helper personel memakai fallback data lama
  ✓ api bulk personel berhasil dan dilindungi anti idor
  Tests: 14 passed (87 assertions)

  php artisan test
  Tests: 65 passed (351 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-10b.md`

---

### 7. [B-13 A] Penyimpanan pengajuan surat sebagai draf
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [ ] 1. Skema status 'Draf' pada tabel pengajuan dan isolasi query TU (Backend)
  - [ ] 2. Tombol aksi 'Simpan Draf' dan penanganan status di form pengajuan (Front-end)
  - [ ] 3. Endpoint API simpan draf pengajuan parsial (Backend)
  - [ ] 4. Uji simpan draf dan verifikasi draf tidak masuk ke antrean TU (Pengujian)

---

### 8. [B-13 B] Pembukaan dan pengubahan draf surat milik pengusul
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [ ] 1. Endpoint get detail draf surat dengan guard kepemilikan akun (Backend)
  - [ ] 2. Halaman edit draf dan populate data lama ke dalam form (Front-end)
  - [ ] 3. Aksi 'Kirim Pengajuan' dari draf untuk update status ke 'Diajukan' (Backend)
  - [ ] 4. Uji proteksi akses lintas akun (IDOR 403) pada endpoint dan UI edit draf (Pengujian)

---

### 9. [B-14] Penghapusan permanen draf pengajuan surat
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **Checklist:** Menunggu perumusan checklist detail.

---

### 10. [B-19, B-20, B-27, B-28] Fitur Antrean & Riwayat Pengajuan
* **Status:** Belum dikerjakan (Product Backlog Sprint 2)
* **Cakupan:**
  - **B-19:** Pengurutan antrean surat secara FIFO di dasbor TU.
  - **B-20:** Verifikasi surat oleh Admin TU beserta catatan revisi.
  - **B-27:** Daftar riwayat seluruh pengajuan surat di akun pengusul.
  - **B-28:** Pratinjau (*preview*) dokumen surat dari daftar riwayat.

---

## Panduan Update Dokumen Ini
1. Saat sedang mengerjakan task, ubah kolom **Status** di tabel ringkasan menjadi `Sedang Berjalan`.
2. Centang checkbox `[x]` pada item checklist yang sudah selesai dikerjakan.
3. Cantumkan nama file dan perintah test unit/feature yang dijalankan sebagai bukti verifikasi.
4. Jika task sudah selesai 100%, ubah status menjadi `Selesai`.
