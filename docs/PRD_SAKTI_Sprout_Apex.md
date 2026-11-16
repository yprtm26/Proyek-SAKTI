# PRODUCT REQUIREMENTS DOCUMENT (PRD)

# SAKTI -- Sistem Administrasi dan Kelola Tata Informasi

**Aplikasi Administrasi Surat Berbasis Digital**

  Informasi      Keterangan
  -------------- -------------------------------
  Nama Produk    SAKTI
  Nama Tim       Sprout Apex
  Jenis Produk   Aplikasi administrasi surat
  Platform       Aplikasi berbasis web
  Dokumen        Product Requirements Document
  Versi          1.0
  Status         Draft
  Tujuan         Dokumentasi tugas kuliah

------------------------------------------------------------------------

## 1. Product Overview

### 1.1 Deskripsi Produk

SAKTI (Sistem Administrasi dan Kelola Tata Informasi) merupakan aplikasi
administrasi surat berbasis digital yang dirancang untuk membantu proses
pengajuan, pemeriksaan, persetujuan, penerbitan, dan pengarsipan surat
di lingkungan fakultas.

Aplikasi ini bertujuan menggantikan proses administrasi surat yang
sebelumnya dilakukan secara manual menjadi proses digital yang lebih
terstruktur, transparan, dan efisien.

Melalui SAKTI, mahasiswa atau pihak pengusul dapat mengajukan surat
secara online, memantau status pengajuan, serta mengunduh dokumen surat
yang telah selesai diproses.

Sementara itu, Admin Tata Usaha (TU), Sekretaris Fakultas, dan Dekan
dapat menjalankan proses pemeriksaan serta persetujuan sesuai dengan
kewenangan masing-masing.

### 1.2 Latar Belakang

Proses administrasi surat di lingkungan fakultas membutuhkan koordinasi
antara pengusul dan beberapa pihak yang bertanggung jawab dalam
pemeriksaan maupun persetujuan surat.

Apabila proses tersebut dilakukan secara manual, beberapa permasalahan
dapat muncul, seperti:

-   Pengajuan surat membutuhkan proses komunikasi secara berulang.
-   Pengusul kesulitan mengetahui perkembangan status surat.
-   Pemeriksaan dan persetujuan surat membutuhkan koordinasi
    antarbagian.
-   Pengelolaan template surat berpotensi tidak konsisten.
-   Pencarian dokumen surat yang sudah diterbitkan membutuhkan waktu.
-   Pengarsipan surat masuk dan surat keluar belum terpusat.

Oleh karena itu, SAKTI dikembangkan sebagai solusi digital untuk
mengelola seluruh proses administrasi surat dalam satu sistem.

### 1.3 Visi Produk

Menjadi sistem administrasi surat digital yang terintegrasi, mudah
digunakan, transparan, dan mampu meningkatkan efisiensi pelayanan
administrasi di lingkungan fakultas.

## 2. Problem Statement

Permasalahan utama yang ingin diselesaikan oleh SAKTI adalah belum
optimalnya pengelolaan administrasi surat yang menyebabkan proses
pengajuan, pemeriksaan, persetujuan, penerbitan, dan pencarian dokumen
menjadi kurang efisien.

**Rumusan masalah:**

Bagaimana membangun aplikasi administrasi surat yang dapat mempermudah
pengajuan surat, mengelola tahapan persetujuan, memberikan informasi
status secara transparan, serta menyimpan arsip surat secara
terstruktur?

## 3. Product Goals

Tujuan utama pengembangan SAKTI adalah:

1.  Mendigitalisasi proses pengajuan surat.
2.  Mempermudah pemeriksaan dan persetujuan surat.
3.  Memberikan informasi status pengajuan secara transparan.
4.  Mempermudah pengelolaan template dan format surat.
5.  Menghasilkan dokumen surat final dalam format PDF.
6.  Menyediakan nomor surat dan QR Code untuk kebutuhan verifikasi
    dokumen.
7.  Mempermudah pencarian serta pengelolaan arsip surat masuk dan
    keluar.

### 3.1 Indikator Keberhasilan

  Indikator         Target keberhasilan
  ----------------- ----------------------------------------------
  Pengajuan surat   Dapat dilakukan secara online
  Pelacakan surat   Status dapat dilihat oleh pengusul
  Persetujuan       Setiap tahapan tercatat dalam sistem
  Dokumen final     Dapat diunduh dalam PDF
  Penomoran         Nomor surat tervalidasi dan unik
  Pengarsipan       Surat masuk dan keluar tersimpan terstruktur

