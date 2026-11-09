<?php

if (!function_exists('bulanRomawi')) {
    function bulanRomawi($bulan)
    {
        return match ($bulan) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
            default => '-',
        };
    }
}

if (!function_exists('namaProdi')) {
    function namaProdi($kode)
    {
        return match ($kode) {
            'TI'   => 'Teknik Informatika',
            'PdSI' => 'Perpustakaan dan Sains Informasi',
            default => '-',
        };
    }
}

if (!function_exists('namaKaprodi')) {
    function namaKaprodi($kode)
    {
        return match ($kode) {
            'TI'   => 'Elan Suherlan, M.Si.',
            'PdSI' => 'Danang Dwijo Kangko, S.Hum., M.P.',
            default => '-',
        };
    }
}


if (!function_exists('namaPenandatangan')) {
    function namaPenandatangan($surat)
    {
        return $surat->penandatangan ?? '-';
    }
}

if (!function_exists('labelFieldSurat')) {
    /**
     * Label ramah pengguna untuk field dinamis template surat.
     */
    function labelFieldSurat($field)
    {
        $kamus = [
            'nama' => 'Nama',
            'npm' => 'NPM',
            'nidn' => 'NIDN',
            'no_nip' => 'NIP/NIDN',
            'prodi' => 'Program Studi',
            'tahun_akademik' => 'Tahun Akademik',
            'ipk' => 'IPK',
            'sks' => 'SKS',
            'kepada_yth' => 'Kepada Yth.',
            'nama_instansi' => 'Nama Instansi',
            'kota_instansi' => 'Kota Instansi',
            'jenis_kegiatan' => 'Jenis Kegiatan',
            'mata_kuliah' => 'Mata Kuliah',
            'judul_skripsi' => 'Judul Skripsi',
            'batas_pembayaran' => 'Batas Pembayaran',
            'nominal_pembayaran' => 'Nominal Pembayaran',
            'tipe_magang' => 'Tipe Magang',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_selesai' => 'Tanggal Selesai',
            'no_kontak' => 'No. Kontak',
            'nama_mitra' => 'Nama Mitra',
            'alamat_mitra' => 'Alamat Mitra',
            'link_jurnal' => 'Link Jurnal',
            'nama_jurnal' => 'Nama Jurnal',
            'nama_dosen' => 'Nama Dosen',
            'nama_pelatihan' => 'Nama Pelatihan',
            'tanggal_pelatihan' => 'Tanggal Pelatihan',
            'waktu_pelatihan' => 'Waktu Pelatihan',
            'judul_artikel' => 'Judul Artikel',
            'tujuan_penulisan' => 'Tujuan Penulisan',
            'informasi_penelitian' => 'Informasi Penelitian',
            'nama_lomba' => 'Nama Lomba',
            'informasi_lomba' => 'Informasi Lomba',
            'tanggal_lomba' => 'Tanggal Lomba',
            'waktu_lomba' => 'Waktu Lomba',
            'tempat_lomba' => 'Tempat Lomba',
            'informasi' => 'Informasi',
            'keperluan' => 'Keperluan',
            'judul_karya' => 'Judul Karya',
            'tanggal_pendaftaran' => 'Tanggal Pendaftaran',
            'nama_matkul' => 'Nama Mata Kuliah',
            'nama_penugas' => 'Nama Penugas',
            'nama_ditugaskan' => 'Nama Ditugaskan',
            'jabatan_penugas' => 'Jabatan Penugas',
            'jabatan_ditugaskan' => 'Jabatan Ditugaskan',
            'instansi_penugas' => 'Instansi Penugas',
            'instansi_ditugaskan' => 'Instansi Ditugaskan',
            'nama_kegiatan' => 'Nama Kegiatan',
            'deskripsi' => 'Deskripsi',
            'keterangan' => 'Keterangan',
            'anggota' => 'Anggota',
            'anggota_tim' => 'Anggota Tim',
            'mahasiswa' => 'Mahasiswa',
            'pengalaman' => 'Pengalaman',
            'project' => 'Proyek',
            'perihal' => 'Perihal',
            'kepada' => 'Kepada',
            'instansi' => 'Instansi',
            'tempat' => 'Tempat',
            'tanggal' => 'Tanggal',
            'waktu' => 'Waktu',
            'semester' => 'Semester',
            'judul' => 'Judul',
        ];

        return $kamus[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
}

if (!function_exists('tipeFieldSurat')) {
    /**
     * Tebak tipe input yang tepat dari nama field template.
     */
    function tipeFieldSurat($field)
    {
        if (in_array($field, [
            'perihal',
            'deskripsi',
            'keterangan',
            'informasi',
            'informasi_penelitian',
            'informasi_lomba',
            'alamat_mitra',
            'keperluan',
            'tujuan_penulisan',
            'judul_skripsi',
            'judul_artikel',
            'judul_karya',
            'judul',
            'nama_kegiatan',
        ], true)) {
            return 'textarea';
        }

        if ($field === 'tanggal' || str_starts_with($field, 'tanggal') || str_ends_with($field, '_tanggal')) {
            return 'date';
        }

        if ($field === 'waktu' || str_starts_with($field, 'waktu')) {
            return 'time';
        }

        if (in_array($field, ['nominal_pembayaran', 'sks'], true)) {
            return 'number';
        }

        if ($field === 'ipk') {
            return 'number';
        }

        if (str_contains($field, 'link')) {
            return 'url';
        }

        if ($field === 'email') {
            return 'email';
        }

        return 'text';
    }
}
