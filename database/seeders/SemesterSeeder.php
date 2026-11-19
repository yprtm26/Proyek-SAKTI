<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        // Semester paruh (Ganjil/Genap) untuk form akademik.
        $paruh = [
            ['kode' => 'ganjil', 'nama' => 'Ganjil', 'urutan' => 1],
            ['kode' => 'genap', 'nama' => 'Genap', 'urutan' => 2],
        ];

        foreach ($paruh as $semester) {
            Semester::updateOrCreate(
                ['kode' => $semester['kode']],
                $semester + ['jenis' => 'paruh', 'aktif' => true]
            );
        }

        // Semester tingkat (1-8) untuk form magang dan sejenisnya.
        for ($tingkat = 1; $tingkat <= 8; $tingkat++) {
            Semester::updateOrCreate(
                ['kode' => (string) $tingkat],
                [
                    'nama' => 'Semester ' . $tingkat,
                    'jenis' => 'tingkat',
                    'urutan' => $tingkat,
                    'aktif' => true,
                ]
            );
        }
    }
}