*Target tersebut merupakan indikator awal untuk pengembangan dan
evaluasi produk, bukan hasil pengukuran yang sudah dicapai.*

## 4. Target Users

SAKTI memiliki empat peran utama.

  -----------------------------------------------------------------------
  Pengguna                            Deskripsi
  ----------------------------------- -----------------------------------
  Pengusul                            Pihak yang mengajukan permohonan
                                      pembuatan surat

  Admin TU                            Petugas yang memeriksa dan
                                      mengelola administrasi surat

  Sekretaris Fakultas                 Pihak yang melakukan pemeriksaan
                                      dan persetujuan pada tahap
                                      Sekretaris Fakultas

  Dekan                               Pihak yang memberikan keputusan
                                      akhir persetujuan surat
  -----------------------------------------------------------------------

### 4.1 User Roles and Permissions

  ----------------------------------------------------------------------------
  Fitur           Pengusul        Admin TU         SekFak           Dekan
  ------------ --------------- --------------- --------------- ---------------
  Login               ✓               ✓               ✓               ✓

  Mengajukan          ✓              \-              \-              \-
  surat                                                        

  Mengedit            ✓              \-              \-              \-
  draf                                                         

  Melihat             ✓               ✓               ✓               ✓
  status surat                                                 

  Memeriksa          \-               ✓               ✓               ✓
  pengajuan                                                    

  Mengelola          \-               ✓              \-              \-
  template                                                     

  Mengelola          \-               ✓              \-              \-
  nomor surat                                                  

  Memberikan         \-              \-              \-               ✓
  keputusan                                                    
  akhir                                                        

  Melihat        Sesuai hak           ✓          Sesuai hak      Sesuai hak
  arsip surat       akses                           akses           akses
  ----------------------------------------------------------------------------

**Catatan:** Hak akses terperinci untuk arsip dan beberapa tindakan
administratif perlu disepakati kembali dengan pihak klien.

## 5. Product Scope

Bagian ini menjelaskan fitur yang termasuk dalam pengembangan SAKTI.

### 5.1 In Scope

  -----------------------------------------------------------------------
  ID                      Modul                   Deskripsi
  ----------------------- ----------------------- -----------------------
  MOD-01                  Manajemen Akun          Login dan pengelolaan
                                                  akses pengguna

  MOD-02                  Pengajuan Surat         Membuat dan mengirim
                                                  pengajuan surat

  MOD-03                  Manajemen Draf          Menyimpan dan mengedit
                                                  pengajuan sebagai draf

  MOD-04                  Pengelolaan Template    Membuat, mengubah, dan
                                                  mengaktifkan template

  MOD-05                  Verifikasi Surat        Memeriksa kelengkapan
                                                  dan kebenaran pengajuan

  MOD-06                  Persetujuan Surat       Mengelola tahapan
                                                  persetujuan SekFak dan
                                                  Dekan

  MOD-07                  Pelacakan Status        Memantau perkembangan
                                                  pengajuan

  MOD-08                  Penomoran Surat         Mengelola dan
                                                  memvalidasi nomor surat

  MOD-09                  Dokumen Digital         Menghasilkan PDF final
                                                  dan QR Code verifikasi

  MOD-10                  Pengarsipan             Mengelola arsip surat
                                                  masuk dan surat keluar
  -----------------------------------------------------------------------

### 5.2 Out of Scope

Hal-hal berikut tidak menjadi bagian dari ruang lingkup utama dokumen
ini:

-   Integrasi otomatis dengan sistem akademik eksternal.
-   Integrasi dengan sistem keuangan universitas.
-   Pengiriman surat fisik secara otomatis.
-   Integrasi dengan layanan tanda tangan elektronik pihak ketiga.
-   Pengembangan aplikasi native Android dan iOS.

Batasan ini dapat berubah apabila terdapat kebutuhan baru yang disetujui
oleh klien.

## 6. Functional Requirements

Functional Requirements menjelaskan fungsi yang wajib disediakan oleh
sistem.

