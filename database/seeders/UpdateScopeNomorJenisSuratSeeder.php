<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisSurat;

class UpdateScopeNomorJenisSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        // Surat berbasis PRODI
        JenisSurat::whereIn('slug', [
            'surat-keterangan-aktif',
        ])->update([
            'scope_nomor' => 'PRODI'
        ]);

        // Surat berbasis FTI
        JenisSurat::whereIn('slug', [
            'surat-observasi-penelitian-skripsi',
        ])->update([
            'scope_nomor' => 'FTI'
        ]);
    }
}
