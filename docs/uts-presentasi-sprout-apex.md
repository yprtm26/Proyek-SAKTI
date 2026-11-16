# Naskah Presentasi UTS — Proyek SAKTI (Tim Sprout Apex)

| Item | Keterangan |
| --- | --- |
| Mata kuliah | Trek Peminatan Pengembangan Aplikasi Multiplatform (Trek PAM) |
| Proyek | SAKTI — Sistem Administrasi Surat Akademik Terintegrasi |
| Klien | Fakultas Teknologi Informasi Universitas YARSI (Tata Usaha/Sekretariat Fakultas) |
| Tim | Sprout Apex |
| Anggota | Arman Dava Wicaksono (1402024010), Yuga Pratama Andika Syaputra (1402024061), Josua Rajak (1402024025) |
| Presenter naskah ini | Arman Dava Wicaksono |
| Fitur yang dipresentasikan | B-06 / US-06 — Pemilihan jenis surat dari daftar template aktif |
| Durasi | 3 menit |
| Gaya presentasi | **Demo Dulu** |

> Cara pakai dokumen: Bagian 1–2 untuk kesepakatan kelompok. Bagian 3–6 adalah naskah yang diucapkan. Bagian 7–8 untuk latihan tanya-jawab klien. Bagian 9 untuk Uji Naskah di Google Docs.
>
> Sapaan “Ibu” di naskah bisa diganti “Bapak/Ibu” sesuai klien yang hadir.

---

## 1. Pembagian Fitur Kelompok

Setiap anggota memilih satu fitur yang berbeda. Pembagian ini mengikuti kontribusi aktual di repository dan dokumen progres sprint.

| Anggota | NPM | Fitur yang dipresentasikan | User story | Bukti kontribusi |
| --- | --- | --- | --- | --- |
| **Arman Dava Wicaksono** | 1402024010 | **B-06 — Pemilihan jenis surat dari daftar template aktif** | US-06 | `TemplateSuratController`, `form-dinamis.blade.php`, `PemilihanTemplateAktifTest.php`, commit `9708779` |
| **Yuga Pratama Andika Syaputra** | 1402024061 | **B-01 A — Kontrol tampilan menu dan data sesuai role** | US-01 A | `RoleMiddleware`, `HakAksesRoleTest.php`, `docs/hak-akses.md`, commit `4463d11` |
| **Josua Rajak** | 1402024025 | **B-15a — Pembuatan dan pengelolaan template surat oleh Admin TU** | US-15 A–C | Form create/edit template Admin TU, route `/admin/template-surat`, commit `be67384` |

**Catatan pembagian:**

- Tidak ada fitur yang sama. B-06 fokus pada **sisi pengusul** (memilih template aktif), B-15a fokus pada **sisi Admin TU** (membuat dan mengelola template), B-01 fokus pada **pagar akses lintas role**.
- Fitur B-02 (pengajuan terikat akun) dan B-05 (antrean Admin TU) tidak masuk sesi 3 menit karena keterbatasan waktu, tetapi tetap tercatat di backlog dan dokumentasi sprint.
- Naskah 3 menit di bawah adalah bagian Arman. Yuga dan Josua dapat memakai kerangka yang sama dengan mengganti fitur, demo, dan bukti ujinya.

---

## 2. Gaya Presentasi: Demo Dulu

**Pilihan: Demo Dulu.** Presentasi dibuka dengan aplikasi yang benar-benar berjalan, bukan dengan slide atau penjelasan konsep.

### Alasan memilih Demo Dulu

