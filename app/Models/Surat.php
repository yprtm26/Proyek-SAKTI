<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    // Tambahkan ini supaya Eloquent pakai nama tabel yang benar
    protected $table = 'surat';

    protected $fillable = [
        'no_surat',
        'perihal',
        'file_path',
        'created_by',
        'jenis_surat_id',
        'tgl_surat',          // tanggal di surat
        'tgl_terima_surat',   // tanggal diterima
        'nama_pengirim',      // nama pengirim surat
        'jumlah_pembayaran',  // opsional
        'pilihan_kirim',
    ];

    protected $casts = [
        'tgl_surat' => 'datetime',
        'tgl_terima_surat' => 'datetime',
    ];

    // Relasi ke user (admin yang buat)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke jenis surat
    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    // Relasi ke disposisi
    public function disposisi()
    {
        return $this->hasMany(Disposisi::class, 'surat_id');
    }
}
