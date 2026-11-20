# CATATAN KONSEP MANDIRI (TUGAS 1: VALIDASI INPUT)
**Mode Proyek:** Mode A · Proyek LDAP UY  
**Nama Mahasiswa:** Arman Dava Wicaksono  
**NPM:** 1402024010  
**Nama Tim:** Sprout Apex (Anggota 1)  
**Aplikasi:** SAKTI (Sistem Administrasi Surat Akademik Terintegrasi) — Universitas YARSI  
**Stack Teknologi:** Laravel 12 (PHP 8.4), Blade Template, MySQL, OpenLDAP  

---

## 1. Input yang Masuk ke Aplikasi SAKTI dan Analisis Risikonya (Mode A)

Di aplikasi kampus kita (SAKTI), pintu masuk data berasal dari tiga jalur utama:

* **Input dari Form:**
  1. *Form Autentikasi:* Input `username` (NPM mahasiswa atau NIDN dosen) dan `password`.
  2. *Form Pengajuan Surat:* Input `jenis_surat`, `keperluan` (textarea bebas), `semester`, `tahun_akademik`, serta berkas lampiran `file_pendukung` (PDF bukti bayar/KRS).
  3. *Form Verifikasi Admin TU:* Input `status_persetujuan`, `nomor_surat_resmi`, dan `catatan_revisi`.
* **Input dari URL (Query String & Route Parameter):**
  1. *Route Parameter:* ID dokumen surat pada endpoint rute, misalnya `/surat/detail/{id}` atau `/surat/unduh-pdf/{id}`.
  2. *Query Parameter:* Parameter filter tabel dan pencarian, misalnya `?status=pending`, `?kategori=akademik`, `?search=arman`, dan penomoran halaman `?page=2`.
* **Input dari Data Server LDAP:**
  Data profil sivitas akademika yang diambil setelah proses *bind* LDAP berhasil, meliputi atribut `cn` / `displayName` (nama lengkap pengguna), `mail` (email institusi `@yarsi.ac.id`), `ou` (fakultas/program studi), dan `title` (status akademik).

### Input Mana yang Paling Berisiko dan Kenapa?
Yang paling berbahaya di sistem kita sebenarnya ada dua: **data dari LDAP** dan **input form teks bebas (`keperluan`)**.

Khusus data dari LDAP, risikonya sangat tinggi karena adanya **asumsi palsu (*false sense of security*)**. Banyak developer mengira data dari LDAP itu 100% aman karena berasal dari server direktori resmi kampus, padahal dari sudut pandang aplikasi SAKTI, **semua data eksternal tetaplah input yang tidak tepercaya (*untrusted input*)**. Jika atribut `displayName` atau `title` di LDAP pernah disisipkan karakter kutip atau tag HTML/JavaScript (baik dari data migrasi lama, admin LDAP yang salah input, atau akun LDAP yang disusupi), lalu aplikasi kita mencetaknya begitu saja ke Blade tanpa proteksi, aplikasi kita langsung terkena celah **Stored Cross-Site Scripting (XSS)**. Begitu pula jika atribut LDAP digabungkan langsung ke query SQL lokal, itu bisa memicu **Second-Order SQL Injection**.

Sementara untuk input form `keperluan`, teksnya bersifat bebas dan dinamis sehingga sangat rawan disusupi script berbahaya jika kita tidak menerapkan sanitasi dan validasi panjang karakter yang ketat di sisi server.

---

## 2. Perbedaan Validasi Input vs Output Encoding dan Kasus Nama LDAP (Mode A)

Secara sederhana, perbedaannya terletak pada **posisi penanganan** dan **tujuannya**:

* **Validasi Input (Di Pintu Masuk / Server):**  
  Dilakukan saat data pertama kali tiba di server aplikasi. Tujuannya memastikan format, tipe, ukuran, dan isi data sesuai dengan aturan bisnis yang diizinkan (*allowlist*). Contohnya: field NPM wajib berupa 10 digit angka murni, dan `jenis_surat` hanya boleh memilih opsi yang tersedia di sistem. Kalau input melenceng dari aturan, sistem langsung **menolak permintaan** (mengembalikan error 422 Unprocessable Content).