1. **Fitur B-06 bersifat visual dan interaktif.** Perubahan paling meyakinkan terlihat saat klien menyaksikan sendiri template aktif muncul, form berubah mengikuti field template, dan identitas terkunci.
2. **Klien lebih cepat percaya pada bukti daripada janji.** Satu alur demo 60 detik lebih kuat daripada tiga slide yang menjelaskan “sistem akan menampilkan template aktif”.
3. **Waktu hanya 3 menit.** Demo memangkas waktu menjelaskan konsep. Satu kali klik dan satu kali percobaan akses URL langsung sudah membuktikan dua hal sekaligus: fitur berjalan dan pagar keamanannya aktif.
4. **Audiens utama bukan programmer.** Admin TU dan dosen tidak perlu memahami istilah controller atau middleware; mereka perlu melihat pekerjaan yang lebih cepat dan lebih aman.
5. **Risiko demo gagal bisa dikendalikan.** Saya menyiapkan rekaman layar 60–90 detik dan tangkapan layar hasil uji sebagai cadangan.

### Mengapa bukan dua gaya lainnya

| Gaya | Kenapa tidak dipilih untuk sesi ini |
| --- | --- |
| Storytelling | Butuh waktu untuk membangun konteks dan tokoh. Dalam 3 menit, waktu habis sebelum bagian demo yang paling meyakinkan. |
| BLUF | Bagus untuk rapat pengambilan keputusan, tetapi kurang menunjukkan bukti visual. BLUF tetap dipakai di **penutup**: satu kalimat kesimpulan dan satu permintaan keputusan. |

**Pola akhir yang dipakai:** Demo Dulu di pembuka → rumus fitur sebagai jembatan → batas dan rencana → BLUF di penutup (kesimpulan + pertanyaan keputusan).

---

## 3. Naskah 3 Menit

### 3.1 Persiapan sebelum tampil (wajib beres 15 menit sebelum sesi)

**Data demo:**

- [ ] Siapkan akun mahasiswa uji yang sudah login. Pastikan nama, NPM, dan prodi terisi.
- [ ] Buat satu template dinamis baru melalui akun Admin TU, misalnya **“Surat Keterangan Bebas Laboratorium”** dengan field `nama_laboratorium`, `tujuan_penggunaan`, dan `tanggal_pengambilan`. Pastikan template **aktif**, target **mahasiswa**, dan belum punya view khusus agar form dinamis benar-benar terpakai.
- [ ] Siapkan satu template **nonaktif** milik mahasiswa. Catat URL-nya untuk demo penolakan akses.
- [ ] Catat URL satu template milik **dosen** untuk demo penolakan lintas role.
- [ ] Pastikan template yang dipakai demo tidak memiliki view khusus. Kalau punya, form dinamis tidak akan tampil.

**Tab yang disiapkan:**

1. Dashboard mahasiswa.
2. Form template dinamis yang aktif.
3. URL template nonaktif.
4. URL template dosen.
5. (Opsional) Halaman Admin TU yang menunjukkan status aktif/nonaktif.

**Teknis:**

- [ ] Zoom browser 110–125%, tutup bookmark bar dan notifikasi desktop.
- [ ] Rekam layar 60–90 detik sebagai plan B.
- [ ] Simpan tangkapan layar halaman 404/penolakan akses dan output test `19 tests, 109 assertions`.
- [ ] Siapkan stopwatch atau timer 3 menit yang terlihat oleh presenter, bukan oleh klien.

### 3.2 Naskah lisan

#### 0:00–0:15 — Pembuka + langsung buka aplikasi

**Lakukan:** Buka dashboard mahasiswa. Jangan tampilkan slide judul lebih dari 3 detik.

> “Selamat pagi, Ibu. Saya Arman dari tim Sprout Apex. Hari ini saya tidak mulai dengan slide. Saya akan langsung membuka SAKTI dan menunjukkan satu hal: bagaimana Ibu bisa memastikan mahasiswa hanya memakai surat yang benar-benar aktif. Perhatikan layar ini.”

#### 0:15–0:40 — Untuk siapa fitur ini

**Lakukan:** Arahkan kursor ke daftar kartu template. Sorot satu kartu, lalu ke kolom pencarian.

> “Fitur ini bernama Pemilihan Template Aktif. Pengguna utamanya mahasiswa dan dosen sebagai pengusul. Mereka butuh surat yang cepat dan tidak salah pilih format. Pengguna keduanya Admin TU: Ibu perlu memastikan hanya format yang masih berlaku yang bisa dipakai. Jadi ini bukan sekadar tampilan, tapi pagar pertama agar surat yang keluar konsisten.”

