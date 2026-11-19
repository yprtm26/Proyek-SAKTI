# NOTULEN KEPUTUSAN KELOMPOK (MODE A)
## VALIDASI INPUT DAN PENGAMANAN DATA LDAP — APLIKASI SAKTI

* **Nama Tim:** Sprout Apex
* **Aplikasi:** SAKTI (Sistem Administrasi dan Kelola Tata Informasi)
* **Mode Proyek:** MODE A · PROYEK LDAP UY: Input Kampus dan Data LDAP
* **Waktu Diskusi:** 11.00 – 12.20 WIB

---

### 1. Lima (5) Input Paling Penting dan Aturan Allowlist Masing-Masing
*(Prinsip Allowlist: Sistem hanya menerima data yang cocok dengan kriteria yang ditentukan secara ketat. Di luar kriteria ini, sistem langsung menolak).*

1. **NPM Mahasiswa (`mahasiswa.*.npm`)**
   * **Dipakai pada:** Form pengajuan surat mahasiswa (misal: Surat Permohonan Magang & Surat Pengantar Remedial Bahasa Inggris).
   * **Tipe Data:** Angka / Teks Numerik (`string`)
   * **Panjang:** Tepat 10 karakter (`size:10`)
   * **Format / Allowlist:** Hanya angka 0–9 dengan regex `^[0-9]{10}$` (contoh: `1402021001`). Tidak boleh mengandung huruf, spasi, tanda kutip, maupun karakter SQL.

2. **Username Akun (`username`)**
   * **Dipakai pada:** Form login pengguna dan form pembuatan akun baru oleh administrator.
   * **Tipe Data:** Teks Alfanumerik (`string`)
   * **Panjang:** Minimal 3 karakter, maksimal 30 karakter
   * **Format / Allowlist:** Hanya huruf, angka, titik, dan strip dengan regex `^[a-zA-Z0-9._-]+$`. Tidak boleh mengandung spasi atau karakter khusus SQL/LDAP.

3. **Nomor Surat (`nomor_surat`)**
   * **Dipakai pada:** Proses finalisasi surat oleh Admin Tata Usaha (TU).
   * **Tipe Data:** Teks (`string`)
   * **Panjang:** Minimal 5 karakter, maksimal 100 karakter
   * **Format / Allowlist:** Format nomor resmi universitas dengan regex `^[0-9A-Za-z\/\.\-]+$` (contoh: `045/FTI/B/XI/2026`). Hanya mengizinkan huruf, angka, garis miring `/`, titik `.`, dan tanda hubung `-`. Dilarang memuat simbol aneh atau tag HTML.

4. **Keperluan / Perihal Surat (`data.*`)**
   * **Dipakai pada:** Form dinamis pengajuan surat oleh mahasiswa dan dosen.
   * **Tipe Data:** Teks Kalimat Biasa (`string`)
   * **Panjang:** Minimal 5 karakter, maksimal 500 karakter
   * **Format / Allowlist:** Teks narasi bebas tag HTML (regex `^[^<>]*$`). Sistem melarang keras simbol `<` dan `>` untuk mencegah injeksi kode berbahaya.

5. **Alasan Penolakan Surat (`alasan_tolak`)**
   * **Dipakai pada:** Form saat Admin, Kaprodi, atau Dekan menolak pengajuan surat.
   * **Tipe Data:** Teks Kalimat Biasa (`string`)
   * **Panjang:** Minimal 5 karakter, maksimal 500 karakter
   * **Format / Allowlist:** Teks narasi bebas tag HTML (regex `^[^<>]*$`). Nilai ini akan disimpan dan ditampilkan pada notifikasi pengguna sehingga wajib bebas dari script.

---

### 2. Query Mana yang Masih Disusun dengan Menggabungkan String (Raw Query)?
* **Temuan di Aplikasi SAKTI:** **Tidak ada.** Tidak ditemukan satupun query SQL yang dirangkai secara manual dengan menggabungkan string (seperti `DB::select("... WHERE npm = '" . $npm . "'")` atau `whereRaw("status = '$status'")`).
* **Penyebab Aman:** Seluruh interaksi database di aplikasi SAKTI sudah menggunakan **Eloquent ORM** bawaan Laravel (contoh: `User::where('username', $username)->first()`).
* **Mekanisme Perlindungan:** Eloquent ORM secara default selalu memakai **Prepared Statements / Parameter Binding PDO** (menggunakan placeholder `?`). Nilai dari pengguna diperlakukan murni sebagai nilai data (literal value), bukan sebagai sintaks perintah database. Oleh karena itu, input berbahaya seperti `' OR '1'='1` tidak akan pernah bisa mengubah struktur query SQL.

---

### 3. Di Mana Data Pengguna dan Data LDAP Ditampilkan, dan Apakah Sudah Di-encode?
* **Titik Penampilan Data:**
  1. Header aplikasi (nama pengguna dan peran/role).
  2. Dashboard masing-masing role (Mahasiswa, Dosen, Admin TU, Kaprodi, Dekan).
  3. Halaman Profil Pengguna (`profile/index.blade.php`).
  4. Halaman Detail Surat dan Riwayat Pengajuan.
  5. Lembar cetak dokumen PDF surat resmi (`pdf/*.blade.php`).
* **Status Encoding:**
  * **Sudah aman di-encode.** Aplikasi SAKTI menggunakan template engine bawaan Laravel (Blade) dengan tanda kurung kurawal ganda `{{ $user->name }}`.
  * Tanda `{{ }}` secara otomatis memanggil fungsi PHP `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` yang mengubah karakter berbahaya menjadi entitas HTML aman (contoh: `<` diubah menjadi `&lt;` dan `>` menjadi `&gt;`).
  * Dengan demikian, apabila data dari LDAP memuat tag script seperti `<script>alert('XSS')</script>`, teks tersebut hanya akan tampil sebagai tulisan biasa di layar dan browser tidak akan menjalankannya sebagai script berbahaya.
  * *Tindakan Perbaikan:* Satu-satunya tag unescaped `{!! $titleRole !!}` yang ditemukan pada `resources/views/layouts/app.blade.php:49` telah diidentifikasi untuk diganti sepenuhnya menjadi elemen HTML terpisah dengan sintaks aman `{{ }}`.

---

### 4. Kalau Input Ditolak, Pesan Apa yang Ditampilkan ke Pengguna?
Pesan kesalahan dirancang dengan prinsip: **jelas dan informatif bagi pengguna**, namun **tidak membocorkan detail teknis sistem** (seperti struktur tabel, nama kolom database, query SQL, atau stack trace).

* **Contoh Pesan Validasi:**
  * **Jika format NPM salah / disusupi karakter SQL:**
    > *"Format NPM tidak valid. Harap masukkan 10 digit angka resmi."*
  * **Jika format nomor surat tidak sesuai:**
    > *"Format nomor surat tidak valid. Hanya boleh memuat huruf, angka, garis miring (/), titik (.), dan tanda hubung (-)."*
  * **Jika input mengandung tag HTML atau karakter berbahaya:**
    > *"Input tidak valid. Tidak boleh mengandung tag HTML atau karakter khusus."*
  * **Jika login gagal:**
    > *"Username atau password salah."* *(Tidak membocorkan apakah username atau password yang salah untuk mencegah user enumeration).*
* **Respon Teknis:** Sistem mengembalikan status HTTP 422 (Unprocessable Content) jika diakses via API/AJAX, atau melakukan redirect kembali ke formulir dengan membawa flash message `$errors`.
