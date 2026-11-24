# Progres B-13 B — Pembukaan dan Pengubahan Draf Surat Milik Pengusul

> **Sprint 2 · SAKTI — Mode A · Proyek LDAP UY, Universitas YARSI**

---

## 1. User Story

> Sebagai pengguna (Mahasiswa, Dosen, Tendik, atau Admin), saya ingin dapat membuka dan mengubah kembali (*edit*) pengajuan surat yang telah disimpan sebagai draf, agar saya bisa melengkapi data yang kurang atau mengoreksi kesalahan sebelum dokumen tersebut benar-benar dikirimkan di aplikasi SAKTI.

---

## 2. Acceptance Criteria

| # | Kriteria | Status |
|---|----------|--------|
| AC-1 | Pengusul dapat membuka draf miliknya; semua isian sebelumnya terisi di form dan dapat diubah. | ✅ Selesai |
| AC-2 | Pengusul dapat memperbarui data draf lalu menekan 'Kirim Pengajuan' untuk mengubah status jadi 'menunggu'. | ✅ Selesai |
| AC-3 | Sistem mengembalikan 403 Forbidden bila pengusul mencoba membuka draf milik akun lain via URL ID. | ✅ Selesai |

---

## 3. Checklist Trello

- [x] 1. Endpoint get detail draf surat dengan guard kepemilikan akun (Backend)
- [x] 2. Halaman edit draf dan populate data lama ke dalam form (Front-end)
- [x] 3. Aksi 'Kirim Pengajuan' dari draf untuk update status ke 'menunggu' (Backend)
- [x] 4. Uji proteksi akses lintas akun (IDOR 403) pada endpoint dan UI edit draf (Pengujian)

---

## 4. Implementasi

### 4.1 Routes Baru (`routes/web.php`)

**Dosen:**
| Method | URL | Keterangan |
|--------|-----|------------|
| `GET` | `/dosen/surat/{id}/edit-draf` | Buka form edit draf. 403 jika bukan pemilik. 404 jika bukan draf. |
| `POST` | `/dosen/surat/{id}/update-draf` | Update data draf. Jika `action=submit`, status diubah jadi `menunggu` dan notifikasi admin dikirim. |

**Mahasiswa:**
| Method | URL | Keterangan |
|--------|-----|------------|
| `GET` | `/mahasiswa/surat/{id}/edit-draf` | Buka form edit draf. 403 jika bukan pemilik. 404 jika bukan draf. |
| `POST` | `/mahasiswa/surat/{id}/update-draf` | Update data draf. Jika `action=submit`, status diubah jadi `menunggu` dan notifikasi admin dikirim. |

### 4.2 View Baru

| File | Keterangan |
|------|------------|
| `resources/views/surat/edit-draf.blade.php` | Halaman generik edit draf berbasis field dinamis template. Semua nilai lama dari `$draf->data` di-populate ke form. Tombol **Simpan Draf** (amber) & **Kirim Pengajuan** (hijau). Banner info draf amber di atas form. Data lama di luar field template (mis. `personel`, `anggota`) dipertahankan lewat input hidden agar tidak hilang saat simpan ulang. |

> **Catatan penting:** route `GET .../edit-draf` sengaja **selalu** memakai `surat.edit-draf`, walaupun template memiliki form khusus (`dosen.surat.form.*` / `mahasiswa.surat.form.*`). Form khusus adalah form *create*: tidak membaca `$draf` sehingga isian lama tidak muncul, dan `action`-nya masih menuju endpoint create sehingga "Kirim Pengajuan" akan membuat surat baru alih-alih memperbarui draf.

### 4.3 View yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/dosen/surat/log.blade.php` | Tab filter **Draf** (abu-abu), badge warna abu-abu untuk draf, tombol **Edit Draf** (amber) menggantikan "Detail" pada baris draf. |
| `resources/views/mahasiswa/surat/log.blade.php` | Sama seperti dosen. |
| `resources/views/dosen/surat/detail.blade.php` | Tombol **Edit Draf** (amber) muncul di panel aksi jika `status === 'draf'`. |
| `resources/views/mahasiswa/surat/detail.blade.php` | Sama seperti dosen. |

### 4.4 Helper yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Helpers/StatusHelper.php` | Tambah `case 'draf' => 'Draf'` pada fungsi `statusDosen()` dan `statusMahasiswa()`. |

---

## 5. Keamanan & Isolasi

| Mekanisme | Detail |
|-----------|--------|
| **Guard kepemilikan** | `GET` dan `POST` edit-draf memeriksa `$draf->user_id !== Auth::id()` → `abort(403)`. |
| **Filter status** | Query menggunakan `->where('status', 'draf')` sehingga surat berstatus lain tidak bisa diakses via URL ini (mengembalikan 404). |
| **Allowlist field** | Hanya field yang terdaftar di template (`$jenisSurat->fields`) yang diterima dari request (XSS sanitasi via `strip_tags`). |
| **Identitas terkunci** | Nama, NPM/NIDN, dan Prodi selalu ditimpa dari `Auth::user()` di server. |
| **Notifikasi kondisional** | Admin hanya dinotifikasi saat `action=submit`, bukan saat `action=draft`. |

