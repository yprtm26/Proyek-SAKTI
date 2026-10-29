# Aturan Hak Akses Menu & Endpoint

Dokumen ini adalah acuan resmi **Sprint 1 (Backend)** untuk menentukan siapa
boleh mengakses menu dan endpoint apa pada aplikasi SAKTI.

---

## 1. Daftar Role

| Role | Kode | Keterangan |
|------|------|------------|
| Mahasiswa | `mahasiswa` | Mengajukan surat, melihat & mengunduh surat miliknya |
| Dosen | `dosen` | Mengajukan surat tugas, melihat & mengunduh surat miliknya |
| Admin TU | `admin` | Memproses, menyetujui, menolak, dan memfinalisasi surat; mengelola disposisi |
| Kaprodi | `kaprodi` | Menyetujui surat tingkat prodi; melihat arsip fakultas |
| Dekan | `dekan` | Menyetujui surat tingkat fakultas; menerima disposisi |
| Administrator | `administrator` | Super admin: mengelola akun admin/kaprodi/dekan |

> **Penting:** `administrator` **tidak otomatis** memiliki akses ke menu role lain.
> Aksesnya hanya untuk manajemen akun, kecuali secara eksplisit ditambahkan.

---

## 2. Aturan Umum

1. Semua endpoint selain `/login`, `/logout`, `/`, `/up`, `/storage/*`, dan
   `/surat/validasi/{token}` **wajib login** (`auth`).
2. Endpoint dengan prefix role (`/mahasiswa/*`, `/dosen/*`, `/admin/*`,
   `/kaprodi/*`, `/dekan/*`, `/administrator/*`) **wajib** memiliki role yang
   sesuai, diperiksa di sisi server oleh `RoleMiddleware` (alias `role`).
3. Role middleware dapat menerima lebih dari satu role:
   `->middleware('role:admin,kaprodi,dekan')`.
4. Selain role, beberapa endpoint menerapkan **aturan kepemilikan** (ownership):
   user hanya boleh mengakses data miliknya sendiri.
5. Jika user belum login → redirect ke `/login`.
   Jika role tidak sesuai → HTTP **403** dan percobaan dicatat di log
   (`storage/logs/laravel.log`).
6. Menu di UI hanya alat bantu. **Otorisasi tetap dilakukan di server**, bukan
   hanya dengan menyembunyikan tombol.

---

## 3. Matriks Menu per Role

| Menu | mahasiswa | dosen | admin | kaprodi | dekan | administrator |
|------|:---------:|:-----:|:-----:|:-------:|:-----:|:-------------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Notifikasi | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Panduan & Sumber Daya | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Profil / Edit Profil | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Pengajuan Surat | ✅ | ✅ | — | — | — | — |
| Log / Riwayat Surat | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Proses & Finalisasi Surat | — | — | ✅ | — | — | — |
| Persetujuan Kaprodi | — | — | — | ✅ | — | — |
| Persetujuan Dekan | — | — | — | — | ✅ | — |
| Arsip Fakultas | — | — | — | ✅ | — | — |
| Disposisi (surat masuk) | — | ✅ | ✅ | ✅ | ✅ | — |
| Export Excel | — | — | ✅ | — | — | — |
| Manajemen Akun | — | — | — | — | — | ✅ |

> Menu Disposisi hanya muncul untuk role yang terlibat. Admin membuat disposisi,
> Dekan/Dosen/Kaprodi menerima atau meneruskan disposisi.

---

## 4. Matriks Endpoint per Role

### 4.1 Publik (tanpa login)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/` | Redirect ke login |
| GET | `/login` | Form login |
| POST | `/login` | Proses login (LDAP + fallback DB) |
| POST | `/logout` | Logout |
| GET | `/surat/validasi/{token}` | Validasi keaslian surat via QR (publik) |
| GET | `/up` | Health check |

### 4.2 Semua Role (login)

| Method | Endpoint | Aturan tambahan |
|--------|----------|-----------------|
| GET | `/notifikasi` | Hanya notifikasi milik user |
| POST | `/notifikasi/read-all` | Hanya notifikasi milik user |
| GET | `/profile` | Data user sendiri |
| GET | `/profile/edit` | Data user sendiri |
| POST | `/profile/update` | Field yang boleh diubah dibatasi per role |
| GET | `/sumberdaya` | Filter: `role = null` atau `role = role user` |
| GET | `/sumberdaya/download/{id}` | File ber-role hanya untuk role tersebut |
| GET | `/disposisi` | Admin & Dekan: semua; lainnya: hanya yang terkait |
| GET | `/disposisi/{id}` | Admin & Dekan: semua; lainnya: hanya pengirim/penerima |
| POST | `/disposisi/{id}/terima` | Hanya penerima (bukan admin) |
| POST | `/disposisi/{id}/lempar` | Hanya penerima (bukan admin) |
| POST | `/disposisi/{id}/kirim-admin` | Hanya penerima |
| POST | `/disposisi/{id}/upload-file` | Hanya admin penerima |

> Catatan: `/mahasiswa/surat/{id}/pdf` dan `/dosen/surat/{id}/pdf` berada di
> grup `auth` karena dipakai juga oleh staf. Otorisasinya dilakukan di dalam
> handler: **pemilik surat** atau **admin/kaprodi/dekan**.

