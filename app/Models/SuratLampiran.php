<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratLampiran extends Model
{
    protected $table = 'surat_lampiran';

    protected $fillable = [
        'surat_pengajuan_id',
        'nama_file_asli',
        'path_file',
        'mime_type',
        'size',
    ];
    // RELASI: lampiran milik surat pengajuan
    public function surat()
    {
        return $this->belongsTo(SuratPengajuan::class, 'surat_pengajuan_id');
    }
}
