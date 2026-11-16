# Progres B-15a — Pembuatan dan Penyimpanan Template Surat Baru oleh Admin TU

**Status:** Selesai — Development 100% (4/4 subtask dev tuntas, sisa QA testing)  
**Papan Trello:** Sprint 1 — Card B-15a  

---

## 1. Ringkasan Fitur
Memberikan kemampuan bagi Admin Tata Usaha (TU) untuk membuat, menyimpan, mengedit, dan menghapus template surat baru secara mandiri melalui antarmuka web. Setiap template mencakup nama surat, kode klasifikasi opsional, target pengguna (Mahasiswa/Dosen), alur persetujuan yang dapat dikonfigurasi, serta daftar field dinamis yang nantinya akan diisi oleh pengusul saat mengajukan surat.

---

## 2. Penjelasan Detail Pengerjaan Task

### Task 1: Buat tabel database dan migration untuk template & field (Backend)
* **Pekerjaan:** Merancang dan menjalankan migration tabel `jenis_surat` beserta kolom pendukungnya.
* **File Terkait:**
  * `database/migrations/2025_12_27_170239_create_jenis_surat_table.php` — Tabel utama.
  * `database/migrations/2026_01_17_184959_add_scope_nomor_to_jenis_surat_table.php` — Kolom `scope_nomor` dan `perlu_dekan`.
  * `database/migrations/2026_01_24_203643_alter_scope_nomor_nullable_on_jenis_surat_table.php` — Penyesuaian nullable.
  * `database/migrations/2026_01_25_144916_add_ttd_qr_type_to_jenis_surat_table.php` — Kolom `ttd_qr_type`.
  * `database/migrations/2026_01_26_065940_add_target_user_to_jenis_surat.php` — Kolom `target_user`.
* **Struktur Tabel `jenis_surat`:**

  | Kolom | Tipe | Keterangan |
  |-------|------|------------|
  | `id` | bigint (PK) | Auto-increment |
  | `nama` | varchar(255) | Nama template surat |
  | `slug` | varchar (unique) | URL-friendly identifier |
  | `kode` | varchar (nullable) | Kode klasifikasi surat |
  | `target_user` | varchar | `mahasiswa` atau `dosen` |
  | `alur` | JSON | Array alur persetujuan: `["admin","kaprodi","dekan"]` |
  | `fields` | JSON | Array field dinamis: `["semester","tahun_akademik"]` |
  | `scope_nomor` | varchar (nullable) | Ruang lingkup penomoran (`FTI` / `PRODI`) |
  | `perlu_dekan` | boolean | Apakah alur menyertakan tanda tangan dekan |
  | `ttd_qr_type` | varchar | Tipe TTD QR code |
  | `aktif` | boolean (default: true) | Status template aktif/nonaktif |
  | `created_at` | timestamp | Waktu pembuatan |
  | `updated_at` | timestamp | Waktu pembaruan terakhir |

* **Model:** `app/Models/JenisSurat.php`
  * `$fillable`: `nama`, `slug`, `kode`, `target_user`, `scope_nomor`, `fields`, `alur`, `aktif`, `perlu_dekan`, `ttd_qr_type`.
  * `$casts`: `alur` → array, `fields` → array, `ttd_qr_type` → string.
  * Relasi: `suratPengajuan()` → `hasMany(SuratPengajuan::class, 'jenis_surat_id')`.

---

### Task 2: Buat endpoint simpan template surat baru beserta field-nya (Backend)
* **Pekerjaan:** Menyediakan endpoint CRUD lengkap untuk pengelolaan template surat.
* **File Terkait:** `routes/web.php` (bagian `TEMPLATE SURAT (B-15a)`).
* **Daftar Endpoint:**

  | Method | URL | Fungsi | Route Name |
  |--------|-----|--------|------------|
  | GET | `/admin/template-surat` | Daftar semua template (dipisah per kategori) | `admin.template-surat.index` |
  | GET | `/admin/template-surat/create` | Form buat template baru | `admin.template-surat.create` |
  | POST | `/admin/template-surat` | Simpan template baru | `admin.template-surat.store` |
  | GET | `/admin/template-surat/{id}/edit` | Form edit template | `admin.template-surat.edit` |
  | PUT | `/admin/template-surat/{id}` | Perbarui template | `admin.template-surat.update` |
  | DELETE | `/admin/template-surat/{id}` | Hapus template | `admin.template-surat.destroy` |

* **Logika Penting pada Store:**
  1. Slug unik digenerate otomatis dari nama template via `Str::slug()`. Jika slug sudah ada, ditambahkan suffix angka urut (`-2`, `-3`, dst).
  2. `scope_nomor` diisi otomatis berdasarkan alur: `FTI` jika ada `dekan`, `PRODI` jika ada `kaprodi`.
  3. `perlu_dekan` di-set `true` jika alur mengandung `dekan`.
  4. `target_user` disimpan via mass assignment (`$fillable`).
