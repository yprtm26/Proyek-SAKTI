# Progres B-01 A — Kontrol Tampilan Menu dan Data Sesuai Role Pengguna

**Status:** Selesai (100%)  
**Papan Trello:** Sprint 1 — Card B-01 A  
**Referensi:** `docs/hak-akses.md`

---

## 1. Ringkasan Fitur
Memastikan sistem SAKTI membatasi akses halaman, endpoint API, dan navigasi menu secara otomatis sesuai dengan peran (*role*) pengguna yang sedang aktif (Mahasiswa, Dosen, Admin TU, Kaprodi, Dekan) serta mencegah akses ilegal antar-role.

---

## 2. Penjelasan Detail Pengerjaan Task

### Task 1: Susun aturan hak akses menu dan endpoint untuk tiap role (Backend)
* **Pekerjaan:** Memetakan batasan akses untuk 5 role pengguna di seluruh URL aplikasi.
* **Hasil:** Dibuat matriks hak akses formal di `docs/hak-akses.md`.
  * **Mahasiswa:** Hanya rute `/mahasiswa/*` (pengajuan surat mahasiswa, riwayat surat pribadi).
  * **Dosen:** Hanya rute `/dosen/*` (pengajuan surat tugas dosen, riwayat kegiatan dosen).
  * **Admin (Tata Usaha):** Hanya rute `/admin/*` (pemrosesan surat, disposisi, arsip).
  * **Kaprodi:** Hanya rute `/kaprodi/*` (persetujuan/rekomendasi prodi).
  * **Dekan:** Hanya rute `/dekan/*` (tanda tangan dan validasi dekanat).

### Task 2: Buat middleware pengecekan role pengguna di sisi server (Backend)
* **Pekerjaan:** Mengimplementasikan middleware otorisasi di sisi server.
* **File Terkait:** `app/Http/Middleware/RoleMiddleware.php` & `routes/web.php`.
* **Cara Kerja:**
  1. Middleware mencegat setiap request HTTP sebelum masuk ke Controller.
  2. Memeriksa status login via `Auth::check()`. Jika tidak ada sesi login, pengguna diarahkan ke `/login`.
  3. Membandingkan kolom `role` pada model `User` dengan parameter role yang diizinkan pada grup route.
  4. Jika role pengguna tidak cocok, middleware memanggil `abort(403, 'Unauthorized access.')`.

### Task 3: Atur tampilan navigasi menu sesuai role akun yang login (Front-end)
* **Pekerjaan:** Menyesuaikan komponen navigasi navbar dan sidebar dashboard per role.
* **File Terkait:** View layout Blade masing-masing role.
* **Cara Kerja:**
  - Menu navigasi dipisah berdasarkan template masing-masing role.
  - Mahasiswa tidak melihat tombol/menu pengelolaan surat TU.
  - Dosen hanya melihat alur surat tridharma dan tugas dosen.
  - Admin TU melihat menu antrean surat dan administrasi.

### Task 4: Buat halaman penolakan akses 403 (Forbidden) (Front-end)
* **Pekerjaan:** Membangun antarmuka khusus ketika pengguna mencoba mengakses halaman di luar kewenangannya.
* **File Terkait:** `resources/views/errors/403.blade.php`.
* **Cara Kerja:**
  - Menampilkan pesan error 403 yang ramah dan jelas.
  - Menyediakan tombol navigasi untuk kembali ke dashboard sesuai role masing-masing pengguna tanpa merusak alur aplikasi.

### Task 5: Uji coba akses menu dan endpoint lintas role akun (Pengujian)
* **Pekerjaan:** Membuat unit/feature test otomatis untuk menguji seluruh skenario hak akses.
* **File Terkait:** `tests/Feature/HakAksesRoleTest.php`.
* **Skenario yang Diuji:**
  - Akses tanpa login dicegah dan dialihkan ke form login.
  - Pengguna role `mahasiswa` dicegah (403) saat mengakses rute `dosen`, `admin`, `kaprodi`, `dekan`.
  - Pengguna role `dosen` dicegah (403) saat mengakses rute `mahasiswa` atau `admin`.
  - Pengguna diarahkan ke dashboard yang benar saat login berhasil.
* **Hasil Eksekusi:**
  ```text
  php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php
  OK (7 tests, 22 assertions)
  ```

### Task 6: Review kode PR dan catat aturan role di dokumentasi (Lainnya)
* **Pekerjaan:** Melakukan audit implementasi rute di `routes/web.php` dan memastikan dokumentasi teknis tercatat lengkap di folder `docs/`.

---

## 3. Verifikasi
Jalankan perintah berikut untuk menguji ulang seluruh logika B-01 A:
```bash
php vendor/bin/phpunit tests/Feature/HakAksesRoleTest.php
```
