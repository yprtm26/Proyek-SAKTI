<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'TI', 'nama' => 'Teknik Informatika', 'urutan' => 1],
            ['kode' => 'PdSI', 'nama' => 'Perpustakaan dan Sains Informasi', 'urutan' => 2],
        ];

        foreach ($data as $prodi) {
            Prodi::updateOrCreate(
                ['kode' => $prodi['kode']],
                $prodi + ['jenjang' => 'S1', 'aktif' => true]
            );
        }
    }
}