* **Proteksi Hapus:** Template yang sudah memiliki pengajuan surat tidak bisa dihapus (dicek via relasi `suratPengajuan()->exists()`), menampilkan pesan agar template dinonaktifkan saja.

---

### Task 3: Bangun halaman form pembuatan template baru untuk Admin TU (Front-end)
* **Pekerjaan:** Membangun antarmuka CRUD template surat lengkap.
* **File Terkait:**
  * `resources/views/admin/template-surat/index.blade.php` — Halaman daftar template dengan tab Mahasiswa/Dosen.
  * `resources/views/admin/template-surat/_tabel.blade.php` — Partial tabel reusable per kategori.
  * `resources/views/admin/template-surat/create.blade.php` — Halaman buat template baru.
  * `resources/views/admin/template-surat/edit.blade.php` — Halaman edit template.
  * `resources/views/admin/template-surat/_form.blade.php` — Shared form partial (create & edit).
* **Fitur Antarmuka:**
  - Input nama template, kode klasifikasi (opsional), target pengguna (dropdown), status aktif (checkbox).
  - Checkbox alur persetujuan: Admin TU, Kaprodi, Dekan.
  - Repeater field dinamis dengan JavaScript (tambah/hapus baris). Minimal 1 baris field wajib ada.
  - Halaman index menampilkan 2 tab terpisah: **Mahasiswa** dan **Dosen** dengan badge jumlah template.
  - Tampilan error validasi global dan per-field.

---

### Task 4: Tambahkan validasi field wajib pada penyimpanan template (Backend)
* **Pekerjaan:** Memperkuat aturan validasi server-side pada store dan update template.
* **File Terkait:** `routes/web.php` (closure `$validasiTemplate`).
* **Aturan Validasi:**

  | Field | Aturan | Pesan Error |
  |-------|--------|-------------|
  | `nama` | required, string, max:255, **unique** (ignore self saat edit) | "Nama template wajib diisi." / "Nama template sudah digunakan, gunakan nama lain." |
  | `kode` | nullable, string, max:255, **unique** (ignore self saat edit) | "Kode klasifikasi sudah digunakan oleh template lain." |
  | `target_user` | required, in:mahasiswa,dosen | "Target pengguna wajib dipilih." / "Target pengguna tidak valid." |
  | `alur` | required, array, min:1 | "Pilih minimal satu alur persetujuan." |
  | `alur.*` | in:admin,kaprodi,dekan | "Alur persetujuan tidak valid." |
  | `fields` | required, array, min:1 | "Tambahkan minimal satu field dinamis." |
  | `fields.*` | required, string, max:100, distinct, regex:`/^[a-z0-9_]+$/` | "Nama field tidak boleh kosong." / "Nama field tidak boleh sama." / "Nama field hanya boleh huruf kecil, angka, dan garis bawah." |
  | `aktif` | nullable, boolean | — |

* **Mekanisme Unique pada Update:** Menggunakan `Rule::unique('jenis_surat', 'kolom')->ignore($excludeId)` agar validasi unik tidak memblokir data milik record yang sedang diedit sendiri.

---

### Task 5: Uji pembuatan template baru dan skenario gagal validasi (Pengujian)
* **Status:** Belum dikerjakan — menjadi tanggung jawab anggota QA (Arman Dava Wicaksono).
* **Skenario yang perlu diuji:**
  1. Simpan template dengan data valid → redirect ke index dengan flash success.
  2. Simpan template dengan nama kosong → error "Nama template wajib diisi."
  3. Simpan template dengan nama duplikat → error "Nama template sudah digunakan."
  4. Simpan template dengan field kosong → error "Nama field tidak boleh kosong."
  5. Simpan template dengan field format salah (huruf kapital/spasi) → error regex.
  6. Simpan template dengan field duplikat → error "Nama field tidak boleh sama."
  7. Hapus template yang sudah punya pengajuan → ditolak dengan pesan nonaktifkan.
  8. Edit template tanpa mengubah nama → lolos validasi unique (tidak error).

---

## 3. Catatan Batas Cakupan (Scope Notes)
* Template surat yang sudah memiliki pengajuan surat terkait tidak dapat dihapus; hanya bisa dinonaktifkan.
* Slug template tidak diubah saat proses edit agar konsistensi data pengajuan lama tetap terjaga.
* Halaman index template dipisahkan per tab (Mahasiswa dan Dosen) untuk mempermudah pencarian.

---

## 4. Verifikasi Manual
Akses halaman berikut melalui browser (login sebagai Admin TU):
```
http://sistem-pengajuan-surat.test/admin/template-surat          → Daftar template (tab Mahasiswa/Dosen)
http://sistem-pengajuan-surat.test/admin/template-surat/create   → Form buat template baru
```
