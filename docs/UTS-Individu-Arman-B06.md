# TUGAS INDIVIDU UTS — NASKAH PRESENTASI 3 MENIT

**Nama:** Arman Dava Wicaksono  
**NPM:** 1402024010  
**Kelompok:** Sprout Apex  
**Proyek:** SAKTI — Sistem Administrasi Surat Akademik Terintegrasi  
**Fitur individu:** B-06 / US-06 — Pemilihan jenis surat dari daftar template aktif  
**Gaya presentasi:** Demo Dulu  
**Durasi:** 3 menit

> **Analisis soal:** Soal ini bersifat individu, tetapi meminta pembagian fitur bersama kelompok. Karena itu, Jawaban 1 memuat pembagian fitur kelompok, sedangkan Jawaban 2–6 difokuskan pada fitur yang saya kerjakan sendiri, yaitu B-06. Setiap anggota memilih fitur yang berbeda.

---

## JAWABAN SOAL 1 — Pembagian Fitur Kelompok

| No | Anggota | NPM | Fitur yang dikerjakan | User story | Bukti |
| --- | --- | --- | --- | --- | --- |
| 1 | **Arman Dava Wicaksono** | 1402024010 | **B-06 — Pemilihan jenis surat dari daftar template aktif** | US-06 | `TemplateSuratController`, `form-dinamis.blade.php`, `PemilihanTemplateAktifTest.php` |
| 2 | **Yuga Pratama Andika Syaputra** | 1402024061 | **B-01 A — Kontrol tampilan menu dan data sesuai role** | US-01 A | `RoleMiddleware`, `HakAksesRoleTest.php`, `docs/hak-akses.md` |
| 3 | **Josua Rajak** | 1402024025 | **B-15a — Pembuatan dan pengelolaan template surat oleh Admin TU** | US-15 A–C | Form create/edit template Admin TU, route `/admin/template-surat` |

**Keterangan pembagian:**

- Tidak ada fitur yang sama.
- B-06 fokus pada sisi **pengusul** (mahasiswa/dosen memilih template aktif).
- B-01 A fokus pada **pagar akses lintas role**.
- B-15a fokus pada sisi **Admin TU** (membuat dan mengelola template).
- Fitur B-02 dan B-05 tidak dipresentasikan dalam sesi 3 menit karena keterbatasan waktu, tetapi tetap tercatat di backlog kelompok.

---

## JAWABAN SOAL 2 — Gaya Presentasi dan Alasannya

**Gaya yang dipilih: Demo Dulu.**

Presentasi dibuka dengan aplikasi yang benar-benar berjalan, bukan dengan slide atau penjelasan konsep.

**Alasan memilih Demo Dulu:**

1. **Fitur B-06 bersifat visual dan interaktif.** Klien dapat langsung melihat template aktif muncul, form berubah mengikuti field template, dan identitas pengusul terkunci.
2. **Klien lebih cepat percaya pada bukti daripada janji.** Satu alur demo 60 detik lebih kuat daripada tiga slide yang menjelaskan “sistem akan menampilkan template aktif”.
3. **Waktu hanya 3 menit.** Demo memangkas waktu menjelaskan konsep. Satu klik dan satu percobaan akses URL langsung sudah membuktikan fitur berjalan sekaligus pagar keamanannya aktif.
4. **Audiens utama bukan programmer.** Admin TU dan dosen tidak perlu memahami istilah controller atau middleware; mereka perlu melihat pekerjaan yang lebih cepat dan lebih aman.
5. **Risiko demo gagal dapat dikendalikan.** Saya menyiapkan rekaman layar 60–90 detik dan tangkapan layar hasil uji sebagai cadangan.

**Mengapa bukan Storytelling:** Storytelling membutuhkan waktu untuk membangun konteks dan tokoh. Dalam 3 menit, waktu habis sebelum bagian demo yang paling meyakinkan.

**Mengapa bukan BLUF:** BLUF bagus untuk rapat pengambilan keputusan, tetapi kurang menunjukkan bukti visual. BLUF tetap saya pakai di **penutup** untuk menyimpulkan dan meminta keputusan klien.

**Pola presentasi:** Demo Dulu di pembuka → rumus kalimat fitur → batas dan rencana → BLUF di penutup.

