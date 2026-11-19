# SAKTI — Sistem Administrasi dan Kelola Tata Informasi

Aplikasi administrasi surat digital untuk Fakultas Teknologi Informasi.
Mahasiswa/Dosen mengajukan surat online → alur persetujuan bertingkat → PDF + QR code.

## Tech Stack

| Layer        | Teknologi                                       |
| ------------ | ----------------------------------------------- |
| Framework    | Laravel 12 (PHP 8.2+)                           |
| Auth         | LdapRecord-Laravel 3.4 (OpenLDAP) + fallback DB |
| Frontend     | Blade + Tailwind CSS 4 + Vite 7                 |
| PDF          | barryvdh/laravel-dompdf 3.1                     |
| QR Code      | simplesoftwareio/simple-qrcode 4.2              |
| Excel Export | maatwebsite/excel 3.1                           |
| Testing      | PHPUnit 11                                      |
| Database     | Dikonfigurasi via `.env`; test pakai SQLite in-memory |

## Arsitektur & Konvensi

### Routing
- **Hampir semua logic ada di `routes/web.php`** (~2500+ baris, closures).
- Hanya `AuthController` dan `TemplateSuratController` yang controller proper.
- Patuhi pola ini: tambahkan route closure kecuali kompleksitas butuh controller.

### Struktur Direktori

```
app/
├── Exports/           SuratPengajuanExport, DisposisiExport (Excel)
├── Helpers/           NomorSuratHelper, NotificationHelper, StatusHelper, SuratHelper
├── Http/
│   ├── Controllers/   AuthController, TemplateSuratController
│   └── Middleware/    RoleMiddleware, EnforceSessionTimeout
├── Ldap/              AttributeHandler (sync LDAP → DB)
├── Models/            User, SuratPengajuan, JenisSurat, Surat, Disposisi,
│                      Notification, SuratLampiran, SumberDaya, Semester, Prodi
├── Providers/         AppServiceProvider, ViewServiceProvider
└── Support/           MasterData, DateRange, TimeRange

resources/views/
├── layouts/           app.blade.php
├── auth/              login
├── mahasiswa/         dashboard, surat/*
├── dosen/             dashboard, surat/*
├── admin/             dashboard, surat/*, template-surat/*, disposisi/*
├── kaprodi/           dashboard, surat/*, arsip-fakultas
├── dekan/             dashboard, surat/*
├── administrator/     dashboard, tambah-akun
├── pdf/               7 template mahasiswa + 11 dosen + 3 magang
├── preview/           preview PDF templates
├── disposisi/         dashboard, detail
├── components/        combobox, date-field, time-field
├── profile/           index, edit
├── notifikasi/        index
└── sumberdaya/        index

database/
├── migrations/        25 migrasi
└── seeders/           UserSeeder, ProdiSeeder, SemesterSeeder, JenisSuratSeeder

tests/Feature/         10 file test
tests/js/              3 file test JS
docs/                  PRD, hak-akses, progres sprint
```

### Helper Global
Dimuat via `composer.json` `files` autoload:
- `NomorSuratHelper` — format & auto-increment nomor surat
- `NotificationHelper` — `pushNotif()` buat notifikasi DB
- `StatusHelper` — mapping & transisi status pengajuan
- `SuratHelper` — logic bantu surat (penandatangan, dsb.)

## Roles (6)

| Role          | Kode            | Akses                                           |
| ------------- | --------------- | ----------------------------------------------- |
| Mahasiswa     | `mahasiswa`     | Ajukan surat, lihat/unduh milik sendiri          |
| Dosen         | `dosen`         | Ajukan surat tugas, lihat/unduh milik sendiri    |
| Admin TU      | `admin`         | Proses, setujui/tolak, finalisasi, disposisi, template |
| Kaprodi       | `kaprodi`       | Setujui surat prodi, arsip fakultas              |
| Dekan         | `dekan`         | Setujui surat fakultas, terima disposisi         |
| Administrator | `administrator` | Kelola akun admin/kaprodi/dekan saja             |

