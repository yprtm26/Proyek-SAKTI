<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->enum('target_user', ['mahasiswa', 'dosen'])
                  ->after('kode')
                  ->default('mahasiswa');

            $table->boolean('perlu_dekan')
                  ->after('fields')
                  ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->dropColumn(['target_user', 'perlu_dekan']);
        });
    }
};