---

## JAWABAN SOAL 3 — Naskah Presentasi 3 Menit

### 3.1 Pembuka (0:00–0:15)

**Arahan panggung:** Buka dashboard mahasiswa. Jangan menampilkan slide judul lebih dari 3 detik.

**Kalimat lisan:**

> “Selamat pagi, Ibu. Saya Arman dari tim Sprout Apex. Hari ini saya tidak mulai dengan slide. Saya akan langsung membuka SAKTI dan menunjukkan satu hal: bagaimana Ibu bisa memastikan mahasiswa hanya memakai surat yang benar-benar aktif. Perhatikan layar ini.”

### 3.2 Untuk Siapa Fitur Ini (0:15–0:40)

**Arahan panggung:** Arahkan kursor ke daftar kartu template, lalu ke kolom pencarian.

**Kalimat lisan:**

> “Fitur ini bernama Pemilihan Template Aktif. Pengguna utamanya mahasiswa dan dosen sebagai pengusul. Mereka butuh surat yang cepat dan tidak salah pilih format. Pengguna keduanya Admin TU: Ibu perlu memastikan hanya format yang masih berlaku yang bisa dipakai. Jadi ini bukan sekadar tampilan, tapi pagar pertama agar surat yang keluar konsisten.”

**Penjelasan untuk menjawab soal:**

- **Pengguna utama:** mahasiswa dan dosen sebagai pengusul.
- **Pengguna kedua:** Admin TU yang mengaktifkan atau menonaktifkan template.
- **Bukan pengguna fitur ini:** Kaprodi dan Dekan, karena mereka menyetujui surat, bukan mengajukan surat.

### 3.3 Demo dengan Rumus Kalimat Fitur (0:40–2:10)

**Rumus kalimat fitur:**

> Fitur **[nama fitur]** membantu **[pengguna]** untuk **[tujuan]** dengan **[cara kerja]**, sehingga **[hasil/manfaat]**.

**Kalimat fitur yang diucapkan:**

> “Rumus kalimat fitur kami: fitur **[nama]** membantu **[pengguna]** untuk **[tujuan]** dengan **[cara kerja]**, sehingga **[hasil]**.”

> “Untuk fitur ini: **Pemilihan Template Aktif** membantu **mahasiswa dan dosen** untuk **mengajukan surat yang tepat**, dengan **hanya menampilkan template aktif sesuai role dan merender form otomatis dari field template**, sehingga **pengajuan lebih cepat, konsisten, dan tidak salah format**.”

**Langkah demo:**

1. **Klik template aktif** “Surat Keterangan Bebas Laboratorium”.

   > “Saya pilih satu. Form ini tidak saya tulis manual. Sistem membaca daftar field dari template. Kalau Admin TU menambah field, form ikut berubah tanpa membuat halaman baru.”

2. **Scroll ke bagian identitas pengusul.**

   > “Nama, NPM, dan prodi terkunci. Saya tidak bisa mengubahnya dari browser; server akan menimpa dengan akun yang sedang login. Jadi identitas tidak bisa dipalsukan.”

3. **Buka URL template nonaktif, tekan Enter.**

   > “Sekarang saya coba akal-akalan: membuka template nonaktif lewat URL langsung. Sistem menolak, halaman tidak ditemukan. Jadi bukan hanya tombolnya yang disembunyikan.”

4. **Buka URL template milik dosen.**

   > “Terakhir, saya coba template milik dosen. Sistem juga menolak. Artinya pembatasan role terjadi di server, bukan hanya di tampilan. Bukti ujinya, 19 test lulus dengan 109 assertion.”

### 3.4 Yang Belum Selesai dan Rencananya (2:10–2:40)

**Arahan panggung:** Kembali ke dashboard. Bicara tanpa defensif.

**Kalimat lisan:**

> “Saya sampaikan yang belum selesai secara terbuka. Pertama, template baru belum punya preview dan PDF otomatis; halaman detail masih menampilkan pesan preview belum tersedia. Kedua, form dinamis baru mendukung satu nilai teks per field; tabel anggota berulang dan lampiran khusus masih memakai form khusus. Rencana Sprint 2: preview dan PDF generik, tipe field lanjutan, lalu versioning template agar pengajuan lama tidak rusak saat template diubah.”

