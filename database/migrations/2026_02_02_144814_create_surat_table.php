<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();

            // relasi ke jenis surat
            $table->foreignId('jenis_surat_id')
                ->constrained('jenis_surat');

            $table->string('perihal')->nullable();
            $table->string('file_path');

            // tambahan field baru
            $table->date('tgl_surat')->nullable();       // tanggal di surat
            $table->date('tgl_terima_surat')->nullable(); // tanggal diterima
            $table->string('nama_pengirim')->nullable();
            $table->string('pilihan_kirim')->nullable();
            $table->decimal('jumlah_pembayaran', 15, 2)->nullable();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
