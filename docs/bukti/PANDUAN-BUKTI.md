# PANDUAN MENGAMBIL BUKTI (SCREENSHOT) PENGERJAAN
## Mode A — Proyek LDAP UY: Sesi Setelah Login LDAP

Panduan ini menjelaskan cara melihat dan mengambil bukti bahwa kode benar-benar
dibuat dan diuji. Ada empat jenis bukti:

| Jenis | Menunjukkan | Tingkat kekuatan |
|---|---|---|
| A. Skrip bukti otomatis | Semua kode + hasil test dalam satu tampilan | Cepat, lengkap |
| B. Screenshot kode di editor | Baris kode asli dengan nomor baris | Kuat |
| C. Screenshot diff commit | Baris yang ditambahkan (hijau) | Paling kuat untuk "pembuatan kode" |
| D. Screenshot browser/DevTools | Cookie sebelum/sesudah login, logout | Kuat untuk perilaku nyata |

---

## A. Cara Tercepat: Jalankan Skrip Bukti

Dari root proyek (`C:\Users\ARMAN DAVA WICAKSONO\Proyek-SAKTI`):

```powershell
powershell -ExecutionPolicy Bypass -File .\docs\bukti\ambil-bukti.ps1
```

Skrip mencetak **13 blok bukti** berurutan:

| Blok | Isi |
|---|---|
| BUKTI 1 | `git status` + riwayat commit + file pada commit fitur |
| BUKTI 2 | `AuthController` baris 106-120 — regenerasi ID sesi |
| BUKTI 3 | `AuthController` baris 186-215 — identitas minimal + purge |
| BUKTI 4 | `AuthController` baris 221-239 — logout aman |
| BUKTI 5 | `config/session.php` baris 30-50 — idle & absolute |
| BUKTI 6 | `config/session.php` baris 180-217 — atribut cookie |
| BUKTI 7 | `.env.example` baris 30-46 — nilai environment |
| BUKTI 8 | `config/ldap.php` baris 55-62 — logging LDAP mati |
| BUKTI 9 | `EnforceSessionTimeout` baris 35-120 — middleware |
| BUKTI 10 | Daftar 6 test + lokasi mock LDAP |
| BUKTI 11 | Hasil test: 6 passed (60 assertions) |
| BUKTI 12 | Nilai konfigurasi aktif dari Laravel |
| BUKTI 13 | Git bebas secret |

**Cara screenshot:** perbesar terminal (`Ctrl + scroll`), lalu `Win + Shift + S`
untuk memotong area tiap blok. Simpan dengan nama:

```
bukti-01-git-status.png
bukti-02-regenerasi-sesi.png
bukti-03-sanitasi-sesi.png
bukti-04-logout.png
bukti-05-konfigurasi-timeout.png
bukti-06-atribut-cookie.png
bukti-07-env-example.png
bukti-08-ldap-logging.png
bukti-09-middleware.png
bukti-10-daftar-test.png
bukti-11-test-pass.png
bukti-12-konfigurasi-aktif.png
bukti-13-git-secret.png
```

> Jika ingin output lebih rapi, jalankan di **Windows Terminal** dengan font
> `Cascadia Mono` ukuran 14 dan lebar jendela maksimal.

---

## B. Screenshot Kode di VS Code (dengan nomor baris)

Buka file berikut, aktifkan nomor baris (`View → Appearance → Show Line Numbers`
atau `Ctrl + ,` → `editor.lineNumbers: on`), lalu screenshot.

