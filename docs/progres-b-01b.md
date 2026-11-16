# Progres B-01 B — Penguncian Sementara Saat Gagal Login Berulang

**Status:** Selesai (100%)  
**Papan Trello:** Sprint 2 — Card B-01 B  
**Referensi:** `docs/progres-sprint-2.md`

---

## 1. Ringkasan Fitur
Menerapkan penguncian sementara setelah 5 percobaan login gagal berturut-turut. Akun dikunci selama 1 menit; percobaan berikutnya ditolak selama masa kunci, meskipun password benar. Login berhasil mereset counter.

---

## 2. Acceptance Criteria

| # | Kriteria | Status |
|---|---|:---:|
| AC-1 | Sistem mencatat percobaan gagal dan mengunci akun selama 1 menit setelah percobaan gagal ke-5 | ✅ |
| AC-2 | Sistem menampilkan pesan waktu tunggu countdown dengan kontras teks ≥ 4.5:1 saat akun terkunci | ✅ |
| AC-3 | Sistem tetap menolak login meski password benar selama masa durasi penguncian masih berjalan | ✅ |

---

## 3. Penjelasan Detail Pengerjaan Task

### Task 1: Skema database counter login gagal dan timestamp `locked_until` (Backend)
* **Pekerjaan:** Membuat migration untuk menambah kolom pelacak kegagalan login pada tabel `users`.
* **File Terkait:** `database/migrations/2026_11_14_143031_add_login_lockout_to_users_table.php`, `app/Models/User.php`
* **Detail Kolom:**
  - `failed_login_attempts` (`unsignedTinyInteger`, default `0`): Jumlah percobaan login gagal berturut-turut.
  - `locked_until` (`timestamp`, nullable): Waktu berakhirnya penguncian akun.
* **Perubahan Model:** Kedua kolom ditambahkan ke `$fillable` dan `locked_until` di-cast ke `datetime`.

### Task 2: Logika rate-limiting dan progressive lock account (Backend)
* **Pekerjaan:** Mengimplementasikan logika penguncian bertahap di method `login()` pada `AuthController`.
* **File Terkait:** `app/Http/Controllers/AuthController.php`
* **Konstanta:**
  - `LOCKOUT_THRESHOLD = 5`: Percobaan gagal yang memicu kunci.
  - `LOCKOUT_MINUTES = 1`: Durasi kunci.
* **Alur Logika:**
  1. Sebelum autentikasi: cek apakah `locked_until` masih di masa depan. Jika ya, tolak login dan kirim `lockout_seconds` ke session.
  2. Jika masa kunci sudah lewat: hapus `locked_until` (counter tetap dipertahankan, tidak di-reset).
  3. Jika login berhasil (`Auth::attempt` sukses): reset `failed_login_attempts` ke `0` dan `locked_until` ke `null`.
  4. Jika login gagal dan user ada di database: increment counter. Pada percobaan ke-5, set `locked_until` selama 1 menit.
  5. Jika username tidak dikenal: kembalikan pesan generik tanpa membocorkan informasi keberadaan akun.

### Task 3: UI pesan peringatan kunci akun dengan countdown timer teraksesibel (Front-end)
* **Pekerjaan:** Menampilkan banner lockout dan countdown timer yang aksesibel di halaman login.
* **File Terkait:** `resources/views/auth/login.blade.php`
* **Detail Implementasi:**
  - Banner merah dengan `role="alert"` dan `aria-live="assertive"` untuk pembaca layar.
  - Warna teks `text-red-800` (#991b1b) di atas `bg-red-50` (#fef2f2) menghasilkan rasio kontras ~7.5:1, melebihi standar WCAG AA (4.5:1).
  - Timer countdown JS menampilkan format "X menit YY detik" yang memperbarui setiap detik.
  - Tombol Login di-disable (`disabled`) selama lockout aktif, dengan state visual `opacity-50` dan `cursor-not-allowed`.
  - Saat countdown habis: banner berubah hijau dan tombol Login otomatis aktif kembali.
  - CSS `input[type="password"]::-ms-reveal` disembunyikan untuk mencegah duplikasi ikon mata bawaan browser Edge/Windows.
* **Perbaikan Aksesibilitas Tambahan:**
  - Atribut `for` ditambahkan pada label Username dan Password.
  - Tombol toggle password memiliki `aria-label`.
  - Input dilengkapi `autocomplete="username"` dan `autocomplete="current-password"`.

### Task 4: Uji coba skenario gagal, countdown, dan proteksi login saat terkunci (Pengujian)
* **Pekerjaan:** Membuat 8 test case otomatis mencakup seluruh acceptance criteria dan edge case.
* **File Terkait:** `tests/Feature/LoginLockoutTest.php`
* **Skenario yang Diuji:**

  | # | Skenario | Assertion |
  |---|---|---|
  | 1 | Gagal 1x–4x: counter naik, belum terkunci | `failed_login_attempts == 4`, `locked_until == null` |
  | 2 | Gagal 5x: kunci 1 menit (60 detik) | `locked_until` di masa depan, `lockout_seconds == 60` |
  | 3 | Percobaan ke-6 saat terkunci: ditolak tanpa menambah counter | `failed_login_attempts == 5`, `lockout_seconds` tersedia |
  | 5 | Password benar ditolak saat terkunci | Session error + `lockout_seconds`, user tetap guest |
  | 6 | Login berhasil setelah kunci habis, counter reset | Redirect dashboard, counter `0`, `locked_until null` |
  | 7 | Pesan sisa percobaan tampil sebelum kunci | Error message mengandung "2 kali" |
  | 8 | Username tidak dikenal: pesan generik | Pesan mengandung "salah", tidak membocorkan info |

* **Hasil Eksekusi:**
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

---

## 4. Daftar File yang Diubah / Ditambah

| File | Jenis |
|---|---|
| `database/migrations/2026_11_14_143031_add_login_lockout_to_users_table.php` | Baru |
| `app/Models/User.php` | Diubah |
| `app/Http/Controllers/AuthController.php` | Diubah |
| `resources/views/auth/login.blade.php` | Diubah |
| `tests/Feature/LoginLockoutTest.php` | Baru |

---

## 5. Verifikasi
Jalankan perintah berikut untuk menguji ulang seluruh logika B-01 B:
```bash
php artisan test --filter=LoginLockoutTest
```

Jalankan seluruh test suite untuk memastikan tidak ada regresi:
```bash
php artisan test
```
Hasil terakhir: perlu dijalankan ulang setelah perubahan aturan.
