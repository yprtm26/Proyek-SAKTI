# Progres B-13 A — Penyimpanan Pengajuan Surat sebagai Draf

**Status:** Selesai (implementasi + pengujian otomatis)
**Papan Trello:** Sprint 2 — Card B-13 A
**User Story:** Sebagai pengguna (Mahasiswa, Dosen, Tendik, atau Admin), saya ingin dapat menyimpan isian formulir pengajuan surat sebagai draf, agar saya dapat melanjutkan proses pengisian di lain waktu tanpa kehilangan data yang sudah dimasukkan jika ada informasi atau dokumen yang belum lengkap di aplikasi Sakti.

---

## 1. Acceptance Criteria (Trello) vs Implementasi

| Kriteria | Implementasi | Status |
| --- | --- | --- |
| Pengusul menekan **Simpan Draf** → data tersimpan dengan status `Draf` | Tombol `Simpan Draf` dengan `formaction="/surat/draf"` + `formnovalidate` di form dinamis dan form dosen; endpoint `POST /surat/draf` menyimpan `SuratPengajuan` dengan `status = 'draf'` | ✅ |
| Draf tidak tampil di antrean verifikasi Admin TU | Scope `SuratPengajuan::bukanDraf()` dipakai di `GET /admin/surat`; detail admin/dekan menolak draf (`abort(404)`); draf tidak memicu notifikasi admin | ✅ |
| Sistem menolak simpan draf jika template belum dipilih | Endpoint memvalidasi `jenis_surat_id` wajib, harus aktif, dan sesuai role pengusul (`JenisSurat::aktif()->untukPengguna()`) | ✅ |

---

## 2. Detail Teknis

### Backend — `routes/web.php`
- Endpoint baru: `POST /surat/draf` (middleware `auth` + `role:mahasiswa,dosen`), nama rute `surat.draf.store`.
- Menerima isian **parsial**: hanya field yang dikenal template (`JenisSurat.fields`) ditampilkan, ditambah field kompatibilitas (`mahasiswa`, `anggota`, `anggota_tim`, `personel`, `label_lampiran`).
- Nilai dibersihkan (`strip_tags`) dan dibatasi panjangnya (maks. 500 karakter per nilai) sebelum disimpan ke JSON `data`.
- Identitas pengusul (`nama`, `nama_dosen`, `nama_ditugaskan`, `npm`, `nidn`, `no_nip`, `prodi`) **ditimpa dari akun login**, bukan dipercaya dari request (lanjutan B-02).
- Response: redirect dengan flash `success` untuk browser, JSON `201` untuk klien API.

### Model — `app/Models/SuratPengajuan.php`
- `scopeBukanDraf()` — filter antrean TU/arsip.
- `scopeDraf()` — filter draf milik pengusul (dipakai B-13 B).
- `isDraf(): bool` — helper pengecekan status.

### Frontend
- `resources/views/surat/form-dinamis.blade.php`
- `resources/views/dosen/surat/form/surat-tugas-kegiatan-kuliah.blade.php`
  - Hidden input `jenis_surat_id` untuk validasi template.
  - Tombol **Simpan Draf** (`formaction="/surat/draf"`, `formnovalidate`) dan **Ajukan Surat** (`action=submit`).
  - Target klik minimal `44px`, focus ring kontras tinggi.

### Perbaikan terkait
- `resources/views/layouts/app.blade.php`: memulihkan variabel `$roleTitle` / `$subTitle` yang rusak akibat rename di commit `ded71fd` (semua halaman auth sebelumnya 500).

---

## 3. Pengujian

Berkas: `tests/Feature/SimpanDrafB13ATest.php` (8 test).

```bash
php artisan test tests/Feature/SimpanDrafB13ATest.php --testdox
```

Skenario yang diuji:
1. Isian parsial tersimpan sebagai `draf`.
2. Tanpa template → validasi gagal (redirect + 422 JSON).
3. Template nonaktif / template role lain → ditolak.
4. Identitas pengusul dikunci di server.
5. Draf tidak muncul di `/admin/surat`, pengajuan `menunggu` tetap muncul.
6. Simpan draf tidak mengirim notifikasi ke admin.
7. Endpoint hanya untuk role `mahasiswa,dosen` (admin → 403).
8. Form merender tombol draf, hidden template, dan `formnovalidate`.

Hasil: **8 passed (29+ assertions)**, dan 19 test `PemilihanTemplateAktifTest` tetap lolos.

---

## 4. Catatan Integrasi

- **B-13 B belum dikerjakan.** Draf tersimpan tetapi belum ada halaman edit / aksi *Kirim Pengajuan* / proteksi IDOR 403. Scope `draf()` sudah disiapkan untuk itu.
- **B-10 B berada di branch `feature/mode-a-session-security`**, belum tergabung ke `main`. Saat di-merge, perhatikan:
  - konflik pada `routes/web.php`, `SuratPengajuan.php`, `form-dinamis.blade.php`, dan test;
  - script validasi personel di `tabel-personel.blade.php` memblokir `submit` dengan baris kosong — perlu dikecualikan untuk tombol **Simpan Draf** (`formnovalidate`) agar draf parsial tetap bisa disimpan;
  - `resources/views/surat/partials/tabel-personel.blade.php` yang ada di working tree `main` saat ini adalah salinan lama dan belum direferensikan view mana pun.
