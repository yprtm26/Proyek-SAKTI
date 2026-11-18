<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeJenis($query, ?string $jenis)
    {
        return $query->when(
            in_array($jenis, ['paruh', 'tingkat'], true),
            fn ($q) => $q->where('jenis', $jenis)
        );
    }
}