**Rincian rencana:**

| Prioritas | Yang belum selesai | Rencana | Target |
| --- | --- | --- | --- |
| P1 | Preview dan PDF otomatis untuk template baru | Preview generik dari `fields`, lalu render PDF generik | Sprint 2 |
| P1 | Field lanjutan: tabel anggota berulang dan unggah lampiran | Tambah tipe field `repeatable_table` dan `file` | Sprint 2 |
| P2 | Versioning dan audit perubahan template | Simpan versi template dan snapshot field pada pengajuan | Sprint 2–3 |
| P2 | Uji usability bersama 2 Admin TU | Sesi uji 30 menit, catat 3 hambatan utama | Sebelum Sprint Review |

### 3.5 Pertanyaan untuk Klien (2:40–3:00)

**Arahan panggung:** Berhenti di dashboard. Tatap klien, bukan layar.

**Kalimat lisan:**

> “Sebelum saya tutup, saya perlu keputusan Ibu. Dari template yang aktif hari ini, tiga mana yang paling sering dipakai? Kami akan prioritaskan preview dan PDF-nya. Dan kalau sebuah template dinonaktifkan, apakah pengajuan lama dengan template itu harus tetap bisa dibuka dan diunduh? Terima kasih.”

**Daftar pertanyaan:**

1. **Pertanyaan utama:** “Dari template yang aktif hari ini, tiga mana yang paling sering dipakai? Kami akan prioritaskan preview dan PDF-nya.”
2. **Pertanyaan kedua:** “Kalau sebuah template dinonaktifkan, apakah pengajuan lama dengan template itu harus tetap bisa dibuka dan diunduh?”
3. **Pertanyaan cadangan:** “Untuk template baru, Ibu lebih butuh unggah lampiran khusus atau cukup isian teks? Ini menentukan urutan pekerjaan kami di Sprint 2.”

**Tujuan pertanyaan:** mendapatkan prioritas dan batas perilaku yang bisa langsung masuk backlog, bukan hanya komentar “bagus” atau “tidak bagus”.

### 3.6 Persiapan Demo (catatan teknis)

- Akun mahasiswa uji sudah login; nama, NPM, dan prodi terisi.
- Buat template dinamis baru melalui Admin TU: “Surat Keterangan Bebas Laboratorium” dengan field `nama_laboratorium`, `tujuan_penggunaan`, dan `tanggal_pengambilan`. Status aktif, target mahasiswa, dan belum punya view khusus.
- Siapkan satu template nonaktif milik mahasiswa dan catat URL-nya.
- Catat URL satu template milik dosen.
- Siapkan tab: dashboard, form dinamis, URL nonaktif, URL template dosen.
- Rekam layar 60–90 detik sebagai plan B.
- Simpan tangkapan layar halaman penolakan akses dan output test `OK (19 tests, 109 assertions)`.
- Siapkan timer 3 menit.

---

## JAWABAN SOAL 4 — Kalimat Siap Pakai untuk 4 Kartu Klien

Pola yang dipakai untuk semua kartu: **Dengar → Akui → Klarifikasi → Jawab → Komitmen**.

### 4.1 Kartu Kritik

**Klien bilang:**

> “Fitur ini malah bikin ribet. Saya harus klik template dulu, belum tentu benar.”

**Kalimat siap pakai:**

> “Terima kasih, Bu. Saya paham kritiknya: alurnya terasa lebih panjang dari cara lama. Supaya saya perbaiki dengan tepat, bagian mana yang paling mengganggu—memilih template, mengisi form, atau memastikan suratnya benar? … Betul, saat ini ada satu layar tambahan. Saya tidak akan menghapus validasinya, tetapi saya bisa membuat template yang paling sering dipakai muncul pertama dan mengingat pilihan terakhir. Apakah itu cukup mengurangi rasa ribetnya? Saya catat sebagai perbaikan prioritas dan saya tunjukkan alternatifnya di Sprint 2.”

**Jangan:** “Tidak mungkin, Bu, sistemnya sudah benar.” atau “Itu memang harus begitu.”

**Tindak lanjut:** catat bagian yang paling mengganggu, ukur jumlah klik, tawarkan satu perbaikan konkret.

