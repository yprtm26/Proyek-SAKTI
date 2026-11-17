# LAPORAN & DOKUMEN PENGUMPULAN TUGAS — MODE A
## Pengamanan Autentikasi dan Manajemen Sesi Setelah Login LDAP

| Item | Keterangan |
|---|---|
| **Nama Penyusun** | Arman Dava Wicaksono |
| **NPM** | 1402024010 |
| **Kelompok** | Sprout Apex |
| **Program Studi** | Teknik Informatika — Universitas YARSI |
| **Proyek** | SAKTI (Sistem Administrasi Surat Akademik Terintegrasi) |
| **Mode Tugas** | MODE A — Proyek LDAP UY: Sesi Setelah Login LDAP |
| **Framework** | Laravel 12, PHP 8.2+ (runtime uji: PHP 8.4.15) |
| **Pustaka LDAP** | `directorytree/ldaprecord-laravel` |
| **Repositori** | https://github.com/yprtm26/Proyek-SAKTI |
| **Branch Fitur** | `feature/mode-a-session-security` |
| **Tanggal** | 17 November 2026 |
| **Status Automated Test** | **6 passed (60 assertions)** |

---

## RINGKASAN HASIL

Seluruh kontrol keamanan wajib pada rubrik Mode A telah diterapkan langsung pada basis kode dan
diverifikasi otomatis tanpa koneksi ke server LDAP kampus:

| No | Kontrol Keamanan | Status | Bukti Utama |
|:---:|---|:---:|---|
| 1 | Regenerasi ID sesi setelah login LDAP (anti session fixation) | Selesai | `AuthController::login()` — `session()->regenerate()`; Test 1 |
| 2 | Sanitasi data sesi (identitas minimal, bebas password/kredensial) | Selesai | `storeMinimalSessionIdentity()`, `EnforceSessionTimeout::SENSITIVE_KEYS`; Test 3 |
| 3 | Atribut cookie `HttpOnly`, `SameSite=Lax`, `Secure` via `.env` | Selesai | `config/session.php`, `.env.example`; Test 6 |
| 4 | Idle timeout 30 menit + absolute timeout 8 jam | Selesai | `EnforceSessionTimeout`; Test 4 & 5 |
| 5 | Logout menghapus sesi server + cookie browser | Selesai | `AuthController::logout()`; Test 2 |
| 6 | Automated test dengan LDAP di-mock (offline) | Selesai | `tests/Feature/SessionSecurityTest.php` — 6 passed |
| 7 | Pull Request ditinjau rekan tim | Menunggu review | Bagian 5 dokumen ini |

> **Catatan penting:** aplikasi ini adalah aplikasi web berbasis **session cookie**, bukan aplikasi
> mobile berbasis token. Karena itu mekanisme yang dipakai adalah session Laravel (`web` guard),
> bukan secure storage/refresh token.

---

# BAGIAN 1 — NOTULEN KEPUTUSAN ARSITEKTUR KEAMANAN
### (Maksimal 1 halaman)

> **NOTULEN KEPUTUSAN ARSITEKTUR KEAMANAN**
> **Pengamanan Autentikasi dan Manajemen Sesi Web (Mode A)**
> Proyek SAKTI — Sistem Administrasi Surat Akademik Terintegrasi
>
> **Subjek:** Standarisasi manajemen sesi dan autentikasi pengguna LDAP UY
> **Penyusun:** Arman Dava Wicaksono (NPM 1402024010) — Kelompok Sprout Apex
> **Dasar:** Hasil audit keamanan sesi dan rubrik tugas Mode A (100 poin)
>
> Berdasarkan audit pada `AuthController`, `config/session.php`, `config/ldap.php`,
> `bootstrap/app.php`, dan `routes/web.php`, ditetapkan empat keputusan arsitektur berikut.
> Seluruh keputusan telah diterapkan pada kode dan diuji otomatis.

**1. Data yang disimpan dan dilarang disimpan di sesi setelah login LDAP**

Setelah `Auth::attempt()` memverifikasi kredensial ke LDAP berhasil, ID sesi lama **wajib**
dibuang dan diganti dengan ID baru melalui `$request->session()->regenerate()` sebelum respons
redirect dikirim. Sesi server hanya menyimpan identitas minimal yang benar-benar dibutuhkan
antarmuka dan otorisasi:

- `auth_identity.user_id` — primary key pengguna untuk guard Laravel;
- `auth_identity.nama` — nama tampilan;
- `auth_identity.role` — peran untuk otorisasi menu/route;
- `auth_identity.npm` atau `auth_identity.nidn` — nomor identitas akademik;
- `auth_login_at` — timestamp login pertama (dasar absolute timeout, tidak pernah diperpanjang);
- `auth_last_activity` — timestamp aktivitas terakhir (dasar idle timeout);
- `_token` — token CSRF bawaan Laravel.

**Dilarang keras** disimpan di sesi: password dalam bentuk apa pun (plaintext maupun hash),
password/kredensial bind LDAP, serta atribut privat direktori (`userPassword`, `mail`
alternatif, `telephoneNumber`). Larangan ini ditegakkan berlapis: (a) `sync_passwords` bernilai
`false` pada provider LDAP sehingga hash password tidak pernah disalin ke basis data; (b)
`storeMinimalSessionIdentity()` hanya menulis kunci di atas; (c) middleware
`EnforceSessionTimeout` memanggil `forget()` terhadap kunci sensitif (`password`,
`password_confirmation`, `credentials`, `ldap_password`, `ldap_credentials`, `bind_password`,
`userpassword`) pada setiap request sebagai pertahanan berlapis; dan (d) logging LDAP
dinonaktifkan secara default melalui `'enabled' => env('LDAP_LOGGING', false)` agar kredensial
tidak bocor ke berkas log.

**2. Batas waktu idle 30 menit dan absolut 8 jam**

Middleware `EnforceSessionTimeout` — terdaftar pada grup `web` di `bootstrap/app.php` — memeriksa
dua ambang batas pada setiap request terautentikasi:

- **Idle timeout 30 menit** (`SESSION_LIFETIME=30`): logout bila
  `($now - auth_last_activity) >= 30 × 60` detik. `auth_last_activity` diperbarui pada setiap
  request yang sah.
- **Absolute timeout 8 jam / 480 menit** (`SESSION_ABSOLUTE_LIFETIME=480`): logout bila
  `($now - auth_login_at) >= 480 × 60` detik. Nilai ini **tidak pernah diperpanjang** oleh
  aktivitas apa pun.

Alasan kontekstual sivitas akademika SAKTI: 30 menit adalah durasi wajar bagi pengguna yang
meninggalkan komputer bersama di laboratorium, ruang tata usaha, atau perpustakaan tanpa logout;
akun tidak dibiarkan terbuka berjam-jam hanya karena satu tab terlupa. Batas absolut 8 jam setara
satu shift kerja atau satu hari perkuliahan aktif, sehingga pengguna yang terus bekerja tetap
dipaksa melakukan autentikasi ulang sekali per hari dan hak aksesnya diverifikasi kembali ke
direktori pusat LDAP. Kedua ambang dihitung dengan operator `>=` agar batas yang disebut benar-benar
berlaku, dan respons pemaksaan logout berupa redirect `302` ke `/login` untuk request web atau
`401 Unauthorized` berformat JSON untuk request API/AJAX.

**3. Alur logout di server dan peramban**

Saat pengguna memanggil `POST /logout`, aplikasi mengeksekusi urutan berikut secara berurutan:

1. `Auth::logout()` — status autentikasi guard `web` dicabut dan `remember_token` pengguna
   dihapus, sehingga sesi tidak dapat dipulihkan lewat "remember me".
2. `$request->session()->invalidate()` — seluruh data sesi di server dihancurkan dan ID sesi
   dimigrasikan; baris/berkas sesi lama tidak berlaku lagi.
3. `$request->session()->regenerateToken()` — token CSRF lama diganti, sehingga form atau request
   yang masih memegang token lama ditolak (mencegah replay CSRF pasca-logout).
4. `Cookie::queue(Cookie::forget(config('session.cookie'), config('session.path'),
   config('session.domain')))` — respons membawa header `Set-Cookie` dengan nilai kosong dan
   `expires` di masa lampau untuk nama cookie sesi tersebut, sehingga peramban menghapus cookie
   seketika. Cookie diantrekan (bukan `withCookie`) agar header penghapusan ditulis setelah
   `StartSession` menulis cookie sesi baru dan tidak tertimpa.
5. Redirect `302` ke `/login`.