### 6.1 Manajemen Akun dan Akses

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-001                  Sistem menyediakan      Must Have
                          fitur login menggunakan 
                          email dan password.     

  FR-002                  Sistem memvalidasi      Must Have
                          kredensial pengguna.    

  FR-003                  Sistem mengarahkan      Must Have
                          pengguna ke dashboard   
                          sesuai role.            

  FR-004                  Sistem membatasi akses  Must Have
                          fitur berdasarkan hak   
                          akses pengguna.         
  -----------------------------------------------------------------------

### 6.2 Pengajuan Surat

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-005                  Pengusul dapat membuat  Must Have
                          pengajuan surat baru.   

  FR-006                  Sistem menyediakan      Must Have
                          formulir pengajuan      
                          sesuai jenis surat.     

  FR-007                  Pengusul dapat mengisi  Must Have
                          data kegiatan dan       
                          informasi pendukung.    

  FR-008                  Pengusul dapat          Must Have
                          menentukan waktu mulai  
                          dan selesai kegiatan.   

  FR-009                  Pengusul dapat          Must Have
                          menentukan peran setiap 
                          anggota yang terlibat.  

  FR-010                  Sistem memvalidasi      Must Have
                          kelengkapan data        
                          sebelum pengajuan       
                          dikirim.                

  FR-011                  Pengusul dapat mengirim Must Have
                          pengajuan kepada Admin  
                          TU.                     
  -----------------------------------------------------------------------

### 6.3 Manajemen Draf

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-012                  Pengusul dapat          Should Have
                          menyimpan pengajuan     
                          sebagai draf.           

  FR-013                  Pengusul dapat membuka  Should Have
                          kembali draf yang       
                          tersimpan.              

  FR-014                  Pengusul dapat mengubah Should Have
                          isi draf sebelum        
                          dikirim.                

  FR-015                  Draf tidak masuk ke     Should Have
                          antrean pemeriksaan     
                          Admin TU sebelum        
                          dikirim.                
  -----------------------------------------------------------------------

### 6.4 Pengelolaan Template Surat

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-016                  Admin TU dapat membuat  Must Have
                          template surat baru.    

  FR-017                  Admin TU dapat mengubah Must Have
                          template surat.         

  FR-018                  Admin TU dapat          Must Have
                          mengaktifkan template   
                          surat.                  

  FR-019                  Admin TU dapat          Must Have
                          menonaktifkan template  
                          surat.                  

  FR-020                  Sistem hanya            Must Have
                          menyediakan template    
                          yang berstatus aktif    
                          untuk pengajuan baru.   

  FR-021                  Perubahan template      Must Have
                          tidak mengubah dokumen  
                          surat yang sudah        
                          difinalisasi.           
  -----------------------------------------------------------------------

### 6.5 Verifikasi dan Persetujuan Surat

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-022                  Admin TU dapat melihat  Must Have
                          daftar pengajuan surat. 

  FR-023                  Admin TU dapat          Must Have
                          memeriksa kelengkapan   
                          pengajuan.              

  FR-024                  Admin TU dapat          Must Have
                          mengembalikan pengajuan 
                          untuk diperbaiki.       

  FR-025                  Pengajuan yang lolos    Must Have
                          pemeriksaan TU dapat    
                          diteruskan kepada       
                          SekFak.                 

  FR-026                  SekFak dapat memeriksa  Must Have
                          surat setelah           
                          verifikasi TU.          

  FR-027                  Surat yang lolos        Must Have
                          pemeriksaan SekFak      
                          dapat diteruskan kepada 
                          Dekan.                  

  FR-028                  Dekan dapat menyetujui  Must Have
                          atau menolak pengajuan  
                          surat.                  

  FR-029                  Sistem menyimpan        Must Have
                          keputusan dan alasan    
                          penolakan.              

  FR-030                  Sistem mencatat riwayat Must Have
                          proses pemeriksaan dan  
                          persetujuan.            
  -----------------------------------------------------------------------

**Alur persetujuan utama:**

1.  Pengusul mengirim pengajuan surat.
2.  Admin TU memeriksa administrasi dan kelengkapan.
3.  Sekretaris Fakultas melakukan pemeriksaan dan persetujuan tahap
    SekFak.
4.  Dekan memberikan keputusan akhir.
5.  Surat yang disetujui diproses untuk penerbitan.

Apabila pengajuan ditolak atau dikembalikan, sistem harus memberikan
informasi kepada pihak terkait sesuai tahapan prosesnya.

