<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $table = 'jenis_surat';

    protected $casts = [
        'alur' => 'array',
        'fields' => 'array',
        'ttd_qr_type' => 'string',
    ];

    protected $fillable = [
        'nama',
        'slug',
        'kode',
        'target_user',
        'scope_nomor',
        'fields',
        'alur',
        'aktif',
        'perlu_dekan',
        'ttd_qr_type',
    ];

    /**
     * Hanya template yang boleh dipakai untuk pengajuan.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Filter template berdasarkan role pengusul (mahasiswa/dosen).
     */
    public function scopeUntukPengguna($query, string $targetUser)
    {
        return $query->where('target_user', $targetUser);
    }

    /**
     * Pencarian nama template (opsional).
     */
    public function scopeCari($query, ?string $search)
    {
        return $query->when(
            filled($search),
            fn ($q) => $q->where('nama', 'like', '%' . $search . '%')
        );
    }

    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'jenis_surat_id');
    }
}
