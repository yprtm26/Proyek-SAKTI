<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->dateTime('tanggal_disetujui_final')
                  ->nullable()
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->dateTime('tanggal_disetujui_final')
                  ->nullable(false)
                  ->change();
        });
    }
};