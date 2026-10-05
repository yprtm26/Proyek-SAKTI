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
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');             // e.g. Surat Keterangan Aktif
            $table->string('slug')->unique();   // surat-keterangan-aktif
            $table->string('kode')->nullable(); // kode klasifikasi surat: SKet PP.30.02
            $table->json('alur');               // ["admin","kaprodi", "dekan"]
            $table->json('fields');             // ["semester","tahun_akademik"]
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat');
    }
};
