<?php

if (!function_exists('statusMahasiswa')) {
    function statusMahasiswa($status)
    {
        return match ($status) {
            'menunggu' => 'Menunggu Verifikasi TU',
            'menunggu_kaprodi' => 'Menunggu Kaprodi',
            'menunggu_dekan' => 'Menunggu Dekan',
            'disetujui_final' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($status),
        };
    }
}

// =======================
// STATUS DOSEN --BARU--
// =======================
if (!function_exists('statusDosen')) {
    function statusDosen($status)
    {
        return match ($status) {
            'menunggu' => 'Menunggu Verifikasi TU',
            'menunggu_kaprodi' => 'Menunggu Persetujuan Kaprodi',
            'menunggu_dekan' => 'Menunggu Persetujuan Dekan',
            'disetujui_final' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}