| No | File | Baris | Yang dibuktikan |
|---|---|---|---|
| B1 | `app/Http/Controllers/AuthController.php` | 106-120 | `$request->session()->regenerate()` tepat setelah `Auth::attempt` |
| B2 | `app/Http/Controllers/AuthController.php` | 186-215 | Hanya `user_id, nama, role, npm, nidn` + purge kunci sensitif |
| B3 | `app/Http/Controllers/AuthController.php` | 221-239 | `Auth::logout` + `invalidate` + `regenerateToken` + `Cookie::queue` |
| B4 | `app/Http/Middleware/EnforceSessionTimeout.php` | 19-27 | Daftar kunci sensitif |
| B5 | `app/Http/Middleware/EnforceSessionTimeout.php` | 64-114 | Cek idle/absolute + forced logout + 401/redirect |
| B6 | `config/session.php` | 35, 49 | `lifetime=30`, `absolute_lifetime=480` |
| B7 | `config/session.php` | 186, 199, 216 | `secure`, `http_only`, `same_site` |
| B8 | `.env.example` | 30-45 | Variabel environment dengan nilai aman |
| B9 | `config/ldap.php` | 60 | `LDAP_LOGGING` default `false` |
| B10 | `bootstrap/app.php` | 22-24 | Middleware terdaftar di grup `web` |
| B11 | `tests/Feature/SessionSecurityTest.php` | 80-95 | Mock LDAP (Mockery + bind closure) |
| B12 | `tests/Feature/SessionSecurityTest.php` | 107-131 | Test wajib 1: ID sesi berbeda |
| B13 | `tests/Feature/SessionSecurityTest.php` | 134-170 | Test wajib 2: cookie lama ditolak |

**Tips:** tekan `Ctrl + G` lalu ketik nomor baris untuk lompat langsung.

---

## C. Screenshot Diff Commit (Bukti Pembuatan Kode)

Ini bukti paling kuat karena memperlihatkan baris yang **ditambahkan** (hijau)
dan **dihapus** (merah).

1. Lihat daftar commit:

```powershell
git log --oneline -5
```

2. Cari commit berjudul `feat: implement secure session management ...`
   (di repositori ini hash-nya `2f2bb37`, sesuaikan bila berubah).

3. Tampilkan diff file tertentu:

```powershell
git show 2f2bb37 -- app/Http/Middleware/EnforceSessionTimeout.php
git show 2f2bb37 -- app/Http/Controllers/AuthController.php
git show 2f2bb37 -- config/session.php
```

4. Screenshot output terminal (baris `+` hijau = kode baru).

Alternatif di VS Code:
- Buka panel **Source Control** (`Ctrl + Shift + G`).
- Klik commit di bagian **Graph/History**.
- Klik file → tampil diff berdampingan.
- Screenshot panel diff.

Untuk rekap file yang berubah pada commit tersebut:

```powershell
git show --stat 2f2bb37
```

---

## D. Bukti Browser dan DevTools

### D.1 Jalankan aplikasi

```powershell
php artisan config:clear
php artisan serve
```

Buka `http://127.0.0.1:8000/login`.

> Jika server LDAP kampus tidak dapat diakses dari laptop, gunakan akun lokal
> untuk kebutuhan screenshot cookie (login fallback ke database):
>
> ```powershell
> php artisan tinker --execute="\App\Models\User::updateOrCreate(['username'=>'demo.bukti'],['name'=>'Demo Bukti','password'=>bcrypt('Demo12345!'),'role'=>'mahasiswa','prodi'=>'TI','npm'=>'000000000002']);"
> ```
>
> Login dengan `demo.bukti` / `Demo12345!`. Untuk bukti autentikasi LDAP
> sebenarnya, gunakan akun LDAP kampus dan screenshot juga halaman dashboard-nya.

### D.2 Bukti cookie SEBELUM login

1. Buka DevTools (`F12`) → tab **Application** → **Cookies** →
   `http://127.0.0.1:8000`.
2. Screenshot panel cookie. Jika cookie `laravel-session` belum ada, screenshot
   kondisi kosong; ini tetap sah sebagai "sebelum login".
3. Catat nilainya (mis. `abc123...`).

### D.3 Bukti cookie SESUDAH login

1. Login.
2. Buka kembali **Application → Cookies**.
3. Screenshot cookie `laravel-session`. Pastikan terlihat:
   - **Value berubah** dibanding sebelum login,
   - **HttpOnly** tercentang,
   - **SameSite = Lax**,
   - **Secure** kosong (lokal HTTP).
4. Buka **Console**, jalankan:

```javascript
document.cookie
```

Cookie sesi **tidak muncul** → membuktikan `HttpOnly` bekerja. Screenshot hasilnya.

### D.4 Bukti logout menghapus cookie

