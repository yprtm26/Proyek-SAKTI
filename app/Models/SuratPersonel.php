<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPersonel extends Model
{
    protected $table = 'surat_personel';

    protected $fillable = [
        'surat_pengajuan_id',
        'nama',
        'peran',
        'instansi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /**
     * Surat pengajuan induk yang memiliki anggota kegiatan ini.
     */
    public function suratPengajuan(): BelongsTo
    {
        return $this->belongsTo(SuratPengajuan::class, 'surat_pengajuan_id');
    }
}
