# Laporan Pengerjaan Tugas: Autentikasi dan Batas Login
## Mode A · Proyek LDAP UY (Sesi Setelah Login LDAP)

**Kelompok:** Sprout Apex  
**Anggota Tim:**
1. Arman Dava Wicaksono (NPM: 1402024010) — *Implementer Keamanan Sesi & Backend*
2. Yuga Pratama (NPM: 1402024008) — *Reviewer Pull Request & Backend*
3. Muhammad Fikri (NPM: 1402024015) — *Frontend & Integrasi*

**Proyek:** SAKTI (Sistem Administrasi Surat Akademik Terintegrasi) — Universitas YARSI  
**Framework / Stack:** Laravel 12 (PHP 8.4), OpenLDAP (`directorytree/ldaprecord-laravel`), PHPUnit  
**Repositori GitHub:** https://github.com/yprtm26/Proyek-SAKTI  
**Link Folder Bukti (Google Drive):** `https://bit.ly/SproutApex-Autentikasi`  
**Tautan Pull Request:** https://github.com/yprtm26/Proyek-SAKTI/pull/1  

---

## 1. Notulen Keputusan Diskusi Kelompok

> **NOTULEN DISKUSI KELOMPOK — KEAMANAN SESI SETELAH LOGIN LDAP**  
> **Hari/Tanggal:** Senin, 16 November 2026  
> **Waktu Diskusi:** 11.00 – 12.20 WIB  
> **Tempat:** Laboratorium Komputer / Diskusi Tim Sprout Apex  
> **Notulis:** Arman Dava Wicaksono  
> **Topik:** Penentuan kebijakan sesi web setelah autentikasi LDAP pada aplikasi SAKTI

Setelah membandingkan catatan konsep masing-masing anggota terkait cara kerja server LDAP yang hanya memverifikasi password satu kali di awal, tim kami menyepakati 4 keputusan arsitektur berikut untuk mengamankan sesi aplikasi:

### 1. Data yang Disimpan dan Dilarang Disimpan di Sesi
* **Data yang Disimpan:**  
  Sesi server hanya menyimpan data identitas minimal yang diperlukan untuk kebutuhan antarmuka dan otorisasi menu:
  - `user_id` (ID pengguna di database lokal untuk guard Laravel).
  - `nama` (nama lengkap mahasiswa/dosen untuk ditampilkan pada header profil).
  - `role` (peran: `mahasiswa`, `dosen`, atau `admin_tu` untuk guard hak akses).
  - `npm` / `nidn` (nomor induk akademik agar otomatis mengisi formulir surat).
  - `auth_login_at` (catatan waktu login pertama, dipakai untuk patokan batas absolut).
  - `auth_last_activity` (catatan waktu aktivitas terakhir, dipakai untuk patokan batas idle).
  - `_token` (token CSRF untuk memproteksi form pengajuan surat).
* **Data yang Dilarang Disimpan:**  
  Password pengguna dalam bentuk apa pun (baik teks asli maupun hash), kredensial akun *bind* LDAP kampus, serta atribut direktori yang tidak relevan (seperti `userPassword`, nomor telepon, dan email pribadi alternatif). Kami juga mematikan log LDAP (`LDAP_LOGGING=false`) agar password akun tidak pernah tercatat di file log server.

### 2. Batas Waktu Idle dan Batas Waktu Absolut
Kami menetapkan dua batas waktu untuk sesi pengguna:
* **Batas Idle (Tidak Ada Aktivitas):** **30 Menit** (`SESSION_LIFETIME=30`).
* **Batas Absolut (Sejak Pertama Login):** **8 Jam** (`SESSION_ABSOLUTE_LIFETIME=480`).
* **Alasan Pemilihan Angka:**  
  Aplikasi SAKTI adalah sistem administrasi persuratan akademik yang sering diakses mahasiswa dan dosen melalui komputer bersama di kampus (seperti PC laboratorium, ruang tata usaha, atau perpustakaan). Durasi idle 30 menit dirasa paling pas: cukup fleksibel jika pengguna sedang mengetik draf surat atau membaca dokumen, namun cukup cepat mengunci/mengeluarkan akun jika komputer ditinggal tanpa sempat logout. Sedangkan batas absolut 8 jam disesuaikan dengan satu jam kerja normal kampus (satu shift kerja TU / satu hari perkuliahan). Dengan batas ini, sesi akan dipaksa selesai setelah 8 jam meskipun pengguna masih aktif mengklik menu, sehingga akun wajib melakukan autentikasi ulang ke server LDAP minimal sehari sekali.

