<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    // Tambahkan ini supaya Eloquent tidak menambahkan 's' otomatis
    protected $table = 'disposisi';

    protected $fillable = [
        'surat_id',
        'from_user_id',
        'to_user_id',
        'catatan',
        'prioritas',
        'status',
        'file_tambahan',
    ];

    // Relasi ke surat
    public function surat()
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    // Relasi ke user penerima
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Relasi ke user pengirim (optional)
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