### 4.3 Mahasiswa (`role:mahasiswa`)

| Method | Endpoint |
|--------|----------|
| GET | `/mahasiswa/dashboard` |
| GET | `/mahasiswa/surat` |
| GET | `/mahasiswa/surat/ajukan/{id}` |
| POST | `/mahasiswa/surat/ajukan/{id}` |
| GET | `/mahasiswa/surat/{id}` (hanya milik sendiri) |
| POST | `/mahasiswa/surat/{id}/batal` (hanya milik sendiri) |
| GET | `/mahasiswa/surat/{id}/pdf` (pemilik atau staf) |

### 4.4 Dosen (`role:dosen`)

| Method | Endpoint |
|--------|----------|
| GET | `/dosen/dashboard` |
| GET | `/dosen/surat` |
| GET | `/dosen/surat/ajukan/{id}` |
| POST | `/dosen/surat/ajukan/{id}` |
| GET | `/dosen/surat/{id}` (hanya milik sendiri) |
| POST | `/dosen/surat/{id}/batal` (hanya milik sendiri) |
| GET | `/dosen/surat/{id}/pdf` (pemilik atau staf) |

### 4.5 Admin TU (`role:admin`)

| Method | Endpoint |
|--------|----------|
| GET | `/admin/dashboard` |
| GET | `/admin/dashboard-dosen` |
| GET | `/admin/surat` |
| GET | `/admin/surat/{id}` |
| POST | `/admin/surat/{id}/setujui` |
| POST | `/admin/surat/{id}/tolak` |
| POST | `/admin/surat/{id}/finalisasi` |
| GET | `/admin/surat/export/excel` |
| GET | `/admin/disposisi` |
| GET | `/admin/disposisi/create` |
| POST | `/admin/disposisi` |
| GET | `/admin/disposisi/export/excel` |
| POST | `/admin/disposisi/{id}/selesai` |

### 4.6 Kaprodi (`role:kaprodi`)

| Method | Endpoint |
|--------|----------|
| GET | `/kaprodi/dashboard` |
| GET | `/kaprodi/surat` |
| GET | `/kaprodi/surat/{id}` |
| POST | `/kaprodi/surat/{id}/setujui` |
| POST | `/kaprodi/surat/{id}/tolak` |
| GET | `/kaprodi/arsip-fakultas` |
| GET | `/kaprodi/arsip-fakultas/{id}` |

### 4.7 Dekan (`role:dekan`)

| Method | Endpoint |
|--------|----------|
| GET | `/dekan/dashboard` |
| GET | `/dekan/dashboard-dosen` |
| GET | `/dekan/surat` |
| GET | `/dekan/surat/{id}` |
| POST | `/dekan/surat/{id}/setujui` |
| POST | `/dekan/surat/{id}/tolak` |

### 4.8 Administrator (`role:administrator`)

| Method | Endpoint |
|--------|----------|
| GET | `/administrator/dashboard` |
| GET | `/administrator/tambah-akun` |
| POST | `/administrator/simpan-akun` |
| DELETE | `/administrator/hapus-akun/{id}` |

---

## 5. Implementasi Middleware

Middleware: `app/Http/Middleware/RoleMiddleware.php`
Alias: `role` (didaftarkan di `bootstrap/app.php`)

Contoh pemakaian:

```php
// Satu role
Route::middleware('role:admin')->group(function () {
    // ...
});

// Beberapa role
Route::middleware('role:admin,kaprodi,dekan')->group(function () {
    // ...
});
```

Perilaku middleware:

| Kondisi | Respons |
|---------|---------|
| Belum login (HTML) | Redirect ke `/login` |
| Belum login (JSON) | `401 Unauthorized` |
| Role tidak sesuai (HTML) | `403 Forbidden` |
| Role tidak sesuai (JSON) | `403 Forbidden` + pesan JSON |
| Role sesuai | Lanjut ke handler |

Setiap penolakan akses dicatat sebagai `warning` pada log aplikasi agar mudah
diaudit.

---

## 6. Aturan Kepemilikan (Ownership)

| Objek | Aturan |
|-------|--------|
| Surat Pengajuan | Mahasiswa/Dosen hanya boleh melihat, membatalkan, dan mengunduh surat miliknya |
| PDF Surat | Pemilik surat atau `admin`/`kaprodi`/`dekan` |
| Disposisi | `admin` & `dekan` boleh melihat semua; role lain hanya disposisi yang dikirim/diterima |
| Sumber Daya | File dengan `role` hanya dapat diunduh role tersebut; `role = null` untuk semua |
| Profil | User hanya dapat mengubah profilnya sendiri; field yang dapat diubah dibatasi per role |

---

## 7. Catatan Pengembangan

- Jangan menambahkan endpoint baru dengan hanya `auth` jika seharusnya khusus
  role tertentu. Gunakan selalu `role:<role>`.
- Jika endpoint harus diakses banyak role, sebutkan semuanya secara eksplisit.
- Untuk endpoint yang memakai aturan kepemilikan, tetap lakukan pengecekan di
  handler (contoh: `$surat->user_id !== Auth::id()`).
- Perubahan aturan hak akses wajib disertai pembaruan dokumen ini dan test
  pada `tests/Feature/HakAksesRoleTest.php`.