### 4.2 Kartu Permintaan Fitur Baru

**Klien bilang:**

> “Bisa tambahkan tombol kirim ke WhatsApp? Atau tanda tangan otomatis?”

**Kalimat siap pakai:**

> “Terima kasih, Bu, idenya bagus. Saya catat. Sebelum saya bawa ke tim, saya ingin memastikan masalah yang ingin diselesaikan: apakah Ibu ingin pengusul tahu status tanpa membuka aplikasi, atau ingin mengurangi chat manual ke TU? … Kalau tujuannya notifikasi status, solusinya belum tentu WhatsApp; bisa email atau badge di dashboard. Saya bawa dua opsi ini ke sprint planning lengkap dengan estimasi dan risikonya, lalu saya kabari keputusannya paling lambat [tanggal]. Apakah boleh saya prioritaskan notifikasi status dulu?”

**Jangan:** langsung berkata “bisa” tanpa tahu biaya, atau “tidak bisa” tanpa menjelaskan alternatif.

**Tindak lanjut:** tulis di backlog sebagai usulan, beri label `requested-by-client`, bawa ke sprint planning, beri keputusan tertulis.

### 4.3 Kartu Pertanyaan Teknis

**Klien bilang:**

> “Apakah data ini aman? Kalau orang tahu URL-nya, bisa buka surat orang lain?”

**Kalimat siap pakai:**

> “Pertanyaan yang tepat, Bu. Ada tiga lapis pengaman. Pertama, semua halaman wajib login. Kedua, role dicek di server, bukan hanya menu disembunyikan; pengusul hanya bisa membuka template role-nya. Ketiga, template nonaktif dan surat milik orang lain ditolak saat diakses lewat URL langsung—halaman tidak ditemukan atau akses ditolak. Identitas pengusul juga dikunci dari akun yang login, jadi tidak bisa diubah dari browser. Kami mengujinya dengan 19 test; semuanya lulus. Kalau Ibu mau, saya tunjukkan log penolakan aksesnya.”

**Jangan:** menjawab “aman” tanpa bukti, atau menjelaskan istilah `middleware`, `route`, dan `token` tanpa analogi.

**Tindak lanjut:** tunjukkan bukti uji dan log. Jika klien ingin detail, tawarkan sesi teknis terpisah.

### 4.4 Kartu Menagih

**Klien bilang:**

> “Katanya minggu lalu sudah selesai. Kok sekarang belum bisa dipakai?”

**Kalimat siap pakai:**

> “Terima kasih sudah menagih, Bu; saya memang harus lebih rajin memberi kabar. Status jujurnya: [bagian A] sudah selesai dan hari ini bisa saya demokan. [Bagian B] belum, karena [alasan konkret, misalnya preview PDF untuk template baru belum ada]. Saya tidak mau memberi janji yang belum pasti. Usulan saya: hari ini Ibu menerima [A] plus bukti ujinya, dan [B] saya kejar selesai [tanggal] dengan update setiap [hari]. Apakah tanggal itu masih membantu Ibu, atau ada batas lain yang harus saya kejar?”

**Jangan:** menyalahkan anggota lain, menyebut “hampir selesai” tanpa tanggal, atau menghilang setelah janji gagal.

**Tindak lanjut:** kirim status tertulis: selesai / sedang dikerjakan / belum mulai, plus tanggal dan bukti.

### 4.5 Bonus: Kalau Kita yang Menagih Klien

**Kalimat siap pakai:**

> “Bu, kami perlu keputusan Ibu untuk [hal] sebelum [tanggal] agar Sprint 2 tidak tertunda. Saya kirim ringkasan tiga poin: [A], [B], [C]. Cukup Ibu pilih A atau B, atau beri catatan singkat. Kalau belum sempat, boleh saya anggap [A] sebagai default sementara dan saya perbaiki di sprint berikutnya?”

---

## JAWABAN SOAL 5 — Jawaban Latihan “Klien Bilang…”

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

**Cara berlatih:** teman membacakan satu kalimat secara acak; saya menjawab maksimal 30 detik tanpa membaca; teman menilai **sopan**, **jelas**, dan **ada tindak lanjut**.

---

## JAWABAN SOAL 6 — Uji Naskah di Google Docs