### 6.6 Pelacakan Status Pengajuan

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-031                  Pengusul dapat melihat  Must Have
                          daftar pengajuan surat. 

  FR-032                  Pengusul dapat melihat  Must Have
                          status setiap           
                          pengajuan.              

  FR-033                  Sistem memperbarui      Must Have
                          status berdasarkan      
                          tahapan proses.         

  FR-034                  Sistem menampilkan      Should Have
                          riwayat perubahan       
                          status.                 

  FR-035                  Pengusul dapat          Must Have
                          mengetahui apabila      
                          surat dikembalikan      
                          untuk diperbaiki.       
  -----------------------------------------------------------------------

Status yang direncanakan:

-   Draft
-   Diajukan
-   Dalam Pemeriksaan Admin TU
-   Dalam Pemeriksaan Sekretaris Fakultas
-   Menunggu Persetujuan Dekan
-   Ditolak
-   Perlu Revisi
-   Disetujui
-   Selesai

### 6.7 Penomoran dan Dokumen Digital

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-036                  Admin TU dapat          Must Have
                          mengelola penomoran     
                          surat.                  

  FR-037                  Sistem memvalidasi agar Must Have
                          nomor surat tidak       
                          duplikat.               

  FR-038                  Sistem menghasilkan     Must Have
                          dokumen surat           
                          berdasarkan template.   

  FR-039                  Sistem menghasilkan     Must Have
                          dokumen final dalam     
                          format PDF.             

  FR-040                  Sistem menghasilkan QR  Should Have
                          Code untuk verifikasi   
                          surat.                  

  FR-041                  Pengusul dapat          Must Have
                          mengunduh surat yang    
                          telah selesai diproses. 

  FR-042                  Dokumen final tidak     Must Have
                          dapat diubah melalui    
                          perubahan template.     
  -----------------------------------------------------------------------

### 6.8 Pengarsipan Surat

  -----------------------------------------------------------------------
  ID                      Requirement             Prioritas
  ----------------------- ----------------------- -----------------------
  FR-043                  Admin TU dapat melihat  Must Have
                          arsip surat masuk dan   
                          surat keluar.           

  FR-044                  Sistem menyimpan nomor  Must Have
                          surat, tanggal,         
                          perihal, dan jenis      
                          surat.                  

  FR-045                  Admin TU dapat melihat  Must Have
                          detail arsip surat.     

  FR-046                  Admin TU dapat mencari  Should Have
                          arsip berdasarkan nomor 
                          surat dan perihal.      

  FR-047                  Admin TU dapat          Should Have
                          memfilter arsip         
                          berdasarkan tanggal dan 
                          jenis surat.            
  -----------------------------------------------------------------------

## 7. Non-Functional Requirements

Non-Functional Requirements menjelaskan standar kualitas yang harus
dipenuhi aplikasi.

  -----------------------------------------------------------------------
  ID                      Kategori                Kebutuhan
  ----------------------- ----------------------- -----------------------
  NFR-001                 Security                Sistem harus menerapkan
                                                  autentikasi pengguna.

  NFR-002                 Authorization           Pengguna hanya dapat
                                                  mengakses fitur sesuai
                                                  role.

  NFR-003                 Data Integrity          Data pengajuan dan
                                                  dokumen harus tersimpan
                                                  secara konsisten.

  NFR-004                 Performance             Sistem harus memberikan
                                                  respons yang wajar
                                                  ketika pengguna
                                                  menjalankan fitur.

  NFR-005                 Usability               Antarmuka harus mudah
                                                  dipahami dan digunakan.

  NFR-006                 Compatibility           Aplikasi dapat diakses
                                                  melalui browser modern.

  NFR-007                 Reliability             Sistem harus menjaga
                                                  agar data pengajuan
                                                  tidak hilang ketika
                                                  proses penyimpanan
                                                  berhasil.

  NFR-008                 Maintainability         Sistem harus mudah
                                                  diperbaiki dan
                                                  dikembangkan.

  NFR-009                 Auditability            Sistem menyimpan
                                                  riwayat tindakan
                                                  penting dalam proses
                                                  administrasi.
  -----------------------------------------------------------------------

Target angka performa, kapasitas pengguna, dan ketersediaan layanan
perlu ditentukan setelah kebutuhan operasional dikonfirmasi.

## 8. User Flow

### 8.1 Alur Pengajuan Surat oleh Pengusul

