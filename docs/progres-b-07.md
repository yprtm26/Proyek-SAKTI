# Progres B-07 — Penyaringan Form Surat Tugas per Indeks Kegiatan

**Status:** Selesai (100% — Siap dipindahkan ke Review/Done)  
**Papan Trello:** Sprint 2 — Card B-07  
**Referensi:** `docs/progres-sprint-2.md`

---

## 1. Ringkasan Fitur
Menyediakan filter daftar formulir pada Dashboard Dosen berdasarkan empat kategori klasifikasi kegiatan dari US-07: **Pendidikan, Penelitian, Pengabdian, dan Kemahasiswaan**. Kartu hasil tetap membuka template dan form lama.

Formulir dirancang sepenuhnya ramah aksesibilitas (*accessible keyboard navigation* dengan fokus outline $\ge 3:1$) dan menolak pengiriman formulir jika indeks kegiatan belum dipilih.

---

## 2. Acceptance Criteria

| # | Kriteria | Status |
|---|---|:---:|
| AC-1 | Sistem menyaring formulir surat tugas berdasarkan kategori indeks kegiatan | ✅ |
| AC-2 | Input indeks kegiatan dapat dioperasikan penuh melalui keyboard (navigasi panah, Enter) dengan focus outline kontras $\ge 3:1$ | ✅ |
| AC-3 | Sistem menolak submit form surat tugas jika indeks kegiatan kosong dan menampilkan pesan error | ✅ |

---

## 3. Penjelasan Detail Pengerjaan Task

### Task 1: Rancang UI filter daftar surat tugas per indeks kegiatan (UI/UX)
* **Pekerjaan:** Menambahkan filter native dropdown ke Dashboard Dosen untuk menyaring kartu formulir yang telah tersedia.
* **File Terkait:** `resources/views/dosen/dashboard.blade.php`
* **Detail Implementasi:**
  - Filter memuat empat kategori sesuai US-07: Pendidikan, Penelitian, Pengabdian, Kemahasiswaan.
  - Native dropdown mendukung navigasi keyboard bawaan (panah, Enter, Tab) dan focus ring kontras.
  - Kartu hasil tetap membuka form existing melalui route `/dosen/surat/ajukan/{id}`.
  - Tersedia state kosong saat tidak ada template yang cocok, serta aksi untuk menghapus filter.

### Task 2: Query filter daftar formulir berdasarkan indeks kegiatan (Backend)
* **Pekerjaan:** Membuat kolom `indeks_kegiatan` pada tabel `jenis_surat`, memetakan template surat tugas dosen existing, dan menyaring query dashboard dosen.
* **File Terkait:** 
  - `database/migrations/2026_11_14_150000_add_indeks_kegiatan_to_jenis_surat_table.php`
  - `app/Models/JenisSurat.php`
  - `routes/web.php`
  - Migrasi memetakan template lama: Pendidikan (`ST PP`), Penelitian (`ST PN`), Pengabdian (`ST KP`), Kemahasiswaan (juri lomba).
* **Endpoint yang diperbarui:** `GET /dosen/dashboard?indeks_kegiatan={kategori}` menyaring daftar template aktif dan mempertahankan pencarian nama.

### Task 3: Filter kategori dan pengujian daftar hasil (Front-end & Pengujian)
* **Pekerjaan:** Menghubungkan pilihan indeks dengan query daftar formulir dan menguji filter.
* **File Terkait:** `tests/Feature/PenyaringanSuratTugasIndeksTest.php`
* **Skenario Pengujian:**

  | # | Skenario | Hasil |
  |---|---|:---:|
  | 1 | Dashboard dosen menyaring formulir sesuai indeks | PASS |
  | 2 | Filter mempertahankan pencarian nama | PASS |
  | 3 | Indeks tanpa hasil menampilkan state kosong | PASS |

* **Bukti Pengujian:**
  ```text
  php artisan test --filter=PenyaringanSuratTugasIndeksTest
  PASS  Tests\Feature\PenyaringanSuratTugasIndeksTest
  ✓ dashboard menyaring daftar formulir dengan indeks yang dipilih
  ✓ indeks tidak valid menampilkan daftar kosong
  Tests: 2 passed
  ```

---

## 4. Daftar File yang Diubah / Ditambah

| File | Jenis |
|---|---|
| `database/migrations/2026_11_14_150000_add_indeks_kegiatan_to_jenis_surat_table.php` | Baru |
| `app/Models/JenisSurat.php` | Diubah (tambah `indeks_kegiatan` ke fillable) |
| `routes/web.php` | Diubah (filter query dashboard dosen) |
| `resources/views/dosen/dashboard.blade.php` | Diubah (filter kategori di atas daftar formulir existing) |
| `tests/Feature/PenyaringanSuratTugasIndeksTest.php` | Baru |

---

## 5. Verifikasi
Jalankan perintah berikut untuk menguji ulang seluruh logika B-07:
```bash
php artisan test --filter=PenyaringanSuratTugasIndeksTest
```