1. Sebelum logout, salin nilai cookie `laravel-session`.
2. Klik **Logout**.
3. Buka **Application → Cookies** → cookie `laravel-session` hilang/kedaluwarsa.
4. Screenshot panel cookie setelah logout.

### D.5 Bukti cookie lama ditolak

1. Masih di DevTools → **Application → Cookies**.
2. Tambahkan cookie baru:
   - Name: `laravel-session`
   - Value: nilai lama yang Anda salin
   - Path: `/`
3. Buka `http://127.0.0.1:8000/mahasiswa/dashboard`.
4. Buka tab **Network** → klik request `dashboard`.
5. Screenshot yang menunjukkan:
   - **Status 302**,
   - **Response Headers → Location: http://127.0.0.1:8000/login**.

Alternatif terminal (tanpa DevTools):

```powershell
curl.exe -i -b "laravel-session=<NILAI_COOKIE_LAMA>" http://127.0.0.1:8000/mahasiswa/dashboard
curl.exe -i -H "Accept: application/json" -b "laravel-session=<NILAI_COOKIE_LAMA>" http://127.0.0.1:8000/mahasiswa/dashboard
```

Expected:
- Request web: `HTTP/1.1 302 Found` + `Location: .../login`
- Request JSON: `HTTP/1.1 401 Unauthorized` + `{"message":...,"code":"idle_timeout"}`

### D.6 Bukti timeout (opsional, memperkuat)

Jalankan test khusus timeout:

```powershell
php artisan test tests/Feature/SessionSecurityTest.php --filter=timeout
```

Screenshot dua baris PASS:
- `idle timeout 30 menit memaksa logout`
- `absolute timeout 8 jam memaksa logout`

---

## E. Pemetaan Bukti ke Poin Rubrik

| Poin Rubrik | Bukti yang ditunjukkan |
|---|---|
| Regenerasi ID sesi | BUKTI 2, B12, D.2 vs D.3 (nilai cookie berubah) |
| Sanitasi data sesi | BUKTI 3, BUKTI 8, B11 |
| Atribut cookie | BUKTI 6, BUKTI 12, D.3 (`HttpOnly`, `SameSite`, `Secure`) |
| Idle timeout 30 menit | BUKTI 5, BUKTI 12, D.6 |
| Absolute timeout 8 jam | BUKTI 5, BUKTI 12, D.6 |
| Logout aman | BUKTI 4, D.4, D.5 |
| Test wajib lulus | BUKTI 11, B12, B13 |
| LDAP di-mock | BUKTI 10, B11 |
| Git bebas secret | BUKTI 1, BUKTI 13 |

---

## F. Checklist Cepat Sebelum Dikumpulkan

- [ ] Bukti 1 — daftar file/commit perubahan
- [ ] Bukti 2 — regenerasi ID sesi
- [ ] Bukti 3 — sanitasi sesi
- [ ] Bukti 4 — logout server + cookie
- [ ] Bukti 5 — idle 30 menit & absolute 8 jam
- [ ] Bukti 6 — `HttpOnly=true`, `SameSite=Lax`, `Secure` via env
- [ ] Bukti 10 — daftar test + mock LDAP
- [ ] Bukti 11 — 6 test PASS (60 assertions)
- [ ] Bukti 12 — nilai konfigurasi aktif (`30`, `480`, `true`, `lax`)
- [ ] Bukti 13 — tidak ada secret ter-track
- [ ] Screenshot browser: cookie sebelum vs sesudah login
- [ ] Screenshot browser: cookie lama ditolak (302/401)

---

## G. Troubleshooting

| Masalah | Solusi |
|---|---|
| Nilai config masih lama | `php artisan config:clear` |
| Halaman error 419 saat login | Refresh halaman login, pastikan cookie CSRF ada |
| Logout tidak menghapus cookie | Pastikan `SESSION_DOMAIN=null` dan akses lewat `127.0.0.1` (bukan campur `localhost`) |
| Test lambat | `LoginLockoutTest` memanggil LDAP asli; jalankan test fokus `SessionSecurityTest` untuk screenshot |
| Ingin bukti tanpa server LDAP | Gunakan `SessionSecurityTest` (LDAP di-mock) — sudah membuktikan semua skenario |
