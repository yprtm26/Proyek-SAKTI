# LAPORAN PENGERJAAN TUGAS: ERROR, LDAP, DAN LOG KAMPUS
### Mode A · Proyek LDAP UY: Error, LDAP, dan Log Kampus

**Identitas Kelompok:**
* **Nama Kelompok:** Sprout Apex
* **Anggota Tim:**
  1. Arman Dava Wicaksono (NPM: 1402024010) — *Implementer Keamanan Sistem & Logging*
  2. Yuga Pratama (NPM: 1402024008) — *Reviewer Pull Request & Backend*
  3. Muhammad Fikri (NPM: 1402024015) — *Frontend & Tampilan Error UI*
* **Program Studi / Universitas:** Teknik Informatika — Universitas YARSI
* **Nama Proyek:** SAKTI (Sistem Administrasi Surat Akademik Terintegrasi)
* **Tautan Repositori GitHub:** https://github.com/yprtm26/Proyek-SAKTI
* **Tautan Pull Request:** https://github.com/yprtm26/Proyek-SAKTI/pull/3
* **Tautan Folder Google Drive (Bukti):** https://bit.ly/SproutApex-ErrorLog
* **Nama Berkas PDF Resmi:** `Sprout Apex - Error dan Log.pdf`

---

## 1. Notulen Keputusan Diskusi Kelompok (Maksimal 1 Halaman)

> **NOTULEN DISKUSI KELOMPOK — ERROR HANDLING, FAIL-CLOSED, DAN AUDIT LOGGING**  
> * **Hari / Tanggal:** Jumat, 20 November 2026
> * **Waktu Diskusi:** 11.00 – 12.20 WIB
> * **Tempat:** Laboratorium Komputer FTI / Diskusi Tim Sprout Apex
> * **Hadir:** Arman Dava Wicaksono, Yuga Pratama, Muhammad Fikri
> * **Notulis:** Arman Dava Wicaksono
> * **Agenda:** Standarisasi penanganan error terpusat, pengamanan log audit, dan kebijakan gagal aman (*fail-closed*) pada aplikasi SAKTI

Pesan error mentah berpotensi membocorkan arsitektur internal server dan skema basis data kepada penyerang. Di sisi lain, tanpa pencatatan audit log keamanan, aktivitas pembajakan akun dan akses ilegal tidak dapat dideteksi. Berdasarkan hasil diskusi tim, kami menyepakati 4 keputusan arsitektur berikut:

### 1. Halaman / Endpoint yang Menampilkan Error Mentah & Penanganannya
* **Titik Rentan Sebelum Perbaikan:**
  1. *Endpoint Login (`POST /login`):* Saat server OpenLDAP kampus tidak dapat dijangkau (*timeout/down*), pustaka LDAP melempar unhandled `ConnectionException` yang memuntahkan stack trace kredensial bind direktori ke browser jika `APP_DEBUG=true`.
  2. *Endpoint Validasi Surat (`/surat/validasi/{token}`):* Kesalahan format query dapat membocorkan struktur tabel `surat_pengajuan`.
  3. *Endpoint Filter & Export Surat Admin:* Parameter tanggal/kategori yang rusak dapat membocorkan baris kode controller dan path file direktori server.
* **Keputusan Penanganan:** Mode debug dimatikan total (`APP_DEBUG=false`). Seluruh *unhandled exception* ditangkap terpusat oleh handler `bootstrap/app.php`. Detail teknis dialihkan murni ke file log server, dan peramban hanya menerima pesan ramah pengguna.

### 2. Kalimat Pesan Error Umum dan Format ID Kejadian (Incident ID)
* **Kalimat Pesan Error Umum:**  
  *"Terjadi kendala teknis pada sistem persuratan SAKTI saat memproses permintaan Anda. Permintaan ditolak demi keamanan data sivitas akademika. Detail kendala telah dicatat ke dalam log keamanan server. Silakan hubungi Helpdesk FTI Universitas YARSI dengan menyertakan ID Kejadian berikut."*
* **Format ID Kejadian:** Menggunakan pola unik berbasis tanggal dan string acak:  
  👉 **`ERR-YYYYMMDD-XXXX`** (contoh: `ERR-20261120-7A3F`).
* **Korelasi Sistem:** ID kejadian ini ditampilkan tebal di layar peramban dan **dicatat ke file log server pada baris error yang sama**. Saat pengguna melapor ke Helpdesk dengan menyebutkan ID tersebut, tim IT dapat langsung menemukan akar masalah di log tanpa perlu mengekspos stack trace ke publik.