#### 0:40–2:10 — Demo dengan rumus kalimat fitur

**Lakukan:** Tampilkan rumus di layar kedua atau ucapkan perlahan, lalu kembali ke aplikasi.

> “Rumus kalimat fitur kami: fitur **[nama]** membantu **[pengguna]** untuk **[tujuan]** dengan **[cara kerja]**, sehingga **[hasil]**.”

> “Untuk fitur ini: **Pemilihan Template Aktif** membantu **mahasiswa dan dosen** untuk **mengajukan surat yang tepat**, dengan **hanya menampilkan template aktif sesuai role dan merender form otomatis dari field template**, sehingga **pengajuan lebih cepat, konsisten, dan tidak salah format**.”

**Lakukan:** Klik salah satu template aktif, misalnya “Surat Keterangan Bebas Laboratorium”.

> “Saya pilih satu. Form ini tidak saya tulis manual. Sistem membaca daftar field dari template. Kalau Admin TU menambah field, form ikut berubah tanpa membuat halaman baru.”

**Lakukan:** Scroll ke bagian identitas pengusul.

> “Nama, NPM, dan prodi terkunci. Saya tidak bisa mengubahnya dari browser; server akan menimpa dengan akun yang sedang login. Jadi identitas tidak bisa dipalsukan.”

**Lakukan:** Buka tab URL template nonaktif, tekan Enter.

> “Sekarang saya coba akal-akalan: membuka template nonaktif lewat URL langsung. Sistem menolak, halaman tidak ditemukan. Jadi bukan hanya tombolnya yang disembunyikan.”

**Lakukan:** Buka tab URL template dosen.

> “Terakhir, saya coba template milik dosen. Sistem juga menolak. Artinya pembatasan role terjadi di server, bukan hanya di tampilan. Bukti ujinya, 19 test lulus dengan 109 assertion.”

#### 2:10–2:40 — Yang belum selesai dan rencananya

**Lakukan:** Kembali ke dashboard. Bicara tanpa defensif; ini bagian menunjukkan kejujuran dan perencanaan.

> “Saya sampaikan yang belum selesai secara terbuka. Pertama, template baru belum punya preview dan PDF otomatis; halaman detail masih menampilkan pesan preview belum tersedia. Kedua, form dinamis baru mendukung satu nilai teks per field; tabel anggota berulang dan lampiran khusus masih memakai form khusus. Rencana Sprint 2: preview dan PDF generik, tipe field lanjutan, lalu versioning template agar pengajuan lama tidak rusak saat template diubah.”

#### 2:40–3:00 — Pertanyaan untuk klien

**Lakukan:** Berhenti di dashboard. Tatap klien, bukan layar.

> “Sebelum saya tutup, saya perlu keputusan Ibu. Dari template yang aktif hari ini, tiga mana yang paling sering dipakai? Kami akan prioritaskan preview dan PDF-nya. Dan kalau sebuah template dinonaktifkan, apakah pengajuan lama dengan template itu harus tetap bisa dibuka dan diunduh? Terima kasih.”

### 3.3 Ringkasan waktu

| Waktu | Bagian | Tujuan |
| --- | --- | --- |
| 0:00–0:15 | Pembuka + demo langsung | Memancing perhatian, menunjukkan bukti |
| 0:15–0:40 | Untuk siapa | Menjelaskan pengguna dan nilai |
| 0:40–2:10 | Demo + rumus fitur | Membuktikan fitur bekerja dan aman |
| 2:10–2:40 | Belum selesai + rencana | Menunjukkan kejujuran dan arah sprint |
| 2:40–3:00 | Pertanyaan klien | Meminta keputusan, bukan sekadar tepuk tangan |

### 3.4 Plan B kalau demo gagal

Jangan panik dan jangan mengulang klik berkali-kali. Ucapkan:

