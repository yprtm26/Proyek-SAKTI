# Progres B-05 — Akses Pemrosesan Semua Jenis Surat oleh Admin TU

**Status:** Selesai (100% untuk antrean Mahasiswa dan Dosen)  
**Papan Trello:** Sprint 1 — Card B-05  

---

## 1. Ringkasan Fitur
Memberikan kewenangan kepada Admin Tata Usaha (TU) untuk dapat mengakses, memantau, memfilter, dan memproses seluruh antrean permohonan surat yang masuk ke sistem secara terpusat. Akses antrean dipisahkan secara rapi antara kelompok pengusul Mahasiswa dan Dosen, serta dilindungi secara ketat agar tidak dapat dibuka oleh role lain di luar Admin TU.

---

## 2. Penjelasan Detail Pengerjaan Task

### Task 1: Desain tata letak tabel antrean surat untuk Admin TU (UI/UX)
* **Pekerjaan:** Penataan tabel antrean surat yang informatif dan efisien bagi petugas Tata Usaha.
* **Hasil:** Mengoptimalkan antarmuka tabel antrean dengan kolom identitas pengusul, nomor identitas (NPM/NIDN), program studi, jenis surat, status pengajuan, tanggal masuk, serta tombol aksi proses/detail.

### Task 2: Buat query dan endpoint daftar antrean semua pengusul (Backend)
* **Pekerjaan:** Menyediakan query database dengan eager loading relasi dan filter pencarian yang efisien.
* **File Terkait:** `routes/web.php`.
* **Endpoint:**
  * `GET /admin/dashboard` — Antrean khusus pengajuan kelompok **Mahasiswa**.
  * `GET /admin/dashboard-dosen` — Antrean khusus pengajuan kelompok **Dosen**.
* **Fitur Query Backend:**
  1. Eager loading relasi `user` dan `jenisSurat` untuk mencegah query n+1.
  2. Filter status aktif antrean (`menunggu` dan `menunggu_admin_final`).
  3. Filter pencarian teks (`q`) berdasarkan nama pengusul, identitas (NPM/NIDN), atau nama jenis surat.
  4. Filter berdasarkan Program Studi (khusus mahasiswa).
  5. Filter dropdown berdasarkan `jenis_surat_id`.
  6. Pengurutan waktu pengajuan (`asc` / `desc`).

### Task 3: Bangun halaman tabel antrean dan detail surat di Admin TU (Front-end)
* **Pekerjaan:** Mengintegrasikan tabel antrean dan halaman aksi detail pemrosesan surat.
* **File Terkait:**
  * `resources/views/admin/dashboard.blade.php` (Antrean Mahasiswa)
  * `resources/views/admin/dashboard-dosen.blade.php` (Antrean Dosen)
  * `resources/views/admin/surat/detail.blade.php` & alur verifikasi surat
* **Cara Kerja:**
  - Admin TU dapat berpindah antar-antrean mahasiswa dan dosen melalui navigasi dashboard.
  - Setiap baris antrean memiliki tombol aksi langsung ke detail surat untuk melakukan persetujuan (*approval*), revisi, penolakan, maupun disposisi.

### Task 4: Uji tampilan antrean Admin TU dan blokir akses non-TU (Pengujian)
* **Pekerjaan:** Pengujian keamanan akses endpoint antrean TU dan verifikasi filtering data.
* **File Terkait:** `tests/Feature/HakAksesRoleTest.php`.
* **Skenario yang Diuji:**
  - Role selain `admin` (Mahasiswa, Dosen, Kaprodi, Dekan) langsung ditolak HTTP 403 Forbidden ketika mencoba membuka `/admin/dashboard`, `/admin/dashboard-dosen`, maupun aksi pemrosesan surat lainnya.
  - Pengguna tanpa sesi login diarahkan kembali ke halaman login.
* **Hasil Eksekusi:**
  ```text
  php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php --testdox
  OK (9 tests, 32 assertions)
  ```

---

## 3. Catatan Batas Cakupan (Scope Notes)
* Berdasarkan klarifikasi pengguna dan arsitektur database, peran Tendik dilekatkan pada hak akses Admin TU. Antrean dipetakan pada 2 kelompok pemohon utama fakultas: Mahasiswa dan Dosen.
* Seluruh endpoint dilindungi oleh middleware `['auth', 'role:admin']`.
