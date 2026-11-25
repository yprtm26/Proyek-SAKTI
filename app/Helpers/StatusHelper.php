<?php

if (! function_exists('statusMahasiswa')) {
    function statusMahasiswa($status)
    {
        return match ($status) {
            'draf'                 => 'Draf',
            'menunggu'             => 'Menunggu Verifikasi TU',
            'diverifikasi_tu'      => 'Diverifikasi TU',
            'perlu_revisi'         => 'Perlu Revisi',
            'menunggu_kaprodi'     => 'Menunggu Kaprodi',
            'menunggu_dekan'       => 'Menunggu Dekan',
            'menunggu_admin_final' => 'Menunggu Finalisasi Admin',
            'disetujui_final'      => 'Disetujui',
            'ditolak'              => 'Ditolak',
            default                => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }
}

if (! function_exists('statusDosen')) {
    function statusDosen($status)
    {
        return match ($status) {
            'draf'                 => 'Draf',
            'menunggu'             => 'Menunggu Verifikasi TU',
            'diverifikasi_tu'      => 'Diverifikasi TU',
            'perlu_revisi'         => 'Perlu Revisi',
            'menunggu_kaprodi'     => 'Menunggu Persetujuan Kaprodi',
            'menunggu_dekan'       => 'Menunggu Persetujuan Dekan',
            'menunggu_admin_final' => 'Menunggu Finalisasi Admin',
            'disetujui_final'      => 'Disetujui',
            'ditolak'              => 'Ditolak',
            default                => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }
}

if (! function_exists('statusBadgeMeta')) {
    /**
     * B-27: Metadata badge status pengajuan dengan rasio kontras teks >= 4.5:1 (WCAG AA).
     *
     * @return array{class: string, bg: string, fg: string}
     */
    function statusBadgeMeta(string $status): array
    {
        return match ($status) {
            'draf' => [
                'class' => 'sakti-badge sakti-badge-draf',
                'bg' => '#374151',
                'fg' => '#FFFFFF',
            ],
            'diverifikasi_tu' => [
                'class' => 'sakti-badge sakti-badge-diverifikasi',
                'bg' => '#1D4ED8',
                'fg' => '#FFFFFF',
            ],
            'perlu_revisi' => [
                'class' => 'sakti-badge sakti-badge-revisi',
                'bg' => '#B45309',
                'fg' => '#FFFFFF',
            ],
            'disetujui_final' => [
                'class' => 'sakti-badge sakti-badge-disetujui',
                'bg' => '#15803D',
                'fg' => '#FFFFFF',
            ],
            'ditolak' => [
                'class' => 'sakti-badge sakti-badge-ditolak',
                'bg' => '#B91C1C',
                'fg' => '#FFFFFF',
            ],
            default => [
                'class' => 'sakti-badge sakti-badge-menunggu',
                'bg' => '#9A3412',
                'fg' => '#FFFFFF',
            ],
        };
    }
}