* **Output Encoding (Di Pintu Keluar / Tampilan Browser):**  
  Dilakukan tepat saat data hendak dirender ke antarmuka pengguna (HTML/Blade). Tujuannya bukan menolak data, melainkan **menerjemahkan karakter kontrol berbahaya** (seperti `<`, `>`, `"`, `'`, `&`) menjadi entitas HTML aman (`&lt;`, `&gt;`, `&quot;`, `&#039;`, `&amp;`). Dengan begitu, browser akan memperlakukan data tersebut murni sebagai teks string statis, bukan sebagai perintah kode yang dieksekusi.

### Kenapa Nama dari LDAP Tetap Wajib Di-encode Saat Ditampilkan?
Nama orang di Indonesia dan sivitas kampus sering kali memuat karakter yang secara linguistik valid, seperti tanda petik satu / apostrof (misal: *Ma'ruf*, *D'Artagnan*, *Siti Nur'aini*) atau tanda baca gelar (*Dr. Ir. H. Ahmad, S.Kom., M.Kom.*). Kita tidak bisa memvalidasi nama dengan aturan yang terlalu kaku karena bisa merusak nama asli orang tersebut.

Selain itu, aplikasi SAKTI tidak memiliki kendali langsung atas integritas database OpenLDAP kampus. Jika ada payload jahat tersimpan di atribut nama LDAP, dan kita menampilkannya menggunakan render mentah (seperti `{!! $user->name !!}` di Blade atau `.innerHTML` di JavaScript), script tersebut akan langsung aktif di browser siapa pun yang membuka halaman profil atau disposisi surat. Dengan menerapkan **output encoding** bawaan Blade (`{{ $user->name }}`), nama dengan karakter apa pun akan tetap tampil sempurna dan aman di mata pembaca tanpa risiko eksekusi kode jahat.

---

## 3. Kaitan Video Wajib Computerphile dengan Aplikasi SAKTI

> **Format Jawaban:**  
> Video *SQL Injection - Computerphile* (Tom Scott), menit 03:45: video menunjukkan bagaimana menggabungkan input pengguna secara langsung ke dalam string query SQL memungkinkan karakter kutip memanipulasi logika *WHERE clause* sehingga query menampilkan data yang seharusnya dirahasiakan. Di aplikasi kami, hal ini berarti jika pencarian surat atau login mahasiswa disusun menggunakan penggabungan string (*string concatenation*), penyerang dapat menyusupkan logika `OR 1=1` untuk membocorkan seluruh berkas surat rahasia mahasiswa lain tanpa izin.

---

## 4. Analisis Pengerjaan Lab PortSwigger: SQL Injection in WHERE Clause

### A. Skenario dan Input yang Dikirim
Pada lab PortSwigger (*SQL injection vulnerability in WHERE clause allowing retrieval of hidden data*), terdapat fitur filter produk berdasarkan kategori di URL:
```text
https://[lab-id].web-security-academy.net/filter?category=Gifts
```
Aplikasi menjalankan query SQL asli di belakang layar seperti ini:
```sql
SELECT * FROM products WHERE category = 'Gifts' AND released = 1
```
Kondisi normal hanya menampilkan produk yang sudah dirilis (`released = 1`).

Payload yang saya kirimkan pada parameter `category` adalah:
```text
'+OR+1=1--
```
Sehingga URL permintaan menjadi:
```text
https://[lab-id].web-security-academy.net/filter?category=Gifts'+OR+1=1--
```

### B. Bagaimana Input Mengubah Query di Server
Saat input digabungkan langsung ke string query, database menginterpretasikan perintah baru sebagai berikut:
```sql
SELECT * FROM products WHERE category = 'Gifts' OR 1=1--' AND released = 1
```
1. Karakter petik satu `'` pertama berhasil memotong dan menutup nilai string `'Gifts'`.
2. Klausa `OR 1=1` disuntikkan ke dalam struktur logika query. Karena perbandingan `1=1` selalu bernilai **BENAR (TRUE)**, maka seluruh baris data pada tabel produk otomatis lolos seleksi.
3. Karakter urutan strip ganda `--` berfungsi sebagai penanda komentar SQL, sehingga sisa query asli di belakangnya (`AND released = 1`) diabaikan dan tidak dieksekusi oleh mesin database.
4. Hasilnya, aplikasi menampilkan seluruh produk di toko, termasuk produk-produk tersembunyi yang belum dirilis (`released = 0`), dan status lab berubah menjadi **SOLVED**.

### C. Bagaimana Query Berparameter (Prepared Statements) Mencegahnya
Prepared statements / query berparameter (seperti yang digunakan ORM Eloquent di Laravel) bekerja dengan memisahkan tahap kompilasi struktur query dengan pengiriman datanya:
```php
// Contoh proteksi menggunakan query berparameter di Laravel
$products = DB::select('SELECT * FROM products WHERE category = ? AND released = 1', [$inputCategory]);
```
Mesin database akan menyusun dan mengunci struktur sintaks SQL terlebih dahulu. Ketika input `Gifts' OR 1=1--` dikirimkan, nilai tersebut diperlakukan murni sebagai satu kesatuan nilai literal teks (string). Mesin database tidak akan pernah menganggap tanda kutip sebagai penutup string atau kata `OR` sebagai operator logika, melainkan hanya mencari produk yang nama kategorinya persis bernilai string teks `"Gifts' OR 1=1--"`. Karena tidak ada kategori bernama demikian, manipulasi query gagal total.

### D. Bukti Tangkapan Layar Lab Solved
*(Tempelkan tangkapan layar bukti lab PortSwigger dengan banner hijau "Congratulations, you solved the lab!" di bawah ini)*

```
+-----------------------------------------------------------------------------------+
|                                                                                   |
|            [ TANGKAPAN LAYAR BUKTI LAB PORTSWIGGER SUDAH SOLVED ]                 |
|                                                                                   |
|   Pastikan terlihat jelas banner:                                                 |
|   "Congratulations, you solved the lab!" beserta URL browser Anda                 |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

## 5. Ringkasan Bahan OWASP (Anggota 1) dan Kaitannya dengan SAKTI

* **Nama Bahan:** *OWASP Input Validation Cheat Sheet*  
* **Bagian yang Dibaca:** Bagian *Allowlist vs Blocklist Validation* dan *Server-Side Validation*.

### Dua Hal Penting yang Perlu Diketahui Rekan Sekelompok:
1. **Prinsip Allowlist (Validasi Positif) Jauh Lebih Unggul daripada Blocklist:**  
   Kita tidak boleh mengamankan aplikasi hanya dengan berusaha mencari dan memblokir karakter jahat (seperti memblokir kata `<script>`, `UNION`, atau tanda kutip). Penyerang selalu punya teknik untuk mengakali filter *blocklist* menggunakan variasi encoding (URL encode, unicode, double quotes, null bytes). Pendekatan yang benar adalah *allowlist*: kita mendefinisikan secara tegas kriteria data apa saja yang **boleh** masuk (tipe datanya apa, panjang karakternya berapa, karakternya apa saja, dan format polanya seperti apa). Di luar pola yang diizinkan, data wajib ditolak.
2. **Validasi Wajib Mutlak Dijalankan di Sisi Server (*Server-Side*):**  
   Validasi di sisi frontend (menggunakan JavaScript, atribut form HTML5 `required`, atau regex browser) tujuannya murni hanya untuk kenyamanan pengguna (*user experience*), bukan lapisan keamanan. Penyerang dapat dengan sangat mudah mematikan JavaScript browser atau langsung menembak endpoint HTTP kita menggunakan alat seperti cURL, Postman, atau Burp Suite. Oleh sebab itu, server backend Laravel kita wajib menjadi benteng validasi utama sebelum data diteruskan ke controller atau database.

### Kaitan Penerapannya dengan Proyek SAKTI:
Pada aplikasi SAKTI, prinsip ini langsung kita terapkan pada Form Request Laravel (`SuratRequest.php` dan `AuthRequest.php`):
* Pada input `npm`, kita menggunakan validasi *allowlist* ketat:
  ```php
  'npm' => ['required', 'string', 'numeric', 'digits:10']
  ```
  Dengan aturan ini, input NPM hanya diizinkan tepat 10 digit angka murni, sehingga mustahil disisipi karakter SQL injection atau script XSS.
* Pada input `jenis_surat`, kita menerapkan aturan *enum allowlist*:
  ```php
  'jenis_surat' => ['required', Rule::in(['surat_aktif', 'surat_beasiswa', 'surat_magang'])]
  ```
  Jika pengguna mengirimkan nilai di luar ketiga opsi yang sudah ditentukan server, Laravel akan langsung memotong request dan melempar respon HTTP 422 tanpa pernah memprosesnya ke query basis data.