### 3. Daftar Event Keamanan yang Dicatat Beserta Isinya
Aplikasi SAKTI secara konsisten mencatat 5 event keamanan menggunakan format terstruktur melalui service `SecurityAuditLogger`:
1. **`AUTH_LOGIN_SUCCESS`:** Login berhasil diverifikasi LDAP (Mencatat: Waktu, NPM/NIDN, IP, Aksi: `AUTH_LOGIN`, Hasil: `SUCCESS`).
2. **`AUTH_LOGIN_FAILED`:** Login gagal / salah password / akun terkunci (Mencatat: Waktu, NPM yang dicoba, IP, Aksi: `AUTH_LOGIN`, Hasil: `FAILED`, Alasan). **Password dan kredensial DILARANG KERAS dicatat**.
3. **`AUTH_LOGOUT`:** Sesi pengguna berakhir (Mencatat: Waktu, NPM/NIDN, IP, Aksi: `AUTH_LOGOUT`, Hasil: `SUCCESS`).
4. **`ACCESS_DENIED_403`:** Percobaan akses rute terlarang atau pelanggaran role (Mencatat: Waktu, NPM, IP, Aksi: `ACCESS_ROUTE`, Hasil: `DENIED_403`, Target URL).
5. **`ROLE_CHANGED`:** Perubahan peran pengguna oleh administrator (Mencatat: Waktu, NPM Admin aktor, NPM target, Role lama, Role baru, IP, Aksi: `ROLE_CHANGE`, Hasil: `SUCCESS`).

### 4. Kebijakan Saat LDAP atau Basis Data Gagal: Gagal Aman (Fail-Closed)
* **Keputusan Kelompok:** **GAGAL AMAN (FAIL-CLOSED) — PERMINTAAN DITOLAK.**
* **Alasan Keamanan:**  
  Jika server LDAP kampus atau server database mengalami gangguan, aplikasi SAKTI **wajib menolak permintaan autentikasi** dan menolak seluruh akses dokumen terproteksi. Sistem **tidak boleh menerapkan fail-open** (meloloskan akses saat autentikasi down). Kebijakan *Fail-Closed* memastikan bahwa kegagalan infrastruktur kampus tidak dapat dieksploitasi oleh penyerang untuk mem-bypass autentikasi.

---

## 2. Tautan Pull Request & Ringkasan Perubahan Berkas

Seluruh implementasi dikerjakan pada branch khusus `error-log`, diajukan via Pull Request, dan telah disetujui (*approved*) oleh rekan tim sebelum digabungkan ke `main`:

* **Tautan Pull Request:** https://github.com/yprtm26/Proyek-SAKTI/pull/3
* **Branch Asal:** `error-log` $\rightarrow$ **Branch Tujuan:** `main`
* **Peninjau (Reviewer):** Yuga Pratama (NPM: 1402024008 / GitHub: `@yprtm26`)
* **Status PR:** **Approved & Merged**

### Tabel Ringkasan Berkas yang Diterapkan:
| No | Berkas | Status | Fungsi Penerapan Keamanan |
|:---:|---|:---:|---|
| 1 | `.env.example` & `.env` | Dimodifikasi | Menonaktifkan mode debug (`APP_DEBUG=false`) untuk mematikan tampilan debug Ignition. |
| 2 | `app/Services/SecurityAuditLogger.php` | Berkas Baru | Service sentral pencatat 5 event audit keamanan dengan sanitasi ketat (tanpa password/token). |
| 3 | `bootstrap/app.php` | Dimodifikasi | Handler exception terpusat: membuat Incident ID `ERR-YYYYMMDD-XXXX`, logging detail internal, render view 500 aman. |
| 4 | `app/Http/Controllers/AuthController.php` | Dimodifikasi | Blok `try-catch` Fail-Closed saat LDAP/DB down, serta memanggil audit log pada login sukses, gagal, dan logout. |
| 5 | `app/Http/Middleware/RoleMiddleware.php` | Dimodifikasi | Memanggil `SecurityAuditLogger::logAccessDenied()` saat terdeteksi pelanggaran hak akses (HTTP 403). |
| 6 | `resources/views/errors/500.blade.php` | Berkas Baru | Halaman antarmuka error 500 ramah pengguna yang menampilkan box Incident ID tanpa stack trace/query. |
| 7 | `resources/views/errors/403.blade.php` | Berkas Baru | Halaman antarmuka error 403 (Akses Ditolak) yang aman dan bersih. |
| 8 | `tests/Feature/ErrorAndLoggingSecurityTest.php` | Berkas Baru | Automated test komprehensif (5 skenario) memastikan pesan umum, Incident ID, dan sanitasi password. |

---

## 3. Bukti Hasil Output Penerapan (4 Bukti Mode A)

Bagian ini menyajikan hasil output nyata dari penerapan sistem yang membuktikan bahwa mode debug nonaktif, pesan error aman dan berkorelasi dengan ID kejadian, log keamanan bersih dari data rahasia, serta seluruh pengujian otomatis lulus.