> “Saya siapkan rekaman singkat agar kita tidak kehilangan waktu. Saya putar 60 detik bagian utamanya, lalu kita lanjut ke bukti uji dan rencana.”

Setelah rekaman, langsung tunjukkan:

- Tangkapan layar template aktif vs nonaktif.
- Tangkapan layar halaman yang menolak akses.
- Output test `OK (19 tests, 109 assertions)`.

Kalimat transisi:

> “Yang penting bukan animasi di layar, Bu, tetapi buktinya: template nonaktif tidak bisa dipakai, dan identitas pengusul tidak bisa dipalsukan.”

---

## 4. Rumus Kalimat Fitur

**Rumus:**

> Fitur **[nama fitur]** membantu **[pengguna]** untuk **[tujuan]** dengan **[cara kerja]**, sehingga **[hasil/manfaat]**.

**Versi fitur B-06:**

> Fitur **Pemilihan Template Aktif** membantu **mahasiswa dan dosen** untuk **mengajukan surat yang tepat** dengan **hanya menampilkan template aktif sesuai role dan merender form otomatis dari field template**, sehingga **pengajuan lebih cepat, konsisten, dan tidak salah format**.

**Versi singkat untuk slide:**

> “Hanya template aktif yang bisa dipilih, form mengikuti template, identitas dikunci dari akun.”

**Versi untuk Admin TU:**

> “Fitur **Pemilihan Template Aktif** membantu **Admin TU** untuk **menjaga hanya format surat yang berlaku yang dipakai pengusul** dengan **menonaktifkan template lama dan mengaktifkan template baru**, sehingga **surat yang keluar konsisten dan tidak memakai format kedaluwarsa**.”

---

## 5. Yang Belum Selesai dan Rencananya

| Prioritas | Yang belum selesai | Dampak bagi pengguna | Rencana | Target |
| --- | --- | --- | --- | --- |
| P1 | Preview dan PDF otomatis untuk template baru | Pengusul belum bisa melihat hasil akhir sebelum mengunduh; halaman detail masih menampilkan “Preview surat belum tersedia” | Buat preview generik dari `fields` template, lalu render PDF generik | Sprint 2 |
| P1 | Field lanjutan: tabel anggota berulang dan unggah lampiran khusus | Template baru masih terbatas pada satu nilai teks per field; kasus tim/kelompok memakai form khusus | Tambah tipe field `repeatable_table` dan `file` pada form dinamis | Sprint 2 |
| P2 | Versioning dan audit perubahan template | Jika template diubah, tampilan pengajuan lama berisiko berubah; belum ada jejak siapa mengubah apa | Simpan versi template dan snapshot field pada pengajuan | Sprint 2–3 |
| P2 | Uji usability bersama 2 Admin TU | Alur create/edit template belum diuji dengan pengguna nyata | Sesi uji 30 menit, catat 3 hambatan utama, perbaiki | Sebelum Sprint Review |

**Kalimat jujur untuk presentasi:**

> “Yang sudah selesai adalah alur inti: template aktif tampil, form dinamis berjalan, identitas terkunci, dan akses nonaktif ditolak. Yang belum selesai adalah pengalaman setelah pengajuan: preview, PDF, dan field lanjutan. Itu masuk Sprint 2, bukan saya klaim selesai hari ini.”

---

## 6. Pertanyaan untuk Klien

**Pertanyaan utama (wajib ditanyakan):**

> “Dari template yang aktif hari ini, tiga mana yang paling sering dipakai? Kami akan prioritaskan preview dan PDF-nya.”

**Pertanyaan kedua:**

> “Kalau sebuah template dinonaktifkan, apakah pengajuan lama dengan template itu harus tetap bisa dibuka dan diunduh?”

**Pertanyaan cadangan (kalau waktu tersisa):**

> “Untuk template baru, Ibu lebih butuh unggah lampiran khusus atau cukup isian teks? Ini menentukan urutan pekerjaan kami di Sprint 2.”

**Keputusan yang diminta di akhir:**

