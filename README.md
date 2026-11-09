# Proyek SAKTI — Sistem Pengajuan Surat

Aplikasi pengajuan surat Fakultas Teknologi Informasi berbasis Laravel. Pengusul
(mahasiswa/dosen) memilih jenis surat, mengisi form, lalu pengajuan diproses
melalui alur Admin TU, Kaprodi, dan/atau Dekan.

## Fitur Sprint 1

- Login memakai akun kampus (LDAP) dan data pengguna lokal.
- Kontrol menu, halaman, dan endpoint berdasarkan role.
- Pengajuan surat otomatis terikat pada akun pengusul (identitas read-only).
- Antrean pemrosesan surat mahasiswa dan dosen oleh Admin TU.
- Pengelolaan template surat oleh Admin TU (B-15a).
- Pemilihan jenis surat dari daftar template aktif (B-06):
  - Hanya template `aktif` yang tampil dan dapat dipakai.
  - Form dirender otomatis dari `fields` template.
  - Template nonaktif tidak bisa dibuka lewat URL langsung.
  - Identitas pengusul dikunci di server, bukan hanya read-only di form.
  - Endpoint daftar template aktif khusus pengusul: `GET /api/template-surat`
    (mahasiswa/dosen; hanya template aktif sesuai role).

## Kebutuhan

- PHP 8.2+ (diuji pada PHP 8.4)
- Composer
- Node.js & NPM
- Database (konfigurasi pada `.env`; test memakai SQLite in-memory)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Pengujian

Seluruh test:

```bash
php vendor/bin/phpunit --testdox
```

Test khusus B-06:

```bash
php vendor/bin/phpunit tests/Feature/PemilihanTemplateAktifTest.php --testdox
```

## Dokumentasi

- `docs/hak-akses.md` — aturan hak akses per role
- `docs/progres-sprint-1.md` — ringkasan progres Sprint 1
- `docs/progres-b-01.md` — progres kontrol akses berbasis role
- `docs/progres-b-02.md` — progres pengajuan terikat akun pengusul
- `docs/progres-b-06.md` — progres pemilihan template surat aktif

## Struktur Penting

| Bagian | Lokasi |
| --- | --- |
| Route aplikasi | `routes/web.php` |
| Model | `app/Models` |
| Helper | `app/Helpers` |
| Controller | `app/Http/Controllers` |
| View | `resources/views` |
| Migration & seeder | `database` |
| Test | `tests/Feature`, `tests/Unit` |