Konsekuensinya, cookie sesi lama yang sempat disalin penyerang tidak dapat dipakai ulang: sesi
tersebut sudah tidak ada di server. Request berikutnya dengan cookie lama akan dibuatkan sesi baru
tanpa status autentikasi, lalu ditolak dan dialihkan ke `/login` (atau `401` untuk JSON).

**4. Atribut cookie sesi dan alasan teknis**

| Atribut | Nilai | Perlindungan |
|---|---|---|
| `HttpOnly` | `true` (`SESSION_HTTP_ONLY=true`) | Cookie tidak dapat dibaca/dimanipulasi `document.cookie`, sehingga pencurian sesi lewat **XSS** tidak mungkin dilakukan dari JavaScript. |
| `SameSite` | `Lax` (`SESSION_SAME_SITE=lax`) | Cookie tidak dikirim pada request lintas situs yang berisiko (POST/iframe), sehingga mempersempit **CSRF**; `Lax` tetap mengizinkan navigasi tautan biasa agar alur akademik tidak terganggu. |
| `Secure` | Dinamis via `SESSION_SECURE_COOKIE`; default `true` saat `APP_ENV=production`, `false` untuk HTTP lokal | Cookie hanya dikirim melalui HTTPS/TLS, sehingga tidak dapat disadap atau dibajak pada jaringan **Man-in-the-Middle**. |
| `Path` / `Domain` | `/` dan `null` | Membatasi cakupan cookie ke aplikasi, bukan seluruh domain kampus. |

Kombinasi ketiganya memberi pertahanan berlapis: XSS tidak bisa mencuri cookie, CSRF tidak bisa
memanfaatkan cookie lintas situs, dan MitM tidak bisa menyadap cookie di jaringan.

---

# BAGIAN 2 — TABEL RINGKASAN PERUBAHAN FILE KODE

| No | File | Status | Fungsi Keamanan |
|:---:|---|:---:|---|
| 1 | `app/Http/Controllers/AuthController.php` | Diubah | Memanggil `$request->session()->regenerate()` tepat setelah `Auth::attempt()` sukses (anti session fixation); `storeMinimalSessionIdentity()` hanya menulis identitas minimal dan menghapus kunci sensitif; `logout()` memanggil `Auth::logout()`, `invalidate()`, `regenerateToken()`, lalu `Cookie::queue(Cookie::forget(...))`. |
| 2 | `app/Http/Middleware/EnforceSessionTimeout.php` | **Baru** | Menegakkan idle timeout 30 menit dan absolute timeout 480 menit pada setiap request terautentikasi; membersihkan kunci sensitif dari sesi; memaksa logout + invalidasi sesi + regenerasi token + penghapusan cookie; mengembalikan `302 /login` atau `401` JSON. |
| 3 | `bootstrap/app.php` | Diubah | Mendaftarkan `EnforceSessionTimeout` ke grup middleware `web` (dijalankan setelah `StartSession`), dan alias `role`. |
| 4 | `config/session.php` | Diubah | `lifetime` default 30 menit; menambah `absolute_lifetime` 480 menit; `secure` dinamis dari `.env` dengan default aman `true` di production; `http_only=true`; `same_site=lax`; `driver=database` (default aman untuk produksi). |
| 5 | `config/ldap.php` | Diubah | `'logging.enabled' => env('LDAP_LOGGING', false)` — logging LDAP mati secara default sehingga kredensial bind/user dan atribut direktori tidak tercatat di log. |
| 6 | `.env.example` | Diubah | Menambahkan `SESSION_LIFETIME=30`, `SESSION_ABSOLUTE_LIFETIME=480`, `SESSION_SECURE_COOKIE=false` (lokal)/`true` (production HTTPS), `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`, dan `LDAP_LOGGING=false`, tanpa secret asli. |
| 7 | `tests/Feature/SessionSecurityTest.php` | **Baru** | 6 feature test offline dengan `LdapUserRepository` dan `LdapUserAuthenticator` di-mock (Mockery): regenerasi ID sesi, replay cookie pasca-logout, sanitasi sesi, idle timeout, absolute timeout, dan atribut cookie. |
| 8 | `docs/MODE-A-SPRINT-APEX-AUTENTIKASI.md` | **Baru** | Dokumen laporan & pengumpulan ini (notulen, ringkasan perubahan, panduan bukti, format pengumpulan). |
| 9 | `.gitignore` | Diverifikasi | `.env`, `.env.backup`, `.env.production` sudah diabaikan; `.env` asli tidak pernah ter-track. Hanya `.env.example` yang di-commit. |