> “Boleh saya catat: prioritas preview/PDF untuk tiga template teratas, dan pengajuan lama tetap bisa diakses setelah template dinonaktifkan?”

Tujuan pertanyaan bukan mencari jawaban “bagus/tidak bagus”, tetapi mendapatkan **prioritas** dan **batas perilaku** yang bisa langsung masuk backlog.

---

## 7. Kartu Klien — Kalimat Siap Pakai

Gunakan pola lima langkah ini untuk semua kartu:

1. **Dengar** sampai klien selesai.
2. **Akui** perasaan atau dampaknya, bukan langsung membela diri.
3. **Klarifikasi** satu pertanyaan supaya masalahnya spesifik.
4. **Jawab** dengan fakta, batas, atau opsi.
5. **Komitmen** pada satu tindakan dan satu waktu tindak lanjut.

### 7.1 Kartu Kritik

**Klien bilang:**

> “Fitur ini malah bikin ribet. Saya harus klik template dulu, belum tentu benar.”

**Kalimat siap pakai:**

> “Terima kasih, Bu. Saya paham kritiknya: alurnya terasa lebih panjang dari cara lama. Supaya saya perbaiki dengan tepat, bagian mana yang paling mengganggu—memilih template, mengisi form, atau memastikan suratnya benar? … Betul, saat ini ada satu layar tambahan. Saya tidak akan menghapus validasinya, tetapi saya bisa membuat template yang paling sering dipakai muncul pertama dan mengingat pilihan terakhir. Apakah itu cukup mengurangi rasa ribetnya? Saya catat sebagai perbaikan prioritas dan saya tunjukkan prototipenya di Sprint 2.”

**Jangan:** “Tidak mungkin, Bu, sistemnya sudah benar.” atau “Itu memang harus begitu.”

**Tindak lanjut:** Catat bagian yang paling mengganggu, ukur jumlah klik, tawarkan satu perbaikan konkret.

### 7.2 Kartu Permintaan Fitur Baru

**Klien bilang:**

> “Bisa tambahkan tombol kirim ke WhatsApp? Atau tanda tangan otomatis?”

**Kalimat siap pakai:**

> “Terima kasih, Bu, idenya bagus. Saya catat. Sebelum saya bawa ke tim, saya ingin memastikan masalah yang ingin diselesaikan: apakah Ibu ingin pengusul tahu status tanpa membuka aplikasi, atau ingin mengurangi chat manual ke TU? … Kalau tujuannya notifikasi status, solusinya belum tentu WhatsApp; bisa email atau badge di dashboard. Saya bawa dua opsi ini ke sprint planning lengkap dengan estimasi dan risikonya, lalu saya kabari keputusannya paling lambat [tanggal]. Apakah boleh saya prioritaskan notifikasi status dulu?”

**Jangan:** langsung berkata “bisa” tanpa tahu biaya, atau “tidak bisa” tanpa menjelaskan alternatif.

**Tindak lanjut:** Tulis di backlog sebagai usulan, beri label `requested-by-client`, bawa ke sprint planning, beri keputusan tertulis.

### 7.3 Kartu Pertanyaan Teknis

**Klien bilang:**

> “Apakah data ini aman? Kalau orang tahu URL-nya, bisa buka surat orang lain?”

**Kalimat siap pakai:**

> “Pertanyaan yang tepat, Bu. Ada tiga lapis pengaman. Pertama, semua halaman wajib login. Kedua, role dicek di server, bukan hanya menu disembunyikan; pengusul hanya bisa membuka template role-nya. Ketiga, template nonaktif dan surat milik orang lain ditolak saat diakses lewat URL langsung—halaman tidak ditemukan atau akses ditolak. Identitas pengusul juga dikunci dari akun yang login, jadi tidak bisa diubah dari browser. Kami mengujinya dengan 19 test; semuanya lulus. Kalau Ibu mau, saya tunjukkan log penolakan aksesnya.”

**Jangan:** menjawab “aman” tanpa bukti, atau menjelaskan istilah `middleware`, `route`, dan `token` tanpa analogi.

