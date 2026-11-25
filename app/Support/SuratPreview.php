<?php

namespace App\Support;

use App\Models\SuratPengajuan;
use App\Models\User;

/**
 * B-28: Otorisasi dan resolusi template pratinjau dokumen surat.
 */
class SuratPreview
{
    /**
     * Cari surat dan pastikan pengguna berhak melihat pratinjaunya.
     * - 404 jika surat tidak ditemukan atau staf mencoba membuka draf pengusul.
     * - 403 jika pengusul mencoba membuka surat milik akun lain.
     */
    public static function authorizeAndFind(int|string $id, User $user): SuratPengajuan
    {
        $surat = SuratPengajuan::with(['user', 'jenisSurat', 'personel'])->find($id);

        if (! $surat) {
            abort(404, 'Dokumen surat tidak ditemukan.');
        }

        if (in_array($user->role, ['mahasiswa', 'dosen'], true)) {
            if ((int) $surat->user_id !== (int) $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pratinjau dokumen surat milik akun lain.');
            }

            return $surat;
        }

        if (in_array($user->role, ['admin', 'kaprodi', 'dekan'], true)) {
            if ($surat->status === 'draf') {
                abort(404, 'Surat masih berstatus draf dan belum diajukan.');
            }

            return $surat;
        }

        abort(403, 'Anda tidak memiliki hak akses ke pratinjau dokumen surat.');
    }

    /**
     * Tentukan nama Blade view resmi untuk pratinjau dokumen surat.
     */
    public static function resolveView(SuratPengajuan $surat): string
    {
        $slug = $surat->jenisSurat?->slug;

        if ($slug === 'surat-permohonan-magang') {
            $tipe = $surat->data['tipe_magang'] ?? null;
            if (is_string($tipe) && $tipe !== '' && view()->exists('preview.magang.' . $tipe)) {
                return 'preview.magang.' . $tipe;
            }
        }

        if ($slug && $surat->user?->role === 'dosen' && view()->exists('preview.dosen.' . $slug)) {
            return 'preview.dosen.' . $slug;
        }

        if ($slug && view()->exists('preview.' . $slug)) {
            return 'preview.' . $slug;
        }

        return 'preview._generic';
    }
}