---

## 6. Test Otomatis (`tests/Feature/EditDrafSuratB13BTest.php`)

| # | Nama Test | Membuktikan |
|---|-----------|-------------|
| 1 | `test_mahasiswa_dapat_membuka_halaman_edit_draf_miliknya` | AC-1: nilai lama tampil di form. |
| 2 | `test_dosen_dapat_membuka_halaman_edit_draf_miliknya` | AC-1: berlaku juga untuk role dosen. |
| 3 | `test_mahasiswa_lain_tidak_dapat_membuka_draf_milik_orang_lain` | AC-3: 403 pada GET. |
| 4 | `test_dosen_lain_tidak_dapat_membuka_draf_milik_dosen_lain` | AC-3: 403 pada GET dosen. |
| 5 | `test_endpoint_post_update_draf_mengembalikan_403_jika_bukan_pemilik` | AC-3: 403 pada POST. |
| 6 | `test_mahasiswa_dapat_memperbarui_isian_draf_tanpa_mengirim_pengajuan` | AC-2: simpan ulang tanpa kirim. |
| 7 | `test_mahasiswa_dapat_mengirim_pengajuan_dari_draf_sehingga_status_menjadi_menunggu` | AC-2: status jadi `menunggu`. |
| 8 | `test_dosen_dapat_mengirim_pengajuan_dari_draf_sehingga_status_menjadi_menunggu` | AC-2: berlaku untuk dosen. |
| 9 | `test_kirim_pengajuan_dari_draf_gagal_jika_field_wajib_kosong` | Validasi penuh saat kirim pengajuan. |
| 10 | `test_simpan_draf_ulang_boleh_dengan_isian_parsial_tanpa_error` | Validasi longgar saat simpan draf. |
| 11 | `test_surat_non_draf_tidak_dapat_diakses_via_endpoint_edit_draf` | 404 untuk surat berstatus bukan `draf`. |
| 12 | `test_surat_yang_dikirim_dari_draf_muncul_di_antrean_admin` | Surat yang dikirim dari draf masuk antrean Admin TU (nama pengusul, jenis surat, status, dan notifikasi admin). |
| 13 | `test_edit_draf_template_dengan_form_khusus_memunculkan_isian_lama` | Regresi: template dengan form khusus tetap ter-populate dan POST ke endpoint `update-draf`. |
| 14 | `test_kirim_draf_dari_form_khusus_memperbarui_surat_yang_sama` | Regresi: kirim dari form khusus tidak membuat surat baru (tetap 1 baris). |

**Hasil terakhir:** `Tests: 14 passed (46 assertions)`.

---

## 7. Cara Verifikasi Manual (di Browser via Laragon)

1. Login sebagai **Dosen/Mahasiswa** di `http://proyek-sakti.test/`.
2. Buka form pengajuan surat → klik **Simpan Draf** (tombol kuning-amber).
3. Pergi ke **Log Surat** (`/dosen/surat` atau `/mahasiswa/surat`).
4. Lihat badge **Draf** abu-abu dan tombol **Edit Draf** (amber) pada baris yang sesuai.
5. Klik **Edit Draf** → isian lama harus muncul di form dengan banner info draf.
6. Ubah beberapa field → klik **Simpan Draf** lagi → status tetap Draf.
7. Ubah beberapa field → klik **Kirim Pengajuan** → redirect ke detail surat dengan status **Menunggu Verifikasi TU**.
8. Verifikasi IDOR: salin URL edit-draf, logout, login sebagai akun lain, buka URL tersebut → harus tampil **403 Forbidden**.

---

## 8. File yang Diubah / Dibuat

| File | Status |
|------|--------|
| `routes/web.php` | ✏️ Diubah — tambah 4 route (GET/POST edit-draf dosen & mahasiswa) |
| `resources/views/surat/edit-draf.blade.php` | ➕ Dibuat baru |
| `resources/views/dosen/surat/log.blade.php` | ✏️ Diubah — tab Draf, badge, tombol Edit Draf |
| `resources/views/mahasiswa/surat/log.blade.php` | ✏️ Diubah — tab Draf, badge, tombol Edit Draf |
| `resources/views/dosen/surat/detail.blade.php` | ✏️ Diubah — tombol Edit Draf di panel aksi |
| `resources/views/mahasiswa/surat/detail.blade.php` | ✏️ Diubah — tombol Edit Draf di panel aksi |
| `app/Helpers/StatusHelper.php` | ✏️ Diubah — case 'draf' di statusDosen() dan statusMahasiswa() |
| `tests/Feature/EditDrafSuratB13BTest.php` | ➕ Dibuat baru — 12 test cases |
| `docs/progres-b-13b.md` | ➕ Dibuat baru |
| `docs/progres-sprint-2.md` | ✏️ Diubah — B-13B ditandai Selesai |