1.  Pengusul melakukan login.
2.  Pengusul masuk ke dashboard.
3.  Pengusul memilih menu pengajuan surat.
4.  Pengusul memilih jenis surat.
5.  Pengusul mengisi formulir pengajuan.
6.  Pengusul melengkapi data dan dokumen pendukung.
7.  Sistem melakukan validasi.
8.  Pengusul menyimpan sebagai draf atau mengirim pengajuan.
9.  Pengajuan yang dikirim masuk ke antrean Admin TU.
10. Pengusul dapat memantau status pengajuan.

### 8.2 Alur Pemeriksaan dan Persetujuan

1.  Admin TU menerima pengajuan.
2.  Admin TU memeriksa data dan kelengkapan dokumen.
3.  Jika terdapat kekurangan, pengajuan dikembalikan untuk diperbaiki.
4.  Jika memenuhi persyaratan, pengajuan diteruskan ke SekFak.
5.  SekFak melakukan pemeriksaan dan meneruskan pengajuan.
6.  Dekan memberikan keputusan akhir.
7.  Jika disetujui, surat diproses untuk penerbitan.
8.  Admin TU menyelesaikan penomoran dan dokumen final sesuai prosedur.
9.  Sistem menyimpan dokumen ke arsip.
10. Pengusul dapat mengunduh surat yang telah selesai.

### 8.3 Alur Pengarsipan

1.  Admin TU membuka menu arsip.
2.  Sistem menampilkan daftar surat masuk dan keluar.
3.  Admin TU memilih jenis arsip.
4.  Admin TU dapat melakukan pencarian atau filter.
5.  Admin TU memilih surat untuk melihat detail.
6.  Sistem menampilkan informasi surat dan dokumen terkait.

## 9. UI/UX Requirements

Antarmuka SAKTI harus dirancang dengan memperhatikan kemudahan
penggunaan dan konsistensi tampilan.

  Halaman              Kebutuhan
  -------------------- -------------------------------------------
  Login                Form email dan password
  Dashboard            Ringkasan aktivitas dan status surat
  Pengajuan Surat      Form pengisian data surat
  Daftar Pengajuan     Tabel daftar surat dan status
  Detail Pengajuan     Informasi lengkap dan riwayat proses
  Manajemen Template   Daftar dan pengaturan template
  Pemeriksaan Surat    Detail pengajuan dan tindakan pemeriksaan
  Persetujuan Surat    Informasi surat dan tindakan keputusan
  Arsip Surat          Daftar arsip, pencarian, dan filter
  Detail Arsip         Informasi dan dokumen surat

**Prinsip desain:**

-   Navigasi sederhana dan konsisten.
-   Informasi status mudah dibedakan.
-   Formulir memiliki label yang jelas.
-   Pesan kesalahan mudah dipahami.
-   Tampilan responsif pada perangkat yang didukung.
-   Setiap role mendapatkan tampilan sesuai kebutuhan tugasnya.

## 10. Acceptance Criteria

Setiap fitur harus memiliki kriteria penerimaan agar dapat diuji oleh
tim QA.

### AC-01: Login

1.  Pengguna berhasil masuk menggunakan email dan password yang valid.
2.  Sistem menolak kredensial yang tidak valid.
3.  Dashboard yang ditampilkan sesuai dengan role pengguna.

### AC-02: Pengajuan Surat

1.  Pengusul dapat memilih jenis surat yang tersedia.
2.  Pengusul dapat mengisi seluruh data wajib.
3.  Sistem menolak pengajuan apabila data wajib belum lengkap.
4.  Pengajuan yang berhasil dikirim muncul dalam daftar pengajuan.

### AC-03: Penyimpanan Draf

1.  Pengusul dapat menyimpan pengajuan tanpa mengirimkannya.
2.  Draf dapat dibuka kembali.
3.  Draf dapat diedit sebelum dikirim.
4.  Draf tidak muncul di antrean pemeriksaan Admin TU.

### AC-04: Persetujuan Surat

1.  Admin TU dapat memeriksa pengajuan.
2.  Pengajuan yang lolos pemeriksaan dapat diteruskan kepada SekFak.
3.  SekFak dapat memeriksa dan meneruskan surat kepada Dekan.
4.  Dekan dapat menyetujui atau menolak pengajuan.
5.  Sistem menyimpan keputusan dan alasan penolakan jika ada.

