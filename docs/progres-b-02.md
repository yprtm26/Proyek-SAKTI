# Progres B-02 — Pengajuan Surat Terikat Otomatis pada Akun Pengusul

**Status:** Selesai (100% untuk role Mahasiswa dan Dosen)  
**Papan Trello:** Sprint 1 — Card B-02  

---

## 1. Ringkasan Fitur
Menjamin setiap formulir pengajuan surat secara otomatis terikat pada akun pengusul yang sedang login. Mengunci kolom identitas agar tidak bisa diedit secara manual dan memproteksi sisi backend agar data `user_id` tidak dapat dimanipulasi atau dipalsukan (*anti-spoofing*).

---

## 2. Penjelasan Detail Pengerjaan Task

### Task 1: Desain tampilan kolom identitas otomatis pada form surat (UI/UX)
* **Pekerjaan:** Koordinasi layout tampilan pengisian otomatis identitas.
* **Catatan:** Sesuai arahan langsung dari *client*, desain baru dilewati agar tidak merombak tata letak formulir yang sudah ada. Implementasi langsung memanfaatkan form yang sudah berjalan dengan menambahkan nilai otomatis dan status terkunci.

### Task 2: Buat API pengambil data profil pengusul yang sedang login (Backend)
* **Pekerjaan:** Menyediakan endpoint JSON untuk membaca profil akun aktif.
* **File Terkait:** `routes/web.php`.
* **Endpoint:** `GET /api/profil-pengusul` dan alias `GET /api/me`.
* **Cara Kerja:** Dilindungi middleware `auth`. Mengembalikan JSON berisi `id`, `name`, `username` (NPM/NIDN), `email`, `role`, dan program studi pengusul.

### Task 3: Tampilkan dan kunci (read-only) kolom identitas di form (Front-end)
* **Pekerjaan:** Mengunci input identitas pengusul di seluruh form surat mahasiswa dan dosen.
* **File Terkait:**
  * Form Mahasiswa: `resources/views/mahasiswa/surat/form/*.blade.php` (Surat Keterangan Aktif, Permohonan Magang, Observasi Skripsi, Remedial Bahasa Inggris, Permohonan Aktif Akademik, Observasi Tugas Mata Kuliah).
  * Form Dosen: `resources/views/dosen/surat/form/*.blade.php` (Surat Tugas Narasumber, Juri Lomba, Pelatihan, HKI, Modul, RPS, Buku Ajar, Penelitian, Publikasi Artikel Ilmiah).
* **Cara Kerja:** Input Nama, NPM/NIDN, dan Program Studi langsung diisi via Blade `auth()->user()` serta diberi atribut `readonly`/`disabled` dengan styling kelas CSS `bg-gray-100 cursor-not-allowed`. Pengusul tidak bisa mengganti identitas dirinya menjadi akun lain.

### Task 4: Simpan relasi pengajuan surat ke ID akun pengusul di DB (Backend)
* **Pekerjaan:** Mengamankan alur simpan pengajuan di server agar bebas dari manipulasi request.
* **File Terkait:** `routes/web.php`.
* **Cara Kerja:**
  1. Server mengabaikan field `user_id` yang dikirim dari form request.
  2. Nilai `user_id` dipaksa mengambil dari session autentikasi server (`Auth::id()`).
  3. Pada form kelompok (seperti magang atau remedial), nama pengusul pertama dipaksa memakai identitas akun login.
  4. Pada surat tugas dosen, field nama, NIDN, dan prodi ditimpa dengan data akun dosen yang login sebelum data disimpan ke tabel `surat_pengajuan`.

### Task 5: Uji pengisian otomatis dan keamanan kunci identitas (Pengujian)
* **Pekerjaan:** Membuat unit & feature test otomatis untuk memverifikasi keamanan data persuratan.
* **File Terkait:** `tests/Feature/PengajuanTerikatKeAkunTest.php`.
* **Skenario yang Diuji:**
  - Endpoint API `/api/profil-pengusul` mewajibkan login dan merespons profil akun yang tepat.
  - Surat yang diajukan tersimpan dengan `user_id` milik akun yang login.
  - Percobaan manipulasi identitas dosen pada payload request POST berhasil digagalkan dan ditimpa data server.
  - Mahasiswa pemohon pertama pada pengajuan surat kelompok dipaksa sesuai data akun.
  - File template Blade form memuat atribut proteksi `readonly` pada kolom identitas.
* **Hasil Eksekusi:**
  ```text
  php vendor/bin/phpunit tests/Feature/PengajuanTerikatKeAkunTest.php --testdox
  OK (5 tests, 20 assertions)
  ```

---

## 3. Batas Cakupan (Scope Notes)
* Role Admin TU berstatus pemroses surat, bukan pengusul surat melalui form mahasiswa/dosen. Tidak ada form pengajuan Admin TU pada cakupan B-02.
* Kolom identitas NIK untuk tendik/admin belum dipetakan karena belum ada kepastian kolom `serial_number` pada skema database lokal.

---

## 4. Verifikasi
Jalankan perintah berikut untuk menguji ulang seluruh logika B-02:
```bash
php vendor/bin/phpunit tests/Feature/PengajuanTerikatKeAkunTest.php
```
