<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_personel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_pengajuan_id')
                ->constrained('surat_pengajuan')
                ->cascadeOnDelete();
            $table->string('nama');
            $table->string('peran');
            $table->string('instansi')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['surat_pengajuan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_personel');
    }
};
