<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * B-10 B: tabel relasi personel surat.
     *
     * Satu surat pengajuan bisa memiliki banyak personel (anggota kegiatan)
     * beserta peran dan instansi/NIM masing-masing.
     */
    public function up(): void
    {
        Schema::create('surat_personel', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_pengajuan_id')
                ->constrained('surat_pengajuan')
                ->cascadeOnDelete();

            $table->string('nama', 150);
            $table->string('peran', 100)->nullable();
            $table->string('instansi', 150)->nullable(); // instansi/unit kerja atau NIM/NIDN
            $table->unsignedSmallInteger('urutan')->default(0);

            $table->timestamps();

            $table->index(['surat_pengajuan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_personel');
    }
};