### Perbandingan audit awal → kondisi akhir

| Temuan Audit Awal | Kondisi Akhir |
|---|---|
| Tidak ada batas waktu absolut; sesi dapat hidup selama pengguna aktif | Absolute timeout 8 jam di `EnforceSessionTimeout` |
| Idle bawaan 120 menit terlalu lama | `SESSION_LIFETIME=30` menit |
| Logging LDAP aktif secara default sehingga berisiko membocorkan kredensial | `LDAP_LOGGING=false` sebagai default |
| Logout belum mengirim header penghapusan cookie fisik di browser | `Cookie::queue(Cookie::forget(...))` pada logout dan timeout |
| Belum ada bukti otomatis untuk skenario keamanan sesi | 6 feature test offline, seluruhnya PASS |

---

# BAGIAN 3 — PANDUAN PENGAMBILAN 4 BUKTI SCREENSHOT
## Aturan Penyamaran Nilai (Masking) — Wajib Dibaca Sebelum Screenshot

Tujuan masking: membuktikan **perilaku** keamanan tanpa membocorkan **rahasia** apa pun.

**Wajib disamarkan/dicensor:**

- Seluruh nilai cookie sesi (`Value` pada DevTools), baik sebelum maupun sesudah login.
- Token CSRF (`_token`, `XSRF-TOKEN`) dan nilai header `Set-Cookie`.
- Password, hash password, dan kredensial bind LDAP (jangan pernah tampil di layar).
- NPM/NIDN, email, nomor telepon, dan data pribadi lain yang tidak diperlukan.
- Authorization header dan payload form login.

**Wajib tetap terlihat (tidak boleh disensor):**

- Nama cookie (`laravel-session`) dan kolom `Domain`, `Path`, `Expires`.
- Status centang `HttpOnly` dan nilai `SameSite` (`Lax`).
- Status HTTP (`302 Found`) dan header `Location: .../login`.
- Perintah terminal dan baris hasil `6 passed (60 assertions)`.

**Cara masking yang rapi dan sah:**

1. Ambil screenshot dengan `Win + Shift + S`.
2. Buka di Paint / Snipping Tool / editor gambar.
3. Tutup nilai rahasia dengan kotak hitam pekat, atau ganti dengan teks `[DISENSOR]` /
   `••••••••`. Jangan memakai blur tipis yang masih bisa dibaca.
4. Pertahankan panjang relatif hanya bila perlu (mis. tampilkan 4 karakter awal), selebihnya tutup.
5. Simpan sebagai PNG (bukan JPG) agar teks tetap tajam, lalu beri nama:
   `bukti-01-cookie-sebelum-login.png`, `bukti-02-cookie-sesudah-login.png`,
   `bukti-03-cookie-lama-ditolak.png`, `bukti-04-test-pass.png`.
6. Pastikan tidak ada tab browser, notifikasi, atau nama pengguna OS yang ikut terambil.

> **Tips:** lakukan screenshot pada jendela browser khusus (mode Incognito) dan tutup DevTools
> tambahan sebelum mengambil gambar.

### Bukti 1 — Cookie sebelum login + atribut `HttpOnly` dan `SameSite: Lax`

**Langkah:**

1. Jalankan aplikasi: `php artisan config:clear` lalu `php artisan serve`.
2. Buka `http://127.0.0.1:8000/login` di jendela Incognito.
3. Tekan `F12` → tab **Application** → **Storage → Cookies** → pilih `http://127.0.0.1:8000`.
4. Arahkan kursor ke baris cookie `laravel-session` sehingga panel detail atribut terbuka.
5. Screenshot area tabel cookie **dan** panel atribut (Domain, Path, Expires, HttpOnly,
   Secure, SameSite).
6. Sensor kolom `Value` sepenuhnya.
7. (Opsional, memperkuat) Buka **Console**, jalankan `document.cookie`, tunjukkan cookie sesi
   **tidak muncul** — bukti `HttpOnly` bekerja. Sensor nilai cookie lain yang tampil.

