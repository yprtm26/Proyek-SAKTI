<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisSurat;

class JenisSuratDosenSeeder extends Seeder
{
    public function run(): void
    {
        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-penulisan-publikasi-artikel-ilmiah'
            ],
            [
                'nama' => 'Surat Tugas Penulisan & Publikasi Artikel Ilmiah',
                'slug' => 'surat-tugas-penulisan-publikasi-artikel-ilmiah',
                'kode' => 'ST PN.10',
                'target_user' => 'dosen', 
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_dosen',
                    'judul_artikel',
                    'tujuan_penulisan',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-penulisan-laporan-penelitian'
            ],
            [
                'nama' => 'Surat Tugas Penulisan Laporan Penelitian',
                'slug' => 'surat-tugas-penulisan-laporan-penelitian',
                'kode' => 'ST PN.10',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'informasi_penelitian',
                    'nama_dosen',
                    'tujuan_penulisan',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-pelatihan'
            ],
            [
                'nama' => 'Surat Tugas Pelatihan',
                'slug' => 'surat-tugas-pelatihan',
                'kode' => 'ST KP.40',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_dosen',
                    'nama_pelatihan',
                    'tanggal_pelatihan',
                    'waktu_pelatihan',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-pendaftaran-hak-kekayaan-intelektual'
            ],
            [
                'nama' => 'Surat Tugas Pendaftaran Hak Kekayaan Intelektual',
                'slug' => 'surat-tugas-pendaftaran-hak-kekayaan-intelektual',
                'kode' => 'ST PN.20',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'anggota',
                    'judul_karya',
                    'tanggal_pendaftaran',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-juri-lomba'
            ],
            [
                'nama' => 'Surat Tugas Juri Lomba',
                'slug' => 'surat-tugas-juri-lomba',
                'kode' => 'ST KP.40.03',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_lomba',
                    'nama_dosen',
                    'informasi_lomba',
                    'nama_lomba',
                    'tanggal_lomba',
                    'waktu_lomba',
                    'tempat_lomba',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-narasumber'
            ],
            [
                'nama' => 'Surat Tugas Narasumber',
                'slug' => 'surat-tugas-narasumber',
                'kode' => 'ST KP.40.02',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'informasi',
                    'nama_dosen',
                    'keperluan',
                    'tanggal',
                    'waktu',
                    'tempat',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-pengembangan-rps'
            ],
            [
                'nama' => 'Surat Tugas Pengembangan RPS',
                'slug' => 'surat-tugas-pengembangan-rps',
                'kode' => 'ST PN.00',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_dosen',
                    'nama_matkul',
                    'semester',
                    'tahun_akademik',
                    'prodi',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-penulisan-buku-ajar'
            ],
            [
                'nama' => 'Surat Tugas Penulisan Buku Ajar',
                'slug' => 'surat-tugas-penulisan-buku-ajar',
                'kode' => 'ST PN.20',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'anggota',
                    'judul',
                    'prodi',
                    'tanggal',
                    'tempat',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-penulisan-dan-publikasi-penelitian'
            ],
            [
                'nama' => 'Surat Tugas Penulisan Dan Publikasi Penelitian',
                'slug' => 'surat-tugas-penulisan-dan-publikasi-penelitian',
                'kode' => 'ST PN.10',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'anggota',
                    'judul',
                    'nama_jurnal',
                    'link_jurnal',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-kegiatan-kuliah'
            ],
            [
                'nama' => 'Surat Tugas Kegiatan Kuliah',
                'slug' => 'surat-tugas-kegiatan-kuliah',
                'kode' => 'ST PP.20.05',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_penugas',
                    'nama_ditugaskan',
                    'jabatan_penugas',
                    'jabatan_ditugaskan',
                    'no_nip',
                    'instansi_penugas',
                    'instansi_ditugaskan',
                    'nama_kegiatan',
                    'sks',
                    'tanggal',
                    'waktu',
                    'tempat',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );

        JenisSurat::updateOrCreate(
            [
                'slug' => 'surat-tugas-pengembangan-modul'
            ],
            [
                'nama' => 'Surat Tugas Pengembangan Modul',
                'slug' => 'surat-tugas-pengembangan-modul',
                'kode' => 'ST PN.00',
                'target_user' => 'dosen', // 🔑 PEMBEDA UTAMA
                'aktif' => true,

                // FIELD FORM DOSEN
                'fields' => [
                    'nama_dosen',
                    'nama_matkul',
                    'semester',
                    'tahun_akademik',
                    'prodi',
                ],

                // ALUR VALIDASI
                'alur' => [
                    'admin',
                    'dekan'
                ],

                'perlu_dekan' => true,
            ]
        );
    }
}