### AC-05: Dokumen Final

1.  Sistem menghasilkan dokumen berdasarkan template aktif yang
    digunakan saat proses pembuatan.
2.  Nomor surat harus unik.
3.  Dokumen final dapat diunduh dalam format PDF.
4.  QR Code dapat digunakan untuk memverifikasi keaslian surat sesuai
    mekanisme yang ditetapkan.

### AC-06: Arsip Surat

1.  Admin TU dapat melihat arsip surat masuk dan keluar.
2.  Informasi nomor, tanggal, perihal, dan jenis surat ditampilkan.
3.  Admin TU dapat membuka detail arsip.
4.  Pencarian dan filter menampilkan data sesuai kriteria.

## 11. Technical Requirements

Bagian ini menjelaskan kebutuhan teknis awal untuk pengembangan.

  -----------------------------------------------------------------------
  Komponen                            Kebutuhan
  ----------------------------------- -----------------------------------
  Platform                            Web Application

  Frontend                            Antarmuka aplikasi berbasis web

  Backend                             Pengelolaan proses bisnis dan API

  Database                            Penyimpanan data pengguna,
                                      pengajuan, dan arsip

  Authentication                      Autentikasi pengguna

  Authorization                       Pengaturan akses berdasarkan role

  Document Generation                 Pembuatan dokumen surat dan PDF

  QR Code                             Pembuatan dan verifikasi QR Code

  File Storage                        Penyimpanan dokumen pendukung dan
                                      surat final

  Deployment                          Server yang dapat diakses pengguna
                                      sesuai lingkungan penerapan
  -----------------------------------------------------------------------

Teknologi spesifik belum ditetapkan dalam PRD ini agar keputusan
implementasi dapat disesuaikan dengan hasil diskusi tim dan kebutuhan
klien.

## 12. Data Requirements

Data utama yang perlu dikelola oleh sistem:

  -----------------------------------------------------------------------
  Entitas                             Data utama
  ----------------------------------- -----------------------------------
  User                                ID, nama, email, password
                                      terenkripsi, role

  Pengajuan Surat                     ID pengajuan, pengusul, jenis
                                      surat, tanggal pengajuan, status

  Detail Pengajuan                    Informasi kegiatan, waktu, lokasi,
                                      dan keterangan

  Personil                            Nama anggota dan peran dalam
                                      kegiatan

  Template Surat                      ID template, nama, isi template,
                                      status

  Persetujuan                         ID pengajuan, pemeriksa, keputusan,
                                      catatan, waktu

  Nomor Surat                         Nomor surat, tanggal, jenis surat

  Dokumen                             ID dokumen, lokasi file, format,
                                      versi

  QR Verification                     Kode verifikasi dan referensi
                                      dokumen

  Arsip                               Nomor surat, tanggal surat,
                                      perihal, jenis surat
  -----------------------------------------------------------------------

Data tersebut menjadi gambaran awal kebutuhan database dan belum
merupakan rancangan skema database final.

## 13. Business Rules

Aturan bisnis yang harus diperhatikan dalam pengembangan SAKTI:

1.  Setiap pengguna harus memiliki akun dan role yang sesuai.
2.  Pengusul hanya dapat mengelola pengajuan miliknya sendiri.
3.  Pengajuan harus memenuhi kelengkapan data sebelum dikirim.
4.  Draf tidak masuk ke proses pemeriksaan sebelum dikirim.
5.  Pemeriksaan dan persetujuan mengikuti urutan Admin TU, SekFak,
    kemudian Dekan.
6.  Keputusan penolakan harus disertai alasan.
7.  Nomor surat tidak boleh digunakan lebih dari satu kali.
8.  Template yang tidak aktif tidak dapat digunakan untuk pengajuan
    baru.
9.  Perubahan template tidak boleh mengubah surat yang telah
    difinalisasi.
10. Surat final harus tersimpan sebagai dokumen digital.
11. Setiap proses penting harus tercatat dalam riwayat aktivitas.