**Tindak lanjut:** Tunjukkan bukti uji dan log. Jika klien ingin detail, tawarkan sesi teknis terpisah.

### 7.4 Kartu Menagih

**Klien bilang:**

> “Katanya minggu lalu sudah selesai. Kok sekarang belum bisa dipakai?”

**Kalimat siap pakai:**

> “Terima kasih sudah menagih, Bu; saya memang harus lebih rajin memberi kabar. Status jujurnya: [bagian A] sudah selesai dan hari ini bisa saya demokan. [Bagian B] belum, karena [alasan konkret, misalnya preview PDF untuk template baru belum ada]. Saya tidak mau memberi janji yang belum pasti. Usulan saya: hari ini Ibu menerima [A] plus bukti ujinya, dan [B] saya kejar selesai [tanggal] dengan update setiap [hari]. Apakah tanggal itu masih membantu Ibu, atau ada batas lain yang harus saya kejar?”

**Jangan:** menyalahkan anggota lain, menyebut “hampir selesai” tanpa tanggal, atau menghilang setelah janji gagal.

**Tindak lanjut:** Kirim status tertulis: selesai / sedang dikerjakan / belum mulai, plus tanggal dan bukti.

### 7.5 Bonus: Kalau Kita yang Menagih Klien

**Situasi:** klien belum memberi keputusan atau umpan balik, padahal sprint terhambat.

**Kalimat siap pakai:**

> “Bu, kami perlu keputusan Ibu untuk [hal] sebelum [tanggal] agar Sprint 2 tidak tertunda. Saya kirim ringkasan tiga poin: [A], [B], [C]. Cukup Ibu pilih A atau B, atau beri catatan singkat. Kalau belum sempat, boleh saya anggap [A] sebagai default sementara dan saya perbaiki di sprint berikutnya?”

---

## 8. Jawaban Latihan “Klien Bilang…”

Tabel ini adalah versi cepat untuk latihan berpasangan. Pilih satu kalimat, ucapkan tanpa membaca, lalu minta teman menilai apakah jawabannya jelas, sopan, dan punya tindak lanjut.

| No | Klien bilang… | Jawaban siap pakai | Jenis kartu |
| --- | --- | --- | --- |
| 1 | “Ini bukan yang saya minta.” | “Betul, ada perbedaan. Yang saya tangkap dari pertemuan [tanggal] adalah [kebutuhan]. Saya konfirmasi: prioritas Ibu [A] atau [B]? Saya sesuaikan dan kirim ulang hari ini.” | Kritik |
| 2 | “Saya tidak punya waktu mengisi form panjang.” | “Saya paham. Saya bisa bantu dengan menyimpan draf dan mengisi ulang data yang sama. Bagian mana yang paling makan waktu—memilih template atau mengisi field?” | Kritik |
| 3 | “Tambah fitur ini, sedikit saja.” | “Bisa saya catat, Bu. Supaya tidak menggeser fitur yang sudah dijanjikan, saya perlu tahu dampaknya. Saya bawa ke sprint planning dan kabari keputusannya paling lambat [tanggal].” | Permintaan fitur baru |
| 4 | “Kapan selesai? Sudah telat.” | “Maaf, Bu. Statusnya [A] selesai, [B] belum karena [alasan]. Hari ini saya serahkan [A] plus bukti uji; [B] saya kejar [tanggal] dengan update setiap [hari]. Apakah tanggal itu masih membantu?” | Menagih |
| 5 | “Apakah data saya aman?” | “Aman dalam batas yang kami uji, Bu: wajib login, role dicek di server, template nonaktif ditolak lewat URL langsung, dan identitas dikunci dari akun. Kami punya 19 test untuk alur ini. Saya bisa tunjukkan buktinya.” | Pertanyaan teknis |
| 6 | “Jelaskan tanpa istilah teknis.” | “Baik, Bu. Analoginya seperti pintu berlapis: pintu pertama memastikan orang sudah punya kunci, pintu kedua memastikan kuncinya hanya membuka ruangan yang boleh. Jadi menyembunyikan tombol saja tidak cukup; pengecekan tetap terjadi di pintu.” | Pertanyaan teknis |
| 7 | “Fitur ini tidak berguna.” | “Terima kasih, Bu. Boleh saya tahu bagian mana yang tidak menjawab kebutuhan? Apakah karena [asumsi]? Saya akan cek data pemakaian dan kembali dengan usulan, bukan langsung membela fitur.” | Kritik |
| 8 | “Saya mau lihat sekarang, tanpa janji.” | “Siap, saya buka sekarang. Saya tunjukkan tiga hal: template aktif, form otomatis, dan penolakan akses. Setelah itu saya minta keputusan Ibu pada bagian [X].” | Menagih / demo |
| 9 | “Jangan ubah yang sudah jalan.” | “Baik, saya jaga. Perubahan saya batasi pada [bagian], dan saya siapkan uji regresi agar alur lama tetap berjalan. Saya tunjukkan hasil uji sebelum meminta Ibu mencoba.” | Pertanyaan teknis |
| 10 | “Saya belum sempat memberi jawaban.” | “Tidak masalah, Bu. Supaya tidak menahan pekerjaan, boleh saya anggap [A] sebagai default sementara sampai [tanggal]? Kalau Ibu ingin B, cukup balas sebelum tanggal itu.” | Menagih (kita ke klien) |

