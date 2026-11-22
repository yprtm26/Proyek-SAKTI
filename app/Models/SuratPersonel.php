<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function surat()
    {
        return $this->belongsTo(SuratPengajuan::class, 'surat_pengajuan_id');
    }
}
