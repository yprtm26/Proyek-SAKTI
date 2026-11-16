# Progres Kerja — Sprint 1 (Proyek SAKTI)

**Terakhir Diperbarui:** 07 November 2026
**Sprint Goal:** Menyelesaikan fungsionalitas inti pada aplikasi Sakti yang meliputi integrasi login LDAP kampus, sinkronisasi data ke database lokal, pembatasan kontrol menu berbasis role, serta pengelolaan alur pengajuan dan pembuatan template surat oleh pihak Tata Usaha.

---

## Ringkasan Status

| ID Task | Nama Task | Status | Keterangan |
| :--- | :--- | :---: | :--- |
| **B-01 A** | Kontrol tampilan menu dan data sesuai role pengguna | **Selesai** | Middleware role, penanganan 403, navigasi per role beres. Test lolos (7 tests, 22 assertions). |
| **B-02** | Pengajuan surat terikat otomatis pada akun pengusul | **Selesai** | Auto-bind `user_id`, kunci identitas form mahasiswa & dosen, API me/profil. Test lolos (5 tests, 20 assertions). |
| **B-05** | Akses pemrosesan semua jenis surat oleh Admin TU | **Selesai** | Antrean mahasiswa dan dosen dipisah; detail dilindungi role Admin TU. Test lolos (9 tests, 32 assertions). |
| **B-15a** | Pembuatan dan penyimpanan template surat baru oleh Admin TU | **Sedang Berjalan** | Development tuntas 100% (Subtask 1-4 selesai + revisi tab kategori). Sisa QA/Testing (Subtask 5). |
| **B-06** | Pemilihan jenis surat dari daftar template aktif | **Selesai** | Endpoint template aktif (khusus pengusul), form dinamis, proteksi template nonaktif, lock identitas server-side, dan test B-06 beres. |

---

## Detail Progres Task

### 1. [B-01 A] Kontrol tampilan menu dan data sesuai role pengguna
* **Status:** Selesai (Siap dipindahkan ke Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Susun aturan hak akses menu dan endpoint untuk tiap role (Backend)
  - [x] 2. Buat middleware pengecekan role pengguna di sisi server (Backend) (`app/Http/Middleware/RoleMiddleware.php`)
  - [x] 3. Atur tampilan navigasi menu sesuai role akun yang login (Front-end)
  - [x] 4. Buat halaman penolakan akses 403 (Forbidden) (Front-end) (`resources/views/errors/403.blade.php`)
  - [x] 5. Uji coba akses menu dan endpoint lintas role akun (Pengujian) (`tests/Feature/HakAksesRoleTest.php`)
  - [x] 6. Review kode PR dan catat aturan role di README / docs (`docs/hak-akses.md`)
* **Bukti Pengujian:**
  ```text
  php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php
  OK (7 tests, 22 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-01.md`

---

### 2. [B-02] Pengajuan surat terikat otomatis pada akun pengusul
* **Status:** Selesai (Siap dipindahkan ke Done)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Desain tampilan kolom identitas otomatis pada form surat (UI/UX) *(Dilewati atas arahan client)*
  - [x] 2. Buat API pengambil data profil pengusul yang sedang login (Backend) (`/api/profil-pengusul`, `/api/me`)
  - [x] 3. Tampilkan dan kunci (read-only) kolom identitas di form (Front-end) (Semua form mahasiswa & dosen di `resources/views/{mahasiswa,dosen}/surat/form/`)
  - [x] 4. Simpan relasi pengajuan surat ke ID akun pengusul di DB (Backend) (Otomatis ditimpa di server via `Auth::id()`)
  - [x] 5. Uji pengisian otomatis dan keamanan kunci identitas (Pengujian) (`tests/Feature/PengajuanTerikatKeAkunTest.php`)
* **Bukti Pengujian:**
  ```text
  php vendor/bin/phpunit tests/Feature/PengajuanTerikatKeAkunTest.php
  OK (5 tests, 20 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-02.md`

---

### 3. [B-05] Akses pemrosesan semua jenis surat oleh Admin TU
* **Status:** Selesai (100% untuk antrean Mahasiswa dan Dosen)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Desain tata letak tabel antrean surat untuk Admin TU (UI/UX)
  - [x] 2. Buat query dan endpoint daftar antrean semua pengusul (Backend) (`GET /admin/dashboard`, `GET /admin/dashboard-dosen`)
  - [x] 3. Bangun halaman tabel antrean dan detail surat di Admin TU (Front-end) (`resources/views/admin/dashboard*.blade.php`)
  - [x] 4. Uji tampilan antrean Admin TU dan blokir akses non-TU (Pengujian) (`tests/Feature/HakAksesRoleTest.php`)
* **Bukti Pengujian:**
  ```text
  php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php --testdox
  OK (9 tests, 32 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-05.md`

---

### 4. [B-15a] Pembuatan dan penyimpanan template surat baru oleh Admin TU
* **Status:** Sedang Berjalan (Development tuntas 100%, sisa QA testing)
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Buat tabel database dan migration untuk template & field (Backend) (`jenis_surat` table & migration)
  - [x] 2. Buat endpoint simpan template surat baru beserta field-nya (Backend) (`routes/web.php` CRUD routes)
  - [x] 3. Bangun halaman form pembuatan template baru untuk Admin TU (Front-end) (`resources/views/admin/template-surat/*`)
  - [x] 4. Tambahkan validasi field wajib pada penyimpanan template (Backend) (Validasi unik nama/kode, regex field dinamis, revisi tab kategori Mahasiswa/Dosen)
  - [ ] 5. Uji pembuatan template baru dan skenario gagal validasi (Pengujian) (Tanggung jawab QA: Arman Dava Wicaksono)
* **Dokumentasi Terkait:** `docs/progres-b-15a.md`

---

### 5. [B-06] Pemilihan jenis surat dari daftar template aktif
* **Status:** Selesai
* **PIC:** Yuga Pratama (Backend), Josua Rajak (Frontend), Arman Dava (QA)
* **Checklist:**
  - [x] 1. Buat endpoint daftar template aktif untuk halaman pengusul (Backend) (`GET /api/template-surat`, khusus mahasiswa/dosen)
  - [x] 2. Bangun fitur pilih template & render kolom form dinamis (Front-end) (`resources/views/surat/form-dinamis.blade.php`, identitas dikunci di server)
  - [x] 3. Uji pilih template aktif dan penyembunyian template nonaktif (Pengujian) (`tests/Feature/PemilihanTemplateAktifTest.php`)
  - [x] 4. Review integrasi akhir Sprint 1, rapikan bug, & update README (Lainnya) (`README.md`, `docs/progres-b-06.md`, `docs/hak-akses.md`)
* **Bukti Pengujian:**
  ```text
  php vendor/bin/phpunit tests/Feature/PemilihanTemplateAktifTest.php --testdox
  OK (19 tests, 109 assertions)
  ```
* **Dokumentasi Terkait:** `docs/progres-b-06.md`

---

## Panduan Update Dokumen Ini
1. Saat sedang mengerjakan task, ubah kolom **Status** di tabel ringkasan menjadi `Sedang Berjalan`.
2. Centang checkbox `[x]` pada item checklist yang sudah selesai dikerjakan.
3. Cantumkan nama file dan perintah test unit/feature yang dijalankan sebagai bukti verifikasi.
4. Jika task sudah selesai 100%, ubah status menjadi `Selesai`.