**Yang harus terlihat pada gambar:** nama cookie, `HttpOnly ✓`, `SameSite Lax`, `Expires`
(±30 menit), `Value` tertutup penuh, URL halaman `/login`.

### Bukti 2 — Cookie sesudah login menunjukkan pergantian nilai sesi

**Langkah:**

1. Login sebagai pengguna uji (akun LDAP kampus, atau akun demo lokal bila LDAP tidak terjangkau).
2. Setelah masuk dashboard, buka kembali **Application → Cookies**.
3. Screenshot baris cookie yang sama.
4. Sensor `Value`, lalu beri anotasi/caption: “Nilai berbeda dari Bukti 1” (jangan tulis nilai
   aslinya di caption).
5. Sertakan juga tab **Network** → request `POST /login` → **Response Headers** yang memuat
   `Set-Cookie` baru (nilai disensor), bila memungkinkan.

**Yang harus terlihat:** nama cookie sama, `Value` berubah (disensor), `HttpOnly ✓`,
`SameSite Lax`, `Secure` kosong untuk HTTP lokal / tercentang untuk HTTPS production.

> Bukti 1 vs Bukti 2 adalah inti klaim **anti session fixation**: ID sesi tamu dibuang dan diganti
> ID baru setelah verifikasi LDAP berhasil.

### Bukti 3 — Cookie lama ditolak setelah logout (HTTP 302 → `/login`)

**Langkah (DevTools):**

1. Sebelum logout, salin nilai cookie `laravel-session` ke catatan sementara (jangan
   ditampilkan di screenshot).
2. Klik **Logout**.
3. Buka **Application → Cookies**, tunjukkan cookie sesi sudah hilang/kedaluwarsa.
4. Tambahkan kembali cookie lama secara manual (Name: `laravel-session`, Value: nilai lama,
   Path: `/`).
5. Buka `http://127.0.0.1:8000/mahasiswa/dashboard`.
6. Buka **Network** → klik request `dashboard` → tab **Headers**.
7. Screenshot: **Status Code 302**, **Response Headers → Location:
   `http://127.0.0.1:8000/login`**, dan (bila terlihat) `Set-Cookie` dengan `expires` masa lampau
   (nilai disensor).

**Alternatif terminal (lebih mudah disensor):**

```powershell
curl.exe -i -b "laravel-session=<NILAI_COOKIE_LAMA>" http://127.0.0.1:8000/mahasiswa/dashboard
```

Screenshot output yang memuat:

```text
HTTP/1.1 302 Found
Location: http://127.0.0.1:8000/login
```

Sensor nilai cookie pada baris perintah. Untuk request JSON, tunjukkan juga:

```powershell
curl.exe -i -H "Accept: application/json" -b "laravel-session=<NILAI_COOKIE_LAMA>" `
  http://127.0.0.1:8000/mahasiswa/dashboard
```

Hasil yang diharapkan: `HTTP/1.1 401 Unauthorized` dengan
`{"message":"...","code":"idle_timeout"}`.

### Bukti 4 — Terminal hasil automated test PASS

**Langkah:**

1. Dari root proyek, jalankan:

```powershell
php artisan test tests/Feature/SessionSecurityTest.php
```

2. Perbesar terminal (font minimal 12–14 pt) hingga seluruh baris terlihat.
3. Screenshot yang memuat perintah dan hasil akhir:

```text
PASS  Tests\Feature\SessionSecurityTest
✓ id sesi berbeda sebelum dan sesudah login ldap
✓ cookie sesi lama ditolak setelah logout
✓ sesi hanya menyimpan identitas minimal tanpa password
✓ idle timeout 30 menit memaksa logout
✓ absolute timeout 8 jam memaksa logout
✓ cookie sesi memakai httponly samesite lax dan secure dari env

