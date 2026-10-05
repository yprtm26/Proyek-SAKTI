<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisSurat;

class JenisSuratSeeder extends Seeder
{
    public function run(): void
    {
        JenisSurat::updateOrCreate(
        ['slug' => 'surat-keterangan-aktif'],
        [
            'nama' => 'Surat Keterangan Aktif',
            'kode' => 'SKet PP.30.02',
            'alur' => ['admin', 'kaprodi'],
            'scope_nomor' => 'PRODI',
            'fields' => ['semester', 'tahun_akademik'],
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-observasi-penelitian-skripsi'],
        [
            'nama' => 'Surat Observasi Penelitian Skripsi',
            'kode' => 'PP.20.14',
            'alur' => ['admin', 'dekan'],
            'scope_nomor' => 'FTI',
            'fields' => [
                'perihal',
                'kepada',
                'instansi',
                'kota_instansi',
                'jenis_kegiatan',
                'judul_skripsi',
            ],
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-observasi-tugas-mata-kuliah'],
        [
            'nama'  => 'Surat Observasi Tugas Mata Kuliah',
            'kode'  => 'PP.20.05',
            'alur'  => ['admin', 'dekan'],
            'scope_nomor' => 'FTI',
            'fields'=> [
                'perihal',
                'kepada_yth',
                'nama_instansi',
                'kota_instansi',
                'mata_kuliah',
                'jenis_kegiatan',
                'anggota_tim', // tabel anggota (nama, npm)
            ],
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-pengantar-remedial-bahasa-inggris'],
        [
            'nama'  => 'Surat Pengantar Remedial (OLC)',
            'kode'  => 'PP.20.08',
            'alur'  => ['admin', 'kaprodi'],
            'scope_nomor' => 'PRODI',
            'fields'=> [
                'mahasiswa', // tabel mahasiswa (nama, npm, pilih mata kuliah)
            ],
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-permohonan-aktif-akademik'],
        [
            'nama'  => 'Surat Permohonan Aktif Akademik',
            'kode'  => null, // TIDAK ADA NOMOR SURAT
            'alur'  => ['admin'],
            'scope_nomor' => null,
            'fields'=> [
                'semester',  // ganjil / genap
                'tahun_akademik',
                'batas_pembayaran',
                'nominal_pembayaran',
            ],
            'ttd_qr_type' => 'mahasiswa',
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-persetujuan-aktif-akademik'],
        [
            'nama' => 'Surat Persetujuan Aktif Akademik',
            'kode' => null,
            'scope_nomor' => null,
            'target_user' => 'mahasiswa',
            'fields' => [], 
            'alur' => ['kaprodi'], // langsung kaprodi
            'aktif' => false, // tidak muncul di dashboard mahasiswa
        ]
    );


    JenisSurat::updateOrCreate(
        ['slug' => 'surat-permohonan-magang'], // reguler_ti, reguler_pdsi, mandiri
        [
            'nama'  => 'Surat Permohonan Magang',
            'kode'  => 'PP.20.11',
            'alur'  => ['admin', 'dekan'],
            'scope_nomor' => 'FTI',
            'fields'=> [
                'tipe_magang',
                'perihal',
                'kepada_yth',
                'nama_mitra',
                'alamat_mitra',
                'tanggal_mulai',
                'tanggal_selesai',
                'mahasiswa', // array mhs
            ],
            'aktif' => true,
        ]
    );

    JenisSurat::updateOrCreate(
        ['slug' => 'surat-rekomendasi'], // ada 2: magang dan melamar pekerjaan
        [
            'nama' => 'Surat Rekomendasi',
            'kode' => 'SKet PP.20.11',
            'scope_nomor' => 'PRODI',
            'alur' => ['admin', 'kaprodi'],
            'fields' => [
                'ipk',
                'sks',
                'tujuan',
                'pengalaman',
                'project'
            ],
            'aktif' => true,
        ]
    );

    }
}