### 3. Alur yang Terjadi Saat Pengguna Logout
Saat pengguna menekan tombol Logout di aplikasi, terjadi pembersihan pada dua sisi:
* **Di Sisi Server:**  
  1. Method `Auth::logout()` memutus status autentikasi akun.
  2. Method `$request->session()->invalidate()` menghapus seluruh isi data sesi di server dan membuang ID sesi tersebut.
  3. Method `$request->session()->regenerateToken()` mengganti token CSRF lama dengan token baru.
* **Di Sisi Browser:**  
  Server mengirimkan header respons `Set-Cookie` dengan masa berlaku kedaluwarsa (`Cookie::forget()`). Peramban akan otomatis menghapus file cookie sesi dari penyimpanannya. Jika ada orang lain yang menyalin nilai cookie lama sebelum logout, cookie tersebut tetap tidak bisa dipakai lagi karena sesinya sudah dihapus permanen di server (request akan mental kembali ke `/login`).

### 4. Atribut Cookie Sesi yang Digunakan
* **`HttpOnly = true`:** Mengunci cookie agar tidak bisa dibaca oleh skrip JavaScript (`document.cookie`), sehingga sesi terlindung dari pencurian jika terjadi celah XSS.
* **`SameSite = Lax`:** Mencegah browser mengirim cookie pada request lintas web yang mencurigakan (mencegah CSRF), namun pengguna tetap nyaman saat membuka tautan sistem dari luar.
* **`Secure` (Dinamis via `.env`):** Diatur bernilai `false` di laptop pengembang (karena memakai protokol HTTP `localhost`), dan otomatis diubah menjadi `true` saat sistem diunggah ke server kampus (wajib protokol HTTPS).

---

## 2. Ringkasan Perubahan Kode Program

Untuk menerapkan hasil keputusan diskusi di atas, berikut adalah perubahan yang telah kami lakukan pada kode aplikasi:

| No | Berkas yang Diubah / Dibuat | Status | Perubahan yang Dilakukan |
|:---:|---|:---:|---|
| 1 | `app/Http/Controllers/AuthController.php` | Diubah | Menambahkan `$request->session()->regenerate()` tepat setelah verifikasi LDAP berhasil untuk mencegah pembajakan sesi (*Session Fixation*). Menambahkan fungsi sanitasi data sesi minimal, serta menyempurnakan fungsi `logout()` dengan invalidasi sesi dan penghapusan cookie. |
| 2 | `app/Http/Middleware/EnforceSessionTimeout.php` | **Baru** | Middleware buatan sendiri untuk mengecek selisih waktu aktivitas pengguna pada setiap request. Jika pengguna tidak aktif > 30 menit atau sudah login > 8 jam, middleware akan otomatis memaksa logout dan mengarahkan kembali ke `/login`. |
| 3 | `bootstrap/app.php` | Diubah | Mendaftarkan middleware `EnforceSessionTimeout` ke dalam grup middleware `web` agar aktif di seluruh rute aplikasi. |
| 4 | `config/session.php` | Diubah | Mengatur `lifetime` ke 30 menit, menambahkan konfigurasi `absolute_lifetime` ke 480 menit, dan mengaktifkan atribut `http_only=true` serta `same_site=lax`. |
| 5 | `config/ldap.php` | Diubah | Mengubah nilai default `'logging.enabled' => false` agar kredensial login LDAP tidak tercatat ke file log. |
| 6 | `.env.example` | Diubah | Menambahkan variabel contoh: `SESSION_LIFETIME=30`, `SESSION_ABSOLUTE_LIFETIME=480`, `SESSION_SECURE_COOKIE=false`, dan `LDAP_LOGGING=false` tanpa membocorkan password asli. |
| 7 | `tests/Feature/SessionSecurityTest.php` | **Baru** | Membuat 6 skenario pengujian otomatis menggunakan Mockery untuk memalsukan koneksi LDAP, membuktikan bahwa kode berjalan benar tanpa harus terhubung ke server LDAP kampus asli. |

---

## 3. Hasil Pengujian Otomatis (Automated Testing)

Pengujian dijalankan langsung melalui terminal menggunakan PHPUnit. Koneksi ke server LDAP di-mock (ditiru) menggunakan Mockery agar tes berjalan cepat dan tidak bergantung pada jaringan kampus.

* **Perintah Pengujian:**
  ```powershell
  php artisan test tests/Feature/SessionSecurityTest.php
  ```
* **Hasil Eksekusi:**
  ```text
  PASS  Tests\Feature\SessionSecurityTest
  ✓ id sesi berbeda sebelum dan sesudah login ldap
  ✓ cookie sesi lama ditolak setelah logout
  ✓ sesi hanya menyimpan identitas minimal tanpa password
  ✓ idle timeout 30 menit memaksa logout
  ✓ absolute timeout 8 jam memaksa logout
  ✓ cookie sesi memakai httponly samesite lax dan secure dari env

  Tests:    6 passed (60 assertions)
  Duration: 2.85s
  ```