Middleware: `RoleMiddleware` (alias `role`). Contoh: `->middleware('role:admin,kaprodi')`.

## Model Utama

### SuratPengajuan (surat keluar — diajukan mahasiswa/dosen)
- `user_id` → pemilik
- `jenis_surat_id` → template
- `data` → JSON field values
- `status` → alur persetujuan bertingkat
- `nomor_surat`, `tanggal_ttd`, `qr_token` → diisi saat finalisasi

### JenisSurat (template surat)
- `nama`, `slug`, `kode`
- `target_user` → `mahasiswa` / `dosen`
- `alur` → JSON array urutan approval, misal `['admin', 'kaprodi', 'dekan']`
- `fields` → JSON array nama field untuk form dinamis
- `aktif` → boolean, hanya template aktif yang tampil

### Surat (surat masuk — untuk disposisi)
- File upload surat masuk eksternal
- Terhubung ke `Disposisi`

### Disposisi
- Admin buat dari `Surat` → route ke dekan/dosen/kaprodi
- Penerima bisa: terima (`selesai`), lempar ke user lain, kirim balik ke admin

## Alur Status Pengajuan

```
menunggu
  → admin setujui → menunggu_kaprodi (jika ada di alur)
    → kaprodi setujui → menunggu_dekan (jika ada di alur)
      → dekan setujui → disetujui_final (mahasiswa) / menunggu_admin_final (dosen)
        → admin finalisasi (isi nomor_surat, tanggal_ttd) → disetujui_final

  → ditolak (dengan alasan) di tahap manapun

Khusus: Surat Permohonan Aktif Akademik → otomatis buat child Surat Persetujuan Aktif Akademik
```

## Nomor Surat
Format: `{counter}/FTI/{kode}/{bulan_romawi}/{tahun}` (scope FTI) atau `No.{counter}/{prodi}/{kode}/{bulan}/{tahun}` (scope Prodi).
Counter per `jenis_surat` per tahun, opsional per prodi.

## Keamanan

- **Identitas pengusul dikunci server-side** — `nama`, `npm`, `prodi`, `nidn` overwrite dari `Auth::user()`.
- Login lockout: 5 gagal → kunci 1 menit (`failed_login_attempts`, `locked_until`).
- Session: idle timeout (`session.lifetime`) + absolute timeout (8 jam default).
- Session regeneration on login (anti-fixation).

## Perintah Development

```bash
# Install & setup
composer setup

# Dev server (Laravel + Vite + Queue + Pail)
composer dev

# Test
composer test
# atau
php vendor/bin/phpunit --testdox
```

## Aturan Penting

1. **Jangan tambah endpoint tanpa middleware `role:<role>`** kecuali memang publik.
2. **Ownership check di handler** — cek `$surat->user_id !== Auth::id()` untuk data milik user.
3. **Perubahan hak akses** wajib update `docs/hak-akses.md` + test `HakAksesRoleTest.php`.
4. **Template surat baru** → tambah migration field di `JenisSurat` seeder, buat view di `pdf/` dan `preview/`.
5. **Notifikasi** → pakai `pushNotif()` dari `NotificationHelper`, bukan Laravel built-in Notification.
6. **Form surat** → `JenisSurat.fields` JSON mendefinisikan field, dirender `surat/form-dinamis.blade.php`. Form custom hanya untuk tipe khusus.
7. **PDF** → DomPDF via Blade templates di `resources/views/pdf/`. QR dari `api.qrserver.com`.
8. **Validasi input** → pakai `DateRange`, `TimeRange`, `MasterData` dari `app/Support/`.
9. **Style** → Tailwind CSS 4, komponen custom (combobox, date-field, time-field) di `resources/views/components/`.

## Dokumentasi Terkait

- `docs/PRD_SAKTI_Sprout_Apex.md` — Product Requirements Document
- `docs/hak-akses.md` — aturan hak akses per role (sumber kebenaran)
- `docs/progres-sprint-1.md` — ringkasan Sprint 1
- `docs/progres-sprint-2.md` — ringkasan Sprint 2
- `docs/progres-b-*.md` — progres per backlog item
