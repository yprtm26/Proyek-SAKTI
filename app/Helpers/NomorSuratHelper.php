<?php

use App\Models\SuratPengajuan;

if (!function_exists('generateNomorSurat')) {
    function generateNomorSurat($surat)
    {
        // ===============================
        // 1. Surat tanpa kode = tidak bernomor
        // ===============================
        if (empty($surat->jenisSurat?->kode)) {
            return null;
        }

        $tahun        = now()->year;
        $bulanRomawi  = bulanRomawi(now()->month);
        $scope        = $surat->jenisSurat->scope_nomor; // boleh null

        // ===============================
        // 2. Ambil surat terakhir (counter)
        // ===============================
        $query = SuratPengajuan::query()
            ->whereNotNull('nomor_surat')
            ->whereYear('tanggal_disetujui_final', $tahun)
            ->where('jenis_surat_id', $surat->jenis_surat_id);

        // Scope PRODI → pisah per prodi (khusus mahasiswa)
        if (
            $scope === 'PRODI' &&
            $surat->jenisSurat->target_user === 'mahasiswa'
        ) {
            $query->whereHas('user', function ($q) use ($surat) {
                $q->where('prodi', $surat->user->prodi);
            });
        }

        $lastSurat = $query
            ->orderByDesc('tanggal_disetujui_final')
            ->first();

        // ===============================
        // 3. Ambil nomor terakhir
        // ===============================
        $lastNumber = 0;
        if ($lastSurat && preg_match('/(\d{4})/', $lastSurat->nomor_surat, $m)) {
            $lastNumber = (int) $m[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // ===============================
        // 4. Format nomor surat
        // ===============================
        // Fakultas / Dosen / Default
        if ($scope === 'FTI' || is_null($scope)) {
            return "{$nextNumber}/FTI/{$surat->jenisSurat->kode}/{$bulanRomawi}/{$tahun}";
        }

        // Mahasiswa per PRODI
        return "No.{$nextNumber}/{$surat->user->prodi}/{$surat->jenisSurat->kode}/{$bulanRomawi}/{$tahun}";
    }
}