Semua tes lulus, membuktikan bahwa mekanisme pergantian sesi saat login dan penolakan cookie lama setelah logout sudah berfungsi dengan baik.

---

## 4. Tautan Pull Request (Review Anggota Tim)

Perubahan kode ini diajukan melalui mekanisme Pull Request di GitHub agar ditinjau oleh rekan satu tim sebelum digabungkan:

* **Tautan Pull Request:** https://github.com/yprtm26/Proyek-SAKTI/pull/1
* **Branch Asal:** `feature/mode-a-session-security` $\rightarrow$ **Branch Tujuan:** `main`
* **Peninjau (Reviewer):** Yuga Pratama (Akun GitHub: `yprtm26`)
* **Status:** **Approved** (Telah diperiksa dan disetujui tanpa konflik).

---

## 5. Lampiran Bukti Tangkapan Layar (Screenshot)

> **Catatan Sensor Nilai Cookie:** Sesuai instruksi soal, nilai string token pada kolom *Value* di DevTools telah disamarkan/disensor untuk menjaga keamanan, sedangkan kolom atribut (`Name`, `HttpOnly`, `SameSite`, `Expires`) dibiarkan terlihat jelas.

### Bukti 1: Cookie Sebelum Login & Pengaturan Atribut Sesi
* **Penjelasan:** Diambil pada halaman login (`http://localhost:8000/login`) sebelum memasukkan kredensial akun.
* **Yang Dibuktikan:** Cookie `laravel-session` sudah memiliki atribut `HttpOnly` (tercentang ✔), `SameSite=Lax`, dan masa berlaku kedaluwarsa 30 menit. Nilai cookie disensor.
```text
[ TEMPELKAN GAMBAR SCREENSHOT 1 DI SINI ]
(DevTools tab Application > Cookies pada halaman /login)
```

---

### Bukti 2: Cookie Sesudah Login (Regenerasi Sesi Berhasil)
* **Penjelasan:** Diambil pada halaman dashboard mahasiswa tepat setelah login berhasil diverifikasi.
* **Yang Dibuktikan:** Nilai pada kolom `Value` cookie `laravel-session` telah berubah total dari nilai pada Bukti 1. Ini membuktikan bahwa aplikasi berhasil mengganti ID sesi lama dengan yang baru demi mencegah serangan pembajakan sesi (*Session Fixation*).
```text
[ TEMPELKAN GAMBAR SCREENSHOT 2 DI SINI ]
(DevTools tab Application > Cookies pada halaman dashboard setelah login)
```

---

### Bukti 3: Cookie Lama Ditolak Setelah Logout (Dialihkan ke Login)
* **Penjelasan:** Pengujian dengan menyalin nilai cookie saat login, kemudian melakukan logout, lalu mencoba mengakses kembali URL `/mahasiswa/dashboard` menggunakan cookie lama tersebut.
* **Yang Dibuktikan:** Server menolak request tersebut dengan status **302 Found** dan otomatis melempar browser kembali ke halaman `/login`. Sesi lama terbukti sudah tidak bisa dipakai kembali karena telah dihancurkan di server.
```text
[ TEMPELKAN GAMBAR SCREENSHOT 3 DI SINI ]
(Browser mental kembali ke /login atau DevTools tab Network menunjukkan status 302)
```

---

### Bukti 4: Hasil Pengujian Otomatis di Terminal
* **Penjelasan:** Tampilan layar terminal setelah menjalankan perintah pengujian unit test backend.
* **Yang Dibuktikan:** Seluruh skenario pengujian sesi berhasil lulus (**PASS - 6 tests passed**) dengan layanan LDAP yang di-mock tanpa koneksi internet/kampus.
```text
[ TEMPELKAN GAMBAR SCREENSHOT 4 DI SINI ]
(Tangkapan layar terminal berwarna hijau: PASS Tests\Feature\SessionSecurityTest)
```

---

## 6. Tautan Pengumpulan Akhir
1. **Google Drive (Folder Bukti):** `https://bit.ly/SproutApex-Autentikasi`  
   *(Berisi 4 gambar tangkapan layar di atas serta berkas PDF ini)*.
2. **Repositori GitHub:** `https://github.com/yprtm26/Proyek-SAKTI`  
   *(Kode proyek tim yang sudah bersih dari password dan kredensial rahasia)*.
3. **Nama Berkas PDF:** `Sprout Apex - Autentikasi dan Batas Login.pdf`
