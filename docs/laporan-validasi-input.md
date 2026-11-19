# LAPORAN PENERAPAN KEAMANAN: VALIDASI INPUT, QUERY BERPARAMETER, DAN OUTPUT ENCODING (MODE A)

* **Nama Tim:** Sprout Apex
* **Aplikasi:** SAKTI (Sistem Administrasi dan Kelola Tata Informasi)
* **Mode Proyek:** MODE A · PROYEK LDAP UY: Input Kampus dan Data LDAP
* **Tautan Pull Request:** [https://github.com/yprtm26/Proyek-SAKTI/pull/13](https://github.com/yprtm26/Proyek-SAKTI) *(sesuaikan nomor PR setelah push)*
* **Tautan Google Drive (Bitly):** [https://bit.ly/SproutApex-ValidasiInput](https://bit.ly/SproutApex-ValidasiInput)

---

## 1. Notulen Keputusan Kelompok

### A. Lima (5) Input Paling Penting dan Aturan Allowlist Masing-Masing
1. **NPM Mahasiswa (`mahasiswa.*.npm`)**
   * **Dipakai pada:** Form pengajuan surat mahasiswa (Surat Permohonan Magang & Surat Pengantar Remedial Bahasa Inggris).
   * **Tipe Data:** Angka / Teks Numerik (`string`)
   * **Panjang:** Tepat 10 karakter (`size:10`)
   * **Format / Allowlist:** Hanya angka 0–9 dengan regex `^[0-9]{10}$` (contoh: `1402021001`). Menolak huruf, spasi, tanda kutip, maupun karakter SQL.
   * **Pesan Error:** *"Format NPM tidak valid. Harap masukkan 10 digit angka resmi."*

2. **Username Akun (`username`)**
   * **Dipakai pada:** Form login pengguna (`AuthController`) dan form pendaftaran akun baru oleh administrator (`/administrator/simpan-akun`).
   * **Tipe Data:** Teks Alfanumerik (`string`)
   * **Panjang:** Minimal 3 karakter, maksimal 30 karakter (`min:3|max:30`)
   * **Format / Allowlist:** Huruf, angka, titik, dan strip dengan regex `^[a-zA-Z0-9._-]+$`. Menolak spasi atau karakter injeksi SQL/LDAP.
   * **Pesan Error:** *"Format username tidak valid. Hanya boleh memuat huruf, angka, titik, dan tanda hubung (-)."*

3. **Nomor Surat (`nomor_surat`)**
   * **Dipakai pada:** Proses finalisasi surat oleh Admin Tata Usaha (TU) (`/admin/surat/{id}/finalisasi`).
   * **Tipe Data:** Teks (`string`)
   * **Panjang:** Minimal 5 karakter, maksimal 100 karakter (`min:5|max:100`)
   * **Format / Allowlist:** Format nomor resmi universitas dengan regex `^[0-9A-Za-z\/\.\-]+$` (contoh: `045/FTI/B/XI/2026`). Hanya huruf, angka, garis miring `/`, titik `.`, dan tanda hubung `-`.
   * **Pesan Error:** *"Format nomor surat tidak valid. Hanya boleh memuat huruf, angka, garis miring (/), titik (.), dan tanda hubung (-)."*

4. **Keperluan / Perihal Surat (`data.*` / Form Dinamis)**
   * **Dipakai pada:** Form dinamis pengajuan surat oleh mahasiswa dan dosen (`/mahasiswa/surat/ajukan/{id}` dan `/dosen/surat/ajukan/{id}`).
   * **Tipe Data:** Teks Narasi (`string`/`mixed`)
   * **Panjang:** Maksimal 500 karakter (`max:500`)
   * **Format / Allowlist:** Teks bebas tag HTML (aturan `not_regex:/[<>]/`). Menolak simbol `<` dan `>` untuk memitigasi XSS dan script injection.
   * **Pesan Error:** *"Input tidak valid. Tidak boleh mengandung tag HTML atau karakter khusus."*

5. **Alasan Penolakan Surat (`alasan_tolak`)**
   * **Dipakai pada:** Form saat Admin TU, Kaprodi, atau Dekan menolak pengajuan surat (`/admin/surat/{id}/tolak`, `/kaprodi/surat/{id}/tolak`, `/dekan/surat/{id}/tolak`).
   * **Tipe Data:** Teks Narasi (`string`, `nullable`)
   * **Panjang:** Minimal 5 karakter, maksimal 500 karakter (`min:5|max:500`)
   * **Format / Allowlist:** Teks narasi bebas tag HTML (`regex:/^[^<>]*$/`).
   * **Pesan Error:** *"Alasan penolakan tidak boleh mengandung tag HTML atau karakter khusus."*

---

### B. Hasil Audit Query SQL (Parameterized Query & ORM Binding)
* **Status Query di SAKTI:** Seluruh query database di aplikasi SAKTI dibangun menggunakan **Eloquent ORM** bawaan Laravel (`User::where(...)`, `SuratPengajuan::where(...)`, dll.).
* **Pencegahan SQL Injection:** Tidak ditemukan penggunaan raw concatenation seperti `DB::raw("... WHERE id = " . $id)`. Eloquent secara otomatis menggunakan **PDO Prepared Statements** dengan parameter binding (`?`). Input pengguna (termasuk payload SQL seperti `' OR '1'='1`) selalu diperlakukan sebagai nilai data literal murni dan tidak pernah dapat mengubah struktur query database.

---

### C. Lokasi Penampilan Data & Output Encoding
* **Titik Render:** Header aplikasi (`resources/views/layouts/app.blade.php`), dashboard role, profil pengguna (`resources/views/profile/index.blade.php`), detail surat, dan lembar PDF.
* **Perbaikan Output Encoding:** Tag unescaped `{!! $titleRole !!}` pada `resources/views/layouts/app.blade.php:49` telah dieliminasi dan digantikan dengan sintaks Blade aman:
  ```blade
  <h1 class="text-lg font-semibold leading-tight">
      {{ $roleTitle }}<br>
      <span class="text-sm font-normal">{{ $subTitle }}</span>
  </h1>
  ```
* Seluruh variabel data pengguna dan data LDAP kini dirender 100% menggunakan `{{ $var }}` yang secara otomatis menerapkan fungsi `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

---

### D. Kebijakan Pesan Validasi
* Pesan validasi dibuat jelas dan memandu pengguna ke format yang benar tanpa pernah mengekspos rincian database, nama kolom internal, query SQL, maupun stack trace.
* Status HTTP yang dikembalikan saat validasi gagal adalah **422 Unprocessable Content** (untuk request JSON/AJAX) atau pengembalian pesan error flash `$errors` pada antarmuka web.

---

## 2. Ringkasan Perubahan Kode (Implementation Summary)

| File | Komponen yang Diubah | Tujuan Keamanan |
|---|---|---|
| `app/Http/Controllers/AuthController.php` | Menambahkan allowlist regex `^[a-zA-Z0-9._-]+$`, min:3, max:30 pada `$request->validate()` field `username`. | Mencegah input login disusupi karakter berbahaya atau bypass query. |
| `resources/views/layouts/app.blade.php` | Menghapus tag unescaped `{!! $titleRole !!}` dan memisahkan `$roleTitle` serta `$subTitle` menggunakan sintaks aman `{{ }}`. | Menutup celah Stored XSS dari manipulasi nilai prodi/role. |
| `routes/web.php` | 1. Menambahkan validasi `mahasiswa.*.npm` (`size:10`, `regex:/^[0-9]{10}$/`).<br>2. Menambahkan validasi allowlist `username` pada pembuatan akun admin.<br>3. Menambahkan validasi `nomor_surat` (`min:5`, `max:100`, regex format surat).<br>4. Menerapkan proteksi `not_regex:/[<>]/` pada field pengajuan dosen & mahasiswa.<br>5. Menerapkan validasi `alasan_tolak` pada rute tolak Admin, Kaprodi, dan Dekan. | Memastikan semua input eksternal dan parameter form terverifikasi sebelum masuk ke database. |
| `tests/Feature/ValidasiInputDanEncodingTest.php` | Membuat 2 automated test: uji penolakan payload SQL pada NPM (422) dan uji output encoding tag `<script>` pada nama user. | Menjamin kode teruji secara otomatis dan lulus skenario keamanan. |
| `tests/Feature/DateRangeValidationTest.php` & `MasterDataDropdownTest.php` | Menyesuaikan dummy NPM menjadi 10 digit angka resmi (`1402021001`). | Menjaga konsistensi data uji dengan aturan validasi allowlist resmi. |

---

## 3. Hasil Pengujian Otomatis (Automated Test Execution)

Pengujian dijalankan menggunakan PHPUnit / Pest pada environment testing Laravel:

```text
PASS  Tests\Feature\ValidasiInputDanEncodingTest
✓ input npm berisi karakter sql ditolak 422 dan tidak mengubah query (0.39s)
✓ nama berisi tag script tampil sebagai teks biasa dan terencode (0.03s)

Hasil Keseluruhan Test Suite:
Tests:    79 passed (424 assertions)
Duration: 199.40s
```

* **Test 1 (SQL Injection Blocked):** Input NPM berisi karakter `' OR '1'='1` menghasilkan response HTTP 422 dengan pesan `"Format NPM tidak valid. Harap masukkan 10 digit angka resmi."`. Tidak ada baris surat yang tercipta di database, dan query pencarian model membuktikan parameter diperlakukan sebagai literal biasa.
* **Test 2 (XSS Escaped):** Input nama akun berisi `<script>alert('XSS')</script>` berhasil dirender ke layar sebagai entitas HTML aman (`&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;`) dan diverifikasi melalui `assertDontSee('<script>alert(', false)` serta `assertSeeText()`.

---

## 4. Panduan Pengumpulan & Checklist Berkas

1. **Upload ke GitHub & Buat Pull Request:**
   * Branch: `feature-mode-a-validasi-input`
   * Target: `main`
   * Minta minimal satu anggota tim untuk melakukan **Code Review** dan klik **Approve** di GitHub.
2. **Folder Bukti Google Drive (`bit.ly/SproutApex-ValidasiInput`):**
   * Screenshot 1: Eksekusi `php artisan test` (lulus 79 test termasuk `ValidasiInputDanEncodingTest`).
   * Screenshot 2: Pengujian manual formulir dengan NPM salah / SQL injection yang menampilkan pesan error 422 / flash alert.
   * Screenshot 3: Tampilan nama berisi tag script yang muncul sebagai teks biasa di halaman web (Inspect Element membuktikan entity `&lt;script&gt;`).
   * Screenshot 4: Halaman Pull Request di GitHub lengkap dengan komentar review dan status Approved dari anggota lain.
3. **Ekspor ke PDF:**
   * Konversi dokumen ini beserta tangkapan layar di atas menjadi satu file PDF dengan nama:
     `Sprout Apex - Validasi Input.pdf`.
