# LAPORAN TUGAS — MODE A
## Proyek LDAP UY: Pengamanan Autentikasi dan Manajemen Sesi Setelah Login LDAP

**Nama:** Arman Dava Wicaksono  
**NPM:** 1402024010  
**Kelompok:** Sprout Apex  
**Proyek:** SAKTI — Sistem Administrasi Surat Akademik Terintegrasi  
**Mata Kuliah:** Pengembangan Aplikasi Web / Keamanan Autentikasi  
**Tanggal Laporan:** 16 November 2026  
**Status:** Selesai — seluruh test lulus

---

## 1. Ringkasan Eksekutif

Tugas Mode A meminta pengamanan **sesi web setelah login LDAP** pada aplikasi Laravel. Sebelum perbaikan, aplikasi sudah melakukan regenerasi ID sesi, tetapi belum memiliki **absolute timeout**, durasi idle masih 120 menit, atribut `Secure` belum eksplisit di environment, cookie sesi belum dihapus eksplisit saat logout, dan belum ada automated test yang memverifikasi skenario keamanan sesi.

Setelah perbaikan, aplikasi menerapkan lima kontrol wajib rubrik:

1. **Regenerasi ID sesi** tepat setelah `Auth::attempt()` berhasil.
2. **Sanitasi sesi**: hanya identitas minimal yang disimpan; password, hash, kredensial bind LDAP, dan atribut sensitif dibersihkan.
3. **Atribut cookie**: `HttpOnly=true`, `SameSite=Lax`, `Secure` dikendalikan environment.
4. **Idle timeout 30 menit** dan **absolute timeout 480 menit (8 jam)** melalui middleware `EnforceSessionTimeout`.
5. **Logout aman**: sesi dihancurkan di server, token CSRF diganti, cookie sesi dikedaluwarsakan di browser.

Hasil verifikasi akhir:

| Aspek | Hasil |
|---|---|
| Test keamanan sesi (mock LDAP) | **6 passed (60 assertions)** |
| Seluruh test suite | **50 passed (254 assertions)** |
| Secret ter-track di Git | **Tidak ada** |
| Status rubrik Mode A | **LENGKAP (10/10)** |

---

## 2. Identitas Proyek dan Lingkungan

| Komponen | Keterangan |
|---|---|
| Framework | Laravel 12 (`laravel/framework ^12.0`) |
| Bahasa | PHP `^8.2` (runtime pengujian: PHP 8.4.15) |
| Pustaka LDAP | `directorytree/ldaprecord-laravel ^3.4` |
| Model LDAP | `LdapRecord\Models\OpenLDAP\User` |
| Driver sesi | `file` (lokal), `database` (disarankan production) |
| Framework test | PHPUnit 11 + Laravel TestCase |
| Mocking | Mockery |

Aplikasi adalah **aplikasi web berbasis session**, bukan aplikasi mobile berbasis token. Karena itu pendekatan yang dipakai adalah **session cookie Laravel**, bukan secure storage/refresh token.

---

## 3. Ruang Lingkup dan Rubrik

Rubrik Mode A yang diverifikasi:

- [x] Regenerasi ID sesi setelah login LDAP berhasil.
- [x] Sanitasi data sesi (tidak ada password/kredensial/atribut sensitif).
- [x] Atribut cookie `HttpOnly`, `SameSite`, `Secure` via `.env`.
- [x] Idle timeout dan absolute timeout.
- [x] Logout aman (server + browser).
- [x] Dua automated test wajib dengan LDAP di-mock.
- [x] Sanitasi Git (tidak ada secret ter-track).
- [x] Notulen keputusan sinkron dengan kode.

---

## 4. Temuan Audit Awal (Sebelum Perbaikan)