**Cara berlatih (10 menit):**

1. Teman membacakan satu kalimat secara acak.
2. Presenter menjawab maksimal 30 detik tanpa membaca.
3. Teman menilai tiga hal: **sopan**, **jelas**, **ada tindak lanjut**.
4. Ulangi untuk 4 jenis kartu, minimal satu kali per jenis.

---

## 9. Uji Naskah di Google Docs

### 9.1 Langkah menyiapkan Google Docs

1. Buat Google Docs baru dengan judul: **`[UTS] Naskah Presentasi 3 Menit — Arman — B-06`**.
2. Salin Bagian 3 (Naskah 3 Menit), Bagian 5 (Yang Belum Selesai), dan Bagian 6 (Pertanyaan untuk Klien). Bagian 7–8 boleh disalin sebagai lampiran latihan.
3. Klik **Share** → **General access** → **Anyone with the link** → **Commenter**. Jangan beri akses **Editor** agar naskah tidak berubah tanpa terkontrol.
4. Salin tautan, lalu kirim pesan di bawah ke grup WhatsApp kelompok.
5. Minta komentar pada bagian spesifik, bukan komentar umum “sudah bagus”.
6. Setelah komentar masuk, isi tabel catatan di 9.4 dan lakukan revisi.
7. Ambil tangkapan layar komentar sebagai bukti Uji Naskah.

### 9.2 Pesan WhatsApp untuk teman kelompok

> Hai Yuga dan Josua, naskah UTS 3 menitku sudah aku taruh di Google Docs: **[tempel tautan]**. Minta bantuannya memberi komentar, jangan edit langsung ya. Fokus komentarnya:
>
> 1. Bagian mana yang paling membosankan atau terlalu lama?
> 2. Kalimat mana yang masih membingungkan saat didengar sekali?
> 3. Bagian demo mana yang perlu dipercepat atau diperjelas?
> 4. Apakah bagian “belum selesai dan rencana” sudah terdengar jujur, bukan defensif?
> 5. Ada typo atau istilah yang salah?
>
> Deadline komentar: **[hari, tanggal, jam]**. Cukup 2–3 komentar di bagian yang paling terasa. Makasih banyak!

### 9.3 Pesan follow-up (H-1)

> Reminder halus: besok jam **[jam]** aku mau kunci naskahnya. Kalau sempat, cukup jawab tiga pertanyaan ini di komentar: (1) bagian paling membosankan, (2) kalimat paling membingungkan, (3) satu saran untuk demo. Kalau tidak sempat buka Docs, balas voice note juga boleh. Thanks!

### 9.4 Tabel catatan komentar

