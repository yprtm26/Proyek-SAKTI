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

## Fitur Sprint 2

- Penguncian sementara akun setelah gagal login berulang (B-01 B) dengan countdown timer aksesibel.
- Penyaringan formulir surat tugas dosen berdasarkan indeks kegiatan (B-07).
- Dropdown searchable teraksesibel untuk data master prodi, semester, dan dosen (B-08).
- Date picker (`YYYY-MM-DD`) dan time picker 24 jam (`HH:mm`) dengan validasi rentang di server (B-09, B-10 A).
- Pengisian dinamis daftar anggota dan peran dalam kegiatan (B-10 B).
- Manajemen draf pengajuan surat: simpan parsial (`POST /surat/draf`, B-13 A), buka & ubah draf (`GET/POST /{role}/surat/{id}/edit-draf`, B-13 B), serta hapus permanen draf (`DELETE /surat/{id}/draf`, B-14) dengan modal konfirmasi focus trap dan proteksi status.
- Pengurutan antrean surat secara FIFO di dasbor Admin TU (B-19) dengan kolom `Waktu Masuk` (`DD/MM/YYYY HH:mm`) dan indikator prioritas FIFO.
- Verifikasi surat oleh Admin TU (`POST /admin/surat/{id}/verifikasi` menjadi `Diverifikasi TU`) dan pengembalian revisi (`POST /admin/surat/{id}/revisi` menjadi `Perlu Revisi`) dengan modal catatan revisi wajib isi (B-20).
- Daftar riwayat seluruh pengajuan surat pengusul (`GET /riwayat-surat`, `GET /api/riwayat-surat`, `/mahasiswa/surat`, `/dosen/surat`, B-27) dengan nomor, tanggal, badge status kontras >= 4.5:1, dan empty state.
- Pratinjau dokumen surat terformat (`GET /surat/{id}/pratinjau`, B-28) melalui modal Escape-closable (tipografi >= 14px, kontras 21:1) serta halaman 404 yang aman.

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

Seluruh test PHPUnit:

```bash
php artisan test
```

Test unit JavaScript (aksesibilitas komponen & modal):

```bash
node --test tests/js/*.test.mjs
```

Test fitur Sprint 2 (B-14 hingga B-28):

```bash
php artisan test --filter="HapusDrafB14Test|AntreanFifoTuB19Test|VerifikasiRevisiTuB20Test|RiwayatPengusulB27Test|PratinjauSuratB28Test"
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