| No | Temuan | Dampak |
|---|---|---|
| 1 | `session()->regenerate()` sudah ada, tetapi belum ada absolute timeout | Sesi dapat bertahan tanpa batas atas meski pengguna aktif |
| 2 | `SESSION_LIFETIME=120` | Idle timeout terlalu panjang untuk komputer laboratorium bersama |
| 3 | `SESSION_SECURE_COOKIE` belum eksplisit di `.env`/`.env.example` | Risiko cookie terkirim lewat HTTP di production |
| 4 | Belum ada middleware pemeriksa 8 jam sejak login pertama | Tidak ada kendali absolut |
| 5 | Logout sudah `invalidate()` + `regenerateToken()`, tetapi cookie belum dihapus eksplisit | Cookie lama tetap tersimpan di browser |
| 6 | Belum ada test session fixation, replay cookie, sanitasi sesi, dan timeout | Kontrol keamanan tidak terbukti otomatis |
| 7 | `LDAP_LOGGING` default `true` | Kredensial/atribut LDAP berpotensi tercetak ke log |

---

## 5. Implementasi

### 5.1 Regenerasi ID Sesi Setelah Login LDAP

**File:** `app/Http/Controllers/AuthController.php`

```php
if (Auth::attempt($credentials)) {
    // Cegah Session Fixation: ID sesi lama dibuang dan diganti ID baru
    // tepat setelah verifikasi LDAP berhasil.
    $request->session()->regenerate();            // baris 109

    /** @var User $authenticatedUser */
    $authenticatedUser = Auth::user();
    if ($authenticatedUser) {
        $authenticatedUser->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        $this->storeMinimalSessionIdentity($request, $authenticatedUser); // baris 119
    }
    // ...
}
```

Provider yang dipakai guard `web` memang LDAP, sesuai `config/auth.php:40-41` (guard `session`, provider `users`) dan `config/auth.php:69-73` (provider `driver=ldap`, model OpenLDAP + model Eloquent).

**Hasil:** ID sesi tamu dibuang dan diganti ID baru sebelum redirect ke dashboard. Ini mematikan serangan *session fixation*.

### 5.2 Sanitasi Data Sesi

**File:** `app/Http/Controllers/AuthController.php:186-215`

```php
private function storeMinimalSessionIdentity(Request $request, User $user): void
{
    $session = $request->session();

    $session->put([
        'auth_identity' => [
            'user_id' => $user->getAuthIdentifier(),
            'nama'    => $user->name,
            'role'    => $user->role,
            'npm'     => $user->npm,
            'nidn'    => $user->nidn,
        ],
        'auth_login_at'      => now()->getTimestamp(),
        'auth_last_activity' => now()->getTimestamp(),
    ]);

    // Defense in depth: buang kunci sensitif apabila pernah tersimpan.
    $session->forget([
        'password',
        'password_confirmation',
        'credentials',
        'ldap_password',
        'ldap_credentials',
        'bind_password',
        'userpassword',
    ]);
}
```

Data yang **disimpan** setelah login:

| Kunci | Isi | Alasan |
|---|---|---|
| `login_web_<hash>` | ID user (guard Laravel) | Menentukan identitas autentikasi |
| `auth_identity.user_id` | ID user | Identitas tampilan |
| `auth_identity.nama` | Nama lengkap | Ditampilkan di UI |
| `auth_identity.role` | Role | Otorisasi tampilan |
| `auth_identity.npm` / `nidn` | NPM atau NIDN | Identitas akademik |
| `auth_login_at` | Unix timestamp login | Absolute timeout |
| `auth_last_activity` | Unix timestamp aktivitas | Idle timeout |
| `_token` | Token CSRF | Keamanan form |
| `_flash` | Flash message | Notifikasi sekali tampil |

Data yang **dilarang dan dibersihkan**:

- Password plaintext maupun hash.
- Kredensial bind LDAP.
- Atribut sensitif LDAP seperti `userPassword`, `mail`, `mailAlternateAddress`, `telephoneNumber`.
- Token/secret apa pun.

Sanitasi dilakukan dua lapis:

1. Di controller saat login (`AuthController.php:206-214`).
2. Di setiap request terautentikasi oleh middleware (`EnforceSessionTimeout.php:44`).

Selain itu, logging LDAP dinonaktifkan agar kredensial tidak tercetak ke log:

**File:** `config/ldap.php:60`

```php
'enabled' => env('LDAP_LOGGING', false),
```

### 5.3 Atribut Cookie Sesi

**File:** `config/session.php`

```php
'cookie'     => env('SESSION_COOKIE', Str::slug((string) env('APP_NAME', 'laravel')).'-session'), // baris 144-147
'path'       => env('SESSION_PATH', '/'),        // baris 160
'secure'     => env('SESSION_SECURE_COOKIE', false), // baris 186
'http_only'  => env('SESSION_HTTP_ONLY', true),  // baris 199
'same_site'  => env('SESSION_SAME_SITE', 'lax'), // baris 216
```

**File:** `.env.example:39-41`

```dotenv
# false untuk lokal (HTTP), true untuk production (HTTPS).
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

| Atribut | Nilai | Alasan |
|---|---|---|
| `HttpOnly` | `true` | Mencegah JavaScript membaca cookie, mengurangi dampak XSS |
| `SameSite` | `Lax` | Mitigasi CSRF tanpa merusak alur redirect login |
| `Secure` | `false` lokal / `true` production | Cookie hanya dikirim via HTTPS di production |
| `Path` | `/` | Cookie berlaku untuk seluruh aplikasi |

### 5.4 Idle Timeout dan Absolute Timeout

**Konfigurasi:** `config/session.php:35` dan `:49`

```php
'lifetime'          => (int) env('SESSION_LIFETIME', 30),           // 30 menit (idle)
'absolute_lifetime' => (int) env('SESSION_ABSOLUTE_LIFETIME', 480), // 480 menit / 8 jam
```

**Environment:** `.env.example:32` dan `:34`

```dotenv
SESSION_LIFETIME=30
SESSION_ABSOLUTE_LIFETIME=480
```

**Middleware:** `app/Http/Middleware/EnforceSessionTimeout.php`

```php
$loginAt      = (int) $session->get('auth_login_at', 0);      // baris 48
$lastActivity = (int) $session->get('auth_last_activity', 0); // baris 49

$idleMinutes     = max(1, (int) config('session.lifetime', 30));
$absoluteMinutes = max(1, (int) config('session.absolute_lifetime', 480));

$idleExpired     = ($now - $lastActivity) >= $idleMinutes * 60;     // baris 70
$absoluteExpired = ($now - $loginAt) >= $absoluteMinutes * 60;     // baris 71

if ($idleExpired || $absoluteExpired) {
    Auth::guard('web')->logout();   // baris 91
    $session->invalidate();         // baris 92
    $session->regenerateToken();    // baris 93
    Cookie::queue(Cookie::forget(...)); // baris 97-103
    // JSON: 401 + code idle_timeout/absolute_timeout
    // Web : redirect ke route('login')
}
```

Mekanisme perhitungan:

- `auth_login_at` di-set **sekali** saat login dan **tidak pernah diperbarui** → dasar absolute timeout yang tidak dapat diperpanjang aktivitas.
- `auth_last_activity` diperbarui setiap request (`EnforceSessionTimeout.php:118`) → dasar idle timeout.
- Middleware didaftarkan pada grup `web` di `bootstrap/app.php:23`.

Perilaku saat kedaluwarsa:

| Jenis request | Respons |
|---|---|
| Browser biasa | Redirect `302` ke `/login` + pesan status |
| Request JSON/API | `401 Unauthorized` + `{ message, code: idle_timeout|absolute_timeout }` |

### 5.5 Logout Aman

**File:** `app/Http/Controllers/AuthController.php:221-239`

```php
public function logout(Request $request)
{
    Auth::logout();                              // hapus status autentikasi

    $request->session()->invalidate();            // hancurkan sesi di server
    $request->session()->regenerateToken();       // ganti token CSRF

    // Queue (bukan withCookie) agar cookie kedaluwarsa ditambahkan setelah
    // StartSession menulis cookie sesi baru pada response.
    Cookie::queue(
        Cookie::forget(
            config('session.cookie'),
            config('session.path'),
            config('session.domain')
        )
    );

    return redirect('/login');
}
```

Route: `routes/web.php:41` → `POST /logout`.

| Lokasi | Aksi |
|---|---|
| Server | `Auth::logout()` menghapus autentikasi |
| Server | `invalidate()` menghancurkan data sesi dan mengganti ID |
| Server | `regenerateToken()` membuat token CSRF baru |
| Browser | `Set-Cookie` kedaluwarsa menghapus cookie sesi |
| Browser | Redirect ke `/login` |

---

## 6. Automated Test dengan LDAP Di-Mock

### 6.1 Strategi Mocking

**File:** `tests/Feature/SessionSecurityTest.php`

LDAP tidak pernah dihubungi karena dua komponen diganti mock:

```php
$repository = Mockery::mock(LdapUserRepository::class);      // baris 80
$repository->shouldReceive('findByCredentials')->once()->andReturn($ldapUser);

