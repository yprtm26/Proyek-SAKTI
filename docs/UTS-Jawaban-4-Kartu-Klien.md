# JAWABAN NO. 4 — Kalimat Siap Pakai untuk 4 Kartu Klien

**Nama:** Arman Dava Wicaksono  
**NPM:** 1402024010  
**Kelompok:** Sprout Apex  
**Fitur:** B-06 / US-06 — Pemilihan jenis surat dari daftar template aktif  
**Konteks:** presentasi 3 menit dengan gaya **Demo Dulu**

> Pola yang dipakai untuk semua kartu: **Dengar → Akui → Klarifikasi → Jawab → Komitmen**.
>
> Isi bagian dalam tanda `[...]` sebelum presentasi. Jangan diucapkan apa adanya.

---

## 4.1 Kartu Kritik

**Klien bilang:**

> “Fitur ini malah bikin ribet. Saya harus klik template dulu, belum tentu benar.”

**Kalimat siap pakai:**

> “Terima kasih, Bu. Saya paham kritiknya: alurnya terasa lebih panjang dari cara lama. Supaya saya perbaiki dengan tepat, bagian mana yang paling mengganggu—memilih template, mengisi form, atau memastikan suratnya benar? … Betul, saat ini ada satu layar tambahan. Saya tidak akan menghapus validasinya, karena itu yang mencegah salah format. Yang bisa saya lakukan: menaruh template yang paling sering dipakai di urutan atas dan mengingat pilihan terakhir pengusul. Apakah itu cukup mengurangi rasa ribetnya? Saya catat sebagai perbaikan prioritas dan saya tunjukkan hasilnya di Sprint 2.”

**Jangan:**

- “Tidak mungkin, Bu, sistemnya sudah benar.”
- “Itu memang harus begitu.”
- Langsung membela fitur sebelum memahami bagian yang mengganggu.

**Tindak lanjut:**

- Catat bagian yang paling mengganggu: memilih template, mengisi form, atau memastikan kebenaran surat.
- Ukur jumlah klik pada alur saat ini.
- Tawarkan satu perbaikan konkret, bukan janji umum.

---

## 4.2 Kartu Permintaan Fitur Baru

**Klien bilang:**

> “Bisa tambahkan tombol kirim ke WhatsApp? Atau tanda tangan otomatis?”

**Kalimat siap pakai:**

> “Terima kasih, Bu, idenya bagus. Saya catat. Sebelum saya bawa ke tim, saya ingin memastikan masalah yang ingin diselesaikan: apakah Ibu ingin pengusul tahu status tanpa membuka aplikasi, atau ingin mengurangi chat manual ke TU? … Kalau tujuannya notifikasi status, solusinya belum tentu WhatsApp; bisa email atau badge di dashboard. Untuk tanda tangan otomatis, itu menyentuh aturan tanda tangan dan keamanan dokumen, jadi perlu keputusan terpisah. Saya bawa dua opsi ini ke sprint planning lengkap dengan estimasi dan risikonya, lalu saya kabari keputusannya paling lambat **[tanggal]**. Apakah boleh saya prioritaskan notifikasi status dulu?”

**Jangan:**

- Langsung berkata “bisa” tanpa tahu biaya dan dampaknya.
- Berkata “tidak bisa” tanpa menjelaskan alternatif.
- Menjanjikan fitur masuk sprint tanpa proses prioritas.

**Tindak lanjut:**

- Tulis di backlog sebagai usulan, beri label `requested-by-client`.
- Bawa ke sprint planning dengan estimasi dan risiko.
- Beri keputusan tertulis paling lambat pada tanggal yang dijanjikan.

---

## 4.3 Kartu Pertanyaan Teknis

**Klien bilang:**

> “Apakah data ini aman? Kalau orang tahu URL-nya, bisa buka surat orang lain?”

**Kalimat siap pakai:**

> “Pertanyaan yang tepat, Bu. Ada tiga lapis pengaman. Pertama, semua halaman wajib login. Kedua, role dicek di server, bukan hanya menu disembunyikan; pengusul hanya bisa membuka template sesuai role-nya. Ketiga, template nonaktif dan template milik role lain ditolak saat diakses lewat URL langsung—halaman tidak ditemukan. Identitas pengusul juga dikunci dari akun yang login, jadi nama, NPM/NIDN, dan prodi tidak bisa diubah dari browser. Pengajuan terikat ke akun yang login, jadi tidak bisa mengatasnamakan orang lain. Kami menguji alur B-06 dengan 19 test dan 109 assertion; semuanya lulus. Kalau Ibu mau, saya tunjukkan bukti uji dan log penolakan aksesnya.”

**Jangan:**

- Menjawab “aman” tanpa bukti.
- Menjelaskan istilah `middleware`, `route`, atau `token` tanpa analogi.
- Mengklaim semua data aman tanpa batas yang jelas.

**Tindak lanjut:**

- Tunjukkan bukti test `OK (19 tests, 109 assertions)`.
- Tunjukkan halaman yang menolak akses template nonaktif dan template role lain.
- Jika klien ingin detail, tawarkan sesi teknis terpisah.

---

## 4.4 Kartu Menagih

**Klien bilang:**

> “Katanya minggu lalu sudah selesai. Kok sekarang belum bisa dipakai?”

**Kalimat siap pakai:**

> “Terima kasih sudah menagih, Bu; saya memang harus lebih rajin memberi kabar. Status jujurnya: pemilihan template aktif, form dinamis, proteksi akses, penguncian identitas, dan API template sudah selesai dan hari ini bisa saya demokan. Yang belum: preview dan PDF otomatis untuk template baru, field lanjutan seperti tabel anggota dan unggah lampiran, serta versioning template. Saya tidak mau memberi janji yang belum pasti. Usulan saya: hari ini Ibu menerima bagian yang sudah selesai plus bukti 19 test, dan preview/PDF generik saya kejar selesai **[tanggal]** dengan update setiap **[hari]**. Apakah tanggal itu masih membantu Ibu, atau ada batas lain yang harus saya kejar?”

**Jangan:**

- Menyalahkan anggota lain.
- Berkata “hampir selesai” tanpa tanggal.
- Menghilang setelah janji gagal.
- Menyebut semua fitur selesai padahal masih ada batasan.

**Tindak lanjut:**

- Kirim status tertulis: **selesai / sedang dikerjakan / belum mulai**.
- Lampirkan tanggal dan bukti.
- Beri update berkala sesuai janji.

---

## Ringkasan Cepat

| Kartu | Inti jawaban | Bukti/komitmen |
| --- | --- | --- |
| Kritik | Akui alurnya terasa lebih panjang, tanyakan bagian yang mengganggu | Perbaikan prioritas di Sprint 2 |
| Permintaan fitur baru | Gali masalah, jangan langsung setuju | Bawa ke sprint planning + tanggal keputusan |
| Pertanyaan teknis | Login, role di server, URL nonaktif ditolak, identitas dikunci | 19 test, 109 assertion, log penolakan |
| Menagih | Akui keterlambatan, pisahkan selesai vs belum | Bukti hari ini + tanggal untuk sisanya |

---

## Catatan

- Jawaban ini khusus untuk **B-06**, bukan seluruh proyek SAKTI.
- Jika klien perempuan, gunakan **“Ibu”**. Jika belum diketahui, gunakan **“Bapak/Ibu”**.
- Kalimat boleh dipersingkat saat latihan, tetapi jangan hilangkan urutan **Dengar → Akui → Klarifikasi → Jawab → Komitmen**.
