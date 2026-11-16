# Naskah Presentasi B-06 — 3 Menit

**Nama:** Arman Dava Wicaksono  
**Fitur:** B-06 / US-06 — Pemilihan jenis surat dari daftar template aktif  
**Gaya:** Demo Dulu  
**Durasi:** 3 menit

> Fokus naskah: menjelaskan fitur B-06 yang sudah dikerjakan, bukan seluruh proyek SAKTI.

---

## Naskah Lisan

### 0:00–0:15 — Pembuka + Langsung Demo

**Lakukan:** Buka dashboard mahasiswa. Jangan menampilkan slide judul lebih dari 3 detik.

> “Selamat pagi, Bapak/Ibu. Saya Arman dari tim Sprout Apex. Saya tidak mulai dengan slide; saya langsung buka SAKTI. Perhatikan daftar surat di layar ini.”

### 0:15–0:35 — Untuk Siapa Fitur Ini

**Lakukan:** Arahkan kursor ke kartu-kartu template.

> “Fitur ini bernama Pemilihan Template Aktif. Pengguna utamanya mahasiswa dan dosen sebagai pengusul, yang butuh surat cepat tanpa salah format. Pengguna berikutnya adalah Admin TU, yang perlu memastikan hanya template berlaku yang bisa dipakai.”

### 0:35–0:50 — Tiga Bagian Utama B-06

> “Ada tiga bagian yang saya buat: filter template aktif sesuai role, form dinamis yang mengikuti field template, dan pengamanan akses serta identitas di server.”

### 0:50–2:15 — Demo dan Rumus Fitur

**Lakukan:** Tampilkan rumus di layar kedua atau ucapkan perlahan, lalu kembali ke aplikasi.

> “Rumusnya: fitur **_[nama]_** membantu **_[pengguna]_** untuk **_[tujuan]_** dengan **_[cara kerja]_**, sehingga **_[hasil]_**.”

> “Untuk B-06: **Pemilihan Template Aktif** membantu **mahasiswa dan dosen** mengajukan **surat yang tepat** dengan **hanya menampilkan template aktif sesuai role dan merender form otomatis dari field template**, sehingga **pengajuan lebih cepat, konsisten, dan tidak salah format**.”

**Lakukan:** Tunjukkan dashboard, lalu klik satu template aktif.

> “Di dashboard ini hanya template aktif milik mahasiswa yang tampil. Saya pilih satu. Form ini tidak saya tulis manual; sistem membaca field dari template. Kalau Admin TU menambah field, form ikut berubah tanpa halaman baru. Surat lama yang sudah punya form khusus tetap memakai form lama.”

**Lakukan:** Scroll ke bagian identitas.

> “Nama, NPM, dan prodi terkunci. Saya coba kirim identitas palsu lewat request manual; server tetap menimpa dengan akun yang login.”

**Lakukan:** Buka URL template nonaktif, lalu URL template dosen.

> “Sekarang saya coba buka template nonaktif lewat URL langsung. Sistem menolak, halaman tidak ditemukan. Saya coba template dosen; ditolak juga. Jadi pengecekan terjadi di server, bukan hanya tombol disembunyikan.”

**Lakukan:** Kembali ke dashboard.

> “Saya juga membuat endpoint daftar template aktif. Endpoint ini hanya untuk mahasiswa dan dosen, hanya mengembalikan template aktif sesuai role, dan menolak filter ke role lain. Bukti ujinya: 19 test lulus dengan 109 assertion.”

### 2:15–2:45 — Yang Belum Selesai dan Rencananya

> “Yang belum selesai: form dinamis baru mendukung field teks sederhana. Tabel anggota berulang, unggah lampiran, dan field opsional masih memakai form khusus. Preview dan PDF untuk template baru juga belum ada. Rencana Sprint 2: preview/PDF generik, tipe field lanjutan, lalu versioning agar pengajuan lama tidak rusak saat template diubah.”

### 2:45–3:00 — Pertanyaan untuk Klien

**Lakukan:** Berhenti di dashboard dan tatap klien.

> “Sebelum saya tutup, saya perlu keputusan Bapak/Ibu. Dari template yang aktif, tiga mana yang paling sering dipakai? Kami akan prioritaskan preview dan PDF-nya. Dan kalau template dinonaktifkan, apakah pengajuan lama harus tetap bisa dibuka dan diunduh? Terima kasih.”