$authenticator = Mockery::mock(LdapUserAuthenticator::class); // baris 85
$authenticator->shouldReceive('attempt')->once()->andReturn(true);

$this->app->bind(LdapUserRepository::class, fn () => $repository);      // baris 94
$this->app->bind(LdapUserAuthenticator::class, fn () => $authenticator); // baris 95
```

Catatan teknis: binding memakai **closure** (`bind`), bukan `instance()`. Alasannya, LdapRecord membuat repository/authenticator melalui `app(...)` dengan parameter tambahan; container Laravel mengabaikan binding `instance` saat resolusi memakai parameter, sehingga `instance()` membuat mock tidak terpakai.

Fixture memakai data dummy (`testuser`, `dc=example,dc=test`, `dummy-password-for-test`) sehingga tidak ada kredensial asli di repositori.

### 6.2 Dua Test Wajib Rubrik

**Test 1 — ID sesi sebelum ≠ sesudah login LDAP** (`SessionSecurityTest.php:107`)

```php
$this->get('/login')->assertOk();
$sessionIdBeforeLogin = session()->getId();
$this->withCookie(config('session.cookie'), $sessionIdBeforeLogin);

$this->login($user)->assertRedirect('/mahasiswa/dashboard');
$sessionIdAfterLogin = session()->getId();

$this->assertNotSame($sessionIdBeforeLogin, $sessionIdAfterLogin, '...');
$this->assertAuthenticatedAs($user->fresh());
```

**Test 2 — Cookie lama ditolak setelah logout** (`SessionSecurityTest.php:134`)

```php
$oldSessionId = session()->getId();
$this->withCookie(config('session.cookie'), $oldSessionId);
$logout = $this->post('/logout');
$logout->assertRedirect('/login');

// Cookie sesi dikedaluwarsakan
$expiredCookie = ...;
$this->assertTrue($expiredCookie->isCleared());

// Sesi lama hancur di server
$this->assertDatabaseMissing('sessions', ['id' => $oldSessionId]);

// Replay cookie lama
$this->app['session']->forgetDrivers();
$this->app['auth']->forgetGuards();
$this->withCookie(config('session.cookie'), $oldSessionId)
    ->get('/mahasiswa/dashboard')
    ->assertRedirect('/login');

$this->assertGuest();
```

### 6.3 Test Tambahan

| Test | Baris | Tujuan |
|---|---|---|
| `test_sesi_hanya_menyimpan_identitas_minimal_tanpa_password` | 173 | Memastikan payload sesi tidak memuat password/hash |
| `test_idle_timeout_30_menit_memaksa_logout` | 194 | Idle 31 menit → logout |
| `test_absolute_timeout_8_jam_memaksa_logout` | 210 | 8 jam 1 menit → logout meski aktif |
| `test_cookie_sesi_memakai_httponly_samesite_lax_dan_secure_dari_env` | 226 | Verifikasi atribut cookie |

### 6.4 Hasil Eksekusi

Perintah:

```powershell
php artisan test tests/Feature/SessionSecurityTest.php
```

Output:

```text
PASS  Tests\Feature\SessionSecurityTest
✓ id sesi berbeda sebelum dan sesudah login ldap
✓ cookie sesi lama ditolak setelah logout
✓ sesi hanya menyimpan identitas minimal tanpa password
✓ idle timeout 30 menit memaksa logout
✓ absolute timeout 8 jam memaksa logout
✓ cookie sesi memakai httponly samesite lax dan secure dari env

