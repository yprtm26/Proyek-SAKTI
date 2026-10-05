<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->enum('scope_nomor', ['PRODI', 'FTI'])
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->enum('scope_nomor', ['PRODI', 'FTI'])
                ->default('PRODI')
                ->change();
        });
    }
};