---

## Peta Kesepakatan B-06

| Kesepakatan B-06 | Ditunjukkan di naskah |
|---|---|
| Hanya template aktif yang tampil dan dapat dipakai | Dashboard hanya menampilkan template aktif; demo memilih template aktif |
| Template nonaktif tidak bisa dibuka lewat URL langsung | Demo membuka URL template nonaktif → halaman tidak ditemukan |
| Form dirender otomatis dari `fields` template | Demo menunjukkan form yang mengikuti field template |
| Identitas pengusul dikunci di server | Demo mengirim identitas palsu, tetapi server menimpa dengan akun login |
| Endpoint daftar template aktif khusus pengusul | Disebutkan sebagai fitur pendukung; hanya mahasiswa/dosen, hanya template aktif sesuai role |
| Form khusus yang sudah ada tetap dipakai | Disebutkan bahwa surat lama tetap memakai form lama |

---

## Fitur yang Dijelaskan

| Fitur | Apa yang dilakukan | Manfaat |
|---|---|---|
| Filter template aktif per role | Dashboard dan route hanya memakai template `aktif` dan `target_user` sesuai role | Pengusul tidak salah pilih format |
| Form dinamis | Membaca array `fields` dari template dan merender input otomatis | Template baru bisa dipakai tanpa membuat Blade baru |
| Proteksi akses | GET/POST mengecek ulang template aktif dan role | Template nonaktif/role lain tidak bisa dipakai lewat URL |
| Penguncian identitas | `nama`, `npm`/`no_nip`, dan `prodi` ditimpa dari `Auth::user()` | Identitas tidak bisa dipalsukan |
| API daftar template | `GET /api/template-surat` dengan middleware role dan filter role | Frontend bisa mengambil daftar template aktif |
| Helper label dan tipe input | `labelFieldSurat()` dan `tipeFieldSurat()` | Form lebih rapi dan mudah diisi |

---

## Persiapan Demo

- [ ] Login sebagai mahasiswa uji yang nama, NPM, dan prodi-nya terisi.
- [ ] Buat satu template dinamis aktif lewat Admin TU, misalnya “Surat Keterangan Bebas Laboratorium” dengan field `nama_laboratorium`, `tujuan_penggunaan`, `tanggal_pengambilan`.
- [ ] Pastikan template tersebut belum punya view khusus agar form dinamis benar-benar tampil.
- [ ] Siapkan satu template nonaktif milik mahasiswa dan catat URL-nya.
- [ ] Catat URL satu template milik dosen.
- [ ] Siapkan tab: dashboard, form dinamis, URL template nonaktif, URL template dosen.
- [ ] Rekam layar 60–90 detik sebagai plan B.
- [ ] Simpan screenshot halaman penolakan akses dan output `OK (19 tests, 109 assertions)`.
- [ ] Siapkan timer 3 menit.

---

## Plan B Kalau Demo Gagal

> “Saya siapkan rekaman singkat agar kita tidak kehilangan waktu. Saya putar 60 detik bagian utamanya, lalu kita lanjut ke bukti uji dan rencana.”

Setelah rekaman, tunjukkan:

- Screenshot template aktif vs nonaktif.
- Screenshot halaman yang menolak akses.
- Output test `OK (19 tests, 109 assertions)`.

---

## Ringkasan Waktu

| Waktu | Bagian |
|---|---|
| 0:00–0:15 | Pembuka + langsung demo |
| 0:15–0:35 | Untuk siapa fitur ini |
| 0:35–0:50 | Tiga bagian utama B-06 |
| 0:50–2:15 | Demo + rumus fitur + API |
| 2:15–2:45 | Yang belum selesai + rencana |
| 2:45–3:00 | Pertanyaan untuk klien |

---

## Catatan Penting

- B-06 adalah **sisi pengusul**: memilih dan memakai template aktif.
- B-15a adalah **sisi Admin TU**: membuat dan mengelola template.
- B-02 adalah **ikatan akun**: pengajuan terikat ke pengguna; B-06 memperluas penguncian identitas ke form dinamis.
- B-01 adalah **hak akses role**; B-06 memakai aturan role tersebut di halaman dan API.