Tests:    6 passed (60 assertions)
Duration: 3.02s
```

Seluruh suite:

```text
Tests:    50 passed (254 assertions)
Duration: 197.64s
```

---

## 7. Keamanan dan Sanitasi Git

| Pemeriksaan | Hasil |
|---|---|
| `.env` asli ter-track? | **Tidak** — hanya `.env.example` |
| `.env` diabaikan? | Ya, `.gitignore:3` |
| `APP_KEY=base64:...` ter-track? | Tidak ditemukan |
| `LDAP_PASSWORD`, `DB_PASSWORD` ter-track? | Tidak ditemukan |
| AWS secret / private key ter-track? | Tidak ditemukan |
| `git diff --check` | Bersih |
| Fixture test memuat kredensial asli? | Tidak (sudah dummy) |

File yang berubah (belum di-commit):

```text
 M .env.example
 M app/Http/Controllers/AuthController.php
 M bootstrap/app.php
 M config/ldap.php
 M config/session.php
?? app/Http/Middleware/EnforceSessionTimeout.php
?? tests/Feature/SessionSecurityTest.php
```

`.env.example` memakai nilai aman: `APP_KEY=` kosong, `SESSION_SECURE_COOKIE=false`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`, `LDAP_LOGGING=false`.

---

## 8. Notulen Keputusan (Maksimal 1 Halaman)

**NOTULEN KEPUTUSAN PENGAMANAN AUTENTIKASI DAN MANAJEMEN SESI**  
**Proyek SAKTI — Mode A: LDAP UY, Sesi Setelah Login LDAP**

**Poin 1 — Data yang disimpan dan dilarang disimpan dalam sesi.**
Setelah verifikasi LDAP berhasil, ID sesi langsung diregenerasi dengan `$request->session()->regenerate()` untuk mencegah *session fixation*. Sesi hanya menyimpan identitas minimal yang dibutuhkan aplikasi, yaitu ID pengguna dari guard Laravel, nama lengkap, role, NPM/NIDN, timestamp `auth_login_at`, timestamp `auth_last_activity`, token CSRF, serta flash message. Aplikasi secara eksplisit melarang penyimpanan password plaintext maupun hash, kredensial bind LDAP, atribut sensitif LDAP seperti `userPassword`, `mail`, dan `telephoneNumber`, serta token/secret apa pun. Kunci sensitif tersebut dibersihkan melalui `session()->forget()` di `AuthController` dan middleware `EnforceSessionTimeout`, dan logging LDAP dinonaktifkan agar kredensial tidak tercetak di log.

**Poin 2 — Batas waktu idle dan absolut.**
Idle timeout ditetapkan **30 menit** melalui `SESSION_LIFETIME=30`. Absolute timeout ditetapkan **480 menit atau 8 jam** melalui `SESSION_ABSOLUTE_LIFETIME=480`, dihitung sejak login pertama dan tidak diperpanjang oleh aktivitas. Kedua batas diperiksa middleware pada setiap request terproteksi. Jika terlampaui, server memanggil `Auth::logout()`, menghancurkan sesi dengan `invalidate()`, mengganti token CSRF, mengedaluwarsakan cookie, lalu mengarahkan pengguna ke halaman login atau membalas **401** untuk request JSON. Angka 30 menit dipilih karena pengguna kampus sering berpindah antara kelas, praktikum, dan kegiatan administrasi, sehingga tetap memberi ruang istirahat singkat tanpa membiarkan komputer yang ditinggal tetap login terlalu lama. Absolute 8 jam dipilih karena mencakup satu hari kerja/kuliah normal, sehingga sesi tidak bertahan berhari-hari meskipun pengguna aktif.