## 14. Risks and Dependencies

  -----------------------------------------------------------------------
  Risiko / Ketergantungan Dampak                  Penanganan
  ----------------------- ----------------------- -----------------------
  Format nomor surat      Kesalahan penomoran     Konfirmasi format
  belum ditetapkan                                kepada klien

  Alur persetujuan        Perubahan proses sistem Validasi alur dengan
  berubah                                         pihak fakultas

  Template surat belum    Penerbitan surat        Kumpulkan template
  lengkap                 terhambat               resmi

  Hak akses belum         Kesalahan akses data    Validasi role dan
  terdefinisi detail                              permission

  Mekanisme QR Code belum Verifikasi dokumen      Tentukan metode
  ditentukan              tidak optimal           verifikasi

  Dokumen pendukung tidak Pengajuan terhambat     Tetapkan persyaratan
  lengkap                                         tiap jenis surat
  -----------------------------------------------------------------------

## 15. Assumptions and Open Questions

### 15.1 Asumsi

-   Setiap pengguna telah memiliki akun yang terdaftar.
-   Setiap jenis surat memiliki format dan persyaratan tertentu.
-   Proses persetujuan mengikuti struktur organisasi fakultas.
-   Admin TU bertanggung jawab atas pengelolaan administrasi surat.
-   Dokumen final disimpan secara digital.

### 15.2 Pertanyaan yang Perlu Dikonfirmasi kepada Klien

1.  Apakah terdapat jenis surat tertentu yang memiliki alur persetujuan
    berbeda?
2.  Bagaimana format resmi penomoran surat?
3.  Apakah Sekretaris Fakultas selalu menjadi tahap wajib sebelum Dekan?
4.  Siapa yang berwenang menerbitkan dan mengesahkan dokumen final?
5.  Apakah QR Code harus mengarah ke halaman verifikasi publik?
6.  Apakah arsip surat masuk juga dapat ditambahkan secara manual?
7.  Apakah pengusul diperbolehkan mengubah pengajuan setelah masuk
    proses pemeriksaan?

## 16. Product Priority

Prioritas pengembangan awal menggunakan metode MoSCoW.

  -----------------------------------------------------------------------
  Prioritas               Keterangan              Contoh
  ----------------------- ----------------------- -----------------------
  Must Have               Fitur wajib tersedia    Login, pengajuan,
                                                  pemeriksaan,
                                                  persetujuan

  Should Have             Penting tetapi bukan    Penyimpanan draf,
                          kebutuhan utama awal    pencarian arsip

  Could Have              Fitur tambahan          Penyempurnaan
                                                  notifikasi

  Won't Have              Tidak dikerjakan dalam  Fitur yang telah
                          ruang lingkup saat ini  dikeluarkan dari
                                                  kesepakatan proyek
  -----------------------------------------------------------------------

Penetapan prioritas final mengikuti keputusan klien dan Product Owner.
Item backlog yang sudah diputuskan Won't Have tidak boleh otomatis
dimasukkan kembali ke Sprint 2.

## 17. Definition of Done (DoD)

Sebuah fitur dinyatakan selesai apabila:

1.  Fitur sudah dikembangkan sesuai requirement.
2.  Acceptance Criteria telah terpenuhi.
3.  Tidak terdapat bug kritis yang belum diselesaikan.
4.  Fitur telah diuji oleh tim QA.
5.  Integrasi dengan bagian sistem terkait berjalan dengan baik.
6.  Dokumentasi fitur telah diperbarui.
7.  Fitur telah ditinjau dan disetujui sesuai prosedur tim.

## 18. Document Approval

Dokumen PRD ini menjadi acuan awal bagi tim Sprout Apex dalam melakukan
pengembangan aplikasi SAKTI.

  -----------------------------------------------------------------------
  Pihak                   Tanggung Jawab          Persetujuan
  ----------------------- ----------------------- -----------------------
  Product Owner           Memvalidasi kebutuhan   Belum disetujui
                          produk                  

  Tim Sprout Apex         Mengembangkan aplikasi  Belum disetujui

  Klien / Perwakilan      Menyetujui kebutuhan    Belum disetujui
  Fakultas                dan alur bisnis         
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 19. Catatan Penyelesaian PRD

Dokumen ini mencakup kebutuhan produk dari gambaran umum hingga kriteria
penerimaan dan aturan bisnis.

Sebelum PRD dianggap final, beberapa hal perlu divalidasi:

-   Alur persetujuan dengan klien.
-   Format nomor surat.
-   Aturan QR Code.
-   Hak akses setiap pengguna.
-   Prioritas fitur bersama Product Owner.

**Status dokumen: Draft PRD 1.0**, belum menjadi dokumen kebutuhan yang
disahkan klien.
