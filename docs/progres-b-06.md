# Progres B-06 — Pemilihan Jenis Surat dari Daftar Template Aktif

**Status:** Selesai  
**Papan Trello:** Sprint 1 — Card B-06  
**Tanggal:** 6 November 2026 (diperbarui 7 November 2026)

---

## 1. Ringkasan Fitur

Pengusul (mahasiswa/dosen) hanya melihat dan dapat memakai template surat yang
berstatus **aktif** dan sesuai dengan role-nya. Ketika template dipilih, sistem
menampilkan form yang dirender dari daftar `fields` milik template. Template
nonaktif tidak muncul di dashboard, tidak dapat dibuka lewat URL langsung, dan
tidak dapat dipakai untuk membuat pengajuan.

Form khusus yang sudah ada (mis. Surat Keterangan Aktif, Permohonan Magang,
Surat Tugas Dosen) tetap digunakan. Form dinamis menjadi fallback untuk template
baru yang dibuat Admin TU melalui fitur B-15a dan belum memiliki view khusus.

---

## 2. Pekerjaan per Checklist

### Task 1 — Endpoint daftar template aktif (Backend)
* **File:** `app/Http/Controllers/TemplateSuratController.php`, `app/Models/JenisSurat.php`, `routes/web.php`
* **Endpoint:** `GET /api/template-surat`
* **Query opsional:** `target_user=mahasiswa|dosen`, `search=<kata kunci>`
* **Perilaku:**
  * Endpoint hanya dapat diakses role `mahasiswa` dan `dosen`.
  * Hanya mengembalikan template `aktif = true`.
  * Pengusul otomatis dibatasi ke template milik role-nya sendiri.
  * Filter `target_user` untuk role lain ditolak `403 Forbidden`, sehingga daftar
    template tidak bisa dibaca lintas role.
  * Mengembalikan `id`, `nama`, `slug`, `kode`, `target_user`, `fields`, dan `url_ajukan`.
  * Filter `target_user` divalidasi; nilai selain `mahasiswa`/`dosen` ditolak 422.

### Task 2 — Fitur pilih template & render form dinamis (Front-end)
* **File:** `resources/views/surat/form-dinamis.blade.php`, `app/Helpers/SuratHelper.php`, `routes/web.php`
* **Perilaku:**
  * Dashboard mahasiswa/dosen tetap menampilkan kartu template aktif.
  * Route form memakai view khusus jika tersedia; jika tidak, memakai form dinamis.
  * Form dinamis merender satu input untuk setiap `fields` template.
  * Tipe input disesuaikan otomatis: tanggal, waktu, angka, URL, email, textarea, atau teks.
  * Identitas pengusul ditampilkan read-only dan nilainya dikunci di server (integrasi B-02).
  * Field identitas yang dikunci per role:
    * Mahasiswa: `nama`, `npm`, `prodi`.
    * Dosen: `nama_dosen`, `no_nip`, `prodi` (dan `nama_ditugaskan` pada form khusus).
  * Field identitas tersebut tetap dikirim sebagai input read-only agar validasi
    server terpenuhi, lalu nilainya ditimpa dari akun pengusul di sisi server.

### Task 3 — Pengujian template aktif vs nonaktif
* **File:** `tests/Feature/PemilihanTemplateAktifTest.php`
* **Skenario:**
  * Dashboard hanya menampilkan template aktif sesuai role.
  * Template nonaktif tidak bisa dibuka (GET) maupun diajukan (POST).
  * Template milik role lain tidak bisa dipakai.
  * Form dinamis merender field dan tipe input yang sesuai (date, time, number,
    URL, email, textarea, dan teks).
  * Pengajuan template dinamis menyimpan data field dengan benar.
  * Identitas mahasiswa pada form dinamis dikunci di server, bukan hanya read-only
    di tampilan, dan nilai identitas palsu tidak ditampilkan kembali setelah
    validasi gagal.
  * Field wajib divalidasi dan pengajuan kosong tidak tersimpan.
  * Form khusus yang sudah ada tetap dipakai (tidak regresi).
  * Endpoint hanya dapat diakses pengusul, hanya mengembalikan template aktif
    sesuai role, menolak filter role lain, dan menolak filter tidak valid.
  * Endpoint mengembalikan kunci respons yang dibutuhkan dan mendukung pencarian nama.

### Task 4 — Review integrasi & dokumentasi
* **File:** `README.md`, `docs/progres-sprint-1.md`, `docs/progres-b-06.md`, `docs/hak-akses.md`
* README yang sebelumnya masih memiliki penanda konflik merge dirapikan menjadi
  dokumentasi proyek.
* Dokumentasi progres Sprint 1 diperbarui.
* Review keamanan B-06: endpoint dibatasi ke role pengusul, filter role lain
  ditolak, dan identitas mahasiswa pada form dinamis dikunci di server.
* Test regresi ditambahkan untuk bypass role, lock identitas, tipe input,
  struktur respons endpoint, dan pencarian template.

---

## 3. Bukti Pengujian

```text
php vendor/bin/phpunit tests/Feature/PemilihanTemplateAktifTest.php --testdox
OK (19 tests, 109 assertions)
```

Seluruh test proyek:

```text
php vendor/bin/phpunit --testdox
OK (35 tests, 166 assertions)
```

---

## 4. Batas Cakupan

* Form dinamis menggunakan satu nilai teks per field. Tipe field lanjutan
  (tabel anggota berulang, unggah lampiran khusus) masih memakai form khusus
  yang sudah ada.
* Template baru dari Admin TU otomatis memakai form dinamis tanpa perlu
  menambahkan file Blade baru.
* Preview/PDF khusus untuk template baru belum termasuk cakupan B-06; halaman
  detail tetap menampilkan pesan "Preview surat belum tersedia" bila view
  preview belum ada.
