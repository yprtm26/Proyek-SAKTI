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
        'scope_nomor',
        'fields',
        'alur',
        'aktif',
        'perlu_dekan',
        'ttd_qr_type',
    ];
}
