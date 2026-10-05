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
