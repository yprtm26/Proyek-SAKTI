# Progres Kerja — Sprint 1 (Proyek SAKTI)

**Terakhir Diperbarui:** 04 November 2026  
**Sprint Goal:** Menyelesaikan fungsionalitas inti pada aplikasi Sakti yang meliputi integrasi login LDAP kampus, sinkronisasi data ke database lokal, pembatasan kontrol menu berbasis role, serta pengelolaan alur pengajuan dan pembuatan template surat oleh pihak Tata Usaha.

---

## Ringkasan Status

| ID Task | Nama Task | Status | Keterangan |
| :--- | :--- | :---: | :--- |
| **B-01 A** | Kontrol tampilan menu dan data sesuai role pengguna | **Selesai** | Middleware role, penanganan 403, navigasi per role beres. Test lolos. |
| **B-02** | Pengajuan surat terikat otomatis pada akun pengusul | **Selesai** | Auto-bind `user_id`, kunci identitas form mahasiswa & dosen, API me/profil. Test lolos. |
| **B-05** | Akses pemrosesan semua jenis surat oleh Admin TU | **Selesai** | Antrean mahasiswa dan dosen dipisah; detail dilindungi role Admin TU. |
| **B-15a** | Pembuatan dan penyimpanan template surat baru oleh Admin TU | **Belum** | Fitur buat template dinamis & field baru oleh Admin TU. |
| **B-06** | Pemilihan jenis surat dari daftar template aktif | **Belum dikerjakan** | Diserahkan untuk dikerjakan ulang oleh anggota tim lain. |

---

## Detail Progres Task

### 1. [B-01 A] Kontrol tampilan menu dan data sesuai role pengguna
* **Status:** Selesai (Siap dipindahkan ke Done)
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

---

### 2. [B-02] Pengajuan surat terikat otomatis pada akun pengusul
* **Status:** Selesai (Siap dipindahkan ke Done)
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
* **Status:** Selesai untuk role yang tersedia; Tendik merujuk Admin TU sesuai klarifikasi pengguna
* **Checklist:**
  - [x] 1. Gunakan tabel antrean Admin TU yang sudah ada (UI/UX)
  - [x] 2. Pisahkan antrean mahasiswa (`/admin/dashboard`) dan dosen (`/admin/dashboard-dosen`) (Backend)
  - [x] 3. Tampilkan nama, identitas, role, jenis surat, tanggal, status, dan tautan detail (Front-end)
  - [x] 4. Uji cakupan pengusul dan blokir akses non-Admin TU (`tests/Feature/HakAksesRoleTest.php`)
* **Bukti Pengujian:**
  ```text
  php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php --testdox
  OK (9 tests, 32 assertions)
  ```
* **Batas cakupan:** DB tidak memiliki role Tendik terpisah; klarifikasi pengguna menyatakan Tendik adalah Admin TU. Antrean memisahkan pengajuan berdasarkan role mahasiswa dan dosen.
* **Target Pengerjaan:**
  * Route: `/admin/...`
  * Controller: Alur disposisi dan pemrosesan surat TU.

---

### 4. [B-15a] Pembuatan dan penyimpanan template surat baru oleh Admin TU
* **Status:** Antrean Kerja (*Doing*)
* **Checklist:**
  - [ ] 1. Buat tabel database dan migration untuk template & field (Backend)
  - [ ] 2. Buat endpoint simpan template surat baru beserta field-nya (Backend)
  - [ ] 3. Bangun halaman form pembuatan template baru untuk Admin TU (Front-end)
  - [ ] 4. Tambahkan validasi field wajib pada penyimpanan template (Backend)
  - [ ] 5. Uji pembuatan template baru dan skenario gagal validasi (Pengujian)

---

### 5. [B-06] Pemilihan jenis surat dari daftar template aktif
* **Status:** Belum dikerjakan; diserahkan kepada anggota tim lain untuk dikerjakan dari awal.
* **Catatan:** Perubahan implementasi B-06 pada sesi ini sudah dihapus. Jangan menganggap fitur B-06 selesai berdasarkan progres sebelumnya.

---

## Panduan Update Dokumen Ini
1. Saat sedang mengerjakan task, ubah kolom **Status** di tabel ringkasan menjadi `Sedang Berjalan`.
2. Centang checkbox `[x]` pada item checklist yang sudah selesai dikerjakan.
3. Cantumkan nama file dan perintah test unit/feature yang dijalankan sebagai bukti verifikasi.
4. Jika task sudah selesai 100%, ubah status menjadi `Selesai`.