| No | Pemberi komentar | Bagian naskah | Komentar | Tindakan | Status |
| --- | --- | --- | --- | --- | --- |
| 1 |  |  |  |  |  |
| 2 |  |  |  |  |  |
| 3 |  |  |  |  |  |
| 4 |  |  |  |  |  |
| 5 |  |  |  |  |  |

**Status yang dipakai:** `Diterima`, `Ditolak dengan alasan`, `Ditunda ke latihan berikutnya`.

### 9.5 Bukti Uji Naskah yang perlu disimpan

- [ ] Tangkapan layar Google Docs yang menunjukkan nama pemberi komentar dan isi komentar.
- [ ] Tangkapan layar balasan “sudah dikomentari” di WhatsApp.
- [ ] Versi naskah sebelum dan sesudah revisi (bisa memakai fitur **Version history** Google Docs).
- [ ] Ringkasan tiga perubahan yang dilakukan setelah mendapat komentar.

### 9.6 Kalau teman belum bisa membuka Google Docs

Minta komentar lewat voice note atau balasan chat dengan tiga pertanyaan yang sama. Setelah itu, **pindahkan komentar ke Google Docs sebagai komentar** (atau tempel sebagai catatan di akhir dokumen) agar bukti Uji Naskah tetap ada.

---

## 10. Checklist Sebelum Tampil

### H-1

- [ ] Naskah dibaca keras 3 kali dengan stopwatch; target 2:50–3:00.
- [ ] Rekaman video 3 menit ditonton ulang; tandai kalimat yang membuat sendiri bingung.
- [ ] Demo dijalankan dari awal sampai akhir di lingkungan bersih (bukan hanya tab yang sudah terbuka).
- [ ] Komentar Uji Naskah sudah masuk minimal dari 2 teman.
- [ ] Revisi naskah dicatat di tabel 9.4.

### Hari-H

- [ ] Akun mahasiswa uji bisa login.
- [ ] Template dinamis aktif bisa dibuka dan form tampil.
- [ ] Template nonaktif menghasilkan halaman tidak ditemukan.
- [ ] Template dosen ditolak saat diakses akun mahasiswa.
- [ ] Rekaman cadangan dan screenshot bukti uji siap.
- [ ] Timer 3 menit siap.
- [ ] Air minum, browser bersih, notifikasi mati.

### Kalimat penyelamat waktu

- “Karena waktu, saya lompat ke bagian yang paling penting: bukti bahwa template nonaktif tidak bisa dipakai.”
- “Detail teknisnya saya siapkan di lampiran; saya cukupkan demo di sini.”
- “Saya akan jawab pertanyaan teknis itu di sesi tanya jawab agar demo utama tidak terpotong.”

---

## 11. Ringkasan Satu Halaman untuk Dihafal

**Pembuka:** “Saya tidak mulai dengan slide. Saya tunjukkan langsung bagaimana SAKTI memastikan hanya template aktif yang bisa dipakai.”

**Untuk siapa:** Mahasiswa dan dosen sebagai pengusul; Admin TU sebagai penjaga format.

**Rumus fitur:** “Pemilihan Template Aktif membantu mahasiswa dan dosen mengajukan surat yang tepat dengan hanya menampilkan template aktif sesuai role dan merender form otomatis, sehingga pengajuan lebih cepat, konsisten, dan tidak salah format.”

**Demo:** dashboard → pilih template → form otomatis → identitas terkunci → URL nonaktif ditolak → template dosen ditolak → 19 test lulus.

**Belum selesai:** preview/PDF template baru, field lanjutan (tabel anggota dan lampiran), versioning template. Rencana: Sprint 2.

**Pertanyaan klien:** “Tiga template mana yang paling sering dipakai, dan apakah pengajuan lama harus tetap bisa diakses setelah template dinonaktifkan?”

**Penutup:** “Yang sudah selesai bisa Ibu coba hari ini; yang belum selesai sudah punya rencana dan target. Saya minta keputusan Ibu pada dua pertanyaan tadi.”