Tests:    6 passed (60 assertions)
```

4. Tidak ada nilai rahasia pada output ini; tidak perlu masking, tetapi pastikan path pengguna
   Windows yang memuat nama pribadi tidak ikut terambil bila tidak diperlukan.

### Simpan ke Google Drive

Buat folder Drive bernama **`SproutApex-Autentikasi`**, unggah keempat PNG **dan** PDF laporan,
lalu buat short link: **`bit.ly/SproutApex-Autentikasi`**. Atur izin folder menjadi
“Anyone with the link — Viewer” agar dosen dapat membuka tanpa login.

---

# BAGIAN 4 — BUKTI AUTOMATED TEST (LDAP DI-MOCK, OFFLINE)

**File:** `tests/Feature/SessionSecurityTest.php`
**Perintah:** `php artisan test tests/Feature/SessionSecurityTest.php`
**Hasil aktual:**

```text
   PASS  Tests\Feature\SessionSecurityTest
  ✓ id sesi berbeda sebelum dan sesudah login ldap
  ✓ cookie sesi lama ditolak setelah logout
  ✓ sesi hanya menyimpan identitas minimal tanpa password
  ✓ idle timeout 30 menit memaksa logout
  ✓ absolute timeout 8 jam memaksa logout
  ✓ cookie sesi memakai httponly samesite lax dan secure dari env

  Tests:    6 passed (60 assertions)
```

**Cara LDAP di-mock (tanpa jaringan eksternal):**

- `LdapUserRepository` di-mock dengan Mockery: `findByCredentials()` mengembalikan objek
  `LdapRecord\Models\OpenLDAP\User` buatan (`entryuuid`, `cn`, `displayName`, `title`,
  `description`, `mail`, `mailAlternateAddress`, `telephoneNumber`).
- `LdapUserAuthenticator` di-mock: `setEloquentModel()` mengembalikan diri sendiri dan
  `attempt()` mengembalikan `true`, sehingga tidak ada bind ke server LDAP.
- Keduanya di-bind ke container dan `auth->forgetGuards()` dipanggil agar guard `web`
  memakai mock tersebut.
- GUID antara objek LDAP dan user Eloquent disamakan, sehingga sinkronisasi user berjalan
  realistis namun sepenuhnya offline.

**Skenario yang diuji:**

| Test | Skenario | Assertion Kunci |
|---|---|---|
| 1 (wajib) | ID sesi tamu vs ID sesi setelah login LDAP | `assertNotSame`, `assertAuthenticatedAs` |
| 2 (wajib) | Login → logout → replay cookie lama ke `/mahasiswa/dashboard` | redirect `/login`, `assertGuest`, baris sesi lama hilang dari tabel `sessions`, cookie `isCleared()` |
| 3 | Isi sesi setelah login | hanya identitas minimal; tidak ada `password`, plaintext, maupun hash |
| 4 | Idle 31 menit | redirect `/login`, `assertGuest` |
| 5 | Absolute 8 jam 1 menit | redirect `/login`, `assertGuest` |
| 6 | Atribut cookie | `isHttpOnly()`, `SameSite=lax`, `Secure=false` di lokal |

---

# BAGIAN 5 — PULL REQUEST

| Item | Nilai |
|---|---|
| **Repositori** | https://github.com/yprtm26/Proyek-SAKTI |
| **Base branch** | `main` |
| **Head branch** | `feature/mode-a-session-security` |
| **Judul PR** | `feat(mode-a): pengamanan sesi pasca-login LDAP + automated test` |
| **Link PR** | `[ISI LINK PR DI SINI SETELAH PR DIBUAT]` |
| **Status review** | Menunggu approval rekan tim |

**Tautan untuk membuat PR (buka di browser):**

```text
https://github.com/yprtm26/Proyek-SAKTI/compare/main...feature/mode-a-session-security?expand=1
```

**Template deskripsi PR (salin-tempel):**

```markdown
## Ringkasan
Menerapkan pengamanan sesi setelah login LDAP sesuai rubrik Mode A:
- regenerasi ID sesi (anti session fixation) setelah `Auth::attempt()` sukses;
- sanitasi sesi: hanya identitas minimal, bebas password/kredensial bind;
- atribut cookie HttpOnly, SameSite=Lax, Secure via `.env`;
- idle timeout 30 menit + absolute timeout 8 jam (`EnforceSessionTimeout`);
- logout menghapus sesi server dan cookie browser;
- 6 feature test offline dengan LDAP di-mock (Mockery).

## Bukti
`php artisan test tests/Feature/SessionSecurityTest.php` → **6 passed (60 assertions)**

