# Progres B-10 B — Pengisian Daftar Anggota dan Peran dalam Kegiatan

**Status:** Selesai (100% — Siap Review/Done)  
**Papan Trello:** Sprint 2 — Card B-10 B  
**User Story:** Sebagai pengguna (Mahasiswa, Dosen, Tendik, atau Admin), saya ingin dapat menambahkan beberapa nama anggota sekaligus beserta perannya masing-masing ke dalam formulir kegiatan, agar susunan kepanitiaan atau tim penugasan dapat tercatat secara lengkap dan akurat dalam satu dokumen pengajuan di aplikasi Sakti.  

---

## 1. Ringkasan Fitur
Fitur ini memungkinkan pengusul surat tugas atau kegiatan di SAKTI untuk menginput daftar nama anggota tim, peran dalam kegiatan, serta instansi/NIM/NIDN secara dinamis (tambah dan hapus baris langsung pada form). Data personel disimpan ke tabel relasi `surat_personel` secara *bulk*, dilengkapi validasi anti-baris kosong (HTTP 422), dan memenuhi standar aksesibilitas UI/UX (target klik $\ge 44 \times 44\text{px}$ dan *focus ring* kontras $\ge 3:1$).

---

## 2. Checklist Pengerjaan Trello

- [x] **1. Rancang antarmuka tabel dinamis tambah/hapus anggota kegiatan (Josua Rajak) (UI/UX)**
  - Dibuat komponen tabel terstruktur dengan kolom: No, Nama Lengkap, Peran dalam Kegiatan, Instansi/NIM/NIDN, dan Aksi.
  - Baris pertama otomatis terisi data pengusul (Ketua / Penanggung Jawab) sebagai baris utama.
  - Tombol Tambah Anggota dan Tombol Hapus Baris memenuhi standar ukuran sentuh minimal $44 \times 44\text{px}$ (`min-h-[44px] min-w-[44px]`).
  - Indikator fokus (*focus ring*) memiliki rasio kontras $\ge 3:1$ terhadap latar belakang (`focus:ring-2 focus:ring-offset-2 focus:ring-blue-600` dan `focus:ring-red-600`).

- [x] **2. Tabel relasi personel surat dan endpoint simpan bulk anggota (Yuga Pratama Andika Syaputra) (Backend)**
  - Skema tabel `surat_personel` (`database/migrations/2026_11_23_000001_create_surat_personel_table.php`):
    - `id` (PK)
    - `surat_pengajuan_id` (FK to `surat_pengajuan`, cascade delete)
    - `nama` (varchar 150, required)
    - `peran` (varchar 100, required)
    - `instansi` (varchar 150, nullable)
    - `urutan` (unsigned smallint default 0)
  - Model Eloquent `app/Models/SuratPersonel.php` terhubung timbal-balik via relasi `hasMany` pada `app/Models/SuratPengajuan.php`.
  - Logika penyimpanan *bulk* diintegrasikan pada alur submit surat pengajuan (`routes/web.php`).
  - Disediakan pula endpoint API pengelolaan personel terproteksi anti-IDOR: `GET /api/surat/{id}/personel` dan `POST /api/surat/{id}/personel`.

- [x] **3. Komponen dynamic input row anggota di form pengajuan (Josua Rajak) (Front-end)**
  - Berkas komponen Blade terpusat: `resources/views/surat/partials/tabel-personel.blade.php`.
  - Skrip JavaScript murni tanpa dependensi eksternal:
    - Menambahkan baris baru secara instan tanpa reload halaman.
    - Menghapus baris tertentu dan otomatis melakukan *renumbering* nomor urut 1, 2, 3...
    - Keteroperasian keyboard: tombol dapat diakses via `Tab` dan dipicu via `Enter` / `Space`.
    - Auto-focus: setelah baris baru dibuat, fokus kursor otomatis berpindah ke kolom input Nama baru.
    - Validasi submit: form otomatis diblokir di sisi browser dan menampilkan kotak peringatan jika ada baris anggota yang kolom Nama atau Perannya dibiarkan kosong.
  - Komponen disematkan pada:
    - Form dinamis template kegiatan: `resources/views/surat/form-dinamis.blade.php`.
    - Form khusus tugas dosen: `resources/views/dosen/surat/form/surat-tugas-kegiatan-kuliah.blade.php`.

- [x] **4. Uji validasi baris anggota kosong dan keteroperasian keyboard (Arman Dava Wicaksono) (Pengujian)**
  - Berkas pengujian otomatis: `tests/Feature/PengisianAnggotaKegiatanB10BTest.php` (5 test cases komprehensif).
  - Skenario pengujian yang terbukti lulus:
    1. Pengusul dapat mengajukan surat dengan daftar anggota secara *bulk* (tersimpan ke `surat_personel`).
    2. Sistem memblokir submit jika ada baris anggota dengan Nama kosong (HTTP 422 / error session).
    3. Sistem memblokir submit jika ada baris anggota dengan Peran kosong (HTTP 422 / error session).
    4. Elemen tombol memenuhi target sentuh minimal 44x44px dan focus ring kontras tinggi.
    5. Endpoint API bulk personel berfungsi dan memblokir upaya manipulasi akses lintas akun (IDOR 403).

---

## 3. Bukti Perintah Pengujian
Jalankan pengujian melalui terminal:
```powershell
php artisan test tests/Feature/PengisianAnggotaKegiatanB10BTest.php
```