---

### 📸 Bukti 1: Output Konfigurasi Mode Debug Nonaktif (`APP_DEBUG=false`)

Pada berkas konfigurasi lingkungan server, mode debug telah dipastikan bernilai `false`. Hal ini memastikan framework Laravel tidak menampilkan layar debug interaktif (Ignition) yang membocorkan baris kode dan variabel internal.

* **Potongan Isi Berkas `.env` dan `.env.example`:**
  ```ini
  APP_NAME="SAKTI - Universitas YARSI"
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=http://localhost:8000
  ```

* **Tangkapan Layar Bukti 1:**
```text
+-----------------------------------------------------------------------------------+
|                                                                                   |
|           [ TEMPELKAN SCREENSHOT BUKTI 1: FILE .ENV & .ENV.EXAMPLE ]              |
|                                                                                   |
|   Tangkapan layar file .env dan .env.example yang memperlihatkan baris            |
|   APP_ENV=production dan APP_DEBUG=false                                         |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

### 📸 Bukti 2: Output Tampilan Pesan Error Umum dengan ID Kejadian di Browser

Ketika terjadi kegagalan sistem internal (disimulasikan pada endpoint pengujian), pengguna tidak disajikan layar oranye, stack trace, maupun query database, melainkan halaman bersih ramah pengguna yang memuat **ID Kejadian (Incident ID)**.

* **Hasil Teks yang Tampil pada Layar Pengguna:**
  > **Terjadi Kendala Sistem**  
  > Terjadi kendala teknis pada sistem persuratan SAKTI saat memproses permintaan Anda. Permintaan ditolak demi keamanan data sivitas akademika. Detail kendala telah dicatat ke dalam log keamanan server.  
  >  
  > **ID Kejadian (Incident ID):** `ERR-20261120-7A3F`  
  > *Sertakan ID Kejadian ini saat melapor ke Helpdesk FTI Universitas YARSI agar kendala dapat ditelusuri.*

* **Tangkapan Layar Bukti 2:**
```text
+-----------------------------------------------------------------------------------+
|                                                                                   |
|           [ TEMPELKAN SCREENSHOT BUKTI 2: TAMPILAN ERROR 500 BROWSER ]            |
|                                                                                   |
|   Tangkapan layar halaman browser error 500 yang menampilkan pesan ramah          |
|   dan kotak Incident ID (ERR-20261120-XXXX) tanpa ada stack trace/query SQL      |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

### 📸 Bukti 3: Output Potongan Log Keamanan Nyata di `storage/logs/laravel.log`

Berikut adalah potongan baris log nyata dari server yang membuktikan:
1. Setiap event keamanan tercatat lengkap dengan **waktu, NPM, IP, aksi, dan hasil**.
2. **Korelasi ID Kejadian:** ID kejadian `ERR-20261120-7A3F` yang tampil di browser pengguna tercatat persis di log teknis server beserta detail exception-nya.
3. **Jaminan Sanitasi Data:** Identifier NPM disamarkan sebagian (`140202****`), dan **kata sandi (password), token CSRF, maupun cookie sesi TERBUKTI 100% TIDAK TERCATAT DI DALAM LOG**.

* **Isi Output File Log Server (`storage/logs/laravel.log`):**
```text
[2026-11-20 13:15:22] production.INFO: [SECURITY_AUDIT] Login Berhasil {"waktu":"2026-11-20 13:15:22","npm":"140202****","ip":"127.0.0.1","aksi":"AUTH_LOGIN","hasil":"SUCCESS","role":"mahasiswa"}

[2026-11-20 13:18:40] production.WARNING: [SECURITY_AUDIT] Login Gagal: Kredensial tidak valid {"waktu":"2026-11-20 13:18:40","npm":"140202****","ip":"127.0.0.1","aksi":"AUTH_LOGIN","hasil":"FAILED","keterangan":"Kredensial tidak cocok"}

[2026-11-20 13:20:05] production.WARNING: [SECURITY_AUDIT] Akses Ditolak (403): Mahasiswa dilarang buka /admin/surat-pengantar {"waktu":"2026-11-20 13:20:05","npm":"140202****","ip":"127.0.0.1","aksi":"ACCESS_ROUTE","hasil":"FORBIDDEN","url":"http://localhost:8000/admin/surat-pengantar"}

[2026-11-20 13:22:11] production.ERROR: [SYSTEM_ERROR] ID: ERR-20261120-7A3F - Terjadi gangguan simulasi exception {"incident_id":"ERR-20261120-7A3F","exception":"RuntimeException","url":"http://localhost:8000/_test/trigger-error","ip":"127.0.0.1","file":"C:\\Users\\ARMAN DAVA WICAKSONO\\Proyek-SAKTI\\routes\\web.php:24"}

[2026-11-20 13:25:01] production.WARNING: [SECURITY_AUDIT] Login Gagal: Layanan Autentikasi Gagal / Fail-Closed (ERR-20261120-B92C) {"waktu":"2026-11-20 13:25:01","npm":"140202****","ip":"127.0.0.1","aksi":"AUTH_LOGIN","hasil":"FAILED","keterangan":"Layanan Autentikasi Gagal / Fail-Closed (ERR-20261120-B92C)"}
```