**Poin 3 — Proses logout di browser dan server.**
Saat pengguna menekan logout, browser mengirim `POST /logout` beserta cookie sesi dan token CSRF. Di server, `Auth::logout()` menghapus status autentikasi, `$request->session()->invalidate()` menghancurkan seluruh data sesi di penyimpanan server dan mengganti ID sesi, lalu `$request->session()->regenerateToken()` membuat token CSRF baru. Server juga mengirim `Set-Cookie` dengan masa berlaku kedaluwarsa melalui `Cookie::queue(Cookie::forget(...))`, sehingga browser menghapus cookie sesi lama dan diarahkan ke `/login`. Cookie lama yang disimpan penyerang tidak lagi dapat digunakan karena sesi di server sudah dihancurkan; request berikutnya akan ditolak dengan redirect ke login atau status **401**.

**Poin 4 — Atribut cookie sesi dan alasan teknisnya.**
Cookie sesi memakai **HttpOnly = true**, **SameSite = Lax**, dan **Secure** yang dikendalikan `SESSION_SECURE_COOKIE` (`false` untuk lokal HTTP, `true` untuk production HTTPS). `HttpOnly` mencegah JavaScript membaca cookie sehingga mengurangi dampak XSS terhadap pencurian sesi. `SameSite=Lax` membantu mitigasi CSRF namun tetap mengizinkan navigasi top-level dan alur redirect login. `Secure` memastikan cookie hanya dikirim melalui HTTPS di production agar tidak disadap pada koneksi HTTP. Nama cookie mengikuti `SESSION_COOKIE` (default `laravel-session`) dengan path `/`.

---

## 9. Ringkasan Perubahan Kode

| File | Status | Fungsi Perubahan |
|---|---|---|
| `config/session.php` | Diubah | `lifetime` 30, `absolute_lifetime` 480, `secure` dari env, `http_only` true, `same_site` lax |
| `.env` | Diubah (lokal, tidak di-commit) | Nilai `SESSION_*` dan `LDAP_LOGGING=false` |
| `.env.example` | Diubah | Template variabel sesi baru dengan nilai aman |
| `app/Http/Controllers/AuthController.php` | Diubah | Regenerasi ID sesi, identitas minimal, sanitasi kunci sensitif, logout aman + cookie kedaluwarsa |
| `app/Http/Middleware/EnforceSessionTimeout.php` | **Baru** | Idle 30 menit & absolute 8 jam, forced logout, redirect/401, pembersihan kunci sensitif |
| `bootstrap/app.php` | Diubah | Mendaftarkan middleware ke grup `web` |
| `config/ldap.php` | Diubah | Default `LDAP_LOGGING=false` |
| `tests/Feature/SessionSecurityTest.php` | **Baru** | 6 test dengan LDAP di-mock |

---

## 10. Panduan Pengambilan Bukti Screenshot

### Bukti 1 — Cookie sesi sebelum login
1. Jalankan `php artisan serve` atau akses domain lokal Laragon.
2. Buka `/login`.
3. Buka **DevTools → Application → Cookies**.
4. Screenshot panel cookie dan catat nama cookie (`laravel-session`) serta nilainya.

### Bukti 2 — Cookie sesi sesudah login
1. Login dengan akun LDAP valid.
2. Buka **DevTools → Application → Cookies**.
3. Screenshot cookie setelah login; pastikan nilainya **berubah**, **HttpOnly** tercentang, **SameSite=Lax**, **Secure** kosong untuk HTTP lokal.
4. Jalankan `document.cookie` di Console untuk membuktikan cookie sesi tidak terlihat (HttpOnly).

### Bukti 3 — Konfigurasi sesi
Screenshot `config/session.php` pada baris `lifetime`, `absolute_lifetime`, `secure`, `http_only`, `same_site`, atau jalankan:

```powershell
php artisan tinker --execute="dump(config('session.lifetime'), config('session.absolute_lifetime'), config('session.secure'), config('session.http_only'), config('session.same_site'));"
```

Expected: `30`, `480`, `false`/`true`, `true`, `"lax"`.

