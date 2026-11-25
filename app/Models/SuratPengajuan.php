<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengajuan extends Model
{
    protected $table = 'surat_pengajuan';

    protected $fillable = [
        'user_id',
        'jenis_surat_id',
        'data',
        'status',
        'alasan_tolak',
        'catatan_revisi',
        'diverifikasi_tu_at',
        'nomor_surat',
        'tanggal_disetujui_final',
        'qr_token',
        'penandatangan',
    ];

    protected $casts = [
        'data' => 'array',
        'alasan_tolak' => 'string',
        'catatan_revisi' => 'string',
        'diverifikasi_tu_at' => 'datetime',
        'tanggal_disetujui_final' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(\App\Models\JenisSurat::class);
    }

    public function lampiran()
    {
    return $this->hasMany(SuratLampiran::class, 'surat_pengajuan_id');
    }

    public function parent()
    {
        return $this->belongsTo(SuratPengajuan::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SuratPengajuan::class, 'parent_id');
    }

    public function disposisis()
    {
        return $this->hasMany(Disposisi::class, 'surat_id');
    }

    /**
     * B-10 B: Daftar anggota kegiatan dan perannya yang terikat pada pengajuan surat.
     */
    public function personel()
    {
        return $this->hasMany(SuratPersonel::class, 'surat_pengajuan_id')->orderBy('urutan');
    }

    /**
     * B-13 A: Scope untuk menyaring surat yang BUKAN berstatus draf (antrean publik / verifikasi TU).
     */
    public function scopeBukanDraf($query)
    {
        return $query->where('status', '!=', 'draf');
    }

    /**
     * B-13 A: Scope untuk menyaring surat yang berstatus draf.
     */
    public function scopeDraf($query)
    {
        return $query->where('status', 'draf');
    }

    /**
     * B-13 A: Periksa apakah surat ini berstatus draf.
     */
    public function isDraf(): bool
    {
        return $this->status === 'draf';
    }

    /**
     * B-19: Scope antrean aktif Admin TU (mengecualikan draf, revisi pengusul, dan surat selesai).
     */
    public function scopeAntreanTuAktif($query)
    {
        return $query->whereIn('status', ['menunggu', 'diverifikasi_tu', 'menunggu_admin_final']);
    }

    /**
     * B-19: Pengurutan antrean secara kronologis FIFO (paling lama masuk di urutan teratas).
     */
    public function scopeUrutFifo($query, string $direction = 'asc')
    {
        $dir = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy('created_at', $dir)->orderBy('id', $dir);
    }
}