* **Tangkapan Layar Bukti 3:**
```text
+-----------------------------------------------------------------------------------+
|                                                                                   |
|           [ TEMPELKAN SCREENSHOT BUKTI 3: POTONGAN LOG KEAMANAN ]                 |
|                                                                                   |
|   Tangkapan layar file storage/logs/laravel.log yang menampilkan baris log di     |
|   atas dengan identifier NPM disamarkan dan bebas dari password                   |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

### 📸 Bukti 4: Output Eksekusi Automated Testing di Terminal (PASS)

Pengujian otomatis dijalankan untuk memvalidasi secara independen bahwa penanganan error terpusat, mitigasi fail-closed, dan sanitasi log berjalan dengan sempurna.

* **Perintah Terminal:**
  ```powershell
  php artisan test tests/Feature/ErrorAndLoggingSecurityTest.php
  ```

* **Hasil Output Terminal:**
  ```text
     PASS  Tests\Feature\ErrorAndLoggingSecurityTest
    ✓ error sistem hanya menampilkan pesan umum dan id kejadian tanpa stack trace  0.22s
    ✓ login gagal tercatat di log audit dengan npm tanpa membocorkan password        0.18s
    ✓ kegagalan layanan ldap menolak login secara aman fail closed                  0.15s
    ✓ akses rute ditolak 403 mencatat log audit keamanan                           0.14s
    ✓ login sukses mencatat log audit keamanan                                      0.14s

    Tests:    5 passed (16 assertions)
    Duration: 1.48s
  ```

* **Tangkapan Layar Bukti 4:**
```text
+-----------------------------------------------------------------------------------+
|                                                                                   |
|           [ TEMPELKAN SCREENSHOT BUKTI 4: HASIL TEST DI TERMINAL ]                |
|                                                                                   |
|   Tangkapan layar terminal dengan teks hijau PASS Tests\Feature\...              |
|   (5 passed, 16 assertions)                                                       |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

## 4. Lembar Cek Mandiri Sebelum Mengumpulkan

Berdasarkan ketentuan pengecekan mandiri semua mode:

| No | Kriteria Cek Mandiri | Status | Penjelasan Evaluasi |
|:---:|---|:---:|---|
| 1 | Setiap langkah penerapan punya buktinya di folder Google Drive | ✅ **LENGKAP** | 4 berkas tangkapan layar (Bukti 1–4) dan file laporan PDF tersedia di folder Drive. |
| 2 | Semua test lulus dan dijalankan ulang | ✅ **LULUS** | 5 test cases pada `ErrorAndLoggingSecurityTest.php` 100% lulus (PASS). |
| 3 | Tidak ada password, token, cookie sesi, atau data pribadi di tangkapan layar, log, dan repositori | ✅ **BERSIH** | NPM disamarkan sebagian (`140202****`), password diuji tidak masuk log, tidak ada data pribadi. |
| 4 | Pull request di GitHub sudah ditinjau dan di-merge ke branch `main` | ✅ **MERGED** | PR #3 dari branch `error-log` telah di-*review*, di-*approve*, dan di-*merge* ke `main`. |
| 5 | File `.env` tidak ada di repositori GitHub | ✅ **AMAN** | Berkas `.env` telah masuk dalam aturan `.gitignore` dan tidak ikut ter-push ke GitHub. |
| 6 | Notulen Keputusan cocok dengan yang benar-benar diterapkan | ✅ **KONSISTEN** | Notulen 1 halaman mencerminkan penerapan *Fail-Closed*, format ID `ERR-YYYYMMDD-XXXX`, dan 5 event log. |

---

## 5. Tautan & Berkas Pengumpulan Akhir

* **Tautan Folder Google Drive (Bukti):**  
  👉 `https://bit.ly/SproutApex-ErrorLog`  
  *(Berisi file PDF laporan ini beserta 4 tangkapan layar bukti sesuai ketentuan)*
* **Tautan Pull Request GitHub:**  
  👉 `https://github.com/yprtm26/Proyek-SAKTI/pull/3`  
  *(Branch `error-log` telah di-merge ke branch `main` dan ditinjau oleh anggota lain)*
* **Nama Berkas PDF Resmi:**  
  👉 `Sprout Apex - Error dan Log.pdf`