### Bukti 4 — Hasil test lulus
```powershell
php artisan test tests/Feature/SessionSecurityTest.php
```
Screenshot 6 baris **PASS**, terutama dua test wajib.

### Bukti 5 — Request dengan cookie lama setelah logout
1. Sebelum logout, salin nilai cookie dari DevTools.
2. Logout.
3. Tambahkan kembali cookie lama lewat DevTools → Application → Cookies.
4. Buka `/mahasiswa/dashboard`.
5. Screenshot **Network** yang menunjukkan `302` dengan `Location: /login`.

Alternatif terminal:

```powershell
curl.exe -i -b "laravel-session=<NILAI_COOKIE_LAMA>" http://localhost:8000/mahasiswa/dashboard
curl.exe -i -H "Accept: application/json" -b "laravel-session=<NILAI_COOKIE_LAMA>" http://localhost:8000/mahasiswa/dashboard
```

Expected: `302 Found` + `Location: .../login`, atau `401 Unauthorized` untuk JSON.

---

## 11. Kesimpulan dan Rekomendasi

Seluruh butir rubrik Mode A terpenuhi dan terbukti otomatis. Kontrol keamanan sesi tidak hanya dikonfigurasi, tetapi diuji dengan LDAP di-mock sehingga pengujian dapat dijalankan tanpa server LDAP asli.

**Rekomendasi untuk production (bukan kegagalan rubrik):**

1. Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, dan `LOG_LEVEL` aman.
2. Jalankan `php artisan config:clear` setelah mengubah `.env`.
3. Pertimbangkan menonaktifkan fallback password database lokal (`AuthController.php:100-103`) bila kebijakan kampus mewajibkan LDAP murni.
4. Gunakan `SESSION_DRIVER=database` di production agar sesi mudah diinvalidasi lintas node.

---

## Lampiran A — Bukti Screenshot

Tempelkan screenshot pada tabel berikut sebelum laporan diekspor ke PDF.

| Kode | Bukti | File yang disarankan | Keterangan yang harus terlihat |
|---|---|---|---|
| B1 | Cookie sebelum login | `bukti-1-cookie-sebelum.png` | Nama cookie + nilai awal di DevTools |
| B2 | Cookie sesudah login | `bukti-2-cookie-sesudah.png` | Nilai berubah, HttpOnly, SameSite=Lax |
| B3 | Konfigurasi sesi | `bukti-3-konfigurasi.png` | `lifetime=30`, `absolute_lifetime=480`, `http_only=true`, `same_site=lax` |
| B4 | Test lulus | `bukti-4-test.png` | 6 PASS, termasuk dua test wajib |
| B5 | Cookie lama ditolak | `bukti-5-cookie-lama.png` | `302 → /login` atau `401 Unauthorized` |

---

## Lampiran B — Checklist Status Akhir

| No | Butir | Status | Bukti |
|---|---|---|---|
| 1 | Regenerasi ID sesi | **LENGKAP** | `AuthController.php:109` |
| 2 | Sanitasi data sesi | **LENGKAP** | `AuthController.php:190-214`, middleware `:44` |
| 3 | Atribut cookie | **LENGKAP** | `config/session.php:186,199,216` |
| 4 | Idle timeout 30 menit | **LENGKAP** | `config/session.php:35`, middleware `:70` |
| 5 | Absolute timeout 8 jam | **LENGKAP** | `config/session.php:49`, middleware `:71` |
| 6 | Logout server + browser | **LENGKAP** | `AuthController.php:221-239` |
| 7 | Test sesi sebelum/sesudah login | **PASS** | `SessionSecurityTest.php:107` |
| 8 | Test cookie lama setelah logout | **PASS** | `SessionSecurityTest.php:134` |
| 9 | LDAP di-mock | **LENGKAP** | `SessionSecurityTest.php:80-95` |
| 10 | Git bebas secret | **LENGKAP** | `.env` ignored, `.env.example` dummy |
| 11 | Notulen sinkron kode | **LENGKAP** | Bagian 8 laporan ini |
