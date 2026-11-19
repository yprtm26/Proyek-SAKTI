# Progres B-10 A — Pemilihan Waktu Mulai dan Selesai Kegiatan (Time Picker)

**Status:** Selesai (100% — Siap dipindahkan ke Review/Done)  
**Papan Trello:** Sprint 2 — Card B-10 A  
**Referensi:** `docs/progres-sprint-2.md`  
**Tanggal:** 19 November 2026

---

## 1. Ringkasan Fitur

B-10 A menyediakan **komponen time picker aksesibel** untuk menentukan waktu mulai dan waktu selesai kegiatan dalam format 24 jam (`HH:mm`).

Fitur utama:
- **Keyboard Auto-Masking:** Pengguna dapat mengetik angka langsung dan titik dua (`:`) otomatis ditambahkan. Backspace didukung tanpa tersangkut.
- **Visual Time Popover:** Menyediakan kolom pemilihan jam (00–23) dan menit (interval 5 menit) serta tombol "Sekarang", dengan target sentuh $\ge 44 \times 44\text{px}$ dan navigasi keyboard (`Escape` menutup dialog dan mengembalikan fokus).
- **Validasi Server 24 Jam:** Memastikan format waktu `HH:mm` (00:00–23:59), mendukung pemisah titik (`08.00` dinormalisasi menjadi `08:00`), dan menolak jam/menit di luar rentang dengan HTTP 422.
- **Validasi Urutan Waktu:** Pada hari yang sama (tanggal selesai = tanggal mulai atau satu tanggal kegiatan), sistem menolak pengajuan jika waktu selesai $\le$ waktu mulai dengan pesan: *"Waktu selesai tidak boleh lebih awal atau sama dengan waktu mulai pada tanggal yang sama."*

---

## 2. Acceptance Criteria

| # | Kriteria | Status | Bukti |
|---|---|:---:|---|
| AC-1 | Pengusul dapat memilih waktu mulai dan selesai kegiatan dengan format 24 jam (`HH:mm`) | ✅ | `x-time-field`, `public/js/timepicker.js`; form dinamis dosen & mahasiswa |
| AC-2 | Input time picker mendukung ketik langsung angka dari keyboard dengan auto-format titik dua (`:`) | ✅ | Fungsi `applyTimeMask()` pada `public/js/timepicker.js` + `tests/js/timepicker.test.mjs` |
| AC-3 | Sistem menolak submit jika waktu selesai lebih awal daripada atau sama dengan waktu mulai pada tanggal yang sama | ✅ | `App\Support\TimeRange::validateOrder()`, `tests/Feature/TimeRangeValidationTest.php` (422) |

---

## 3. Arsitektur Berkas

```text
app/Support/TimeRange.php                        <- aturan format 24 jam, normalisasi titik, dan urutan waktu
app/Helpers/SuratHelper.php                      <- kamus label waktu & resolusi tipe input 'time'
routes/web.php                                   <- validasi server form dinamis dosen & mahasiswa
resources/views/components/time-field.blade.php  <- komponen Blade time picker reusable
public/js/timepicker.js                          <- logika masking, parser, pembanding waktu, dan popover
public/css/timepicker.css                        <- gaya visual kontras WCAG AA & target klik 44px
resources/views/layouts/app.blade.php            <- pemuatan stylesheet dan skrip timepicker
resources/views/surat/form-dinamis.blade.php     <- integrasi field bertipe 'time'
tests/js/timepicker.test.mjs                     <- unit test JavaScript (11 pengujian)
tests/Feature/TimeRangeValidationTest.php        <- feature test validasi backend (6 pengujian)
```

---

## 4. Hasil Pengujian

### A. Pengujian Unit JavaScript (`node --test`)
```text
node --test tests/js/timepicker.test.mjs
✔ parseTime menerima format 24 jam HH:mm yang valid (1.7694ms)
✔ parseTime menolak nilai dan format waktu yang tidak valid (0.1342ms)
✔ formatTime menghasilkan string 24 jam dua digit berpaduan HH:mm (0.1444ms)
✔ compareTimes membandingkan urutan kronologis waktu (0.0894ms)
✔ applyTimeMask auto-format titik dua saat mengetik angka (0.1804ms)
✔ applyTimeMask mendukung penghapusan backspace tanpa stuck pada titik dua (0.0917ms)
✔ validateTimeRange menolak input waktu kosong atau tidak lengkap (0.125ms)
✔ validateTimeRange menolak format waktu salah (0.0647ms)
✔ validateTimeRange menolak waktu selesai <= waktu mulai pada hari yang sama (0.1364ms)
✔ validateTimeRange menerima waktu selesai > waktu mulai pada hari yang sama (0.1339ms)
✔ validateTimeRange mengizinkan waktu lintas hari jika tanggal berbeda (0.0755ms)
ℹ tests 11
ℹ pass 11
ℹ fail 0
```

### B. Pengujian Feature Laravel (`php artisan test`)
```text
php artisan test --filter=TimeRangeValidationTest
PASS  Tests\Feature\TimeRangeValidationTest
✓ form menolak format waktu tidak valid
✓ form menolak waktu selesai lebih awal pada hari yang sama
✓ form menolak waktu selesai sama dengan waktu mulai pada hari yang sama
✓ form menerima waktu selesai lebih akhir pada hari yang sama
✓ form menerima waktu lintas hari jika tanggal selesai berbeda
✓ form dinamis merender komponen time picker untuk field waktu

Tests: 6 passed (26 assertions)
```