### 6.1 Langkah yang saya lakukan

1. Membuat Google Docs baru berjudul **`[UTS] Naskah Presentasi 3 Menit — Arman — B-06`**.
2. Menyalin Jawaban 3 (naskah), Jawaban 3.4 (belum selesai), dan Jawaban 3.5 (pertanyaan klien).
3. Membuka **Share** → **General access** → **Anyone with the link** → **Commenter**. Saya tidak memberi akses **Editor** agar naskah tidak berubah tanpa terkontrol.
4. Mengirim tautan ke grup WhatsApp kelompok dan meminta Yuga serta Josua memberikan komentar.
5. Meminta komentar pada bagian spesifik, bukan komentar umum “sudah bagus”.
6. Mencatat komentar di tabel, merevisi naskah, lalu mengambil tangkapan layar sebagai bukti Uji Naskah.

### 6.2 Pesan yang dikirim ke teman kelompok

> Hai Yuga dan Josua, naskah UTS 3 menitku sudah aku taruh di Google Docs: **[tempel tautan]**. Minta bantuannya memberi komentar, jangan edit langsung ya. Fokus komentarnya:
>
> 1. Bagian mana yang paling membosankan atau terlalu lama?
> 2. Kalimat mana yang masih membingungkan saat didengar sekali?
> 3. Bagian demo mana yang perlu dipercepat atau diperjelas?
> 4. Apakah bagian “belum selesai dan rencana” sudah terdengar jujur, bukan defensif?
> 5. Ada typo atau istilah yang salah?
>
> Deadline komentar: **[hari, tanggal, jam]**. Cukup 2–3 komentar di bagian yang paling terasa. Makasih banyak!

### 6.3 Pesan follow-up H-1

> Reminder halus: besok jam **[jam]** aku mau kunci naskahnya. Kalau sempat, cukup jawab tiga pertanyaan ini di komentar: (1) bagian paling membosankan, (2) kalimat paling membingungkan, (3) satu saran untuk demo. Kalau tidak sempat buka Docs, balas voice note juga boleh. Thanks!

### 6.4 Tabel catatan komentar

| No | Pemberi komentar | Bagian naskah | Komentar | Tindakan | Status |
| --- | --- | --- | --- | --- | --- |
| 1 |  |  |  |  |  |
| 2 |  |  |  |  |  |
| 3 |  |  |  |  |  |
| 4 |  |  |  |  |  |
| 5 |  |  |  |  |  |

**Status:** `Diterima`, `Ditolak dengan alasan`, atau `Ditunda ke latihan berikutnya`.

### 6.5 Bukti Uji Naskah yang disimpan

- Tangkapan layar Google Docs yang menunjukkan nama pemberi komentar dan isi komentar.
- Tangkapan layar balasan “sudah dikomentari” di WhatsApp.
- Versi naskah sebelum dan sesudah revisi (bisa memakai **Version history** Google Docs).
- Ringkasan tiga perubahan yang dilakukan setelah mendapat komentar.

### 6.6 Kalau teman belum bisa membuka Google Docs

Minta komentar lewat voice note atau balasan chat dengan tiga pertanyaan yang sama. Setelah itu, pindahkan komentar ke Google Docs sebagai komentar atau catatan di akhir dokumen agar bukti Uji Naskah tetap ada.

---

## CHECKLIST AKHIR

- [ ] Naskah dibaca keras 3 kali dengan stopwatch; target 2:50–3:00.
- [ ] Demo dijalankan dari awal sampai akhir di lingkungan bersih.
- [ ] Rekaman cadangan dan screenshot bukti uji siap.
- [ ] Komentar Uji Naskah sudah masuk minimal dari 2 teman.
- [ ] Revisi naskah dicatat.
- [ ] Tautan Google Docs dan tangkapan layar komentar disimpan sebagai bukti.

---

**Catatan:** Lembar asli “Latihan Klien Bilang…” dan template presentasi UTS dari Kegiatan Mandiri tidak tersedia di repository ini. Jawaban 5 disusun dari 10 kalimat klien yang paling sering muncul untuk 4 jenis kartu. Jika lembar aslinya tersedia, kalimat klien dapat disesuaikan tanpa mengubah pola jawabannya.