## Checklist
- [x] `.env` tidak ter-track, hanya `.env.example`
- [x] `LDAP_LOGGING=false`
- [x] Middleware terdaftar di grup `web`
- [x] Test hijau tanpa koneksi LDAP
```

**Langkah review rekan tim (untuk poin rubrik 7):**

1. Rekan tim membuka link PR → tab **Files changed**.
2. Klik **Review changes** → pilih **Approve** → tulis ulasan positif, misalnya:
   “Implementasi regenerasi sesi, timeout ganda, dan logout sudah sesuai; test offline lulus.”
3. Klik **Submit review**.
4. Screenshot halaman PR yang menampilkan badge **Approved** dan unggah ke folder Drive.

---

# BAGIAN 6 — FORMAT PENGUMPULAN RESMI

| Item | Nilai |
|---|---|
| **Link 1 — Folder bukti (Google Drive, dipendekkan)** | `bit.ly/SproutApex-Autentikasi` |
| **Berkas PDF laporan** | `Sprout Apex - Autentikasi dan Batas Login.pdf` |
| **Link 2 — Repositori GitHub** | https://github.com/yprtm26/Proyek-SAKTI |

**Isi PDF (urutan halaman):**

1. Halaman identitas & ringkasan hasil.
2. Notulen Keputusan Arsitektur Keamanan (1 halaman).
3. Tabel ringkasan perubahan file kode.
4. Link Pull Request + status review.
5. Bukti 1–4 (screenshot tersensor) dengan caption.
6. Lampiran hasil automated test.

**Checklist akhir sebelum dikumpulkan:**

- [ ] `.env` asli tidak ter-track (`git ls-files .env` kosong).
- [ ] `.env.example` berisi nilai aman, tanpa secret.
- [ ] `php artisan test tests/Feature/SessionSecurityTest.php` → 6 passed.
- [ ] Empat screenshot sudah disensor dan diunggah ke folder Drive.
- [ ] PDF diberi nama persis `Sprout Apex - Autentikasi dan Batas Login.pdf`.
- [ ] Link Drive dipendekkan menjadi `bit.ly/SproutApex-Autentikasi` dan dapat diakses publik.
- [ ] PR berstatus **Approved** oleh rekan tim; link dicantumkan di PDF.
- [ ] Link repositori `https://github.com/yprtm26/Proyek-SAKTI` dicantumkan.

---

# LAMPIRAN A — PEMETAAN RUBRIK 100 POIN

| Poin Rubrik | Bobot | Implementasi / Bukti |
|---|---:|---|
| Notulen Keputusan | 15 | Bagian 1 dokumen ini; setiap poin merujuk perilaku kode yang benar-benar ada |
| ID sesi berganti setelah login | 20 | `AuthController::login()` + Test 1 (`assertNotSame`) + Bukti 1 vs 2 |
| Atribut cookie sesi | 15 | `config/session.php` + `.env.example` + Test 6 + Bukti 1/2 |
| Idle & absolute timeout | 15 | `EnforceSessionTimeout` + Test 4 & 5 |
| Logout menghapus sesi server | 15 | `AuthController::logout()` + Test 2 + Bukti 3 |
| Automated test lolos | 10 | 6 passed (60 assertions), LDAP di-mock, Bukti 4 |
| Pull Request ditinjau | 10 | Bagian 5 + screenshot Approved di Drive |

---

# LAMPIRAN B — CARA REPRODUKSI

```powershell
# 1. Pastikan dependensi terpasang
composer install

# 2. Bersihkan cache konfigurasi agar nilai .env terbaru dipakai
php artisan config:clear

# 3. Jalankan seluruh test keamanan sesi (offline, LDAP di-mock)
php artisan test tests/Feature/SessionSecurityTest.php

# 4. Verifikasi .env tidak ter-track Git
git ls-files .env          # harus kosong

# 5. Verifikasi nilai konfigurasi aktif
php artisan tinker --execute="dump([
  'lifetime' => config('session.lifetime'),
  'absolute' => config('session.absolute_lifetime'),
  'secure' => config('session.secure'),
  'http_only' => config('session.http_only'),
  'same_site' => config('session.same_site'),
  'ldap_logging' => config('ldap.logging.enabled'),
]);"
```

**Catatan operasional production:**

- Set `APP_ENV=production`, `APP_URL=https://...`, dan `SESSION_SECURE_COOKIE=true`.
- Gunakan `SESSION_DRIVER=database` atau Redis agar sesi dapat diinvalidasi lintas node.
- Jangan pernah menyalakan `LDAP_LOGGING` di production.
- Jalankan `php artisan config:cache` setelah variabel environment final.
